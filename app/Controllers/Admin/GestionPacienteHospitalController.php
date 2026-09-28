<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GestionPacienteHospitalController extends BaseController
{
    // ── Catálogos ─────────────────────────────────────────────────────────
    private $zonasValidas = [
        'CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA',
    ];

    private $cuidadosValidos = [
        'AGUDOS', 'BÁSICOS', 'MEDIOS', 'INTENSIVOS',
    ];

    private $nivelesRiesgo = [
        'AGUDO LEVE',
        'AGUDO GRAVE',
        'SUB AGUDO MODERADO',
        'TRASTORNOS MENTALES SEVEROS',
    ];

    private $procesosValidos = [
        'OBSERVACIÓN',
        'INTERNACIONES BREVES',
        'INTERNACIONES ESPECIALIZADAS',
        'EXTERNACIÓN ASISTIDA',
    ];

    private $tiposCama = [
        'DE OBSERVACIÓN',
        'DE CUIDADOS BÁSICOS',
        'DE CUIDADOS INTERMEDIOS',
        'DE CUIDADOS INTENSIVOS',
    ];

    // ── Filtros reutilizables ─────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector      = $this->request->getGet('efector')      ?? '';
        $filtro_zona         = $this->request->getGet('zona')         ?? '';
        $filtro_cuidados     = $this->request->getGet('cuidados')     ?? '';
        $filtro_nivel_riesgo = $this->request->getGet('nivel_riesgo') ?? '';
        $filtro_estado       = $this->request->getGet('estado')       ?? '';

        if ($filtro_efector)      $model->where('gestion_cama_hospitales.efector_id', $filtro_efector);
        if ($filtro_zona)         $model->where('gestion_cama_hospitales.zona',        $filtro_zona);
        if ($filtro_cuidados)     $model->where('gestion_cama_hospitales.cuidados',    $filtro_cuidados);
        if ($filtro_nivel_riesgo) $model->where('gestion_cama_hospitales.nivel_riesgo', $filtro_nivel_riesgo);

        if ($filtro_estado != '') {
            $model->where('gestion_cama_hospitales.estado', $filtro_estado);
        } else {
            $model->whereIn('gestion_cama_hospitales.estado', ['activo', 'desactivado']);
        }

        return compact(
            'filtro_efector', 'filtro_zona', 'filtro_cuidados',
            'filtro_nivel_riesgo', 'filtro_estado'
        );
    }

    // ── Index ─────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $efectores    = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $departamentos = model('DepartamentoModel')->orderBy('nombre', 'ASC')->findAll();

        // ── Auditoría ──────────────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'gestion_paciente_hospital')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('GestionPacienteHospitalModel')
            ->whereIn('gestion_cama_hospitales.estado', ['activo', 'desactivado'])
            ->where('gestion_cama_hospitales.created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('GestionPacienteHospitalModel')
            ->whereIn('gestion_cama_hospitales.estado', ['activo', 'desactivado'])
            ->where('gestion_cama_hospitales.updated_at >', $ultimaVista)
            ->where('gestion_cama_hospitales.updated_at != gestion_cama_hospitales.created_at')
            ->countAllResults();

        // ── Listado ────────────────────────────────────────────────────────
        $model   = model('GestionPacienteHospitalModel')->conRelaciones();
        $filtros = $this->aplicarFiltros($model);

        return view('gestion_paciente_hospital/gestion_paciente_hospital_list', array_merge($filtros, [
            'registros'     => $model->orderBy('efector.nombre', 'ASC')
                                     ->orderBy('gestion_cama_hospitales.zona', 'ASC')
                                     ->paginate($perPage),
            'pager'         => $model->pager,
            'efectores'     => $efectores,
            'departamentos' => $departamentos,
            'zonas'         => $this->zonasValidas,
            'cuidados'      => $this->cuidadosValidos,
            'nivelesRiesgo' => $this->nivelesRiesgo,
            'procesos'      => $this->procesosValidos,
            'tiposCama'     => $this->tiposCama,
            'nuevas'        => $nuevas,
            'modificadas'   => $modificadas,
            'ultimaVista'   => $ultimaVista,
        ]));
    }

    // ── Marcar visto ──────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user()->id;
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $registro   = $visitModel->where('user_id', $userId)
                                 ->where('modulo', 'gestion_paciente_hospital')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'gestion_paciente_hospital')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'gestion_paciente_hospital',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('gestion_paciente_hospital_list'));
    }

    // ── Formulario nuevo ──────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('gestion_paciente_hospital/gestion_paciente_hospital_form', [
            'efectores'     => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'departamentos' => model('DepartamentoModel')->orderBy('nombre', 'ASC')->findAll(),
            'zonas'         => $this->zonasValidas,
            'cuidados'      => $this->cuidadosValidos,
            'nivelesRiesgo' => $this->nivelesRiesgo,
            'procesos'      => $this->procesosValidos,
            'tiposCama'     => $this->tiposCama,
        ]);
    }

    // ── Store ─────────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        model('GestionPacienteHospitalModel')->insert([
            'efector_id'      => $this->request->getPost('efector_id'),
            'departamento_id' => $this->request->getPost('departamento_id'),
            'zona'            => $this->request->getPost('zona'),
            'cuidados'        => $this->request->getPost('cuidados'),
            'nivel_riesgo'    => $this->request->getPost('nivel_riesgo'),
            'proceso'         => $this->request->getPost('proceso'),
            'tipo_cama'       => $this->request->getPost('tipo_cama'),
            'tiempo_estancia' => $this->request->getPost('tiempo_estancia'),
            'observacion'     => $this->request->getPost('observacion') ?: null,
            'estado'          => 'activo',
        ]);

        return redirect()->to(route_to('gestion_paciente_hospital_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ── Show / Edit ───────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('GestionPacienteHospitalModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('gestion_paciente_hospital_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('gestion_paciente_hospital/gestion_paciente_hospital_form', [
            'registro'      => $registro,
            'efectores'     => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'departamentos' => model('DepartamentoModel')->orderBy('nombre', 'ASC')->findAll(),
            'zonas'         => $this->zonasValidas,
            'cuidados'      => $this->cuidadosValidos,
            'nivelesRiesgo' => $this->nivelesRiesgo,
            'procesos'      => $this->procesosValidos,
            'tiposCama'     => $this->tiposCama,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────────
    public function update()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id = $this->request->getPost('gestion_cama_hospitales_id');

        model('GestionPacienteHospitalModel')->update($id, [
            'efector_id'      => $this->request->getPost('efector_id'),
            'departamento_id' => $this->request->getPost('departamento_id'),
            'zona'            => $this->request->getPost('zona'),
            'cuidados'        => $this->request->getPost('cuidados'),
            'nivel_riesgo'    => $this->request->getPost('nivel_riesgo'),
            'proceso'         => $this->request->getPost('proceso'),
            'tipo_cama'       => $this->request->getPost('tipo_cama'),
            'tiempo_estancia' => $this->request->getPost('tiempo_estancia'),
            'observacion'     => $this->request->getPost('observacion') ?: null,
            'estado'          => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('gestion_paciente_hospital_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ── Validación ────────────────────────────────────────────────────────
    private function valida(): bool
    {
        return $this->validate([
            'efector_id'      => 'required|integer',
            'departamento_id' => 'required|integer',
            'zona'            => 'required',
            'cuidados'        => 'required',
            'nivel_riesgo'    => 'required',
            'proceso'         => 'required',
            'tipo_cama'       => 'required',
            'tiempo_estancia' => 'required|max_length[50]',
        ], [
            'efector_id'      => ['required' => 'Debe seleccionar un efector.'],
            'departamento_id' => ['required' => 'Debe seleccionar un departamento.'],
            'zona'            => ['required' => 'Debe seleccionar una zona.'],
            'cuidados'        => ['required' => 'Debe seleccionar el tipo de cuidados.'],
            'nivel_riesgo'    => ['required' => 'Debe seleccionar el nivel de riesgo.'],
            'proceso'         => ['required' => 'Debe seleccionar el proceso.'],
            'tipo_cama'       => ['required' => 'Debe seleccionar el tipo de cama.'],
            'tiempo_estancia' => [
                'required'   => 'Ingrese el tiempo de estancia.',
                'max_length' => 'Máximo 50 caracteres.',
            ],
        ]);
    }

    // ── Exportar Excel ────────────────────────────────────────────────────
    public function export()
    {
        $model = model('GestionPacienteHospitalModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')
                           ->orderBy('gestion_cama_hospitales.zona', 'ASC')
                           ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gestión Paciente Hospital');

        $headers = [
            'A' => 'Hospital',
            'B' => 'Nivel de Complejidad',
            'C' => 'Departamento',
            'D' => 'Zona',
            'E' => 'Cuidados',
            'F' => 'Niveles de Riesgo',
            'G' => 'Proceso',
            'H' => 'Tipo de Camas',
            'I' => 'Tiempo de Estancia',
            'J' => 'Observación',
            'K' => 'Estado',
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
            $sheet->setCellValue('B' . $row, $r->efector_nivel);
            $sheet->setCellValue('C' . $row, $r->departamento_nombre);
            $sheet->setCellValue('D' . $row, $r->zona);
            $sheet->setCellValue('E' . $row, $r->cuidados);
            $sheet->setCellValue('F' . $row, $r->nivel_riesgo);
            $sheet->setCellValue('G' . $row, $r->proceso);
            $sheet->setCellValue('H' . $row, $r->tipo_cama);
            $sheet->setCellValue('I' . $row, $r->tiempo_estancia);
            $sheet->setCellValue('J' . $row, $r->observacion ?? '');
            $sheet->setCellValue('K' . $row, $r->estado);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'gestion_paciente_hospital_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ── Destroy ───────────────────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('GestionPacienteHospitalModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('gestion_paciente_hospital_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}