<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ListaEsperaController extends BaseController
{
    // Campos numéricos que se guardan tal cual vienen del form (sin recalcular
    // en el servidor: la fuente de datos original tiene excepciones manuales,
    // asi que no forzamos fórmulas fijas).
    private $camposNumericos = [
        'cantidad_pacientes',
        'comp_quirurgica_alta',
        'comp_quirurgica_mediana',
        'comp_quirurgica_baja',
    ];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio')  ?? '';
        $filtro_efector    = $this->request->getGet('efector_id') ?? '';

        if ($filtro_ejercicio) $model->where('lista_espera.ejercicio', $filtro_ejercicio);
        if ($filtro_efector)   $model->where('lista_espera.efector_id', $filtro_efector);

        return compact('filtro_ejercicio', 'filtro_efector');
    }

    // ─── Arma el payload numérico desde el POST
    private function armarDatosPost(): array
    {
        $datos = [
            'efector_id'      => $this->request->getPost('efector_id'),
            'ejercicio'       => $this->request->getPost('ejercicio'),
            'especialidad_id' => $this->request->getPost('especialidad_id') ?: null,
        ];

        foreach ($this->camposNumericos as $campo) {
            $valor = $this->request->getPost($campo);
            $datos[$campo] = ($valor === null || $valor === '') ? null : intval($valor);
        }

        return $datos;
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('ListaEsperaModel');

        // ── Listas para selects ─────────────────────────
        $ejercicios = array_column(
            $model->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores     = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $especialidades = model('EspecialidadModel')->orderBy('nombre', 'ASC')->findAll();

        // ── KPI CARDS ──
        $totalPacientes = (int) ($model->selectSum('cantidad_pacientes')->first()->cantidad_pacientes ?? 0);
        $totalAlta      = (int) ($model->selectSum('comp_quirurgica_alta')->first()->comp_quirurgica_alta ?? 0);
        $totalMediana   = (int) ($model->selectSum('comp_quirurgica_mediana')->first()->comp_quirurgica_mediana ?? 0);
        $totalBaja      = (int) ($model->selectSum('comp_quirurgica_baja')->first()->comp_quirurgica_baja ?? 0);

        // ── Filtros + listado ──────────────────────────────
        $modelFiltrado = $model->withNombres();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        // OJO: usamos route_to() en vez de escribir el path a mano.
        // Si la ruta 'lista-espera' vive dentro de un group() con prefijo,
        // setPath('lista-espera') genera links rotos (404). route_to() siempre
        // devuelve el path real de la ruta registrada.
        $registros = $modelFiltrado
            ->orderBy('lista_espera.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->paginate($perPage);

        $pager = $model->pager;
        $pager->setPath(route_to('lista_espera_list'));

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'lista_espera')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('lista_espera/lista_espera_list', array_merge($filtros, [
            'registros'       => $registros,
            'pager'           => $pager,
            'ejercicios'      => $ejercicios,
            'efectores'       => $efectores,
            'especialidades'  => $especialidades,
            'totalPacientes'  => $totalPacientes,
            'totalAlta'       => $totalAlta,
            'totalMediana'    => $totalMediana,
            'totalBaja'       => $totalBaja,
            'ultimaVista'     => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'lista_espera')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'lista_espera')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'lista_espera', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('lista_espera_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores      = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $especialidades = model('EspecialidadModel')->orderBy('nombre', 'ASC')->findAll();

        return view('lista_espera/lista_espera_form', [
            'efectores'      => $efectores,
            'especialidades' => $especialidades,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $model = model('ListaEsperaModel');

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

        return redirect()->to(route_to('lista_espera_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show/Edit de un registro ───────────────────────────────────────────
    public function show($id)
    {
        $model    = model('ListaEsperaModel');
        $registro = $model->find($id);

        if (!$registro) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $efectorActual      = model('EfectorModel')->find($registro->efector_id);
        $especialidades     = model('EspecialidadModel')->orderBy('nombre', 'ASC')->findAll();

        return view('lista_espera/lista_espera_form', [
            'listaEsperaRegistro' => true,
            'registro'            => $registro,
            'efectorActual'       => $efectorActual,
            'especialidades'      => $especialidades,
            'formRoute'           => 'lista_espera_update',
        ]);
    }

    // ─── Update de un registro ──────────────────────────────────────────────
    public function update($id = null)
    {
        $id    = $id ?? $this->request->getPost('lista_espera_id');
        $model = model('ListaEsperaModel');

        $registro = $model->find($id);

        if (!$registro) {
            return redirect()->to(route_to('lista_espera_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $model->update($id, $this->armarDatosPost());

        return redirect()->to(route_to('lista_espera_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Destroy de un registro ──────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id');

        if (!$id) {
            return redirect()->to(route_to('lista_espera_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $model = model('ListaEsperaModel');
        $model->delete($id);

        return redirect()->to(route_to('lista_espera_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('ListaEsperaModel')->withNombres();
        $this->aplicarFiltros($model);

        $registros = $model
            ->orderBy('lista_espera.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Efector',
            'C' => 'Especialidad',
            'D' => 'Cantidad de Pacientes',
            'E' => 'Comp. Quirúrgica Alta',
            'F' => 'Comp. Quirúrgica Mediana',
            'G' => 'Comp. Quirúrgica Baja',
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
            $sheet->setCellValue('C' . $row, $r->especialidad_nombre);
            $sheet->setCellValue('D' . $row, $r->cantidad_pacientes);
            $sheet->setCellValue('E' . $row, $r->comp_quirurgica_alta);
            $sheet->setCellValue('F' . $row, $r->comp_quirurgica_mediana);
            $sheet->setCellValue('G' . $row, $r->comp_quirurgica_baja);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'lista_espera_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
    // ─── Guardar especialidad (modal simple desde el listado) ─────────────
public function storeEspecialidad()
{
    $model  = model('EspecialidadModel');
    $nombre = trim($this->request->getPost('nombre'));

    if (empty($nombre)) {
        return redirect()->to(route_to('lista_espera_list'))
            ->with('msg', ['type' => 'danger', 'body' => 'El nombre de la especialidad es obligatorio']);
    }

    $existe = $model->where('nombre', $nombre)->first();
    if ($existe) {
        return redirect()->to(route_to('lista_espera_list'))
            ->with('msg', ['type' => 'danger', 'body' => 'Ya existe una especialidad con ese nombre']);
    }

    $model->insert(['nombre' => $nombre]);

    return redirect()->to(route_to('lista_espera_list'))
        ->with('msg', ['type' => 'success', 'body' => 'Especialidad guardada correctamente']);
}
}