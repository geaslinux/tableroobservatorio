<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Movil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MovilController extends BaseController
{
    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $search           = strtoupper($this->request->getGet('search') ?? '');
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_tipo      = $this->request->getGet('tipo')      ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';
        $estado_modo      = $this->request->getGet('estado_modo') ?? 'todos';

        if ($search != '') {
            $model->groupStart()
                  ->like('UPPER(tipo)', $search)
                  ->groupEnd();
        }

        if ($filtro_ejercicio) $model->where('ejercicio', $filtro_ejercicio);
        if ($filtro_tipo)      $model->where('tipo', $filtro_tipo);

        if ($filtro_estado != '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        return compact('search', 'filtro_ejercicio', 'filtro_tipo', 'filtro_estado', 'estado_modo');
    }

    private function statsBuilder($db, $filtro_ejercicio, $filtro_tipo, $filtro_estado)
    {
        $builder = $db->table('movil');

        if ($filtro_ejercicio !== '') $builder->where('ejercicio', $filtro_ejercicio);
        if ($filtro_tipo !== '')      $builder->like('tipo', $filtro_tipo);

        if ($filtro_estado != '') {
            $builder->where('estado', $filtro_estado);
        } else {
            $builder->whereIn('estado', ['activo', 'desactivado']);
        }

        return $builder;
    }

    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 10;

        // Para select de filtro ejercicio (modelo limpio)
        $ejercicios = array_column(
            model('MovilModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $tipos = array_column(
            model('MovilModel')->select('tipo')->distinct()->orderBy('tipo', 'ASC')->findAll(),
            'tipo'
        );

        // Totales agrupados por ejercicio
        $totalesPorEjercicio = $this->getTotalesPorEjercicio();

        // ── Filtros ────────────────────────────────────
        $model   = model('MovilModel');
        $filtros = $this->aplicarFiltros($model);
        $filtro_ejercicio = $filtros['filtro_ejercicio'];
        $filtro_tipo      = $filtros['filtro_tipo'];
        $filtro_estado    = $filtros['filtro_estado'];

        $db = \Config\Database::connect();

        // ── KPI CARDS ──
        $totalOperativos = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_tipo, $filtro_estado)
                ->where('tipo', 'MOVILES OPERATIVOS')
                ->selectSum('cantidad')->get()->getRow()->cantidad ?? 0
        );

        $totalLogistica = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_tipo, $filtro_estado)
                ->where('tipo', 'MOVILES LOGISTICA')
                ->selectSum('cantidad')->get()->getRow()->cantidad ?? 0
        );

        $totalFueraServicio = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_tipo, $filtro_estado)
                ->where('tipo', 'FUERA DE SERVICIO')
                ->selectSum('cantidad')->get()->getRow()->cantidad ?? 0
        );

        $totalGeneralCantidad = $totalOperativos + $totalLogistica + $totalFueraServicio;

        // ── ESTADÍSTICAS ──
        $statsFilas = $this->statsBuilder($db, $filtro_ejercicio, $filtro_tipo, $filtro_estado)
            ->select('tipo, ejercicio, SUM(cantidad) as cantidad')
            ->groupBy('tipo, ejercicio')
            ->orderBy('ejercicio', 'DESC')
            ->orderBy('tipo', 'ASC')
            ->get()->getResultArray();

        // Obtener TODOS los ejercicios de la BD (sin filtros) ordenados de menor a mayor
        $todosLosEjercicios = array_column(
            $db->table('movil')
                ->select('ejercicio')
                ->distinct()
                ->whereIn('estado', ['activo', 'desactivado'])
                ->orderBy('ejercicio', 'ASC')
                ->get()->getResultArray(),
            'ejercicio'
        );

// Si hay filtro activo de ejercicio, comparamos solo ese. Si no, todos.
$statsEjercicios = ($filtro_ejercicio !== '')
    ? [$filtro_ejercicio]
    : $ejercicios;

        $statsBarras = [];
        $statsTorta  = [];

        if (!empty($statsEjercicios)) {
            $statsBarras = $this->statsBuilder($db, '', $filtro_tipo, $filtro_estado)
                ->whereIn('ejercicio', $statsEjercicios)
                ->select('tipo, ejercicio, SUM(cantidad) as cantidad')
                ->groupBy('tipo, ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->orderBy('tipo', 'ASC')
                ->get()->getResultArray();

            $statsTorta = $this->statsBuilder($db, '', $filtro_tipo, $filtro_estado)
                ->whereIn('ejercicio', $statsEjercicios)
                ->select('ejercicio, SUM(cantidad) as total')
                ->groupBy('ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->get()->getResultArray();
        }

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');
        $registro    = $visitModel->where('user_id', $userId)
                              ->where('modulo', 'moviles')
                              ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('MovilModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('MovilModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('updated_at >', $ultimaVista)
            ->where('updated_at != created_at')
            ->countAllResults();

        return view('movil/movil_list', array_merge($filtros, [
            'moviles'               => $model->orderBy('ejercicio', 'DESC')->orderBy('tipo', 'ASC')->paginate($perPage),
            'pager'                 => $model->pager,
            'ejercicios'            => $ejercicios,
            'todosLosEjercicios'    => $todosLosEjercicios,
            'tipos'                 => $tipos,
            'totalesPorEjercicio'   => $totalesPorEjercicio,
            'totalOperativos'       => $totalOperativos,
            'totalLogistica'        => $totalLogistica,
            'totalFueraServicio'    => $totalFueraServicio,
            'totalGeneralCantidad'  => $totalGeneralCantidad,
            'statsFilas'            => $statsFilas,
            'statsBarras'           => $statsBarras,
            'statsEjercicios'       => $statsEjercicios,
            'statsTorta'            => $statsTorta,
            'nuevas'                => $nuevas,
            'modificadas'           => $modificadas,
            'ultimaVista'           => $ultimaVista,
            'estado_modo'           => $filtros['estado_modo'] ?? 'todos',
        ]));
    }

  public function marcarVisto()
{
    helper('auth');

    $userId = user()->id;
    $ahora  = date('Y-m-d H:i:s');

    $visitModel = model('UserLastVisitModel');

    $registro = $visitModel
        ->where('user_id', $userId)
        ->where('modulo', 'moviles')
        ->first();

    if ($registro) {
        $visitModel
            ->where('user_id', $userId)
            ->where('modulo', 'moviles')
            ->set(['ultima_vista' => $ahora])
            ->update();
    } else {
        $visitModel->insert([
            'user_id'      => $userId,
            'modulo'       => 'moviles',
            'ultima_vista' => $ahora,
        ]);
    }

    return redirect()->to(route_to('movil_list'));
}

    // ─── Total por ejercicio (OPERATIVOS + LOGISTICA) ────────────────────
    private function getTotalesPorEjercicio()
    {
        $db    = \Config\Database::connect();
        $query = $db->query("
            SELECT ejercicio, SUM(cantidad) as total_real
            FROM movil
            WHERE tipo IN ('MOVILES OPERATIVOS','MOVILES LOGISTICA')
            AND estado != 'eliminado'
            GROUP BY ejercicio
            ORDER BY ejercicio DESC
        ");
        $result = [];
        foreach ($query->getResultArray() as $row) {
            $result[$row['ejercicio']] = $row['total_real'];
        }
        return $result;
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('MovilModel');
        $this->aplicarFiltros($model);
        $moviles = $model->orderBy('ejercicio', 'DESC')->orderBy('tipo', 'ASC')->findAll();
        $totales = $this->getTotalesPorEjercicio();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = ['A' => 'Ejercicio', 'B' => 'Tipo', 'C' => 'Cantidad', 'D' => 'Total'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $row                  = 2;
        $ejercicioActual      = null;
        $primeraFilaEjercicio = null;

        foreach ($moviles as $m) {
            if ($m->ejercicio != $ejercicioActual) {
                $ejercicioActual      = $m->ejercicio;
                $primeraFilaEjercicio = $row;
            }

            $sheet->setCellValue('A' . $row, $m->ejercicio);
            $sheet->setCellValue('B' . $row, $m->tipo);
            $sheet->setCellValue('C' . $row, $m->cantidad);

            if ($row == $primeraFilaEjercicio) {
                $sheet->setCellValue('D' . $row, $totales[$m->ejercicio] ?? '');
                $sheet->getStyle('D' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('BDD7EE');
            }

            $row++;
        }

        foreach (['A', 'B', 'C', 'D'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'moviles_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Create ──────────────────────────────────────────────────────────
    public function create()
    {
        helper('form');
        return view('movil/movil_form', ['formRoute' => 'movil_store']);
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $movil         = new Movil($this->request->getPost());
        $movil->estado = 'activo';
        model('MovilModel')->save($movil);

        return redirect()->to(route_to('movil_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Móvil guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($movil_id)
    {
        $model = model('MovilModel');
        if (!$movil = $model->find($movil_id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $movil->setCampoOculto();
        return view('movil/movil_form', [
            'movil'     => $movil,
            'formRoute' => 'movil_update',
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        $id    = $this->request->getPost('movil_id');
        $model = model('MovilModel');

        if (!$model->find($id)) {
            return redirect()->to(route_to('movil_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Móvil no encontrado']);
        }

        if (!$this->valida(true)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $movil = new Movil($this->request->getPost());
        $model->save($movil);

        return redirect()->to(route_to('movil_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Móvil actualizado correctamente']);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('MovilModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('movil_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Móvil eliminado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
   public function valida($isEditing = false)
{
    $anioActual = (int) date('Y');  // Hora del servidor — ya está en Argentina

    return $this->validate([
        'ejercicio' => "required|integer|less_than_equal_to[{$anioActual}]",
        'tipo'      => 'required',
        'cantidad'  => 'required|integer',
    ], [
        'ejercicio' => [
            'less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}. Estamos en {$anioActual}.",
        ],
    ]);
}
}