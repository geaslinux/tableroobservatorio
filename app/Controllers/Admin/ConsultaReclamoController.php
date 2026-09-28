<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ConsultaReclamoController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    private $tiposLlamado = ['RECLAMO', 'CONSULTA', 'SUGERENCIA', 'FELICITACION'];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio  = $this->request->getGet('ejercicio')    ?? '';
        $filtro_mes        = $this->request->getGet('mes')          ?? '';
        $filtro_tipo       = $this->request->getGet('tipo_llamado') ?? '';
        $filtro_categoria  = $this->request->getGet('categoria')    ?? '';
        $filtro_estado     = $this->request->getGet('estado')       ?? '';

        if (strtolower(trim((string) $filtro_mes)) === 'todos') {
            $filtro_mes = '';
        }

        if ($filtro_ejercicio) $model->where('consulta_reclamo.ejercicio',    $filtro_ejercicio);
        if ($filtro_mes)       $model->where('consulta_reclamo.mes',          $filtro_mes);
        if ($filtro_tipo)      $model->where('consulta_reclamo.tipo_llamado', $filtro_tipo);
        if ($filtro_categoria) $model->where('consulta_reclamo.categoria_id', $filtro_categoria);

        if ($filtro_estado != '') {
            $model->where('consulta_reclamo.estado', $filtro_estado);
        } else {
            $model->whereIn('consulta_reclamo.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_tipo', 'filtro_categoria', 'filtro_estado');
    }

    // ─── Index ───────────────────────────────────────────────────────────
  // ─── Index (estadísticas, sin CRUD) ─────────────────────────────────
    public function index()
    {
        helper('auth');

        $ejercicios = array_column(
            model('ConsultaReclamoModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $categorias = model('CategoriaModel')->orderBy('nombre', 'ASC')->findAll();

        // Instancia limpia solo para capturar los valores de filtro (no se usa para consultar)
        $modelFiltros = new \App\Models\ConsultaReclamoModel();
        $filtros      = $this->aplicarFiltros($modelFiltros);

        // ── KPIs por tipo de llamado ──
        $totalesPorTipo = [];
        foreach ($this->tiposLlamado as $tipo) {
            $m = new \App\Models\ConsultaReclamoModel();
            $this->aplicarFiltros($m);
            $m->where('consulta_reclamo.tipo_llamado', $tipo);
            $res = $m->selectSum('atencion')->first();
            $totalesPorTipo[$tipo] = (int) ($res->atencion ?? 0);
        }

        $mTotal = new \App\Models\ConsultaReclamoModel();
        $this->aplicarFiltros($mTotal);
        $resTotal     = $mTotal->selectSum('atencion')->first();
        $totalGeneral = (int) ($resTotal->atencion ?? 0);

        $mostrarMesEnTabla = $filtros['filtro_ejercicio']
            && $filtros['filtro_tipo']
            && $filtros['filtro_mes'] === '';

        // ── Tabla detalle ──
        $mTabla = new \App\Models\ConsultaReclamoModel();
        $this->aplicarFiltros($mTabla);
        $statsFilas = $mTabla->asArray();

        if ($mostrarMesEnTabla) {
            $statsFilas = $statsFilas
                ->select('consulta_reclamo.tipo_llamado as tipo, consulta_reclamo.ejercicio as ejercicio, consulta_reclamo.mes as mes, SUM(consulta_reclamo.atencion) as cantidad')
                ->groupBy('consulta_reclamo.tipo_llamado, consulta_reclamo.ejercicio, consulta_reclamo.mes')
                ->orderBy('consulta_reclamo.ejercicio', 'DESC')
                ->findAll();
        } else {
            $statsFilas = $statsFilas
                ->select('consulta_reclamo.tipo_llamado as tipo, consulta_reclamo.ejercicio as ejercicio, SUM(consulta_reclamo.atencion) as cantidad')
                ->groupBy('consulta_reclamo.tipo_llamado, consulta_reclamo.ejercicio')
                ->orderBy('consulta_reclamo.ejercicio', 'DESC')
                ->orderBy('consulta_reclamo.tipo_llamado', 'ASC')
                ->findAll();
        }

        // ── Ejercicios recientes (para barras) ──
        $ejerciciosDisponibles = array_values(array_unique(array_column($statsFilas, 'ejercicio')));
        rsort($ejerciciosDisponibles);
        $statsEjercicios = $ejerciciosDisponibles;

        // ── Barras: tipo vs ejercicios recientes ──
        $statsBarras = array_values(array_filter($statsFilas, function ($f) use ($statsEjercicios) {
            return in_array($f['ejercicio'], $statsEjercicios);
        }));

        // ── Torta: distribución por ejercicio (todos) ──
        $mTorta = new \App\Models\ConsultaReclamoModel();
        $this->aplicarFiltros($mTorta);
        $statsTorta = $mTorta->asArray()
            ->select('consulta_reclamo.ejercicio as ejercicio, SUM(consulta_reclamo.atencion) as total')
            ->groupBy('consulta_reclamo.ejercicio')
            ->orderBy('consulta_reclamo.ejercicio', 'DESC')
            ->findAll();

        $paletaTipos = ['#ce93d8', '#7fd8be', '#85c1e9', '#f8c471'];
        $tipoColorMap = [];
        foreach ($this->tiposLlamado as $index => $tipo) {
            $tipoColorMap[$tipo] = $paletaTipos[$index % count($paletaTipos)];
        }

         // ── Última vista ────────────────────────────────────────────────
        $ultimaVista = model('UserLastVisitModel')
            ->where('user_id', user_id())
            ->where('modulo', 'consulta_reclamo')
            ->first()->ultima_vista ?? '2000-01-01 00:00:00';

        return view('consulta_reclamo/consulta_reclamo_list', array_merge($filtros, [
            'ejercicios'      => $ejercicios,
            'categorias'      => $categorias,
            'meses'           => $this->mesesValidos,
            'tipos'           => $this->tiposLlamado,
            'tipoColorMap'    => $tipoColorMap,
            'totalesPorTipo'  => $totalesPorTipo,
            'totalGeneral'    => $totalGeneral,
            'statsFilas'      => $statsFilas,
            'mostrarMesEnTabla' => $mostrarMesEnTabla,
            'statsBarras'     => $statsBarras,
            'statsEjercicios' => $statsEjercicios,
            'statsTorta'      => $statsTorta,
            'ultimaVista'      => $ultimaVista,
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
                                 ->where('modulo', 'consulta_reclamo')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'consulta_reclamo')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'consulta_reclamo',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('consulta_reclamo_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('consulta_reclamo/consulta_reclamo_form', [
            'categorias'    => model('CategoriaModel')->orderBy('nombre', 'ASC')->findAll(),
            'sub_categorias'=> model('SubCategoriaModel')->orderBy('nombre', 'ASC')->findAll(),
            'meses'         => $this->mesesValidos,
            'tipos'         => $this->tiposLlamado,
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

        model('ConsultaReclamoModel')->insert([
            'ejercicio'        => $this->request->getPost('ejercicio'),
            'mes'              => $this->request->getPost('mes'),
            'tipo_llamado'     => $this->request->getPost('tipo_llamado'),
            'categoria_id'     => $this->request->getPost('categoria_id')     ?: null,
            'sub_categoria_id' => $this->request->getPost('sub_categoria_id') ?: null,
            'atencion'         => (int) $this->request->getPost('atencion'),
            'estado'           => 'activo',
        ]);

        return redirect()->to(route_to('consulta_reclamo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('ConsultaReclamoModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('consulta_reclamo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('consulta_reclamo/consulta_reclamo_form', [
            'registro'      => $registro,
            'categorias'    => model('CategoriaModel')->orderBy('nombre', 'ASC')->findAll(),
            'sub_categorias'=> model('SubCategoriaModel')->orderBy('nombre', 'ASC')->findAll(),
            'meses'         => $this->mesesValidos,
            'tipos'         => $this->tiposLlamado,
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

        $id = $this->request->getPost('consulta_id');

        model('ConsultaReclamoModel')->update($id, [
            'ejercicio'        => $this->request->getPost('ejercicio'),
            'mes'              => $this->request->getPost('mes'),
            'tipo_llamado'     => $this->request->getPost('tipo_llamado'),
            'categoria_id'     => $this->request->getPost('categoria_id')     ?: null,
            'sub_categoria_id' => $this->request->getPost('sub_categoria_id') ?: null,
            'atencion'         => (int) $this->request->getPost('atencion'),
            'estado'           => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('consulta_reclamo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual = (int) date('Y');

        return $this->validate([
            'ejercicio'    => "required|integer|less_than_equal_to[{$anioActual}]",
            'mes'          => 'required',
            'tipo_llamado' => 'required',
            'atencion'     => 'required|integer',
        ], [
            'ejercicio' => [
                'less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}.",
            ],
            'mes'          => ['required' => 'Debe seleccionar un mes.'],
            'tipo_llamado' => ['required' => 'Debe seleccionar un tipo de llamado.'],
            'atencion'     => ['required' => 'El campo atención es obligatorio.'],
        ]);
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('ConsultaReclamoModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));
        $registros  = $model->orderBy('consulta_reclamo.ejercicio', 'DESC')
                            ->orderBy("FIELD(consulta_reclamo.mes,{$ordenMeses})", '')
                            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consultas y Reclamos');

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Tipo de Llamado',
            'D' => 'Categoría',
            'E' => 'Sub Categoría',
            'F' => 'Atenciones',
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
            $sheet->setCellValue('B' . $row, $r->mes);
            $sheet->setCellValue('C' . $row, $r->tipo_llamado);
            $sheet->setCellValue('D' . $row, $r->categoria_nombre     ?? '');
            $sheet->setCellValue('E' . $row, $r->sub_categoria_nombre ?? '');
            $sheet->setCellValue('F' . $row, $r->atencion);
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'consultas_reclamos_' . date('Ymd_His') . '.xlsx';

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
        $model = model('ConsultaReclamoModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('consulta_reclamo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}