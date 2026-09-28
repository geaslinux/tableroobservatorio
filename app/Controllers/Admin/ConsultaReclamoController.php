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
        // Categoría: selección múltiple (categoria[]); el valor 0 representa "Sin categoría"
        $filtro_categoria = array_values(array_unique(array_map('intval', array_filter(
            (array) $filtro_categoria,
            function ($v) { return is_scalar($v) && ctype_digit((string) $v); }
        ))));
        if ($filtro_categoria) {
            $ids       = array_values(array_filter($filtro_categoria));
            $sinCatego = in_array(0, $filtro_categoria, true);
            $model->groupStart();
            if ($ids)       $model->whereIn('consulta_reclamo.categoria_id', $ids);
            if ($sinCatego) $model->orWhere('consulta_reclamo.categoria_id', null);
            $model->groupEnd();
        }

        if ($filtro_estado != '') {
            $model->where('consulta_reclamo.estado', $filtro_estado);
        } else {
            $model->whereIn('consulta_reclamo.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_tipo', 'filtro_categoria', 'filtro_estado');
    }

    // ─── Index (estadísticas, sin CRUD) ─────────────────────────────────
    public function index()
    {
        helper('auth');

        $ejercicios = array_column(
            model('ConsultaReclamoModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $categorias = model('CategoriaModel')->orderBy('nombre', 'ASC')->findAll();

        // ── Una sola consulta filtrada, agrupada por ejercicio / mes / tipo / categoría ──
        $model   = new \App\Models\ConsultaReclamoModel();
        $filtros = $this->aplicarFiltros($model);
        $filas   = $model->asArray()
            ->select('consulta_reclamo.ejercicio, consulta_reclamo.mes, consulta_reclamo.tipo_llamado AS tipo,
                      categoria.nombre AS categoria, SUM(consulta_reclamo.atencion) AS cantidad')
            ->join('categoria', 'categoria.categoria_id = consulta_reclamo.categoria_id', 'left')
            ->groupBy('consulta_reclamo.ejercicio, consulta_reclamo.mes, consulta_reclamo.tipo_llamado, categoria.nombre')
            ->findAll();

        // Tipos: los 4 conocidos siempre, más cualquier otro cargado en la base
        $tipos = $this->tiposLlamado;
        foreach ($filas as $f) {
            if (!in_array($f['tipo'], $tipos, true)) $tipos[] = $f['tipo'];
        }

        $porTipo      = array_fill_keys($tipos, 0);
        $porMes       = [];
        $porCategoria = [];
        $porEjercicio = [];
        $ordenMes     = array_flip($this->mesesValidos);

        foreach ($filas as $f) {
            $cant = (int) $f['cantidad'];
            $porTipo[$f['tipo']] += $cant;

            $clave = $f['ejercicio'] . '|' . $f['mes'];
            if (!isset($porMes[$clave])) {
                $porMes[$clave] = [
                    'ejercicio' => (string) $f['ejercicio'],
                    'mes'       => $f['mes'],
                    'tipos'     => array_fill_keys($tipos, 0),
                    'total'     => 0,
                ];
            }
            $porMes[$clave]['tipos'][$f['tipo']] += $cant;
            $porMes[$clave]['total']             += $cant;

            $cat = $f['categoria'] ?: 'SIN CATEGORÍA';
            $porCategoria[$cat] = ($porCategoria[$cat] ?? 0) + $cant;

            $porEjercicio[$f['ejercicio']] = ($porEjercicio[$f['ejercicio']] ?? 0) + $cant;
        }

        // Meses en orden cronológico
        $porMes = array_values($porMes);
        usort($porMes, function ($a, $b) use ($ordenMes) {
            return [(int) $a['ejercicio'], $ordenMes[$a['mes']] ?? 99] <=> [(int) $b['ejercicio'], $ordenMes[$b['mes']] ?? 99];
        });
        arsort($porCategoria);
        ksort($porEjercicio);

        // ── KPIs ──
        $total    = array_sum($porTipo);
        $mesPico  = null;
        foreach ($porMes as $m) {
            if ($m['total'] > 0 && ($mesPico === null || $m['total'] > $mesPico['total'])) $mesPico = $m;
        }
        $kpi = [
            'total'      => $total,
            'meses'      => count($porMes),
            'promedio'   => count($porMes) ? round($total / count($porMes)) : 0,
            'mes_pico'   => $mesPico,
            'categorias' => count($porCategoria),
        ];

        // Todos los ejercicios de la base (para asignar el mismo color azul siempre)
        $todosLosEjercicios = array_map('intval', $ejercicios);
        sort($todosLosEjercicios);

        // ── Última vista ────────────────────────────────────────────────
        $ultimaVista = model('UserLastVisitModel')
            ->where('user_id', user_id())
            ->where('modulo', 'consulta_reclamo')
            ->first()->ultima_vista ?? '2000-01-01 00:00:00';

        return view('consulta_reclamo/consulta_reclamo_list', array_merge($filtros, [
            'ejercicios'         => $ejercicios,
            'categorias'         => $categorias,
            'meses'              => $this->mesesValidos,
            'tipos'              => $tipos,
            'porTipo'            => $porTipo,
            'porMes'             => $porMes,
            'porCategoria'       => $porCategoria,
            'porEjercicio'       => $porEjercicio,
            'kpi'                => $kpi,
            'todosLosEjercicios' => $todosLosEjercicios,
            'ultimaVista'        => $ultimaVista,
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