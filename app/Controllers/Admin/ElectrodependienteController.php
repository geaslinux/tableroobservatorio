<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ElectrodependienteController extends BaseController
{
    private $tipos          = ['ADULTO', 'NIÑO'];
    private $factoresRiesgo = ['ALTO', 'MEDIANO', 'BAJO'];
    private $seguimientos   = ['EVALUACION PENDIENTE', 'EVALUACION', 'INFORMADO'];
    private $cudOpciones    = ['SI', 'NO'];
    private $tiemposUso     = ['parcial', 'permanente'];

    // ─── Filtros ─────────────────────────────────────────────────────────
    // $conBuscar = false: el mapa recibe a todos y filtra el texto del buscador en el navegador
    private function aplicarFiltros($model, bool $conBuscar = true)
    {
        $filtro_tipo        = $this->request->getGet('tipo')          ?? '';
        $filtro_localidad   = $this->request->getGet('localidad')     ?? '';
        $filtro_efector     = $this->request->getGet('efector')       ?? '';
        $filtro_obra_social = $this->request->getGet('obra_social')   ?? '';
        $filtro_diagnostico = $this->request->getGet('diagnostico')   ?? '';
        $filtro_factor      = $this->request->getGet('factor_riesgo') ?? '';
        $filtro_seguimiento = $this->request->getGet('seguimiento')   ?? '';
        $filtro_cud         = $this->request->getGet('cud')           ?? '';
        $filtro_estado      = $this->request->getGet('estado')        ?? '';
        $filtro_buscar      = $this->request->getGet('buscar')        ?? '';

        if ($filtro_tipo)        $model->where('electrodependiente.tipo',           $filtro_tipo);
        if ($filtro_localidad)   $model->where('electrodependiente.localidad_id',   $filtro_localidad);
        if ($filtro_efector)     $model->where('electrodependiente.efector_id',     $filtro_efector);
        if ($filtro_obra_social) $model->where('electrodependiente.obra_social_id', $filtro_obra_social);
        if ($filtro_diagnostico) $model->where('electrodependiente.diagnostico_id', $filtro_diagnostico);
        if ($filtro_factor === 'SIN_DATO') {
            // Sin riesgo cargado: vacío o con un valor que no es ALTO, MEDIANO ni BAJO (igual que el mapa)
            $model->where("(electrodependiente.factor_riesgo IS NULL OR UPPER(electrodependiente.factor_riesgo) NOT IN ('ALTO','MEDIANO','BAJO'))", null, false);
        } elseif ($filtro_factor) {
            $model->where('electrodependiente.factor_riesgo', $filtro_factor);
        }
        if ($filtro_seguimiento) $model->where('electrodependiente.seguimiento',    $filtro_seguimiento);
        if ($filtro_cud)         $model->where('electrodependiente.cud',            $filtro_cud);
        if ($filtro_buscar && $conBuscar) $model->groupStart()
                                       ->like('electrodependiente.paciente', $filtro_buscar)
                                       ->orLike('electrodependiente.dni',    $filtro_buscar)
                                       ->orLike('electrodependiente.nro_servicio', $filtro_buscar)
                                       ->groupEnd();

        if ($filtro_estado != '') {
            $model->where('electrodependiente.estado', $filtro_estado);
        } else {
            $model->whereIn('electrodependiente.estado', ['activo', 'desactivado']);
        }

        return compact(
            'filtro_tipo', 'filtro_localidad', 'filtro_efector',
            'filtro_obra_social', 'filtro_diagnostico', 'filtro_factor',
            'filtro_seguimiento', 'filtro_cud', 'filtro_estado', 'filtro_buscar'
        );
    }

    // ─── Filtro de la leyenda del mapa (riesgo y estado, con varios valores) ──
    // Llega como ?m_riesgo=ALTO,MEDIANO&m_estado=desactivado. Dentro de un grupo vale cualquiera
    // de los valores; entre grupos tienen que cumplirse los dos. Se aplica a la tabla, los
    // indicadores y el Excel; el mapa los recibe sin este filtro para poder mostrar los conteos.
    private function seleccionMapa(): array
    {
        $leer = function (string $clave, array $validos): array {
            $v = array_map('trim', explode(',', (string) ($this->request->getGet($clave) ?? '')));

            return array_values(array_intersect($validos, $v));
        };

        return [
            'riesgo' => $leer('m_riesgo', ['ALTO', 'MEDIANO', 'BAJO', 'SD']),
            'estado' => $leer('m_estado', ['activo', 'fallecido', 'desactivado']),
        ];
    }

    private function aplicarSeleccionMapa($model, array $sel): void
    {
        $db = \Config\Database::connect();
        $condRiesgo = [];
        foreach ($sel['riesgo'] as $r) {
            $condRiesgo[] = $r === 'SD'
                ? "(electrodependiente.factor_riesgo IS NULL OR UPPER(electrodependiente.factor_riesgo) NOT IN ('ALTO','MEDIANO','BAJO'))"
                : 'UPPER(electrodependiente.factor_riesgo) = ' . $db->escape($r);
        }
        if ($condRiesgo) {
            $model->where('(' . implode(' OR ', $condRiesgo) . ')', null, false);
        }

        // Un fallecido cuenta solo como fallecido, aunque también esté desactivado (igual que el mapa)
        $vivo = "(electrodependiente.estado_vital IS NULL OR electrodependiente.estado_vital <> 'fallecido')";
        $condEstado = [];
        foreach ($sel['estado'] as $e) {
            $condEstado[] = match ($e) {
                'fallecido'   => "electrodependiente.estado_vital = 'fallecido'",
                'desactivado' => "($vivo AND electrodependiente.estado <> 'activo')",
                default       => "($vivo AND electrodependiente.estado = 'activo')",
            };
        }
        if ($condEstado) {
            $model->where('(' . implode(' OR ', $condEstado) . ')', null, false);
        }
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');
        $reg        = $visitModel->where('user_id', $userId)->where('modulo', 'electrodependiente')->first();
        $ultimaVista = $reg ? $reg->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('ElectrodependienteModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('ElectrodependienteModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('updated_at >', $ultimaVista)
            ->where('updated_at != created_at')
            ->countAllResults();

        $model   = model('ElectrodependienteModel')->conRelaciones();
        $filtros = $this->aplicarFiltros($model);
        $mapaSel = $this->seleccionMapa();
        $this->aplicarSeleccionMapa($model, $mapaSel);

        // ── Indicadores del listado (mismos filtros que la tabla, sin paginar) ──
        // Instancia nueva: model() comparte la instancia y el builder de la tabla.
        $modeloKpi = new \App\Models\ElectrodependienteModel();
        $this->aplicarFiltros($modeloKpi);
        $this->aplicarSeleccionMapa($modeloKpi, $mapaSel);
        $kpis = $modeloKpi->select("COUNT(*) AS total,
                SUM(CASE WHEN electrodependiente.estado = 'activo' THEN 1 ELSE 0 END) AS activos,
                SUM(CASE WHEN electrodependiente.factor_riesgo = 'ALTO' THEN 1 ELSE 0 END) AS alto,
                SUM(CASE WHEN electrodependiente.factor_riesgo IS NULL OR electrodependiente.factor_riesgo = '' THEN 1 ELSE 0 END) AS sin_riesgo,
                SUM(CASE WHEN electrodependiente.nro_servicio IS NOT NULL AND electrodependiente.nro_servicio <> '' THEN 1 ELSE 0 END) AS con_servicio,
                SUM(CASE WHEN electrodependiente.servicio_verificado = 1 THEN 1 ELSE 0 END) AS verificados,
                SUM(CASE WHEN electrodependiente.coordenadas IS NOT NULL AND electrodependiente.coordenadas <> '' THEN 1 ELSE 0 END) AS con_ubicacion", false)
            ->get()->getRowArray() ?: [];

        // ── Mapa: todos los pacientes con coordenadas que cumplen los filtros (no solo la página) ──
        $modeloMapa = (new \App\Models\ElectrodependienteModel())->conRelaciones();
        $this->aplicarFiltros($modeloMapa, false);
        $pacientesMapa = $modeloMapa
            ->select('electrodependiente.electrodependiente_id AS id, electrodependiente.paciente, electrodependiente.dni, electrodependiente.edad,
                      electrodependiente.factor_riesgo AS riesgo, electrodependiente.estado, electrodependiente.estado_vital, electrodependiente.coordenadas, electrodependiente.nro_servicio')
            ->where('electrodependiente.coordenadas IS NOT NULL', null, false)
            ->where('electrodependiente.coordenadas <>', '')
            ->asArray()->findAll();
        // Total sin la selección de la leyenda (para "N sin coordenadas cargadas" bajo el mapa)
        $modeloTotal = new \App\Models\ElectrodependienteModel();
        $this->aplicarFiltros($modeloTotal, false);
        $totalMapa = $modeloTotal->countAllResults();

        // ── Paginar primero para obtener los registros de la página ──────
        $registros = $model->orderBy('electrodependiente.paciente', 'ASC')->paginate($perPage);

        // ── Cargar equipos activos en batch (un solo query) ───────────────
        $equipModel   = model('EquipamientoModel');
        $ids          = array_map(fn($r) => $r->electrodependiente_id, $registros);
        $todosEquipos = [];

        if (!empty($ids)) {
            $equipos = $equipModel
                ->whereIn('electrodependiente_id', $ids)
                ->where('estado', 'activo')
                ->orderBy('equipamiento_id', 'ASC')
                ->findAll();

            foreach ($equipos as $eq) {
                $todosEquipos[$eq['electrodependiente_id']][] = $eq;
            }
        }

        // Inyectar equipos en cada registro
        foreach ($registros as $r) {
            $r->equipos = $todosEquipos[$r->electrodependiente_id] ?? [];
        }
        // ─────────────────────────────────────────────────────────────────

        return view('electrodependiente/electrodependiente_list', array_merge($filtros, [
            'registros'      => $registros,
            'pager'          => $model->pager,
            'localidades'    => model('LocalidadModel')->orderBy('nombre', 'ASC')->findAll(),
            'efectores'      => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'obras_sociales' => model('ObraSocialModel')->orderBy('nombre', 'ASC')->findAll(),
            'diagnosticos'   => model('DiagnosticoModel')->orderBy('nombre', 'ASC')->findAll(),
            'tipos'          => $this->tipos,
            'factores'       => $this->factoresRiesgo,
            'seguimientos'   => $this->seguimientos,
            'cudOpciones'    => $this->cudOpciones,
            'nuevas'         => $nuevas,
            'modificadas'    => $modificadas,
            'ultimaVista'    => $ultimaVista,
            'kpis'           => array_map('intval', $kpis),
            'pacientesMapa'  => $pacientesMapa,
            'mapaSel'        => $mapaSel,
            'totalMapa'      => $totalMapa,
        ]));
    }

    // ─── Marcar visto ────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');
        $userId = user()->id;
        $ahora  = date('Y-m-d H:i:s');
        $visitModel = model('UserLastVisitModel');
        $reg = $visitModel->where('user_id', $userId)->where('modulo', 'electrodependiente')->first();
        if ($reg) {
            $visitModel->where('user_id', $userId)->where('modulo', 'electrodependiente')
                       ->set(['ultima_vista' => $ahora])->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'electrodependiente', 'ultima_vista' => $ahora]);
        }
        return redirect()->to(route_to('electrodependiente_list'));
    }

    // ─── Create ──────────────────────────────────────────────────────────
    public function create()
    {
        helper('form');
        return view('electrodependiente/electrodependiente_form', array_merge(
            $this->datosFormulario(),
            ['equipos' => []]
        ));
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id = model('ElectrodependienteModel')->insert($this->datosPost());

        $equipos = $this->request->getPost('equipos') ?? [];
        if (!empty($equipos)) {
            model('EquipamientoModel')->sincronizar((int)$id, $equipos);
        }

        return redirect()->to(route_to('electrodependiente_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');
        $registro = model('ElectrodependienteModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('electrodependiente_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $registro->setCampoOculto();

        $equipos = model('EquipamientoModel')->porPaciente((int)$id);

        return view('electrodependiente/electrodependiente_form', array_merge(
            $this->datosFormulario(),
            [
                'registro' => $registro,
                'equipos'  => $equipos,
            ]
        ));
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id   = $this->request->getPost('electrodependiente_id');
        $data = $this->datosPost();
        $data['estado'] = $this->request->getPost('estado') ?? 'activo';

        model('ElectrodependienteModel')->update($id, $data);

        $equipos = $this->request->getPost('equipos') ?? [];
        model('EquipamientoModel')->sincronizar((int)$id, $equipos);

        return redirect()->to(route_to('electrodependiente_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id');
        if (model('ElectrodependienteModel')->find($id)) {
            model('ElectrodependienteModel')->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('electrodependiente_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Export Excel ─────────────────────────────────────────────────────
    public function export()
    {
        helper('auth');

        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');
        $reg        = $visitModel->where('user_id', $userId)->where('modulo', 'electrodependiente')->first();
        $ultimaVista = $reg ? $reg->ultima_vista : '2000-01-01 00:00:00';

        $model   = model('ElectrodependienteModel')->conRelaciones();
        $this->aplicarFiltros($model);
        $this->aplicarSeleccionMapa($model, $this->seleccionMapa());
        $registros = $model->orderBy('electrodependiente.paciente', 'ASC')->findAll();

        // ── Cargar equipos activos en batch (un solo query) ───────────────
        $equipModel   = model('EquipamientoModel');
        $idsExport    = array_map(fn($r) => $r->electrodependiente_id, $registros);
        $equiposExport = [];

        if (!empty($idsExport)) {
            $eqRows = $equipModel
                ->whereIn('electrodependiente_id', $idsExport)
                ->where('estado', 'activo')
                ->orderBy('equipamiento_id', 'ASC')
                ->findAll();

            foreach ($eqRows as $eq) {
                $equiposExport[$eq['electrodependiente_id']][] = $eq;
            }
        }
        // ─────────────────────────────────────────────────────────────────

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Electrodependientes');

        // ── Cabeceras unificadas (paciente + equipamiento) ────────────────
        $cabeceras = [
            '#', 'Paciente', 'DNI', 'Fecha Nac.', 'Edad', 'Tipo',
            'Contacto', 'Domicilio', 'Localidad', 'Hospital', 'Obra Social',
            'CUD', 'Diagnóstico', 'Dx Complementario', 'Factor Riesgo',
            'Seguimiento', 'Observación', 'Estado',
            // Servicio eléctrico (dato del paciente)
            'Nº Servicio', 'Titular Servicio', 'DNI Titular', 'Servicio verificado',
            // Equipamiento
            'Equipamiento', 'Marca', 'Serie', 'Modelo',
            'Fecha Entrega', 'Tiempo Uso',
            'Médico Tratante',
            // Novedad
            'Novedad',
        ];

        $sheet->fromArray($cabeceras, null, 'A1');

        // Estilo cabecera — bloque paciente (incluye el servicio eléctrico)
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF13304D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ]);

        // Estilo cabecera — bloque equipamiento
        $sheet->getStyle('W1:AC1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF13304D']],
            'fill' => ['fillType' => Fill::FILL_SOLID,
                       'startColor' => ['argb' => 'FFD0E4F7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
        ]);

        // Estilo cabecera — columna Novedad
        $sheet->getStyle('AD1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF13304D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $colorNuevo      = 'FFD4EDDA';
        $colorModificado = 'FFFFF3CD';
        $fila = 2;

        // ── Filas de datos: UNA FILA POR EQUIPO ──────────────────────────
        foreach ($registros as $r) {
            $esNuevo      = $r->created_at > $ultimaVista;
            $esModificado = ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
            $novedad      = $esNuevo ? '🟢 NUEVO' : ($esModificado ? '🟡 MODIFICADO' : '');

            // Si no tiene equipos → una fila con celdas de equipo vacías
            $eqsPaciente = $equiposExport[$r->electrodependiente_id] ?? [[]];

            foreach ($eqsPaciente as $eq) {
                $sheet->fromArray([
                    $r->electrodependiente_id,
                    $r->paciente,
                    $r->dni                ?? '',
                    $r->fecha_nacimiento   ? date('d/m/Y', strtotime($r->fecha_nacimiento)) : '',
                    $r->edad               ?? '',
                    $r->tipo               ?? '',
                    $r->contacto           ?? '',
                    $r->domicilio          ?? '',
                    $r->localidad_nombre   ?? '',
                    $r->efector_nombre     ?? '',
                    $r->obra_social_nombre ?? '',
                    $r->cud                ?? '',
                    $r->diagnostico_nombre ?? '',
                    $r->dx_complementario  ?? '',
                    $r->factor_riesgo      ?? '',
                    $r->seguimiento        ?? '',
                    $r->observacion        ?? '',
                    $r->estado,
                    // Servicio eléctrico del paciente
                    $r->nro_servicio         ?? '',
                    $r->titular_servicio     ?? '',
                    $r->titular_servicio_dni ?? '',
                    !empty($r->nro_servicio) ? (!empty($r->servicio_verificado) ? 'SI' : 'NO') : '',
                    // Equipamiento
                    $eq['equipamiento']    ?? '',
                    $eq['marca']           ?? '',
                    $eq['serie']           ?? '',
                    $eq['modelo']          ?? '',
                    !empty($eq['fecha_entrega']) ? date('d/m/Y', strtotime($eq['fecha_entrega'])) : '',
                    $eq['tiempo_uso']      ?? '',
                    $eq['medico_tratante'] ?? '',
                    // Novedad
                    $novedad,
                ], null, 'A' . $fila);

                // El Nº de servicio se escribe como texto: conserva ceros iniciales
                $sheet->setCellValueExplicit('S' . $fila, (string) ($r->nro_servicio ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                // Color de novedad en toda la fila
                if ($esNuevo || $esModificado) {
                    $sheet->getStyle('A' . $fila . ':AD' . $fila)->getFill()
                          ->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setARGB($esNuevo ? $colorNuevo : $colorModificado);
                }

                // Color diferenciado en bloque de equipamiento (filas con equipo real)
                if (isset($eq['equipamiento']) && count($eqsPaciente) > 1) {
                    $sheet->getStyle('W' . $fila . ':AC' . $fila)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID,
                                   'startColor' => ['argb' => 'FFE8F4FB']],
                    ]);
                }

                $fila++;
            }
        }

        // ── Auto-ancho y ajustes finales ──────────────────────────────────
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (['AA', 'AB', 'AC', 'AD'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:AD' . ($fila - 1));

        // ── Hoja 2: Equipamiento (detalle completo, sin cambios) ──────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Equipamiento');

        $headersEq = [
            'A' => 'Paciente',        'B' => 'Equipamiento',      'C' => 'Marca',
            'D' => 'Serie',           'E' => 'Modelo',            'F' => 'F. Entrega',
            'G' => 'Tiempo de Uso',   'H' => 'Médico Tratante',   'I' => 'F. Ingreso RECS',
            'J' => 'Última Evaluación',
        ];

        foreach ($headersEq as $col => $h) {
            $sheet2->setCellValue($col . '1', $h);
            $sheet2->getStyle($col . '1')->getFont()->setBold(true);
            $sheet2->getStyle($col . '1')->getFill()
                   ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('13304d');
            $sheet2->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $rowEq = 2;
        foreach ($registros as $r) {
            // Reutilizar el batch ya cargado (sin query extra)
            $equipos = $equiposExport[$r->electrodependiente_id] ?? [];
            foreach ($equipos as $eq) {
                $sheet2->setCellValue('A' . $rowEq, $r->paciente);
                $sheet2->setCellValue('B' . $rowEq, $eq['equipamiento']    ?? '');
                $sheet2->setCellValue('C' . $rowEq, $eq['marca']           ?? '');
                $sheet2->setCellValue('D' . $rowEq, $eq['serie']           ?? '');
                $sheet2->setCellValue('E' . $rowEq, $eq['modelo']          ?? '');
                $sheet2->setCellValue('F' . $rowEq, !empty($eq['fecha_entrega'])    ? date('d/m/Y', strtotime($eq['fecha_entrega']))    : '');
                $sheet2->setCellValue('G' . $rowEq, $eq['tiempo_uso']      ?? '');
                $sheet2->setCellValue('H' . $rowEq, $eq['medico_tratante'] ?? '');
                $sheet2->setCellValue('I' . $rowEq, !empty($eq['fecha_ingreso_recs']) ? date('d/m/Y', strtotime($eq['fecha_ingreso_recs'])) : '');
                $sheet2->setCellValue('J' . $rowEq, !empty($eq['ultima_evaluacion'])  ? date('d/m/Y', strtotime($eq['ultima_evaluacion']))  : '');
                $rowEq++;
            }
        }

        foreach (range('A', 'J') as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet2->freezePane('A2');
        $sheet2->setAutoFilter('A1:J1');

        // ── Descargar ─────────────────────────────────────────────────────
        $sufijo   = ($ultimaVista == '2000-01-01 00:00:00') ? 'primera_vez' : 'desde_' . date('Ymd_Hi', strtotime($ultimaVista));
        $filename = 'electrodependientes_' . $sufijo . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ─── Helpers privados ─────────────────────────────────────────────────
    private function datosPost(): array
    {
        $p = $this->request->getPost();
        return array_merge($this->datosPaciente($p), $this->datosServicio());
    }

    private function datosPaciente(array $p): array
    {
        return [
            'paciente'          => strtoupper(trim($p['paciente'] ?? '')),
            'dni'               => $p['dni']               ?? null,
            'fecha_nacimiento'  => $p['fecha_nacimiento']  ?: null,
            'edad'              => $p['edad']               ?: null,
            'tipo'              => $p['tipo']               ?? null,
            'contacto'          => $p['contacto']          ?? null,
            'domicilio'         => $p['domicilio']         ?? null,
            'coordenadas'       => $p['coordenadas']       ?? null,
            'localidad_id'      => $p['localidad_id']      ?: null,
            'efector_id'        => $p['efector_id']        ?: null,
            'obra_social_id'    => $p['obra_social_id']    ?: null,
            'cud'               => $p['cud']               ?? null,
            'diagnostico_id'    => $p['diagnostico_id']    ?: null,
            'dx_complementario' => $p['dx_complementario'] ?? null,
            'observacion'       => $p['observacion']       ?? null,
            'factor_riesgo'     => $p['factor_riesgo']     ?? null,
            'seguimiento'       => $p['seguimiento']       ?? null,
            'estado'            => 'activo',
        ];
    }

    /**
     * Servicio eléctrico del PACIENTE (misma regla que sistemabasec):
     *  - el número se normaliza con normalizar_nro_servicio();
     *  - "verificado" solo si hay número y se marcó la casilla;
     *  - si cambia el número, la empresa, el titular o su DNI, la verificación anterior no se conserva:
     *    hay que volver a comprobarlo (EJESA consulta solo servicios verificados).
     */
    private function datosServicio(): array
    {
        helper(['servicio', 'auth']);

        $datos = [
            'servicio_empresa'     => 'EJESA',
            'nro_servicio'         => normalizar_nro_servicio((string) $this->request->getPost('nro_servicio')),
            'titular_servicio'     => trim((string) $this->request->getPost('titular_servicio')) ?: null,
            'titular_servicio_dni' => trim((string) $this->request->getPost('titular_servicio_dni')) ?: null,
            'servicio_origen'      => 'CARGA_FICHA',
        ];

        $verificado = $datos['nro_servicio'] !== null && $this->request->getPost('servicio_verificado') === '1';
        $id         = (int) $this->request->getPost('electrodependiente_id');
        $anterior   = $id ? model('ElectrodependienteModel', false)->asArray()->find($id) : null;

        $cambioServicio = $anterior && (
            ($anterior['nro_servicio'] ?? null)         !== $datos['nro_servicio']
            || ($anterior['servicio_empresa'] ?? null)  !== $datos['servicio_empresa']
            || ($anterior['titular_servicio'] ?? null)  !== $datos['titular_servicio']
            || ($anterior['titular_servicio_dni'] ?? null) !== $datos['titular_servicio_dni']
        );

        // Sin cambios en el servicio se conserva el origen y la verificación previa (quién y cuándo)
        if ($anterior && !$cambioServicio && !empty($anterior['servicio_origen'])) {
            $datos['servicio_origen'] = $anterior['servicio_origen'];
        }
        $conservar = !$cambioServicio && !empty($anterior['servicio_verificado']);

        $datos['servicio_verificado']     = $verificado ? 1 : 0;
        $datos['servicio_verificado_at']  = $verificado
            ? (($conservar ? ($anterior['servicio_verificado_at'] ?? null) : null) ?: gmdate('Y-m-d H:i:s'))
            : null;
        $datos['servicio_verificado_por'] = $verificado
            ? (($conservar ? ($anterior['servicio_verificado_por'] ?? null) : null) ?: user_id())
            : null;

        return $datos;
    }

    private function datosFormulario(): array
    {
        return [
            'localidades'    => model('LocalidadModel')->orderBy('nombre', 'ASC')->findAll(),
            'efectores'      => model('EfectorModel')->orderBy('nombre', 'ASC')->findAll(),
            'obras_sociales' => model('ObraSocialModel')->orderBy('nombre', 'ASC')->findAll(),
            'diagnosticos'   => model('DiagnosticoModel')->orderBy('nombre', 'ASC')->findAll(),
            'tipos'          => $this->tipos,
            'factores'       => $this->factoresRiesgo,
            'seguimientos'   => $this->seguimientos,
            'cudOpciones'    => $this->cudOpciones,
            'tiemposUso'     => $this->tiemposUso,
        ];
    }

    private function valida(): bool
    {
        return $this->validate([
            'paciente'             => 'required|max_length[200]',
            'nro_servicio'         => 'permit_empty|max_length[50]|regex_match[/^[^\x00-\x1F\x7F]+$/u]',
            'titular_servicio'     => 'permit_empty|max_length[200]',
            'titular_servicio_dni' => 'permit_empty|max_length[20]|regex_match[/^[0-9.\s-]+$/]',
        ], [
            'paciente'             => ['required' => 'El nombre del paciente es obligatorio.'],
            'nro_servicio'         => [
                'max_length'  => 'El número de servicio admite hasta 50 caracteres.',
                'regex_match' => 'El número de servicio tiene caracteres no válidos.',
            ],
            'titular_servicio'     => ['max_length' => 'El nombre del titular admite hasta 200 caracteres.'],
            'titular_servicio_dni' => [
                'max_length'  => 'El DNI del titular admite hasta 20 caracteres.',
                'regex_match' => 'El DNI del titular solo admite números, puntos y guiones.',
            ],
        ]);
    }

    // ─── Historial ────────────────────────────────────────────────────────
    public function historial($id)
    {
        $paciente = model('ElectrodependienteModel')->find($id);

        if (!$paciente) {
            return redirect()->to(route_to('electrodependiente_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Paciente no encontrado']);
        }

        $historialPaciente = array_map(
            fn($h) => $h->toArray(),
            model('ElectrodependienteHistorialModel')
                ->where('electrodependiente_id', $id)
                ->orderBy('created_at', 'DESC')
                ->findAll()
        );

        $historialEquipos = array_map(
            fn($h) => $h->toArray(),
            model('EquipamientoHistorialModel')
                ->where('electrodependiente_id', $id)
                ->orderBy('created_at', 'DESC')
                ->findAll()
        );

        return view('electrodependiente/electrodependiente_historial', [
            'paciente'          => $paciente,
            'historialPaciente' => $historialPaciente,
            'historialEquipos'  => $historialEquipos,
        ]);
    }
}