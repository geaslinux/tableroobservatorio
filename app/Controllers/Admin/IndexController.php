<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Panel de inicio: una tarjeta por área con sus indicadores principales.
 *
 * Cada indicador usa el último año cerrado con datos de su tabla
 * (si la tabla solo tiene el año en curso, usa ese) y la vista muestra el año.
 * Los datos sin período (bases, camas, electrodependientes) son la foto vigente.
 */
class IndexController extends BaseController
{
    private $db;

    public function inicio()
    {
        helper('auth');
        $this->db = \Config\Database::connect();
        $db = $this->db;

        // ══ PREHOSPITALARIO ═════════════════════════════════════════════
        $anioMovil = $this->anioReferencia('movil', 'ejercicio', true);
        $movil = $db->table('movil')
            ->select("
                COALESCE(SUM(CASE WHEN tipo = 'MOVILES OPERATIVOS' THEN cantidad END), 0) AS operativos,
                COALESCE(SUM(cantidad), 0) AS flota
            ", false)
            ->where('ejercicio', $anioMovil)
            ->whereIn('estado', ['activo', 'desactivado'])
            ->get()->getRowArray();

        $anioAsist = $this->anioReferencia('asistencia', 'ejercicio', true);
        $anioAtenc = $this->anioReferencia('atencion', 'ejercicio');

        $pre = [
            'bases_activas' => $db->table('base')->where('estado', 'activo')->countAllResults(),
            'bases_total'   => $db->table('base')->where('estado !=', 'eliminado')->countAllResults(),
            'moviles_op'    => (int) ($movil['operativos'] ?? 0),
            'moviles_flota' => (int) ($movil['flota'] ?? 0),
            'anio_movil'    => $anioMovil,
            'asistencias'   => $this->suma('asistencia', 'total', 'ejercicio', $anioAsist, true),
            'anio_asist'    => $anioAsist,
            'atenciones'    => $this->suma('atencion', 'total', 'ejercicio', $anioAtenc),
            'anio_atenc'    => $anioAtenc,
        ];
        $pre['pct_flota'] = $pre['moviles_flota'] > 0 ? round($pre['moviles_op'] * 100 / $pre['moviles_flota']) : 0;

        // ══ HOSPITALARIO ════════════════════════════════════════════════
        $anioGuardia = $this->anioReferencia('guardia', 'anio');
        $anioRend    = $this->anioReferencia('rendimiento_hospitalario', 'ejercicio');
        $anioQui     = $this->anioReferencia('produccion_quirofano_hosp', 'ejercicio');

        $rend = $db->table('rendimiento_hospitalario')
            ->select('COALESCE(SUM(total_egresos), 0) AS egresos, COALESCE(SUM(paciente_dia), 0) AS paciente_dia, COALESCE(SUM(cama_disponible), 0) AS cama_disponible', false)
            ->where('ejercicio', $anioRend)
            ->get()->getRowArray();

        $hosp = [
            'guardia'      => $this->suma('guardia', 'cantidad', 'anio', $anioGuardia),
            'anio_guardia' => $anioGuardia,
            'egresos'      => (int) ($rend['egresos'] ?? 0),
            'ocupacion'    => ($rend['cama_disponible'] ?? 0) > 0 ? round($rend['paciente_dia'] * 100 / $rend['cama_disponible'], 1) : 0,
            'anio_rend'    => $anioRend,
            'cirugias'     => $this->suma('produccion_quirofano_hosp', 'sub_total', 'ejercicio', $anioQui),
            'anio_qui'     => $anioQui,
            'camas'        => (int) ($db->table('capacidad_camas')->selectSum('camas_disponibles', 'total')->get()->getRow()->total ?? 0),
        ];

        // ══ GESTIÓN PACIENTE ════════════════════════════════════════════
        $anioTurnos = $this->anioReferencia('turno_hospitalario', 'ejercicio', true);
        $turnos = $db->table('turno_hospitalario')
            ->select('COALESCE(SUM(total_otorgados), 0) AS otorgados, COALESCE(SUM(ausentes), 0) AS ausentes', false)
            ->where('ejercicio', $anioTurnos)->whereIn('estado', ['activo', 'desactivado'])
            ->get()->getRowArray();

        $anioCall = $this->anioReferencia('call_center', 'ejercicio', true);
        $call = $db->table('call_center')
            ->select('COALESCE(SUM(total), 0) AS total, COALESCE(SUM(atendidos), 0) AS atendidos', false)
            ->where('ejercicio', $anioCall)->whereIn('estado', ['activo', 'desactivado'])
            ->get()->getRowArray();

        $anioChat     = $this->anioReferencia('chat_bot', 'ejercicio', true);
        $anioConsulta = $this->anioReferencia('consulta_reclamo', 'ejercicio', true);

        $pac = [
            'turnos'         => (int) ($turnos['otorgados'] ?? 0),
            'pct_ausentes'   => ($turnos['otorgados'] ?? 0) > 0 ? round($turnos['ausentes'] * 100 / $turnos['otorgados']) : 0,
            'anio_turnos'    => $anioTurnos,
            'llamadas'       => (int) ($call['total'] ?? 0),
            'pct_atendidas'  => ($call['total'] ?? 0) > 0 ? round($call['atendidos'] * 100 / $call['total']) : 0,
            'anio_call'      => $anioCall,
            'chat_bot'       => $this->suma('chat_bot', 'turnos_otorgados', 'ejercicio', $anioChat, true),
            'anio_chat'      => $anioChat,
            'consultas'      => $db->table('consulta_reclamo')->where('ejercicio', $anioConsulta)
                                   ->whereIn('estado', ['activo', 'desactivado'])->countAllResults(),
            'anio_consulta'  => $anioConsulta,
        ];

        // ══ SALUD MENTAL (vigente) ══════════════════════════════════════
        $sm = $db->table('electrodependiente')
            ->select("
                COUNT(*)                                          AS pacientes,
                SUM(factor_riesgo = 'ALTO')                       AS riesgo_alto,
                SUM(cud = 'SI')                                   AS con_cud
            ", false)
            ->whereIn('estado', ['activo', 'desactivado'])
            ->get()->getRowArray();
        $sm = array_map('intval', $sm ?? []);

        // Ubicados en el mapa: misma validación de coordenadas que el panel de Salud mental
        $sm['geo'] = 0;
        $coordenadas = $db->table('electrodependiente')->select('coordenadas')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where("TRIM(COALESCE(coordenadas, '')) <>", '')
            ->get()->getResultArray();
        foreach ($coordenadas as $c) {
            $partes = array_map('trim', explode(',', $c['coordenadas']));
            if (count($partes) < 2) continue;
            $lat = (float) $partes[0];
            $lng = (float) $partes[1];
            if (($lat == 0 && $lng == 0) || abs($lat) > 90 || abs($lng) > 180) continue;
            $sm['geo']++;
        }
        $sm['camas'] = (int) ($db->table('salud_mental_camas')
            ->select('COALESCE(SUM(COALESCE(cb_adultos, 0) + COALESCE(cb_pediatricos, 0)), 0) AS total', false)
            ->get()->getRow()->total ?? 0);
        $sm['pct_alto'] = $sm['pacientes'] > 0 ? round($sm['riesgo_alto'] * 100 / $sm['pacientes'], 1) : 0;

        // ══ SERVICIOS TRANSVERSALES ═════════════════════════════════════
        $anioTransf = $this->anioReferencia('transfusion', 'ejercicio', true);
        $anioOper   = $this->anioReferencia('cantidad_operativo', 'ejercicio', true);

        $transfAnt = $this->suma('transfusion', 'total', 'ejercicio', $anioTransf - 1, true);
        $ser = [
            'transfusiones' => $this->suma('transfusion', 'total', 'ejercicio', $anioTransf, true),
            'hospitales'    => $db->table('transfusion')->select('COUNT(DISTINCT efector_id) AS n', false)
                                  ->where('ejercicio', $anioTransf)->whereIn('estado', ['activo', 'desactivado'])
                                  ->get()->getRow()->n ?? 0,
            'anio_transf'   => $anioTransf,
            'operativos'    => $this->suma('cantidad_operativo', 'total', 'ejercicio', $anioOper, true),
            'via_publica'   => $this->suma('cantidad_operativo', 'via_publica', 'ejercicio', $anioOper, true),
            'anio_oper'     => $anioOper,
        ];
        $ser['variacion'] = $transfAnt > 0 ? round(($ser['transfusiones'] - $transfAnt) * 100 / $transfAnt, 1) : null;

        return view('admin/inicio_views', [
            'pre'  => $pre,
            'hosp' => $hosp,
            'pac'  => $pac,
            'sm'   => $sm,
            'ser'  => $ser,
        ]);
    }

    // Último año cerrado con datos en la tabla; si no hay, el más reciente.
    private function anioReferencia(string $tabla, string $campo, bool $conEstado = false): int
    {
        $b = $this->db->table($tabla)->select("{$campo} AS anio")->distinct();
        if ($conEstado) $b->whereIn('estado', ['activo', 'desactivado']);
        $anios = array_map('intval', array_column($b->get()->getResultArray(), 'anio'));

        if (empty($anios)) return (int) date('Y');

        $cerrados = array_filter($anios, function ($a) { return $a < (int) date('Y'); });
        return !empty($cerrados) ? max($cerrados) : max($anios);
    }

    // Suma de un campo en un año
    private function suma(string $tabla, string $campo, string $campoAnio, int $anio, bool $conEstado = false): int
    {
        $b = $this->db->table($tabla)
            ->select("COALESCE(SUM({$campo}), 0) AS total", false)
            ->where($campoAnio, $anio);
        if ($conEstado) $b->whereIn('estado', ['activo', 'desactivado']);

        return (int) ($b->get()->getRow()->total ?? 0);
    }
}
