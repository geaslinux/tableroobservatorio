<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TurnoHospitalarioController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    private $campos = ['turnos_atendidos', 'ausentes', 'cancelados', 'sin_codificar'];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_mes       = $this->request->getGet('mes')       ?? '';
        $filtro_efector   = $this->request->getGet('efector')   ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        if ($filtro_ejercicio) $model->where('turno_hospitalario.ejercicio',  $filtro_ejercicio);
        if ($filtro_mes)       $model->where('turno_hospitalario.mes',        $filtro_mes);
        if ($filtro_efector)   $model->where('turno_hospitalario.efector_id', $filtro_efector);
        if ($filtro_region)    $model->where('efector.region',                $filtro_region);

        if ($filtro_estado != '') {
            $model->where('turno_hospitalario.estado', $filtro_estado);
        } else {
            $model->whereIn('turno_hospitalario.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_efector', 'filtro_region', 'filtro_estado');
    }

    // ─── Index (estadísticas de gestión de especialidades) ─────────────
    public function index()
    {
        helper('auth');

        $ejercicios = array_column(
            model('TurnoHospitalarioModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        // Solo los efectores que tienen turnos cargados
        $efectores = model('EfectorModel')
            ->select('efector.efector_id, efector.nombre')
            ->join('turno_hospitalario', 'turno_hospitalario.efector_id = efector.efector_id')
            ->groupBy('efector.efector_id, efector.nombre')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $regiones = array_values(array_filter(array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        )));

        // ── Una sola consulta filtrada (con efector unido para poder filtrar por región) ──
        $model   = new \App\Models\TurnoHospitalarioModel();
        $model->join('efector', 'efector.efector_id = turno_hospitalario.efector_id', 'left');
        $filtros = $this->aplicarFiltros($model);
        $filas   = $model->asArray()
            ->select('turno_hospitalario.ejercicio, turno_hospitalario.mes,
                      turno_hospitalario.efector_id, efector.nombre AS efector, efector.region,
                      SUM(turno_hospitalario.turnos_atendidos) AS atendidos,
                      SUM(turno_hospitalario.ausentes)         AS ausentes,
                      SUM(turno_hospitalario.cancelados)       AS cancelados,
                      SUM(turno_hospitalario.sin_codificar)    AS sin_codificar')
            ->groupBy('turno_hospitalario.ejercicio, turno_hospitalario.mes, turno_hospitalario.efector_id, efector.nombre, efector.region')
            ->findAll();

        $ordenMes  = array_flip($this->mesesValidos);
        $vacio     = ['atendidos' => 0, 'ausentes' => 0, 'cancelados' => 0, 'sin_codificar' => 0, 'total' => 0];
        $porFila   = [];   // mes + efector
        $porMes    = [];
        $porEfector = [];
        $totales   = $vacio;

        foreach ($filas as $f) {
            $v = [
                'atendidos'     => (int) $f['atendidos'],
                'ausentes'      => (int) $f['ausentes'],
                'cancelados'    => (int) $f['cancelados'],
                'sin_codificar' => (int) $f['sin_codificar'],
            ];
            // Total otorgados = suma de sus partes (así los % siempre cierran en 100)
            $v['total'] = array_sum($v);
            $nombre = $f['efector'] ?: 'SIN EFECTOR';

            $porFila[] = ['ejercicio' => (string) $f['ejercicio'], 'mes' => $f['mes'], 'efector' => $nombre] + $v;

            $clave = $f['ejercicio'] . '|' . $f['mes'];
            if (!isset($porMes[$clave])) $porMes[$clave] = ['ejercicio' => (string) $f['ejercicio'], 'mes' => $f['mes']] + $vacio;
            if (!isset($porEfector[$nombre])) $porEfector[$nombre] = $vacio;
            foreach ($v as $k => $n) {
                $porMes[$clave][$k]    += $n;
                $porEfector[$nombre][$k] += $n;
                $totales[$k]           += $n;
            }
        }

        $cronologico = function ($a, $b) use ($ordenMes) {
            return [(int) $a['ejercicio'], $ordenMes[$a['mes']] ?? 99] <=> [(int) $b['ejercicio'], $ordenMes[$b['mes']] ?? 99];
        };
        // Tabla: el mes más reciente primero y, dentro del mes, los efectores con más turnos
        usort($porFila, function ($a, $b) use ($cronologico) {
            return $cronologico($b, $a) ?: ($b['total'] <=> $a['total']);
        });
        $porMes = array_values($porMes);
        usort($porMes, $cronologico);
        uasort($porEfector, function ($a, $b) { return $b['total'] <=> $a['total']; });

        // ── KPIs ──
        $pct = function ($n) use ($totales) { return $totales['total'] > 0 ? $n * 100 / $totales['total'] : 0; };
        $efectorTop = array_key_first($porEfector);
        $kpi = $totales + [
            'pct_atendidos'     => $pct($totales['atendidos']),
            'pct_ausentes'      => $pct($totales['ausentes']),
            'pct_cancelados'    => $pct($totales['cancelados']),
            'pct_sin_codificar' => $pct($totales['sin_codificar']),
            'efectores'         => count($porEfector),
            'efector_top'       => $efectorTop,
            'efector_top_n'     => $efectorTop !== null ? $porEfector[$efectorTop]['total'] : 0,
        ];

        // Todos los ejercicios de la base (para asignar el mismo color azul siempre)
        $todosLosEjercicios = array_map('intval', $ejercicios);
        sort($todosLosEjercicios);

        // ── Última vista ──
        $reg         = model('UserLastVisitModel')->where('user_id', user_id())->where('modulo', 'turno_hospitalario')->first();
        $ultimaVista = $reg ? $reg->ultima_vista : '2000-01-01 00:00:00';

        return view('turno_hospitalario/turno_hospitalario_list', array_merge($filtros, [
            'ejercicios'         => $ejercicios,
            'efectores'          => $efectores,
            'regiones'           => $regiones,
            'meses'              => $this->mesesValidos,
            'porFila'            => $porFila,
            'porMes'             => $porMes,
            'porEfector'         => $porEfector,
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
        $reg        = $visitModel->where('user_id', $userId)->where('modulo', 'turno_hospitalario')->first();

        if ($reg) {
            $visitModel->where('user_id', $userId)->where('modulo', 'turno_hospitalario')
                       ->set(['ultima_vista' => $ahora])->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'turno_hospitalario', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('turno_hospitalario_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('turno_hospitalario/turno_hospitalario_form', [
            'efectores' => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'meses'     => $this->mesesValidos,
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

        // Verificar duplicado ejercicio+mes+efector
        $existe = model('TurnoHospitalarioModel')
            ->where('ejercicio',  $this->request->getPost('ejercicio'))
            ->where('mes',        $this->request->getPost('mes'))
            ->where('efector_id', $this->request->getPost('efector_id'))
            ->whereIn('estado',   ['activo', 'desactivado'])
            ->first();

        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['efector_id' => 'Ya existe un registro para ese ejercicio, mes y efector.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro para ese ejercicio, mes y efector.']);
        }

        $data = [
            'ejercicio'  => $this->request->getPost('ejercicio'),
            'mes'        => $this->request->getPost('mes'),
            'efector_id' => $this->request->getPost('efector_id'),
            'estado'     => 'activo',
        ];

        $total = 0;
        foreach ($this->campos as $campo) {
            $val         = (int) $this->request->getPost($campo);
            $data[$campo] = $val;
            $total       += $val;
        }
        $data['total_otorgados'] = $total;

        model('TurnoHospitalarioModel')->insert($data);

        return redirect()->to(route_to('turno_hospitalario_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('TurnoHospitalarioModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('turno_hospitalario_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('turno_hospitalario/turno_hospitalario_form', [
            'registro'  => $registro,
            'efectores' => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'meses'     => $this->mesesValidos,
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

        $id = $this->request->getPost('turno_id');

        $data = [
            'ejercicio'  => $this->request->getPost('ejercicio'),
            'mes'        => $this->request->getPost('mes'),
            'efector_id' => $this->request->getPost('efector_id'),
            'estado'     => $this->request->getPost('estado') ?? 'activo',
        ];

        $total = 0;
        foreach ($this->campos as $campo) {
            $val         = (int) $this->request->getPost($campo);
            $data[$campo] = $val;
            $total       += $val;
        }
        $data['total_otorgados'] = $total;

        model('TurnoHospitalarioModel')->update($id, $data);

        return redirect()->to(route_to('turno_hospitalario_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual = (int) date('Y');

        return $this->validate([
            'ejercicio'  => "required|integer|less_than_equal_to[{$anioActual}]",
            'mes'        => 'required',
            'efector_id' => 'required|integer',
        ], [
            'ejercicio'  => ['less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}."],
            'mes'        => ['required'            => 'Debe seleccionar un mes.'],
            'efector_id' => ['required'            => 'Debe seleccionar un efector.'],
        ]);
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('TurnoHospitalarioModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));
        $registros  = $model->orderBy('turno_hospitalario.ejercicio', 'DESC')
                            ->orderBy("FIELD(turno_hospitalario.mes,{$ordenMeses})", '')
                            ->orderBy('efector.nombre', 'ASC')
                            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Turnos Hospitalarios');

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Efector',
            'D' => 'Nivel de Complejidad',
            'E' => 'Turnos Atendidos',
            'F' => 'Ausentes',
            'G' => 'Cancelados',
            'H' => 'Sin Codificar',
            'I' => 'Total Otorgados',
            'J' => '% Atendidos',
            'K' => '% Ausentes',
            'L' => '% Cancelados',
            'M' => '% Sin Codificar',
            'N' => '% Total',
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
            $row  = $i + 2;
            $pct  = ($r->total_otorgados > 0)
                  ? round(($r->turnos_atendidos / $r->total_otorgados) * 100, 1)
                  : 0;

            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->mes);
            $sheet->setCellValue('C' . $row, $r->efector_nombre);
            $sheet->setCellValue('D' . $row, $r->efector_nivel ?? '');
            $sheet->setCellValue('E' . $row, $r->turnos_atendidos);
            $sheet->setCellValue('F' . $row, $r->ausentes);
            $sheet->setCellValue('G' . $row, $r->cancelados);
            $sheet->setCellValue('H' . $row, $r->sin_codificar);
            $sheet->setCellValue('I' . $row, $r->total_otorgados);
            $tot = $r->total_otorgados;
            $pAtendidos  = $tot > 0 ? round(($r->turnos_atendidos / $tot) * 100, 2) : 0;
            $pAusentes   = $tot > 0 ? round(($r->ausentes         / $tot) * 100, 2) : 0;
            $pCancelados = $tot > 0 ? round(($r->cancelados       / $tot) * 100, 2) : 0;
            $pSinCod     = $tot > 0 ? round(($r->sin_codificar    / $tot) * 100, 2) : 0;
            $pTotal      = $tot > 0 ? round($pAtendidos + $pAusentes + $pCancelados + $pSinCod, 2) : 0;
            $sheet->setCellValue('J' . $row, $pAtendidos  . '%');
            $sheet->setCellValue('K' . $row, $pAusentes   . '%');
            $sheet->setCellValue('L' . $row, $pCancelados . '%');
            $sheet->setCellValue('M' . $row, $pSinCod     . '%');
            $sheet->setCellValue('N' . $row, $pTotal      . '%');
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'turnos_hospitalarios_' . date('Ymd_His') . '.xlsx';

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
        $model = model('TurnoHospitalarioModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('turno_hospitalario_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}