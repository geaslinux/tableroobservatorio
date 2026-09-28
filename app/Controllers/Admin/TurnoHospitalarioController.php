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

    // ─── Index ───────────────────────────────────────────────────────────
   // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $ejercicios = array_column(
            model('TurnoHospitalarioModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones  = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        // ── Auditoría ─────────────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $reg         = $visitModel->where('user_id', $userId)->where('modulo', 'turno_hospitalario')->first();
        $ultimaVista = $reg ? $reg->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('TurnoHospitalarioModel')
            ->whereIn('turno_hospitalario.estado', ['activo', 'desactivado'])
            ->where('turno_hospitalario.created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('TurnoHospitalarioModel')
            ->whereIn('turno_hospitalario.estado', ['activo', 'desactivado'])
            ->where('turno_hospitalario.updated_at >', $ultimaVista)
            ->where('turno_hospitalario.updated_at != turno_hospitalario.created_at')
            ->countAllResults();

        // ── Definir orden de meses para consultas ─────────────────────────
        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));

        // ── Filtros (para valores del form) ───────────────────────────────
        $modelFiltros = new \App\Models\TurnoHospitalarioModel();
        $filtros      = $this->aplicarFiltros($modelFiltros);

        // ── KPIs por región (sin conRelaciones, join manual) ──────────────
        $mTotalesRegion = new \App\Models\TurnoHospitalarioModel();
        $this->aplicarFiltros($mTotalesRegion);
        $filasRegion = $mTotalesRegion
            ->join('efector', 'efector.efector_id = turno_hospitalario.efector_id', 'left')
            ->asArray()
            ->select('efector.region as region,
                      SUM(turno_hospitalario.turnos_atendidos + turno_hospitalario.ausentes
                          + turno_hospitalario.cancelados + turno_hospitalario.sin_codificar) as total')
            ->groupBy('efector.region')
            ->orderBy('efector.region', 'ASC')
            ->findAll();

        $totalesPorRegion = [];
        foreach ($filasRegion as $fila) {
            $totalesPorRegion[$fila['region'] ?? '—'] = (int) $fila['total'];
        }

        // ── Total general y por categoría ─────────────────────────────────
        $mTotal = new \App\Models\TurnoHospitalarioModel();
        $this->aplicarFiltros($mTotal);
        $resTotal = $mTotal->asArray()
            ->select('SUM(turnos_atendidos)  as tot_atendidos,
                      SUM(ausentes)          as tot_ausentes,
                      SUM(cancelados)        as tot_cancelados,
                      SUM(sin_codificar)     as tot_sin_codificar')
            ->first();

        $totAtendidos    = (int) ($resTotal['tot_atendidos']    ?? 0);
        $totAusentes     = (int) ($resTotal['tot_ausentes']     ?? 0);
        $totCancelados   = (int) ($resTotal['tot_cancelados']   ?? 0);
        $totSinCodificar = (int) ($resTotal['tot_sin_codificar'] ?? 0);
        $totalGeneral    = $totAtendidos + $totAusentes + $totCancelados + $totSinCodificar;

        // ── Tabla detalle: región / ejercicio / total ─────────────────────
        $mTabla = new \App\Models\TurnoHospitalarioModel();
        $this->aplicarFiltros($mTabla);
        $statsFilas = $mTabla
            ->join('efector', 'efector.efector_id = turno_hospitalario.efector_id', 'left')
            ->asArray()
            ->select('efector.region as region, turno_hospitalario.ejercicio as ejercicio,
                      SUM(turno_hospitalario.turnos_atendidos + turno_hospitalario.ausentes
                          + turno_hospitalario.cancelados + turno_hospitalario.sin_codificar) as cantidad')
            ->groupBy('efector.region, turno_hospitalario.ejercicio')
            ->orderBy('turno_hospitalario.ejercicio', 'DESC')
            ->orderBy('efector.region', 'ASC')
            ->findAll();

        // ── Obtener filtro de ejercicio ──────────────────────────────────────
        $filtro_ejercicio = $filtros['filtro_ejercicio'] ?? '';

        // ── Ejercicios disponibles (para barras) ────────────────────────────
        $ejerciciosDisponibles = array_values(array_unique(array_column($statsFilas, 'ejercicio')));
        rsort($ejerciciosDisponibles);

        $statsEjercicios = ($filtro_ejercicio !== '')
            ? [$filtro_ejercicio]
            : $ejerciciosDisponibles;

        // ── Barras / Torta (si se filtra ejercicio y mes, mostrar datos específicos) ────────
        $statsBarras = [];
        $statsTorta  = [];
        $statsMeses  = [];

        if ($filtros['filtro_ejercicio'] !== '' && $filtros['filtro_mes'] !== '') {
            // Cuando se filtra por ejercicio y mes específico
            $mDatos = new \App\Models\TurnoHospitalarioModel();
            $this->aplicarFiltros($mDatos);
            $statsDatos = $mDatos->asArray()
                ->select('ejercicio, mes, SUM(turnos_atendidos + ausentes + cancelados + sin_codificar) as total')
                ->groupBy('ejercicio, mes')
                ->orderBy("FIELD(mes,{$ordenMeses})", '')
                ->findAll();

            $statsBarras = $statsDatos;
            $statsTorta  = $statsDatos;
        } elseif ($filtros['filtro_ejercicio'] !== '' && $filtros['filtro_mes'] === '') {
            // Cuando se filtra solo por ejercicio (mostrar todos los meses)
            $mMes = new \App\Models\TurnoHospitalarioModel();
            $this->aplicarFiltros($mMes);
            $statsMeses = $mMes->asArray()
                ->select('mes, ejercicio, SUM(turnos_atendidos + ausentes + cancelados + sin_codificar) as total')
                ->groupBy('mes, ejercicio')
                ->orderBy("FIELD(mes,{$ordenMeses})", '')
                ->findAll();

            $statsBarras = $statsMeses;
            $statsTorta  = $statsMeses;
        } else {
            // ── Barras: región vs ejercicios recientes (si no hay filtro de ejercicio específico) ────────────────────────
            $statsBarras = array_values(array_filter($statsFilas, function ($f) use ($statsEjercicios) {
                return in_array($f['ejercicio'], $statsEjercicios);
            }));

            // ── Torta: distribución por ejercicio (todos) ─────────────────────
            $mTorta = new \App\Models\TurnoHospitalarioModel();
            $this->aplicarFiltros($mTorta);
            $statsTorta = $mTorta->asArray()
                ->select('ejercicio,
                          SUM(turnos_atendidos + ausentes + cancelados + sin_codificar) as total')
                ->groupBy('ejercicio')
                ->orderBy('ejercicio', 'DESC')
                ->findAll();
        }

        // ── Listado paginado (con relaciones, sin agregación) ─────────────
        $model = model('TurnoHospitalarioModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $ordenMeses = implode(',', array_map(fn($m) => "'$m'", $this->mesesValidos));

        $registros = $model->orderBy('turno_hospitalario.ejercicio', 'DESC')
                            ->orderBy("FIELD(turno_hospitalario.mes,{$ordenMeses})")
                            ->orderBy('efector.nombre', 'ASC')
                            ->paginate($perPage);

        return view('turno_hospitalario/turno_hospitalario_list', array_merge($filtros, [
            'registros'        => $registros,
            'pager'            => $model->pager,
            'ejercicios'       => $ejercicios,
            'efectores'        => $efectores,
            'regiones'         => $regiones,
            'meses'            => $this->mesesValidos,
            'nuevas'           => $nuevas,
            'modificadas'      => $modificadas,
            'ultimaVista'      => $ultimaVista,
            'totalesPorRegion' => $totalesPorRegion,
            'totalGeneral'     => $totalGeneral,
            'totAtendidos'     => $totAtendidos,
            'totAusentes'      => $totAusentes,
            'totCancelados'    => $totCancelados,
            'totSinCodificar'  => $totSinCodificar,
            'statsFilas'       => $statsFilas,
            'statsBarras'      => $statsBarras,
            'statsEjercicios'  => $statsEjercicios,
            'statsTorta'       => $statsTorta,
            'statsMeses'       => $statsMeses,
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