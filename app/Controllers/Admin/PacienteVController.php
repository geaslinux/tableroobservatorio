<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatBotModel;
use App\Models\CallCenterModel;
use App\Models\ConsultaReclamoModel;
use App\Models\TurnoHospitalarioModel;

class PacienteVController extends BaseController
{
    /**
     * La columna `mes` en estas 4 tablas guarda el NOMBRE del mes en texto
     * (ENERO, FEBRERO, ...) y no un número 1-12. Este mapa se usa tanto
     * para filtrar (whereIn) como para acumular los datos del gráfico.
     */
    private array $mesesNombres = [
        1 => 'ENERO',      2 => 'FEBRERO',   3 => 'MARZO',     4 => 'ABRIL',
        5 => 'MAYO',       6 => 'JUNIO',     7 => 'JULIO',     8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE',  11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
    ];

    public function pacientev()
    {
        $chatBotModel    = new ChatBotModel();
        $callCenterModel = new CallCenterModel();
        $consultaModel   = new ConsultaReclamoModel();
        $turnoModel      = new TurnoHospitalarioModel();

        // ── Filtro de período ──
        $ejercicio_get = $this->request->getGet('ejercicio');
        $ejercicio_actual = ($ejercicio_get !== null && $ejercicio_get !== '')
            ? (int) $ejercicio_get
            : $this->obtenerUltimoEjercicioConDatos($consultaModel, $chatBotModel, $turnoModel, $callCenterModel);

        $mes_desde = (int) ($this->request->getGet('mes_desde') ?? 1);
        $mes_hasta = (int) ($this->request->getGet('mes_hasta') ?? 12);

        // Lista de nombres de mes (texto) que corresponden al rango elegido
        $mesesEnRango = [];
        for ($m = $mes_desde; $m <= $mes_hasta; $m++) {
            $mesesEnRango[] = $this->mesesNombres[$m];
        }

        // ── CONSULTAS Y RECLAMOS ──
        $total_consultas = $consultaModel
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->countAllResults();

        $consultas_resueltas = $consultaModel
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->where('estado', 'resuelto')
            ->countAllResults();

        $pct_consultas = $total_consultas > 0
            ? round(($consultas_resueltas / $total_consultas) * 100)
            : 0;

        // Distribución por tipo de llamado
        $tipos_llamado_raw = $consultaModel
            ->select('tipo_llamado, COUNT(*) as total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->groupBy('tipo_llamado')
            ->asArray()
            ->findAll();

        // ── CHAT BOT — TURNOS OTORGADOS ──
        $chat_bot_row = $chatBotModel
            ->selectSum('turnos_otorgados')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->asArray()
            ->first();
        $turnos_chatbot = (int) ($chat_bot_row['turnos_otorgados'] ?? 0);

        // ── GESTIÓN DE ESPECIALIDADES (turno_hospitalario) ──
        $turno_row = $turnoModel
            ->selectSum('total_otorgados')
            ->selectSum('turnos_atendidos')
            ->selectSum('ausentes')
            ->selectSum('cancelados')
            ->selectSum('sin_codificar')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->asArray()
            ->first();

        $turnos_otorgados_total  = (int) ($turno_row['total_otorgados']  ?? 0);
        $turnos_atendidos_total  = (int) ($turno_row['turnos_atendidos'] ?? 0);
        $turnos_ausentes_total   = (int) ($turno_row['ausentes']         ?? 0);
        $turnos_cancelados_total = (int) ($turno_row['cancelados']       ?? 0);
        $turnos_sin_codificar    = (int) ($turno_row['sin_codificar']    ?? 0);

        $pct_atendidos = $turnos_otorgados_total > 0
            ? round(($turnos_atendidos_total / $turnos_otorgados_total) * 100)
            : 0;

        // ── 0800 CALL CENTER ──
        $call_row = $callCenterModel
            ->selectSum('atendidos')
            ->selectSum('abandonadas')
            ->selectSum('total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->asArray()
            ->first();

        $call_atendidos   = (int) ($call_row['atendidos']   ?? 0);
        $call_abandonadas = (int) ($call_row['abandonadas'] ?? 0);
        $call_total       = (int) ($call_row['total']       ?? 0);

        $pct_call_atencion = $call_total > 0
            ? round(($call_atendidos / $call_total) * 100)
            : 0;

        // ── TOP 5 EFECTORES POR TURNOS OTORGADOS (especialidades) ──
        $top_efectores = $turnoModel
            ->select('efector.nombre AS nombre, SUM(turno_hospitalario.total_otorgados) AS total')
            ->join('efector', 'efector.efector_id = turno_hospitalario.efector_id', 'left')
            ->where('turno_hospitalario.ejercicio', $ejercicio_actual)
            ->whereIn('turno_hospitalario.mes', $mesesEnRango)
            ->groupBy('efector.efector_id')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->asArray()
            ->findAll();

        // ── GRÁFICO MENSUAL: actividad combinada de los 4 módulos ──
        // Mapa inverso NOMBRE => número, para poder ubicar cada fila en el arreglo 1-12
        $nombreAMes = array_flip($this->mesesNombres);

        $datos_mensuales = array_fill(1, 12, 0);

        $acumularMes = function (array $filas, string $campoTotal) use (&$datos_mensuales, $nombreAMes) {
            foreach ($filas as $row) {
                $nombreMes = strtoupper(trim($row['mes'] ?? ''));
                if (!isset($nombreAMes[$nombreMes])) {
                    continue; // dato de mes desconocido/mal cargado, se ignora
                }
                $mes = $nombreAMes[$nombreMes];
                $datos_mensuales[$mes] += (int) ($row[$campoTotal] ?? 0);
            }
        };

        $consultas_mes = $consultaModel
            ->select('mes, COUNT(*) as total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->groupBy('mes')
            ->asArray()
            ->findAll();
        $acumularMes($consultas_mes, 'total');

        $chatbot_mes = $chatBotModel
            ->select('mes, SUM(turnos_otorgados) as total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->groupBy('mes')
            ->asArray()
            ->findAll();
        $acumularMes($chatbot_mes, 'total');

        $turno_mes = $turnoModel
            ->select('mes, SUM(total_otorgados) as total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->groupBy('mes')
            ->asArray()
            ->findAll();
        $acumularMes($turno_mes, 'total');

        $callcenter_mes = $callCenterModel
            ->select('mes, SUM(total) as total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $mesesEnRango)
            ->groupBy('mes')
            ->asArray()
            ->findAll();
        $acumularMes($callcenter_mes, 'total');

        ksort($datos_mensuales);

        return view('admin/paciente_views', [
            'ejercicio_actual' => $ejercicio_actual,
            'mes_desde'        => $mes_desde,
            'mes_hasta'        => $mes_hasta,

            'total_consultas'     => $total_consultas,
            'consultas_resueltas' => $consultas_resueltas,
            'pct_consultas'       => $pct_consultas,
            'tipos_llamado'       => $tipos_llamado_raw,

            'turnos_chatbot' => $turnos_chatbot,

            'turnos_otorgados_total'  => $turnos_otorgados_total,
            'turnos_atendidos_total'  => $turnos_atendidos_total,
            'turnos_ausentes_total'   => $turnos_ausentes_total,
            'turnos_cancelados_total' => $turnos_cancelados_total,
            'turnos_sin_codificar'    => $turnos_sin_codificar,
            'pct_atendidos'           => $pct_atendidos,

            'call_atendidos'    => $call_atendidos,
            'call_abandonadas'  => $call_abandonadas,
            'call_total'        => $call_total,
            'pct_call_atencion' => $pct_call_atencion,

            'top_efectores'   => $top_efectores,
            'datos_mensuales' => $datos_mensuales,
        ]);
    }

    /**
     * Busca el ejercicio (año) más reciente que tenga registros en
     * cualquiera de las 4 tablas del módulo Gestión Paciente.
     */
    private function obtenerUltimoEjercicioConDatos(
        ConsultaReclamoModel $consultaModel,
        ChatBotModel $chatBotModel,
        TurnoHospitalarioModel $turnoModel,
        CallCenterModel $callCenterModel
    ): int {
        $anios = [];

        foreach ([$consultaModel, $chatBotModel, $turnoModel, $callCenterModel] as $modelo) {
            $fila = $modelo->selectMax('ejercicio')->asArray()->first();
            if (!empty($fila['ejercicio'])) {
                $anios[] = (int) $fila['ejercicio'];
            }
        }

        return !empty($anios) ? max($anios) : (int) date('Y');
    }
}