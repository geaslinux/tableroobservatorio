<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CallCenterController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

// ─── Filtros reutilizables ───────────────────────────────────────────
private function aplicarFiltros($model, $ejercicios = [])
{
    $ejercicioRaw   = $this->request->getGet('ejercicio'); // null = primera carga
    $filtro_mes     = $this->request->getGet('mes')    ?? '';
    $filtro_estado  = $this->request->getGet('estado') ?? '';
    $filtro_desde   = $this->fechaValida($this->request->getGet('desde'));
    $filtro_hasta   = $this->fechaValida($this->request->getGet('hasta'));

    // Si es la primera carga (sin GET), se usa por defecto el último ejercicio cargado
    if ($ejercicioRaw === null) {
        $filtro_ejercicio = $ejercicios[0] ?? '';
    } else {
        $filtro_ejercicio = $ejercicioRaw;
    }

    if ($filtro_ejercicio !== '') $model->where('call_center.ejercicio', $filtro_ejercicio);
    if ($filtro_mes)              $model->where('call_center.mes', $filtro_mes);
    if ($filtro_desde !== '')     $model->where('call_center.fecha >=', $filtro_desde);
    if ($filtro_hasta !== '')     $model->where('call_center.fecha <=', $filtro_hasta);

    if ($filtro_estado != '') {
        $model->where('call_center.estado', $filtro_estado);
    } else {
        $model->whereIn('call_center.estado', ['activo', 'desactivado']);
    }

    return compact('filtro_ejercicio', 'filtro_mes', 'filtro_estado', 'filtro_desde', 'filtro_hasta');
}

// Fecha del filtro de período (AAAA-MM-DD); cualquier otro valor se ignora
private function fechaValida($valor): string
{
    $valor = trim((string) $valor);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor) ? $valor : '';
}

public function index()
{
    helper('auth');

    $ejercicios = array_column(
        model('CallCenterModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
        'ejercicio'
    );

    // ── Registros con los filtros (una sola consulta; el resto se arma a partir de ella) ──
    $model   = model('CallCenterModel');
    $filtros = $this->aplicarFiltros($model, $ejercicios);
    $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));

    $registros = $model->orderBy('ejercicio', 'ASC')
                       ->orderBy("FIELD(mes,{$ordenMeses})", '')
                       ->orderBy('fecha', 'ASC')
                       ->findAll();

    // El total de cada fila es atendidos + abandonadas (así cierra siempre con sus partes)
    $porMes = $porDia = $porEjercicio = [];
    $totalAtendidos = $totalAbandonadas = 0;
    foreach ($registros as $r) {
        $at = (int) $r->atendidos;
        $ab = (int) $r->abandonadas;
        $totalAtendidos   += $at;
        $totalAbandonadas += $ab;

        $kMes = $r->ejercicio . '|' . $r->mes;
        $porMes[$kMes] = $porMes[$kMes] ?? ['ejercicio' => $r->ejercicio, 'mes' => $r->mes, 'atendidos' => 0, 'abandonadas' => 0];
        $porMes[$kMes]['atendidos']   += $at;
        $porMes[$kMes]['abandonadas'] += $ab;

        $kDia = $r->fecha ?: ('sin-fecha|' . $r->ejercicio);
        $porDia[$kDia] = $porDia[$kDia] ?? ['ejercicio' => $r->ejercicio, 'fecha' => $r->fecha, 'atendidos' => 0, 'abandonadas' => 0];
        $porDia[$kDia]['atendidos']   += $at;
        $porDia[$kDia]['abandonadas'] += $ab;

        $porEjercicio[$r->ejercicio] = ($porEjercicio[$r->ejercicio] ?? 0) + $at + $ab;
    }

    $conTotal = function (array $filas) {
        return array_values(array_map(function ($f) {
            $f['total'] = $f['atendidos'] + $f['abandonadas'];
            return $f;
        }, $filas));
    };
    $porMes = $conTotal($porMes);                          // orden cronológico
    $porDia = $conTotal($porDia);
    usort($porDia, function ($a, $b) {                     // lo más reciente primero (sin fecha al final)
        return strcmp((string) $b['fecha'], (string) $a['fecha']);
    });

    $totalGeneral = $totalAtendidos + $totalAbandonadas;
    $diasConDatos = count(array_filter($porDia, fn($d) => !empty($d['fecha'])));
    $diaPico = null;
    foreach ($porDia as $d) {
        if (!empty($d['fecha']) && ($diaPico === null || $d['total'] > $diaPico['total'])) $diaPico = $d;
    }

    $kpi = [
        'total'           => $totalGeneral,
        'atendidos'       => $totalAtendidos,
        'abandonadas'     => $totalAbandonadas,
        'pct_atendidos'   => $totalGeneral > 0 ? round($totalAtendidos * 100 / $totalGeneral, 1) : 0,
        'pct_abandonadas' => $totalGeneral > 0 ? round($totalAbandonadas * 100 / $totalGeneral, 1) : 0,
        'dias'            => $diasConDatos,
        'promedio_diario' => $diasConDatos > 0 ? (int) round($totalGeneral / $diasConDatos) : 0,
        'dia_pico'        => $diaPico,
    ];

    // Todos los ejercicios de la BD, para que cada año tenga siempre el mismo color
    $todosLosEjercicios = array_values(array_map('intval', $ejercicios));
    sort($todosLosEjercicios);

    return view('call_center/call_center_list', array_merge($filtros, [
        'ejercicios'         => $ejercicios,
        'todosLosEjercicios' => $todosLosEjercicios,
        'meses'              => $this->mesesValidos,
        'kpi'                => $kpi,
        'porMes'             => $porMes,
        'porDia'             => $porDia,
        'porEjercicio'       => $porEjercicio,
    ]));
}
    // ─── Marcar visto ────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user()->id;
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $reg        = $visitModel->where('user_id', $userId)->where('modulo', 'call_center')->first();

        if ($reg) {
            $visitModel->where('user_id', $userId)->where('modulo', 'call_center')
                       ->set(['ultima_vista' => $ahora])->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'call_center', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('call_center_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('call_center/call_center_form', [
            'meses' => $this->mesesValidos,
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

        $atendidos   = (int) $this->request->getPost('atendidos');
        $abandonadas = (int) $this->request->getPost('abandonadas');

        model('CallCenterModel')->insert([
            'ejercicio'   => $this->request->getPost('ejercicio'),
            'mes'         => $this->request->getPost('mes'),
            'fecha'       => $this->request->getPost('fecha') ?: null,
            'atendidos'   => $atendidos,
            'abandonadas' => $abandonadas,
            'total'       => $atendidos + $abandonadas,
            'estado'      => 'activo',
        ]);

        return redirect()->to(route_to('call_center_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('CallCenterModel')->find($id);

        if (!$registro) {
            return redirect()->to(route_to('call_center_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('call_center/call_center_form', [
            'registro' => $registro,
            'meses'    => $this->mesesValidos,
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

        $id          = $this->request->getPost('call_center_id');
        $atendidos   = (int) $this->request->getPost('atendidos');
        $abandonadas = (int) $this->request->getPost('abandonadas');

        model('CallCenterModel')->update($id, [
            'ejercicio'   => $this->request->getPost('ejercicio'),
            'mes'         => $this->request->getPost('mes'),
            'fecha'       => $this->request->getPost('fecha') ?: null,
            'atendidos'   => $atendidos,
            'abandonadas' => $abandonadas,
            'total'       => $atendidos + $abandonadas,
            'estado'      => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('call_center_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Días hábiles hacia atrás ───────────────────────────────────────
    private function fechaMinima(): string
    {
        $fecha       = new \DateTime();
        $diasHabiles = 0;

        while ($diasHabiles < 3) {
            $fecha->modify('-1 day');
            $diaSemana = (int) $fecha->format('N'); // 1=lunes ... 7=domingo
            if ($diaSemana <= 5) {
                $diasHabiles++;
            }
        }

        return $fecha->format('Y-m-d');
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual  = (int) date('Y');
        $fechaMinima = $this->fechaMinima();
        $fechaHoy    = date('Y-m-d');
        $fechaPost   = $this->request->getPost('fecha');

        // Validar fecha manualmente (CI4 no tiene after_or_equal nativo)
        $errorFecha = null;
        if (!empty($fechaPost)) {
            if ($fechaPost < $fechaMinima) {
                $errorFecha = "La fecha mínima permitida es " . date('d/m/Y', strtotime($fechaMinima)) . " (3 días hábiles atrás).";
            } elseif ($fechaPost > $fechaHoy) {
                $errorFecha = "La fecha no puede ser futura.";
            }
        }

        $ok = $this->validate([
            'ejercicio' => "required|integer|less_than_equal_to[{$anioActual}]",
            'mes'       => 'required',
        ], [
            'ejercicio' => ['less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}."],
            'mes'       => ['required' => 'Debe seleccionar un mes.'],
        ]);

        if ($errorFecha) {
            $this->validator->setError('fecha', $errorFecha);
            $ok = false;
        }

        return $ok;
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('CallCenterModel');
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));
        $registros  = $model->orderBy('ejercicio', 'DESC')
                            ->orderBy("FIELD(mes,{$ordenMeses})", '')
                            ->orderBy('fecha', 'ASC')
                            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Call Center');

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Fecha',
            'D' => 'Atendidos',
            'E' => 'Abandonadas',
            'F' => 'Total',
            'G' => '% Atendidos',
            'H' => '% Abandonadas',
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
            $tot  = (int) $r->total;
            $pAt  = $tot > 0 ? round(($r->atendidos   / $tot) * 100, 2) : 0;
            $pAb  = $tot > 0 ? round(($r->abandonadas / $tot) * 100, 2) : 0;

            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->mes);
            $sheet->setCellValue('C' . $row, $r->fecha ? date('d/m/Y', strtotime($r->fecha)) : '');
            $sheet->setCellValue('D' . $row, (int) $r->atendidos);
            $sheet->setCellValue('E' . $row, (int) $r->abandonadas);
            $sheet->setCellValue('F' . $row, $tot);
            $sheet->setCellValue('G' . $row, $pAt  . '%');
            $sheet->setCellValue('H' . $row, $pAb  . '%');
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'call_center_' . date('Ymd_His') . '.xlsx';

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
        $model = model('CallCenterModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('call_center_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}