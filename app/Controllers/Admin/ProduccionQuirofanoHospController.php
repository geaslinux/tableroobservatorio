<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProduccionQuirofanoHospController extends BaseController
{
    // Campos numéricos que se guardan tal cual vienen del form (sin recalcular
    // en el servidor: la fuente de datos original tiene excepciones manuales
    // documentadas en 'observacion', asi que no forzamos fórmulas fijas).
    private $camposNumericos = [
        'quirofanos_disponibles',
        'quirofanos_urgencias',
        'quirofanos_programadas',
        'cirugias_urgencia',
        'cirugias_prog_alta',
        'cirugias_prog_mediana',
        'cirugias_prog_baja',
        'cirugias_prog_desconocido',
        'total_cirugias_programadas',
        'sub_total',
    ];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio')  ?? '';
        $filtro_efector    = $this->request->getGet('efector_id') ?? '';

        if ($filtro_ejercicio) $model->where('produccion_quirofano_hosp.ejercicio', $filtro_ejercicio);
        if ($filtro_efector)   $model->where('produccion_quirofano_hosp.efector_id', $filtro_efector);

        return compact('filtro_ejercicio', 'filtro_efector');
    }

    // ─── Arma el payload numérico + porcentaje + observacion desde el POST
    private function armarDatosPost(): array
    {
        $datos = [
            'efector_id'  => $this->request->getPost('efector_id'),
            'ejercicio'   => $this->request->getPost('ejercicio'),
            'observacion' => $this->request->getPost('observacion') ?: null,
        ];

        foreach ($this->camposNumericos as $campo) {
            $valor = $this->request->getPost($campo);
            $datos[$campo] = ($valor === null || $valor === '') ? null : intval($valor);
        }

        $porcentaje = $this->request->getPost('porcentaje_provincia');
        $datos['porcentaje_provincia'] = ($porcentaje === null || $porcentaje === '') ? null : floatval($porcentaje);

        return $datos;
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('ProduccionQuirofanoHospModel');

        // ── Listas para selects ─────────────────────────
        $ejercicios = array_column(
            $model->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        // ── KPI CARDS ──
        $totalUrgencia    = (int) ($model->selectSum('cirugias_urgencia')->first()->cirugias_urgencia ?? 0);
        $totalProgramadas = (int) ($model->selectSum('total_cirugias_programadas')->first()->total_cirugias_programadas ?? 0);
        $totalGeneral     = (int) ($model->selectSum('sub_total')->first()->sub_total ?? 0);

        // ── Filtros + listado ──────────────────────────────
        $modelFiltrado = $model->withNombres();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        // OJO: usamos route_to() en vez de escribir el path a mano.
        // Si la ruta 'quirofano' vive dentro de un group() con prefijo,
        // setPath('quirofano') genera links rotos (404). route_to() siempre
        // devuelve el path real de la ruta registrada.
        $registros = $modelFiltrado
            ->orderBy('produccion_quirofano_hosp.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->paginate($perPage);

        $pager = $model->pager;
        $pager->setPath(route_to('quirofano_list'));

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'quirofano')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('quirofano/quirofano_list', array_merge($filtros, [
            'registros'        => $registros,
            'pager'            => $pager,
            'ejercicios'       => $ejercicios,
            'efectores'        => $efectores,
            'totalUrgencia'    => $totalUrgencia,
            'totalProgramadas' => $totalProgramadas,
            'totalGeneral'     => $totalGeneral,
            'ultimaVista'      => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'quirofano')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'quirofano')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'quirofano', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('quirofano_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('quirofano/quirofano_form', [
            'efectores' => $efectores,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $model = model('ProduccionQuirofanoHospModel');

        $efectorId = $this->request->getPost('efector_id');
        $ejercicio = $this->request->getPost('ejercicio');

        if (empty($efectorId) || empty($ejercicio)) {
            return redirect()->back()->withInput()
                ->with('msg', ['type' => 'danger', 'body' => 'Efector y Ejercicio son obligatorios']);
        }

        $existe = $model
            ->where('efector_id', $efectorId)
            ->where('ejercicio', $ejercicio)
            ->first();

        if ($existe) {
            return redirect()->back()->withInput()
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro para ese efector en ese ejercicio']);
        }

        $model->insert($this->armarDatosPost());

        return redirect()->to(route_to('quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show/Edit de un registro ───────────────────────────────────────────
    public function show($id)
    {
        $model    = model('ProduccionQuirofanoHospModel');
        $registro = $model->find($id);

        if (!$registro) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $efectorActual = model('EfectorModel')->find($registro->efector_id);

        return view('quirofano/quirofano_form', [
            'quirofanoRegistro' => true,
            'registro'          => $registro,
            'efectorActual'     => $efectorActual,
            'formRoute'         => 'quirofano_update',
        ]);
    }

    // ─── Update de un registro ──────────────────────────────────────────────
    public function update($id = null)
    {
        $id    = $id ?? $this->request->getPost('produccion_id');
        $model = model('ProduccionQuirofanoHospModel');

        $registro = $model->find($id);

        if (!$registro) {
            return redirect()->to(route_to('quirofano_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $model->update($id, $this->armarDatosPost());

        return redirect()->to(route_to('quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Destroy de un registro ──────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id');

        if (!$id) {
            return redirect()->to(route_to('quirofano_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $model = model('ProduccionQuirofanoHospModel');
        $model->delete($id);

        return redirect()->to(route_to('quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('ProduccionQuirofanoHospModel')->withNombres();
        $this->aplicarFiltros($model);

        $registros = $model
            ->orderBy('produccion_quirofano_hosp.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Efector',
            'C' => 'Quirófanos Disponibles',
            'D' => 'Quirófanos Urgencias',
            'E' => 'Quirófanos Programadas',
            'F' => 'Cirugías Urgencia',
            'G' => 'Cirugías Prog. Alta',
            'H' => 'Cirugías Prog. Mediana',
            'I' => 'Cirugías Prog. Baja',
            'J' => 'Cirugías Prog. Desconocido',
            'K' => 'Total Cirugías Programadas',
            'L' => 'Sub Total',
            'M' => '% Provincia',
            'N' => 'Observación',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($registros as $i => $r) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->efector_nombre);
            $sheet->setCellValue('C' . $row, $r->quirofanos_disponibles);
            $sheet->setCellValue('D' . $row, $r->quirofanos_urgencias);
            $sheet->setCellValue('E' . $row, $r->quirofanos_programadas);
            $sheet->setCellValue('F' . $row, $r->cirugias_urgencia);
            $sheet->setCellValue('G' . $row, $r->cirugias_prog_alta);
            $sheet->setCellValue('H' . $row, $r->cirugias_prog_mediana);
            $sheet->setCellValue('I' . $row, $r->cirugias_prog_baja);
            $sheet->setCellValue('J' . $row, $r->cirugias_prog_desconocido);
            $sheet->setCellValue('K' . $row, $r->total_cirugias_programadas);
            $sheet->setCellValue('L' . $row, $r->sub_total);
            $sheet->setCellValue('M' . $row, $r->porcentaje_provincia);
            $sheet->setCellValue('N' . $row, $r->observacion);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'produccion_quirofano_hosp_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}