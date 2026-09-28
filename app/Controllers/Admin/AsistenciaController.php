<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Asistencia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AsistenciaController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    //3333
    // ─── Helper: entero a número romano ──────────────────────────────────
    private function toRoman(int $n): string
    {
        $map = [
            1000 => 'M',  900 => 'CM', 500 => 'D',  400 => 'CD',
             100 => 'C',   90 => 'XC',  50 => 'L',   40 => 'XL',
              10 => 'X',    9 => 'IX',   5 => 'V',    4 => 'IV',
               1 => 'I'
        ];
        $result = '';
        foreach ($map as $val => $sym) {
            while ($n >= $val) {
                $result .= $sym;
                $n      -= $val;
            }
        }
        return $result;
    }

    // ─── Helper: calcular romano para un nombre dentro de su grupo ────────
    private function calcularRomano(
        string $ejercicio,
        string $mes,
        string $operativa,
        string $nombre,
        array  $nombresNuevos = []
    ): string {
        $model = model('AsistenciaModel');

        $existentes = $model
            ->select('nombre')
            ->where('ejercicio', $ejercicio)
            ->where('mes', $mes)
            ->where('operativa', $operativa)
            ->whereIn('estado', ['activo', 'desactivado'])
            ->findAll();

        $nombresExistentes = array_map(fn($r) => $r->nombre, $existentes);

        $todos = array_unique(array_merge($nombresExistentes, $nombresNuevos, [$nombre]));
        sort($todos);

        $pos = array_search($nombre, $todos);
        return $this->toRoman($pos + 1);
    }

    // ─── Filtros (lee de la request y aplica sobre el modelo dado) ────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_mes       = $this->request->getGet('mes')       ?? '';
        $filtro_nombre    = $this->request->getGet('nombre')    ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        if (strtolower(trim((string) $filtro_mes)) === 'todos') {
            $filtro_mes = '';
        }

        if ($filtro_ejercicio) $model->where('ejercicio', $filtro_ejercicio);
        if ($filtro_mes)       $model->where('mes',       $filtro_mes);
        if ($filtro_nombre)    $model->where('nombre',    $filtro_nombre);

        if ($filtro_estado != '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_nombre', 'filtro_estado');
    }

    private function construirQueryFiltrada(array $filtros)
    {
        $model = model('AsistenciaModel', false);

        if ($filtros['filtro_ejercicio']) $model->where('ejercicio', $filtros['filtro_ejercicio']);
        if ($filtros['filtro_mes'])       $model->where('mes',       $filtros['filtro_mes']);
        if ($filtros['filtro_nombre'])    $model->where('nombre',    $filtros['filtro_nombre']);

        if ($filtros['filtro_estado'] != '') {
            $model->where('estado', $filtros['filtro_estado']);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        return $model;
    }

    public function index()
    {
        // ── Listas para selects ────────────────────────────────────────
        $ejercicios = array_column(
            model('AsistenciaModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $meses = $this->mesesValidos;

        $nombres = array_column(
            model('AsistenciaModel')
                ->select('nombre')->distinct()
                ->orderBy('nombre', 'ASC')
                ->whereIn('estado', ['activo', 'desactivado'])
                ->findAll(),
            'nombre'
        );

        // ── Filtros ─────────────────────────────────────────────────────
        $modelFiltro = model('AsistenciaModel');
        $filtros     = $this->aplicarFiltros($modelFiltro);

        // ── KPIs (sobre datos filtrados) ───────────────────────────────
        $kpiRow = $this->construirQueryFiltrada($filtros)
            ->select('
                SUM(emergencias_con_medico) as total_em_con,
                SUM(emergencias_sin_medico) as total_em_sin,
                SUM(urgencias) as total_urg,
                SUM(total) as total_general
            ')
            ->first();

        $totalEmConMedico = $kpiRow->total_em_con   ?? 0;
        $totalEmSinMedico = $kpiRow->total_em_sin   ?? 0;
        $totalUrgencias   = $kpiRow->total_urg      ?? 0;
        $totalGeneral     = $kpiRow->total_general  ?? 0;

        $totalBases = count(
            $this->construirQueryFiltrada($filtros)
                ->select('nombre')->distinct()->findAll()
        );

        $todasLasBases = array_column(
            model('AsistenciaModel')
                ->select('nombre')
                ->distinct()
                ->whereIn('estado', ['activo', 'desactivado'])
                ->orderBy('nombre', 'ASC')
                ->findAll(),
            'nombre'
        );

        $paletaBase = [
            '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
            '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
            '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
            '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
        ];
        $baseColorMap = [];
        $baseRankingRaw = model('AsistenciaModel')
            ->select('nombre, SUM(total) as total')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->groupBy('nombre')
            ->orderBy('total', 'DESC')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        if ($filtros['filtro_ejercicio']) {
            $baseRankingRaw = model('AsistenciaModel')
                ->select('nombre, SUM(total) as total')
                ->where('ejercicio', $filtros['filtro_ejercicio'])
                ->whereIn('estado', ['activo', 'desactivado'])
                ->groupBy('nombre')
                ->orderBy('total', 'DESC')
                ->orderBy('nombre', 'ASC')
                ->findAll();
        }

        foreach ($baseRankingRaw as $index => $base) {
            $clave = strtoupper(trim($base->nombre));
            if ($clave !== '') {
                $baseColorMap[$clave] = $paletaBase[$index % count($paletaBase)];
            }
        }

        foreach ($todasLasBases as $index => $base) {
            $clave = strtoupper(trim($base));
            if ($clave !== '' && !isset($baseColorMap[$clave])) {
                $baseColorMap[$clave] = $paletaBase[(count($baseColorMap) + $index) % count($paletaBase)];
            }
        }

        // Con ejercicio + base y mes "Todos", la tabla debe mostrar cada mes.
        $mostrarMesEnTabla = $filtros['filtro_ejercicio']
            && $filtros['filtro_nombre']
            && $filtros['filtro_mes'] === '';

        $consultaStatsFilas = $this->construirQueryFiltrada($filtros);

        if ($mostrarMesEnTabla) {
            $statsFilasRaw = $consultaStatsFilas
                ->select('nombre, ejercicio, mes, SUM(total) as cantidad')
                ->groupBy('nombre, ejercicio, mes')
                ->orderBy('ejercicio', 'DESC')
                ->orderBy('nombre', 'ASC')
                ->orderBy('mes', 'ASC')
                ->findAll();
        } else {
            $statsFilasRaw = $consultaStatsFilas
                ->select('nombre, ejercicio, SUM(total) as cantidad')
                ->groupBy('nombre, ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->orderBy('nombre', 'ASC')
                ->findAll();
        }

        $statsFilas = array_map(fn($r) => [
            'nombre'    => $r->nombre,
            'ejercicio' => $r->ejercicio,
            'mes'       => $r->mes ?? null,
            'cantidad'  => $r->cantidad,
        ], $statsFilasRaw);

        if ($mostrarMesEnTabla) {
            $ordenMeses = array_flip($this->mesesValidos);

            usort($statsFilas, function ($a, $b) use ($ordenMeses) {
                return ($ordenMeses[strtoupper($a['mes'])] ?? PHP_INT_MAX)
                    <=> ($ordenMeses[strtoupper($b['mes'])] ?? PHP_INT_MAX);
            });
        }

        // ── Ejercicios recientes (para barras) ──
        $ejerciciosDisponibles = array_values(array_unique(array_column($statsFilas, 'ejercicio')));
        rsort($ejerciciosDisponibles);
        $statsEjercicios = $ejerciciosDisponibles;

        // Año + base: conservar el desglose por mes, incluso si se eligió
        // un mes concreto. La vista usa este campo para alimentar ambos gráficos.
        if ($filtros['filtro_ejercicio'] && $filtros['filtro_nombre']) {
            $statsBarrasRaw = $this->construirQueryFiltrada($filtros)
                ->select('nombre, ejercicio, mes, SUM(total) as cantidad')
                ->groupBy('nombre, ejercicio, mes')
                ->findAll();
        } else {
            $statsBarrasRaw = $this->construirQueryFiltrada($filtros)
                ->select('nombre, ejercicio, SUM(total) as cantidad')
                ->whereIn('ejercicio', $statsEjercicios)
                ->groupBy('nombre, ejercicio')
                ->findAll();
        }

        $statsBarras = array_map(fn($r) => [
            'nombre'    => $r->nombre,
            'ejercicio' => $r->ejercicio,
            'mes'       => $r->mes ?? null,
            'cantidad'  => $r->cantidad,
        ], $statsBarrasRaw);

        // ── Distribución total por ejercicio (torta) ───────────────────
        $statsTortaRaw = $this->construirQueryFiltrada($filtros)
            ->select('ejercicio, SUM(total) as total')
            ->groupBy('ejercicio')
            ->orderBy('ejercicio', 'DESC')
            ->findAll();

        $statsTorta = array_map(fn($r) => [
            'ejercicio' => $r->ejercicio,
            'total'     => $r->total,
        ], $statsTortaRaw);

        // ── Última vista ────────────────────────────────────────────────
        $ultimaVista = model('UserLastVisitModel')
            ->where('user_id', user_id())
            ->where('modulo', 'asistencia')
            ->first()->ultima_vista ?? '2000-01-01 00:00:00';

        return view('asistencia/asistencia_list', array_merge($filtros, [
            'ejercicios'       => $ejercicios,
            'meses'            => $meses,
            'nombres'          => $nombres,
            'todasLasBases'    => $todasLasBases,
            'baseColorMap'     => $baseColorMap,
            'totalEmConMedico' => $totalEmConMedico,
            'totalEmSinMedico' => $totalEmSinMedico,
            'totalUrgencias'   => $totalUrgencias,
            'totalGeneral'     => $totalGeneral,
            'totalBases'       => $totalBases,
            'statsFilas'       => $statsFilas,
            'mostrarMesEnTabla' => $mostrarMesEnTabla,
            'statsBarras'      => $statsBarras,
            'statsEjercicios'  => $statsEjercicios,
            'statsTorta'       => $statsTorta,
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

        $existe = $visitModel
            ->where('user_id', $userId)
            ->where('modulo', 'asistencia')
            ->first();

        if ($existe) {
            $visitModel
                ->where('user_id', $userId)
                ->where('modulo', 'asistencia')
                ->set('ultima_vista', $ahora)
                ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'asistencia',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('asistencia_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        return view('asistencia/asistencia_form');
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $model = model('AsistenciaModel');

        $ejercicio = $this->request->getPost('ejercicio');
        $mes       = $this->request->getPost('mes');
        $operativa = $this->request->getPost('operativa');

        $nombres = $this->request->getPost('nombre');
        $em_con  = $this->request->getPost('em_con');
        $em_sin  = $this->request->getPost('em_sin');
        $urg     = $this->request->getPost('urg');
        $pub     = $this->request->getPost('pub');
        $priv    = $this->request->getPost('priv');
        $int     = $this->request->getPost('int');

        $items = [];
        foreach ($nombres as $i => $nombre) {
            $items[] = [
                'nombre' => strtoupper(trim($nombre)),
                'em_con' => intval($em_con[$i]),
                'em_sin' => intval($em_sin[$i]),
                'urg'    => intval($urg[$i]),
                'pub'    => intval($pub[$i]),
                'priv'   => intval($priv[$i]),
                'int'    => intval($int[$i]),
            ];
        }
        usort($items, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        $nombresNuevos = array_column($items, 'nombre');

        foreach ($items as $item) {
            $total = $item['em_con'] + $item['em_sin'] + $item['urg']
                   + $item['pub']   + $item['priv']   + $item['int'];

            $nro = $this->calcularRomano($ejercicio, $mes, $operativa, $item['nombre'], $nombresNuevos);

            $model->insert([
                'ejercicio'                => $ejercicio,
                'mes'                      => $mes,
                'operativa'                => $operativa,
                'nro'                      => $nro,
                'nombre'                   => $item['nombre'],
                'emergencias_con_medico'   => $item['em_con'],
                'emergencias_sin_medico'   => $item['em_sin'],
                'urgencias'                => $item['urg'],
                'derivacion_publica'       => $item['pub'],
                'derivacion_privada'       => $item['priv'],
                'internacion_domiciliaria' => $item['int'],
                'total'                    => $total,
                'estado'                   => 'activo',
            ]);
        }

        return redirect()->to(route_to('asistencia_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Asistencias guardadas correctamente']);
    }

    // ─── Vista importar ───────────────────────────────────────────────────
    public function import()
    {
        $bases = model('BaseModel')->whereIn('estado', ['activo'])->orderBy('nombre', 'ASC')->findAll();
        return view('asistencia/asistencia_import', ['bases' => $bases]);
    }

    // ─── Procesar importación ─────────────────────────────────────────────
    public function processImport()
    {
        $file = $this->request->getFile('archivo');

        if (!$file->isValid()) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Archivo inválido']);
        }

        if (!in_array(strtolower($file->getClientExtension()), ['xlsx', 'xls'])) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Solo .xlsx o .xls']);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);

            $basesDB        = model('BaseModel')->whereIn('estado', ['activo', 'desactivado'])->findAll();
            $nombresValidos = array_map(fn($b) => strtoupper(trim($b->nombre)), $basesDB);

            $model    = model('AsistenciaModel');
            $inserted = 0;
            $skipped  = 0;
            $errores  = [];

            $nombresInsertados = [];

            foreach ($rows as $rowNum => $row) {
                if ($rowNum == 1) continue;

                $ejercicio   = trim($row['A'] ?? '');
                $mes         = strtoupper(trim($row['B'] ?? ''));
                $operativa   = strtoupper(trim($row['C'] ?? ''));
                $nombre      = strtoupper(trim($row['E'] ?? ''));
                $emCon       = intval(str_replace(['.', ','], '', $row['F'] ?? 0));
                $emSin       = intval(str_replace(['.', ','], '', $row['G'] ?? 0));
                $urgencias   = intval(str_replace(['.', ','], '', $row['H'] ?? 0));
                $derPub      = intval(str_replace(['.', ','], '', $row['I'] ?? 0));
                $derPriv     = intval(str_replace(['.', ','], '', $row['J'] ?? 0));
                $internacion = intval(str_replace(['.', ','], '', $row['K'] ?? 0));
                $total       = $emCon + $emSin + $urgencias + $derPub + $derPriv + $internacion;

                if (empty($ejercicio) || empty($mes) || empty($nombre)) continue;
                if (!is_numeric($ejercicio)) continue;

                if (!in_array($mes, $this->mesesValidos)) {
                    $errores[] = "Fila $rowNum: mes '$mes' no válido";
                    continue;
                }

                $nombreFinal = $this->buscarNombreBase($nombre, $nombresValidos);
                if (!$nombreFinal) {
                    $errores[] = "Fila $rowNum: nombre '$nombre' no coincide con ninguna base registrada";
                    continue;
                }

                $existe = $model
                    ->where('ejercicio', $ejercicio)
                    ->where('mes', $mes)
                    ->where('operativa', $operativa)
                    ->where('nombre', $nombreFinal)
                    ->first();

                if ($existe) {
                    $skipped++;
                    continue;
                }

                $grupoKey = "{$ejercicio}|{$mes}|{$operativa}";
                $nombresInsertados[$grupoKey] = $nombresInsertados[$grupoKey] ?? [];

                $nroCalculado = $this->calcularRomano(
                    $ejercicio,
                    $mes,
                    $operativa,
                    $nombreFinal,
                    $nombresInsertados[$grupoKey]
                );

                $model->insert([
                    'ejercicio'                => $ejercicio,
                    'mes'                      => $mes,
                    'operativa'                => $operativa,
                    'nro'                      => $nroCalculado,
                    'nombre'                   => $nombreFinal,
                    'emergencias_con_medico'   => $emCon,
                    'emergencias_sin_medico'   => $emSin,
                    'urgencias'                => $urgencias,
                    'derivacion_publica'       => $derPub,
                    'derivacion_privada'       => $derPriv,
                    'internacion_domiciliaria' => $internacion,
                    'total'                    => $total,
                    'estado'                   => 'activo',
                ]);

                $nombresInsertados[$grupoKey][] = $nombreFinal;
                $inserted++;
            }

            $msg = "Importación completada: $inserted registros importados.";
            if ($skipped)         $msg .= " $skipped duplicados omitidos.";
            if (!empty($errores)) $msg .= ' | Errores: ' . implode(' | ', $errores);

            return redirect()->to(route_to('asistencia_list'))
                ->with('msg', ['type' => 'success', 'body' => $msg]);

        } catch (\Exception $e) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ─── Buscar nombre más parecido en bases ──────────────────────────────
    private function buscarNombreBase(string $nombre, array $nombresValidos): ?string
    {
        $nombre = strtoupper(trim($nombre));

        if (in_array($nombre, $nombresValidos)) return $nombre;

        $mejorMatch = null;
        $mejorPct   = 0;
        foreach ($nombresValidos as $valido) {
            similar_text($nombre, $valido, $pct);
            if ($pct > $mejorPct) {
                $mejorPct   = $pct;
                $mejorMatch = $valido;
            }
        }

        return ($mejorPct >= 70) ? $mejorMatch : null;
    }

    // ─── Template Excel ───────────────────────────────────────────────────
    public function template()
    {
        $bases = model('BaseModel')->whereIn('estado', ['activo', 'desactivado'])->orderBy('nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Asistencias');

        $headers = [
            'A' => 'EJERCICIO',
            'B' => 'MES',
            'C' => 'OPERATIVA',
            'D' => 'N°',
            'E' => 'NOMBRE',
            'F' => 'EMERGENCIAS CON MEDICO',
            'G' => 'EMERGENCIAS SIN MEDICO',
            'H' => 'URGENCIAS',
            'I' => 'DERIVACION A PUBLICA',
            'J' => 'DERIVACION A PRIVADA',
            'K' => 'INTERNACIÓN DOMICILIARIA',
            'L' => 'TOTAL',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $sheet->setCellValue('A2', date('Y'));
        $sheet->setCellValue('B2', 'ENERO');
        $sheet->setCellValue('C2', 'BASE');
        $sheet->setCellValue('D2', '(auto)');
        $sheet->setCellValue('E2', $bases[0]->nombre ?? 'NOMBRE_BASE');
        foreach (['F', 'G', 'H', 'I', 'J', 'K'] as $col) {
            $sheet->setCellValue($col . '2', 0);
        }
        $sheet->setCellValue('L2', '=SUM(F2:K2)');

        $sheet->getComment('D2')->getText()->createTextRun(
            'El N° romano se calcula automáticamente al importar. Este campo puede dejarse vacío.'
        );

        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Nombres Válidos');
        $refSheet->setCellValue('A1', 'NOMBRES DE BASES VÁLIDOS');
        $refSheet->getStyle('A1')->getFont()->setBold(true);
        $refSheet->getStyle('A1')->getFill()
                 ->setFillType(Fill::FILL_SOLID)
                 ->getStartColor()->setRGB('13304d');
        $refSheet->getStyle('A1')->getFont()->getColor()->setRGB('FFFFFF');

        foreach ($bases as $i => $b) {
            $refSheet->setCellValue('A' . ($i + 2), $b->nombre);
        }
        $refSheet->getColumnDimension('A')->setAutoSize(true);

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $writer   = new Xlsx($spreadsheet);
        $filename = 'template_asistencias.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Exportar Excel con filtros ───────────────────────────────────────
    public function export()
    {
        $model = model('AsistenciaModel');
        $this->aplicarFiltros($model);

        $asistencias = $model
            ->orderBy('ejercicio', 'DESC')
            ->orderBy('FIELD(mes,' . implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos)) . ')', '')
            ->orderBy('operativa', 'ASC')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Operativa',
            'D' => 'N°',
            'E' => 'Nombre',
            'F' => 'Emerg. Con Médico',
            'G' => 'Emerg. Sin Médico',
            'H' => 'Urgencias',
            'I' => 'Deriv. Pública',
            'J' => 'Deriv. Privada',
            'K' => 'Intern. Domiciliaria',
            'L' => 'Total',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($asistencias as $i => $a) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $a->ejercicio);
            $sheet->setCellValue('B' . $row, $a->mes);
            $sheet->setCellValue('C' . $row, $a->operativa);
            $sheet->setCellValue('D' . $row, $a->nro);
            $sheet->setCellValue('E' . $row, $a->nombre);
            $sheet->setCellValue('F' . $row, $a->emergencias_con_medico);
            $sheet->setCellValue('G' . $row, $a->emergencias_sin_medico);
            $sheet->setCellValue('H' . $row, $a->urgencias);
            $sheet->setCellValue('I' . $row, $a->derivacion_publica);
            $sheet->setCellValue('J' . $row, $a->derivacion_privada);
            $sheet->setCellValue('K' . $row, $a->internacion_domiciliaria);
            $sheet->setCellValue('L' . $row, $a->total);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'asistencias_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Show / Edit ──────────────────────────────────────────────────────
    public function show($asistencia_id)
    {
        $model = model('AsistenciaModel');
        if (!$asistencia = $model->find($asistencia_id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $asistencia->setCampoOculto();

        return view('asistencia/asistencia_form', [
            'asistencia' => $asistencia,
            'formRoute'  => 'asistencia_update',
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update()
    {
        $id    = $this->request->getPost('asistencia_id');
        $model = model('AsistenciaModel');

        if (!$model->find($id)) {
            return redirect()->to(route_to('asistencia_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $ejercicio = $this->request->getPost('ejercicio');
        $mes       = $this->request->getPost('mes');
        $operativa = strtoupper(trim($this->request->getPost('operativa')));
        $nombre    = strtoupper(trim($this->request->getPost('nombre')));
        $emCon     = intval($this->request->getPost('em_con'));
        $emSin     = intval($this->request->getPost('em_sin'));
        $urg       = intval($this->request->getPost('urg'));
        $pub       = intval($this->request->getPost('pub'));
        $priv      = intval($this->request->getPost('priv'));
        $int       = intval($this->request->getPost('int'));
        $total     = $emCon + $emSin + $urg + $pub + $priv + $int;

        $existentes = $model
            ->select('nombre')
            ->where('ejercicio', $ejercicio)
            ->where('mes', $mes)
            ->where('operativa', $operativa)
            ->where('asistencia_id !=', $id)
            ->whereIn('estado', ['activo', 'desactivado'])
            ->findAll();

        $todos = array_unique(array_merge(
            array_map(fn($r) => $r->nombre, $existentes),
            [$nombre]
        ));
        sort($todos);
        $nro = $this->toRoman(array_search($nombre, $todos) + 1);

        $model->update($id, [
            'ejercicio'                => $ejercicio,
            'mes'                      => $mes,
            'operativa'                => $operativa,
            'nro'                      => $nro,
            'nombre'                   => $nombre,
            'emergencias_con_medico'   => $emCon,
            'emergencias_sin_medico'   => $emSin,
            'urgencias'                => $urg,
            'derivacion_publica'       => $pub,
            'derivacion_privada'       => $priv,
            'internacion_domiciliaria' => $int,
            'total'                    => $total,
        ]);

        return redirect()->to(route_to('asistencia_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Asistencia actualizada correctamente']);
    }

    // ─── Destroy (soft delete) ────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('AsistenciaModel');

        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }

        return redirect()->to(route_to('asistencia_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}