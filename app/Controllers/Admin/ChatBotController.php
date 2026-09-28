<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ChatBotController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_mes       = $this->request->getGet('mes')       ?? '';
        $filtro_efector   = $this->request->getGet('efector')   ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        if ($filtro_ejercicio) $model->where('chat_bot.ejercicio',  $filtro_ejercicio);
        if ($filtro_mes)       $model->where('chat_bot.mes',        $filtro_mes);
        if ($filtro_efector)   $model->where('chat_bot.efector_id', $filtro_efector);
        if ($filtro_region)    $model->where('efector.region',      $filtro_region);

        if ($filtro_estado != '') {
            $model->where('chat_bot.estado', $filtro_estado);
        } else {
            $model->whereIn('chat_bot.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_mes', 'filtro_efector', 'filtro_region', 'filtro_estado');
    }

    // ─── Index ───────────────────────────────────────────────────────────
   // ─── Index (estadísticas, sin CRUD) ─────────────────────────────────
   // ─── Index (estadísticas, sin CRUD) ─────────────────────────────────
    public function index()
    {
        helper('auth');

        $ejercicios = array_column(
            model('ChatBotModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones  = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        // Instancia limpia solo para capturar los valores de filtro
        $modelFiltros = new \App\Models\ChatBotModel();
        $filtros      = $this->aplicarFiltros($modelFiltros);

        // ── KPIs por región ──
        $mTotalesRegion = new \App\Models\ChatBotModel();
        $this->aplicarFiltros($mTotalesRegion);
        $filasRegion = $mTotalesRegion
            ->join('efector', 'efector.efector_id = chat_bot.efector_id', 'left')
            ->asArray()
            ->select('efector.region as region, SUM(chat_bot.turnos_otorgados) as total')
            ->groupBy('efector.region')
            ->orderBy('efector.region', 'ASC')
            ->findAll();

        $totalesPorRegion = [];
        foreach ($filasRegion as $fila) {
            $totalesPorRegion[$fila['region'] ?? '—'] = (int) $fila['total'];
        }

        $mTotal = new \App\Models\ChatBotModel();
        $this->aplicarFiltros($mTotal);
        $resTotal     = $mTotal->selectSum('turnos_otorgados')->first();
        $totalGeneral = (int) ($resTotal->turnos_otorgados ?? 0);

        // ── Tabla detalle: región / ejercicio / cantidad ──
        $mTabla = new \App\Models\ChatBotModel();
        $this->aplicarFiltros($mTabla);
        $statsFilas = $mTabla
            ->join('efector', 'efector.efector_id = chat_bot.efector_id', 'left')
            ->asArray()
            ->select('efector.region as region, chat_bot.ejercicio as ejercicio, SUM(chat_bot.turnos_otorgados) as cantidad')
            ->groupBy('efector.region, chat_bot.ejercicio')
            ->orderBy('chat_bot.ejercicio', 'DESC')
            ->orderBy('efector.region', 'ASC')
            ->findAll();

        // ── Ejercicios recientes (para barras) ──
        $ejerciciosDisponibles = array_values(array_unique(array_column($statsFilas, 'ejercicio')));
        rsort($ejerciciosDisponibles);
        $statsEjercicios = array_slice($ejerciciosDisponibles, 0, 2);

        // ── Barras: región vs ejercicios recientes ──
        $statsBarras = array_values(array_filter($statsFilas, function ($f) use ($statsEjercicios) {
            return in_array($f['ejercicio'], $statsEjercicios);
        }));

        // ── Torta: distribución por ejercicio (todos) ──
        $mTorta = new \App\Models\ChatBotModel();
        $this->aplicarFiltros($mTorta);
        $statsTorta = $mTorta->asArray()
            ->select('chat_bot.ejercicio as ejercicio, SUM(chat_bot.turnos_otorgados) as total')
            ->groupBy('chat_bot.ejercicio')
            ->orderBy('chat_bot.ejercicio', 'DESC')
            ->findAll();

        return view('chat_bot/chat_bot_list', array_merge($filtros, [
            'ejercicios'       => $ejercicios,
            'efectores'        => $efectores,
            'regiones'         => $regiones,
            'meses'            => $this->mesesValidos,
            'totalesPorRegion' => $totalesPorRegion,
            'totalGeneral'     => $totalGeneral,
            'statsFilas'       => $statsFilas,
            'statsBarras'      => $statsBarras,
            'statsEjercicios'  => $statsEjercicios,
            'statsTorta'       => $statsTorta,
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
                                 ->where('modulo', 'chat_bot')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'chat_bot')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'chat_bot',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('chat_bot_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        return view('chat_bot/chat_bot_form', [
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

        $fecha = $this->request->getPost('fecha');

        model('ChatBotModel')->insert([
            'ejercicio'        => $this->request->getPost('ejercicio'),
            'mes'              => $this->request->getPost('mes'),
            'fecha'            => $fecha ?: null,
            'efector_id'       => $this->request->getPost('efector_id'),
            'turnos_otorgados' => (int) $this->request->getPost('turnos_otorgados'),
            'observacion'      => $this->request->getPost('observacion') ?: null,
            'estado'           => 'activo',
        ]);

        return redirect()->to(route_to('chat_bot_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('ChatBotModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('chat_bot_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        return view('chat_bot/chat_bot_form', [
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

        $id = $this->request->getPost('chat_bot_id');

        model('ChatBotModel')->update($id, [
            'ejercicio'        => $this->request->getPost('ejercicio'),
            'mes'              => $this->request->getPost('mes'),
            'fecha'            => $this->request->getPost('fecha') ?: null,
            'efector_id'       => $this->request->getPost('efector_id'),
            'turnos_otorgados' => (int) $this->request->getPost('turnos_otorgados'),
            'observacion'      => $this->request->getPost('observacion') ?: null,
            'estado'           => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('chat_bot_list'))
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
            'ejercicio'  => "required|integer|less_than_equal_to[{$anioActual}]",
            'mes'        => 'required',
            'efector_id' => 'required|integer',
        ], [
            'ejercicio'  => ['less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}."],
            'mes'        => ['required' => 'Debe seleccionar un mes.'],
            'efector_id' => ['required' => 'Debe seleccionar un hospital.'],
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
        $model = model('ChatBotModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));
        $registros  = $model->orderBy('chat_bot.ejercicio', 'DESC')
                            ->orderBy("FIELD(chat_bot.mes,{$ordenMeses})", '')
                            ->orderBy('chat_bot.fecha', 'ASC')
                            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chat Bot');

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Mes',
            'C' => 'Fecha',
            'D' => 'Hospital',
            'E' => 'Nivel de Complejidad',
            'F' => 'Región',
            'G' => 'Turnos Otorgados',
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
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->mes);
            $sheet->setCellValue('C' . $row, $r->fecha ? date('d/m/Y', strtotime($r->fecha)) : '');
            $sheet->setCellValue('D' . $row, $r->efector_nombre);
            $sheet->setCellValue('E' . $row, $r->efector_nivel ?? '');
            $sheet->setCellValue('F' . $row, $r->efector_region ?? '');
            $sheet->setCellValue('G' . $row, $r->turnos_otorgados);
            $sheet->setCellValue('H' . $row, $r->observacion ?? '');
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'chat_bot_' . date('Ymd_His') . '.xlsx';

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
        $model = model('ChatBotModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('chat_bot_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}