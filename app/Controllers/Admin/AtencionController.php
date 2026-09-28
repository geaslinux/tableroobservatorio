<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Atencion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AtencionController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    // $ejercicios: lista completa de ejercicios (desc) para poder calcular el default
    private function aplicarFiltros($model, $ejercicios = [])
    {
        $ejercicioRaw   = $this->request->getGet('ejercicio'); // null = no vino en la URL
        $filtro_mes     = $this->request->getGet('mes')    ?? '';
        $filtro_estado  = $this->request->getGet('estado') ?? '';

        // Si el parámetro "ejercicio" NO vino en la URL (carga directa, sin submit del form)
        // usamos como default el último ejercicio cargado. Si vino (aunque sea vacío = "Todos"),
        // respetamos lo que eligió el usuario.
        if ($ejercicioRaw === null) {
            $filtro_ejercicio = $ejercicios[0] ?? '';
        } else {
            $filtro_ejercicio = $ejercicioRaw;
        }

        if ($filtro_ejercicio !== '') $model->where('ejercicio', $filtro_ejercicio);
        if ($filtro_mes)              $model->where('mes', $filtro_mes);

        if ($filtro_estado != '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_estado');
    }

    // Mismo criterio de filtros pero para query builder crudo ($db->table), usado en KPIs/stats
    private function statsBuilder($db, $filtro_ejercicio, $filtro_mes, $filtro_estado)
    {
        $builder = $db->table('atencion');

        if ($filtro_ejercicio !== '') $builder->where('ejercicio', $filtro_ejercicio);
        if ($filtro_mes)              $builder->where('mes', $filtro_mes);

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
        $perPage = $config->regPerPage ?? 15;

        $ejercicios = array_column(
            model('AtencionModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        // ── Filtros (con default: último ejercicio si no se especificó nada) ──
        $model   = model('AtencionModel');
        $filtros = $this->aplicarFiltros($model, $ejercicios);
        $filtro_ejercicio = $filtros['filtro_ejercicio'];
        $filtro_mes       = $filtros['filtro_mes'];
        $filtro_estado    = $filtros['filtro_estado'];

        $db = \Config\Database::connect();
        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));

        // ── KPI CARDS (ahora respetan ejercicio/mes/estado) ──
        $totalAtencionesBase = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_mes, $filtro_estado)
                ->selectSum('atenciones_base')->get()->getRow()->atenciones_base ?? 0
        );

        $totalAsistidosCoberturas = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_mes, $filtro_estado)
                ->selectSum('asistidos_coberturas')->get()->getRow()->asistidos_coberturas ?? 0
        );

        $totalCantidadCoberturas = (int) (
            $this->statsBuilder($db, $filtro_ejercicio, $filtro_mes, $filtro_estado)
                ->selectSum('cantidad_coberturas')->get()->getRow()->cantidad_coberturas ?? 0
        );

        $totalGeneralCantidad = $totalAtencionesBase + $totalAsistidosCoberturas;

        // ── ESTADÍSTICAS (tabla, barras, torta) — también filtradas ──
        $statsFilas = $this->statsBuilder($db, $filtro_ejercicio, $filtro_mes, $filtro_estado)
            ->select('mes, ejercicio, SUM(total) as cantidad')
            ->groupBy('mes, ejercicio')
            ->orderBy('ejercicio', 'DESC')
            ->orderBy("FIELD(mes,{$ordenMeses})", '')
            ->get()->getResultArray();

        $todosLosEjercicios = array_column(
                $db->table('atencion')
                    ->select('ejercicio')
                    ->distinct()
                    ->whereIn('estado', ['activo', 'desactivado'])
                    ->orderBy('ejercicio', 'ASC')
                    ->get()->getResultArray(),
                'ejercicio'
        );   

        // Ejercicios a comparar en el gráfico de barras:
        // - si hay un ejercicio filtrado (incluido el default), comparamos solo ese
        // - si el usuario eligió "Todos", comparamos los últimos 2 (como antes)
        $statsEjercicios = ($filtro_ejercicio !== '')
        ? [$filtro_ejercicio]
        : $ejercicios;

        $statsBarras = [];
        $statsTorta  = [];

        if (!empty($statsEjercicios)) {
            $statsBarras = $this->statsBuilder($db, '', $filtro_mes, $filtro_estado)
                ->whereIn('ejercicio', $statsEjercicios)
                ->select('mes, ejercicio, SUM(total) as cantidad')
                ->groupBy('mes, ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->orderBy("FIELD(mes,{$ordenMeses})", '')
                ->get()->getResultArray();

            $statsTorta = $this->statsBuilder($db, '', $filtro_mes, $filtro_estado)
                ->whereIn('ejercicio', $statsEjercicios)
                ->select('ejercicio, SUM(total) as total')
                ->groupBy('ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->get()->getResultArray();
        }

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'atenciones')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('AtencionModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('AtencionModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('updated_at >', $ultimaVista)
            ->where('updated_at != created_at')
            ->countAllResults();

        return view('atencion/atencion_list', array_merge($filtros, [
            'atenciones'                => $model->orderBy('ejercicio', 'DESC')->orderBy("FIELD(mes,{$ordenMeses})", '')->paginate($perPage),
            'pager'                     => $model->pager,
            'ejercicios'                => $ejercicios,
            'todosLosEjercicios'        => $todosLosEjercicios,
            'meses'                     => $this->mesesValidos,
            'totalAtencionesBase'       => $totalAtencionesBase,
            'totalAsistidosCoberturas'  => $totalAsistidosCoberturas,
            'totalCantidadCoberturas'   => $totalCantidadCoberturas,
            'totalGeneralCantidad'      => $totalGeneralCantidad,
            'statsFilas'                => $statsFilas,
            'statsBarras'               => $statsBarras,
            'statsEjercicios'           => $statsEjercicios,
            'statsTorta'                => $statsTorta,
            'nuevas'                    => $nuevas,
            'modificadas'               => $modificadas,
            'ultimaVista'               => $ultimaVista,
        ]));
    }

    // ─── Exportar Excel con filtros ──────────────────────────────────────
    public function export()
    {
        $model = model('AtencionModel');
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));
        $atenciones = $model->orderBy('ejercicio', 'DESC')
                            ->orderBy("FIELD(mes,{$ordenMeses})", '')
                            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Atenciones Base/USE',
            'D' => 'Asistidos Coberturas',
            'E' => 'Cantidad Coberturas',
            'F' => 'Total',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($atenciones as $i => $a) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $a->ejercicio);
            $sheet->setCellValue('B' . $row, $a->mes);
            $sheet->setCellValue('C' . $row, $a->atenciones_base);
            $sheet->setCellValue('D' . $row, $a->asistidos_coberturas);
            $sheet->setCellValue('E' . $row, $a->cantidad_coberturas);
            $sheet->setCellValue('F' . $row, $a->total);
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'atenciones_' . date('Ymd_His') . '.xlsx';

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
        $model = model('AtencionModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('atencion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}