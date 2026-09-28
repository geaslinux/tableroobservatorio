<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RhMaternoController extends BaseController
{
    // Ajustar estos valores si el equipo define otra nomenclatura de semestre
    private $semestresValidos = ['PRIMER', 'SEGUNDO'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector   = $this->request->getGet('efector_id') ?? '';
        $filtro_ejercicio = $this->request->getGet('ejercicio')  ?? '';
        $filtro_semestre  = $this->request->getGet('semestre')   ?? '';
        $filtro_servicio  = $this->request->getGet('servicio')   ?? '';
        $filtro_region    = $this->request->getGet('region')     ?? '';

        if ($filtro_efector)   $model->where('rh_materno.efector_id', $filtro_efector);
        if ($filtro_ejercicio) $model->where('rh_materno.ejercicio', $filtro_ejercicio);
        if ($filtro_semestre)  $model->where('rh_materno.semestre', $filtro_semestre);
        if ($filtro_servicio)  $model->where('rh_materno.servicio', $filtro_servicio);
        if ($filtro_region)    $model->where('efector.region', $filtro_region);

        return compact('filtro_efector', 'filtro_ejercicio', 'filtro_semestre', 'filtro_servicio', 'filtro_region');
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('RhMaternoModel');

        $efectores  = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones   = ['CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA'];
        $servicios  = $model->select('servicio')->distinct()->orderBy('servicio', 'ASC')->findColumn('servicio') ?? [];
        $ejercicios = $model->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findColumn('ejercicio') ?? [];

        // ── KPI CARDS ──
        $totalIngresos         = (int)   ($model->selectSum('ingresos')->first()->ingresos ?? 0);
        $totalAltas            = (int)   ($model->selectSum('altas')->first()->altas ?? 0);
        $totalEgresos          = (int)   ($model->selectSum('total_egresos')->first()->total_egresos ?? 0);
        $promedioOcupacional   = (float) ($model->selectAvg('porcentaje_ocupacional')->first()->porcentaje_ocupacional ?? 0);
        $promedioGiroCama      = (float) ($model->selectAvg('giro_cama')->first()->giro_cama ?? 0);
        $promedioPermanencia   = (float) ($model->selectAvg('promedio_permanencia')->first()->promedio_permanencia ?? 0);

        // ── Filtros + listado (paginado estándar) ──────────────────────────
        $modelFiltrado = $model->conEfector();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        $registros = $modelFiltrado
            ->orderBy('efector.nombre', 'ASC')
            ->paginate($perPage);

        $pager = $model->pager;

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'rh_materno')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('rh_materno/rh_materno_list', array_merge($filtros, [
            'registros'            => $registros,
            'pager'                => $pager,
            'efectores'            => $efectores,
            'regiones'             => $regiones,
            'servicios'            => $servicios,
            'ejercicios'           => $ejercicios,
            'semestres'            => $this->semestresValidos,
            'totalIngresos'        => $totalIngresos,
            'totalAltas'           => $totalAltas,
            'totalEgresos'         => $totalEgresos,
            'promedioOcupacional'  => $promedioOcupacional,
            'promedioGiroCama'     => $promedioGiroCama,
            'promedioPermanencia'  => $promedioPermanencia,
            'ultimaVista'          => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'rh_materno')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'rh_materno')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'rh_materno', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('rh_materno_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('rh_materno/rh_materno_form', [
            'efectores' => $efectores,
            'semestres' => $this->semestresValidos,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $efectorId = $this->request->getPost('efector_id');
        $ejercicio = $this->request->getPost('ejercicio');
        $semestre  = $this->request->getPost('semestre');
        $servicio  = $this->request->getPost('servicio');
        $sector    = $this->request->getPost('sector');

        $model = model('RhMaternoModel');

        if ($model->existeRegistro($efectorId, $ejercicio, $semestre, $servicio, $sector)) {
            return redirect()->back()->withInput()
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro cargado para ese efector, ejercicio, semestre, servicio y sector.']);
        }

        $model->insert($this->datosDelPost());

        return redirect()->to(route_to('rh_materno_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente.']);
    }

    // ─── Show/Edit ────────────────────────────────────────────────────────
    public function show($id)
    {
        $model      = model('RhMaternoModel');
        $rhMaterno  = $model->find($id);

        if (!$rhMaterno) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rhMaterno->setCampoOculto();

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('rh_materno/rh_materno_form', [
            'rhMaterno' => $rhMaterno,
            'efectores' => $efectores,
            'semestres' => $this->semestresValidos,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update()
    {
        $id = $this->request->getPost('rh_materno_id');

        if (!$id) {
            return redirect()->to(route_to('rh_materno_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('RhMaternoModel')->update($id, $this->datosDelPost());

        return redirect()->to(route_to('rh_materno_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente.']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id'); // seguimos usando 'id' porque así está la ruta

        if (!$id) {
            return redirect()->to(route_to('rh_materno_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('RhMaternoModel')->delete($id);

        return redirect()->to(route_to('rh_materno_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Helper: arma el array de datos del form (create/update) ──────────
    // Los "promedio_*" y "%_ocupacional" vienen calculados desde la planilla
    // origen, se cargan tal cual (igual que en RendimientoHospitalarioUti).
    private function datosDelPost(): array
    {
        return [
            'efector_id'                    => $this->request->getPost('efector_id'),
            'ejercicio'                     => intval($this->request->getPost('ejercicio')),
            'semestre'                      => $this->request->getPost('semestre'),
            'servicio'                      => $this->request->getPost('servicio'),
            'sector'                        => $this->request->getPost('sector'),
            'dias_funcionamiento_servicio'  => intval($this->request->getPost('dias_funcionamiento_servicio')),
            'ingresos'                      => intval($this->request->getPost('ingresos')),
            'pases_de'                      => intval($this->request->getPost('pases_de')),
            'altas'                         => intval($this->request->getPost('altas')),
            'defuncion'                     => intval($this->request->getPost('defuncion')),
            'total_egresos'                 => intval($this->request->getPost('total_egresos')),
            'pases_a'                       => intval($this->request->getPost('pases_a')),
            'paciente_dia'                  => intval($this->request->getPost('paciente_dia')),
            'cama_disponible'               => intval($this->request->getPost('cama_disponible')),
            'dias_estada'                   => intval($this->request->getPost('dias_estada')),
            'promedio_cama_disponible'      => floatval($this->request->getPost('promedio_cama_disponible')),
            'promedio_paciente_dia'         => floatval($this->request->getPost('promedio_paciente_dia')),
            'promedio_dias_estada'          => floatval($this->request->getPost('promedio_dias_estada')),
            'promedio_permanencia'          => floatval($this->request->getPost('promedio_permanencia')),
            'porcentaje_ocupacional'        => floatval($this->request->getPost('porcentaje_ocupacional')),
            'estandares'                    => $this->request->getPost('estandares'),
            'tasa_mortalidad'               => floatval($this->request->getPost('tasa_mortalidad')),
            'giro_cama'                     => floatval($this->request->getPost('giro_cama')),
            'giro_sustitucion'              => floatval($this->request->getPost('giro_sustitucion')),
            'egresos_por_dia'               => floatval($this->request->getPost('egresos_por_dia')),
        ];
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('RhMaternoModel')->conEfector();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Efector',                  'B' => 'Ejercicio',
            'C' => 'Semestre',                 'D' => 'Servicio',
            'E' => 'Sector',                   'F' => 'Días Func. Servicio',
            'G' => 'Ingresos',                 'H' => 'Pases De',
            'I' => 'Altas',                    'J' => 'Defunción',
            'K' => 'Total Egresos',            'L' => 'Pases A',
            'M' => 'Paciente Día',             'N' => 'Cama Disponible',
            'O' => 'Días Estada',              'P' => 'Prom. Cama Disp.',
            'Q' => 'Prom. Pcte. Día',          'R' => 'Prom. Días Estada',
            'S' => 'Prom. Permanencia',        'T' => '% Ocupacional',
            'U' => 'Estándares',               'V' => 'Tasa Mortalidad',
            'W' => 'Giro Cama',                'X' => 'Giro Sustitución',
            'Y' => 'Egresos por Día',
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
            $sheet->setCellValue('A' . $row, $r->efector_nombre);
            $sheet->setCellValue('B' . $row, $r->ejercicio);
            $sheet->setCellValue('C' . $row, $r->semestre);
            $sheet->setCellValue('D' . $row, $r->servicio);
            $sheet->setCellValue('E' . $row, $r->sector);
            $sheet->setCellValue('F' . $row, $r->dias_funcionamiento_servicio);
            $sheet->setCellValue('G' . $row, $r->ingresos);
            $sheet->setCellValue('H' . $row, $r->pases_de);
            $sheet->setCellValue('I' . $row, $r->altas);
            $sheet->setCellValue('J' . $row, $r->defuncion);
            $sheet->setCellValue('K' . $row, $r->total_egresos);
            $sheet->setCellValue('L' . $row, $r->pases_a);
            $sheet->setCellValue('M' . $row, $r->paciente_dia);
            $sheet->setCellValue('N' . $row, $r->cama_disponible);
            $sheet->setCellValue('O' . $row, $r->dias_estada);
            $sheet->setCellValue('P' . $row, $r->promedio_cama_disponible);
            $sheet->setCellValue('Q' . $row, $r->promedio_paciente_dia);
            $sheet->setCellValue('R' . $row, $r->promedio_dias_estada);
            $sheet->setCellValue('S' . $row, $r->promedio_permanencia);
            $sheet->setCellValue('T' . $row, $r->porcentaje_ocupacional);
            $sheet->setCellValue('U' . $row, $r->estandares);
            $sheet->setCellValue('V' . $row, $r->tasa_mortalidad);
            $sheet->setCellValue('W' . $row, $r->giro_cama);
            $sheet->setCellValue('X' . $row, $r->giro_sustitucion);
            $sheet->setCellValue('Y' . $row, $r->egresos_por_dia);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'rh_materno_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}