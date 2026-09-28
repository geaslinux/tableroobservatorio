<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RendimientoHospitalarioUtiController extends BaseController
{
    // Ajustar estos valores si el equipo define otra nomenclatura de semestre
    private $semestresValidos = ['PRIMER SEMESTRE', 'SEGUNDO SEMESTRE'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector   = $this->request->getGet('efector_id') ?? '';
        $filtro_ejercicio = $this->request->getGet('ejercicio')  ?? '';
        $filtro_semestre  = $this->request->getGet('semestre')   ?? '';
        $filtro_region    = $this->request->getGet('region')     ?? '';

        if ($filtro_efector)   $model->where('rendimiento_hospitalario_uti.efector_id', $filtro_efector);
        if ($filtro_ejercicio) $model->where('rendimiento_hospitalario_uti.ejercicio', $filtro_ejercicio);
        if ($filtro_semestre)  $model->where('rendimiento_hospitalario_uti.semestre', $filtro_semestre);
        if ($filtro_region)    $model->where('efector.region', $filtro_region);

        return compact('filtro_efector', 'filtro_ejercicio', 'filtro_semestre', 'filtro_region');
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('RendimientoHospitalarioUtiModel');

        $efectores  = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones   = ['CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA'];
        $ejercicios = $model->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findColumn('ejercicio') ?? [];

        // ── KPI CARDS ──
        $totalAltas           = (int)   ($model->selectSum('altas')->first()->altas ?? 0);
        $totalEgresos          = (int)   ($model->selectSum('total_egresos')->first()->total_egresos ?? 0);
        $promedioOcupacional   = (float) ($model->selectAvg('porcentaje_ocupacional')->first()->porcentaje_ocupacional ?? 0);
        $promedioGiroCama      = (float) ($model->selectAvg('giro_cama')->first()->giro_cama ?? 0);

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

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'rendimiento_hospitalario_uti')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('rendimiento_hospitalario_uti/rendimiento_hospitalario_uti_list', array_merge($filtros, [
            'registros'            => $registros,
            'pager'                => $pager,
            'efectores'            => $efectores,
            'regiones'             => $regiones,
            'ejercicios'           => $ejercicios,
            'semestres'            => $this->semestresValidos,
            'totalAltas'           => $totalAltas,
            'totalEgresos'         => $totalEgresos,
            'promedioOcupacional'  => $promedioOcupacional,
            'promedioGiroCama'     => $promedioGiroCama,
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
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'rendimiento_hospitalario_uti')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'rendimiento_hospitalario_uti')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'rendimiento_hospitalario_uti', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('rendimiento_hospitalario_uti_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('rendimiento_hospitalario_uti/rendimiento_hospitalario_uti_form', [
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

        $model = model('RendimientoHospitalarioUtiModel');

        if ($model->existeRegistro($efectorId, $ejercicio, $semestre)) {
            return redirect()->back()->withInput()
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro cargado para ese efector, ejercicio y semestre.']);
        }

        $model->insert($this->datosDelPost());

        return redirect()->to(route_to('rendimiento_hospitalario_uti_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente.']);
    }

    // ─── Show/Edit ────────────────────────────────────────────────────────
    public function show($id)
    {
        $model                     = model('RendimientoHospitalarioUtiModel');
        $rendimientoHospitalarioUti = $model->find($id);

        if (!$rendimientoHospitalarioUti) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rendimientoHospitalarioUti->setCampoOculto();

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('rendimiento_hospitalario_uti/rendimiento_hospitalario_uti_form', [
            'rendimientoHospitalarioUti' => $rendimientoHospitalarioUti,
            'efectores'                  => $efectores,
            'semestres'                  => $this->semestresValidos,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update()
    {
        $id = $this->request->getPost('rendimiento_id');

        if (!$id) {
            return redirect()->to(route_to('rendimiento_hospitalario_uti_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('RendimientoHospitalarioUtiModel')->update($id, $this->datosDelPost());

        return redirect()->to(route_to('rendimiento_hospitalario_uti_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente.']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id'); // seguimos usando 'id' porque así está la ruta

        if (!$id) {
            return redirect()->to(route_to('rendimiento_hospitalario_uti_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('RendimientoHospitalarioUtiModel')->delete($id);

        return redirect()->to(route_to('rendimiento_hospitalario_uti_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Helper: arma el array de datos del form (create/update) ──────────
    private function datosDelPost(): array
    {
        return [
            'efector_id'             => $this->request->getPost('efector_id'),
            'ejercicio'              => intval($this->request->getPost('ejercicio')),
            'semestre'               => $this->request->getPost('semestre'),
            'dias_func_servicio'     => intval($this->request->getPost('dias_func_servicio')),
            'altas'                  => intval($this->request->getPost('altas')),
            'defuncion'              => intval($this->request->getPost('defuncion')),
            'total_egresos'          => intval($this->request->getPost('total_egresos')),
            'pases_a_sala'           => intval($this->request->getPost('pases_a_sala')),
            'dias_estada'            => intval($this->request->getPost('dias_estada')),
            'paciente_dia'           => intval($this->request->getPost('paciente_dia')),
            'cama_disponible'        => intval($this->request->getPost('cama_disponible')),
            'promedio_cama_disp'     => floatval($this->request->getPost('promedio_cama_disp')),
            'promedio_pcte_dia'      => floatval($this->request->getPost('promedio_pcte_dia')),
            'promedio_permanencia'   => floatval($this->request->getPost('promedio_permanencia')),
            'porcentaje_ocupacional' => floatval($this->request->getPost('porcentaje_ocupacional')),
            'estandares'             => $this->request->getPost('estandares'),
            'promedio_dias_estada'   => floatval($this->request->getPost('promedio_dias_estada')),
            'tasa_mortalidad'        => floatval($this->request->getPost('tasa_mortalidad')),
            'giro_cama'              => floatval($this->request->getPost('giro_cama')),
        ];
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('RendimientoHospitalarioUtiModel')->conEfector();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Efector',                  'B' => 'Ejercicio',
            'C' => 'Semestre',                 'D' => 'Días Func. Servicio',
            'E' => 'Altas',                    'F' => 'Defunción',
            'G' => 'Total Egresos',            'H' => 'Pases a Sala',
            'I' => 'Días Estada',              'J' => 'Paciente Día',
            'K' => 'Cama Disponible',          'L' => 'Prom. Cama Disp.',
            'M' => 'Prom. Pcte. Día',          'N' => 'Prom. Permanencia',
            'O' => '% Ocupacional',            'P' => 'Estándares',
            'Q' => 'Prom. Días Estada',        'R' => 'Tasa Mortalidad',
            'S' => 'Giro Cama',
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
            $sheet->setCellValue('A' . $row, $r->nombre);
            $sheet->setCellValue('B' . $row, $r->ejercicio);
            $sheet->setCellValue('C' . $row, $r->semestre);
            $sheet->setCellValue('D' . $row, $r->dias_func_servicio);
            $sheet->setCellValue('E' . $row, $r->altas);
            $sheet->setCellValue('F' . $row, $r->defuncion);
            $sheet->setCellValue('G' . $row, $r->total_egresos);
            $sheet->setCellValue('H' . $row, $r->pases_a_sala);
            $sheet->setCellValue('I' . $row, $r->dias_estada);
            $sheet->setCellValue('J' . $row, $r->paciente_dia);
            $sheet->setCellValue('K' . $row, $r->cama_disponible);
            $sheet->setCellValue('L' . $row, $r->promedio_cama_disp);
            $sheet->setCellValue('M' . $row, $r->promedio_pcte_dia);
            $sheet->setCellValue('N' . $row, $r->promedio_permanencia);
            $sheet->setCellValue('O' . $row, $r->porcentaje_ocupacional);
            $sheet->setCellValue('P' . $row, $r->estandares);
            $sheet->setCellValue('Q' . $row, $r->promedio_dias_estada);
            $sheet->setCellValue('R' . $row, $r->tasa_mortalidad);
            $sheet->setCellValue('S' . $row, $r->giro_cama);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'rendimiento_hospitalario_uti_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}