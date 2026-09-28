<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GestionCamaController extends BaseController
{
    private $tiposEstablecimiento = [
        'RESIDENCIAL', 'HOSPITAL', 'CLÍNICA', 'CENTRO DE SALUD', 'OTRO',
    ];

    private $tiposGestion = [
        'PUBLICO', 'PRIVADO',
    ];

    private $regionesValidas = [
        'PUNA', 'QUEBRADA', 'VALLE', 'CENTRO', 'RAMAL I', 'RAMAL II',
    ];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector      = $this->request->getGet('efector')      ?? '';
        $filtro_region       = $this->request->getGet('region')       ?? '';
        $filtro_tipo_gestion = $this->request->getGet('tipo_gestion') ?? '';
        $filtro_estado       = $this->request->getGet('estado')       ?? '';

        if ($filtro_efector)      $model->where('gestion_cama.efector_id',   $filtro_efector);
        if ($filtro_region)       $model->where('gestion_cama.region',       $filtro_region);
        if ($filtro_tipo_gestion) $model->where('gestion_cama.tipo_gestion', $filtro_tipo_gestion);

        if ($filtro_estado != '') {
            $model->where('gestion_cama.estado', $filtro_estado);
        } else {
            $model->whereIn('gestion_cama.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_efector', 'filtro_region', 'filtro_tipo_gestion', 'filtro_estado');
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        // ── Auditoría ─────────────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'gestion_cama')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('GestionCamaModel')
            ->whereIn('gestion_cama.estado', ['activo', 'desactivado'])
            ->where('gestion_cama.created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('GestionCamaModel')
            ->whereIn('gestion_cama.estado', ['activo', 'desactivado'])
            ->where('gestion_cama.updated_at >', $ultimaVista)
            ->where('gestion_cama.updated_at != gestion_cama.created_at')
            ->countAllResults();

        // ── Listado ───────────────────────────────────────────────────────
        $model   = model('GestionCamaModel')->conRelaciones();
        $filtros = $this->aplicarFiltros($model);

        return view('gestion_cama/gestion_cama_list', array_merge($filtros, [
            'registros'    => $model->orderBy('efector.nombre', 'ASC')
                                    ->orderBy('gestion_cama.region', 'ASC')
                                    ->paginate($perPage),
            'pager'        => $model->pager,
            'efectores'    => $efectores,
            'regiones'     => $this->regionesValidas,
            'tiposGestion' => $this->tiposGestion,
            'nuevas'       => $nuevas,
            'modificadas'  => $modificadas,
            'ultimaVista'  => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user()->id;
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $registro   = $visitModel->where('user_id', $userId)
                                 ->where('modulo', 'gestion_cama')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'gestion_cama')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'gestion_cama',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('gestion_cama_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('gestion_cama/gestion_cama_form', [
            'efectores'            => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'regiones'             => $this->regionesValidas,
            'tiposEstablecimiento' => $this->tiposEstablecimiento,
            'tiposGestion'         => $this->tiposGestion,
        ]);
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        model('GestionCamaModel')->insert([
            'efector_id'                   => $this->request->getPost('efector_id'),
            'tipo_establecimiento'         => $this->request->getPost('tipo_establecimiento'),
            'tipo_gestion'                 => $this->request->getPost('tipo_gestion'),
            'region'                       => $this->request->getPost('region'),
            'cuidados_basicos_adultos'     => (int) $this->request->getPost('cuidados_basicos_adultos'),
            'cuidados_basicos_pediatricos' => (int) $this->request->getPost('cuidados_basicos_pediatricos'),
            'observacion'                  => $this->request->getPost('observacion') ?: null,
            'estado'                       => 'activo',
        ]);

        return redirect()->to(route_to('gestion_cama_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('GestionCamaModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('gestion_cama_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('gestion_cama/gestion_cama_form', [
            'registro'             => $registro,
            'efectores'            => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'regiones'             => $this->regionesValidas,
            'tiposEstablecimiento' => $this->tiposEstablecimiento,
            'tiposGestion'         => $this->tiposGestion,
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id = $this->request->getPost('gestion_cama_id');

        model('GestionCamaModel')->update($id, [
            'efector_id'                   => $this->request->getPost('efector_id'),
            'tipo_establecimiento'         => $this->request->getPost('tipo_establecimiento'),
            'tipo_gestion'                 => $this->request->getPost('tipo_gestion'),
            'region'                       => $this->request->getPost('region'),
            'cuidados_basicos_adultos'     => (int) $this->request->getPost('cuidados_basicos_adultos'),
            'cuidados_basicos_pediatricos' => (int) $this->request->getPost('cuidados_basicos_pediatricos'),
            'observacion'                  => $this->request->getPost('observacion') ?: null,
            'estado'                       => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('gestion_cama_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida(): bool
    {
        return $this->validate([
            'efector_id'                   => 'required|integer',
            'tipo_establecimiento'         => 'required',
            'tipo_gestion'                 => 'required',
            'region'                       => 'required',
            'cuidados_basicos_adultos'     => 'required|integer|greater_than_equal_to[0]',
            'cuidados_basicos_pediatricos' => 'required|integer|greater_than_equal_to[0]',
        ], [
            'efector_id'                   => ['required' => 'Debe seleccionar un efector.'],
            'tipo_establecimiento'         => ['required' => 'Debe seleccionar el tipo de establecimiento.'],
            'tipo_gestion'                 => ['required' => 'Debe seleccionar el tipo de gestión.'],
            'region'                       => ['required' => 'Debe seleccionar una región.'],
            'cuidados_basicos_adultos'     => [
                'required'              => 'Ingrese cuidados básicos adultos.',
                'integer'               => 'Debe ser un número entero.',
                'greater_than_equal_to' => 'No puede ser negativo.',
            ],
            'cuidados_basicos_pediatricos' => [
                'required'              => 'Ingrese cuidados básicos pediátricos.',
                'integer'               => 'Debe ser un número entero.',
                'greater_than_equal_to' => 'No puede ser negativo.',
            ],
        ]);
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('GestionCamaModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')
                           ->orderBy('gestion_cama.region', 'ASC')
                           ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gestión de Camas');

        $headers = [
            'A' => 'Efectores',
            'B' => 'Tipo Establecimiento',
            'C' => 'Región',
            'D' => 'Tipo Gestión',
            'E' => 'Cuidados Básicos - Adultos',
            'F' => 'Cuidados Básicos - Pediátricos',
            'G' => 'Total Cuidados Básicos',
            'H' => 'Observación',
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
            $row   = $i + 2;
            $total = (int) $r->cuidados_basicos_adultos + (int) $r->cuidados_basicos_pediatricos;

            $sheet->setCellValue('A' . $row, $r->efector_nombre);
            $sheet->setCellValue('B' . $row, $r->tipo_establecimiento);
            $sheet->setCellValue('C' . $row, $r->region);
            $sheet->setCellValue('D' . $row, $r->tipo_gestion);
            $sheet->setCellValue('E' . $row, (int) $r->cuidados_basicos_adultos);
            $sheet->setCellValue('F' . $row, (int) $r->cuidados_basicos_pediatricos);
            $sheet->setCellValue('G' . $row, $total);
            $sheet->setCellValue('H' . $row, $r->observacion ?? '');
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'gestion_cama_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('GestionCamaModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('gestion_cama_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}