<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BaseModel;
use App\Models\MovilModel;
use App\Models\AsistenciaModel;
use App\Models\IdentificacionModel;
use App\Models\DepartamentoModel;

class BaseVController extends BaseController
{
    public function basev()
    {
        $baseModel           = new BaseModel();
        $movilModel          = new MovilModel();
        $asistenciaModel     = new AsistenciaModel();
        $identificacionModel = new IdentificacionModel();
        $departamentoModel   = new DepartamentoModel();

        $db = \Config\Database::connect();

        // ── FILTROS GET ────────────────────────────────────────
        $ejercicio_actual = (int)($this->request->getGet('ejercicio') ?? date('Y'));
        $mes_desde        = (int)($this->request->getGet('mes_desde') ?? 1);
        $mes_hasta        = (int)($this->request->getGet('mes_hasta') ?? 12);

        if ($mes_desde < 1 || $mes_desde > 12) $mes_desde = 1;
        if ($mes_hasta < 1 || $mes_hasta > 12) $mes_hasta = 12;
        if ($mes_desde > $mes_hasta)           $mes_hasta = $mes_desde;

        // Mapa número => nombre exacto como está guardado en la BD
        $meses_map = [
            1  => 'ENERO',      2  => 'FEBRERO',   3  => 'MARZO',
            4  => 'ABRIL',      5  => 'MAYO',       6  => 'JUNIO',
            7  => 'JULIO',      8  => 'AGOSTO',     9  => 'SEPTIEMBRE',
            10 => 'OCTUBRE',    11 => 'NOVIEMBRE',  12 => 'DICIEMBRE',
        ];
        $meses_inverso = array_flip($meses_map);

        // Array de nombres de meses seleccionados para el whereIn
        $meses_seleccionados = [];
        for ($m = $mes_desde; $m <= $mes_hasta; $m++) {
            $meses_seleccionados[] = $meses_map[$m];
        }

        // ── BASES ──────────────────────────────────────────────
        $bases_total   = $baseModel->countAll();
        $bases_activas = $baseModel->where('estado', 'activo')->countAllResults();
        $pct_bases     = ($bases_total > 0) ? round(($bases_activas / $bases_total) * 100) : 0;

        // ── MÓVILES ────────────────────────────────────────────
        // PROBLEMA ORIGINAL: usaba where('estado','operativo/mantenimiento/baja')
        // pero la tabla no tiene esos estados — diferencia por TIPO y filtra por ejercicio
        $moviles_op = (int)($db->table('movil')
            ->selectSum('cantidad')
            ->where('ejercicio', $ejercicio_actual)
            ->where('tipo', 'MOVILES OPERATIVOS')
            ->get()->getRowObject()->cantidad ?? 0);

        $moviles_mantenimiento = (int)($db->table('movil')
            ->selectSum('cantidad')
            ->where('ejercicio', $ejercicio_actual)
            ->where('tipo', 'MOVILES LOGISTICA')
            ->get()->getRowObject()->cantidad ?? 0);

        $moviles_baja = (int)($db->table('movil')
            ->selectSum('cantidad')
            ->where('ejercicio', $ejercicio_actual)
            ->where('tipo', 'FUERA DE SERVICIO')
            ->get()->getRowObject()->cantidad ?? 0);

        $moviles_total = $moviles_op + $moviles_mantenimiento + $moviles_baja;
        $pct_moviles   = ($moviles_total > 0) ? round(($moviles_op / $moviles_total) * 100) : 0;

        // ── ASISTENCIAS CON FILTRO ─────────────────────────────
        // PROBLEMA ORIGINAL: el whereIn necesita que los valores coincidan
        // exactamente con lo guardado en BD (mayúsculas, sin espacios extra)
        $asistencia_totales = $db->table('asistencia')
            ->select('
                SUM(emergencias_con_medico)   AS emergencias_con_medico,
                SUM(emergencias_sin_medico)   AS emergencias_sin_medico,
                SUM(urgencias)                AS urgencias,
                SUM(derivacion_publica)       AS derivacion_publica,
                SUM(derivacion_privada)       AS derivacion_privada,
                SUM(internacion_domiciliaria) AS internacion_domiciliaria,
                SUM(total)                    AS total
            ')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $meses_seleccionados)
            ->get()->getRowObject();

        $total_asistencias  = (int)($asistencia_totales->total                    ?? 0);
        $emerg_con_medico   = (int)($asistencia_totales->emergencias_con_medico   ?? 0);
        $emerg_sin_medico   = (int)($asistencia_totales->emergencias_sin_medico   ?? 0);
        $urgencias          = (int)($asistencia_totales->urgencias                ?? 0);
        $derivacion_publica = (int)($asistencia_totales->derivacion_publica       ?? 0);
        $derivacion_privada = (int)($asistencia_totales->derivacion_privada       ?? 0);
        $asist_internacion  = (int)($asistencia_totales->internacion_domiciliaria ?? 0);

        // ── DATOS MENSUALES PARA EL GRÁFICO ───────────────────
        // PROBLEMA ORIGINAL: array_fill(0, 12, 0) — índices 0-11, correcto
        // pero el mapeo necesita trim() y strtoupper() por si hay espacios o case distinto
        $datos_mensuales = array_fill(0, 12, 0);

        $rows_mes = $db->table('asistencia')
            ->select('mes, SUM(total) AS total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $meses_seleccionados)
            ->groupBy('mes')
            ->get()->getResultObject();

        foreach ($rows_mes as $row) {
            $key = strtoupper(trim($row->mes));
            $num = $meses_inverso[$key] ?? null;
            if ($num !== null) {
                $datos_mensuales[$num - 1] = (int)$row->total;
            }
        }

        // ── TOP 5 BASES CON FILTRO ─────────────────────────────
        $top_bases = $db->table('asistencia')
            ->select('nombre, SUM(total) AS total')
            ->where('ejercicio', $ejercicio_actual)
            ->whereIn('mes', $meses_seleccionados)
            ->groupBy('nombre')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->get()->getResultObject();

        // ── INTERNACIÓN DOMICILIARIA — filtrada por ejercicio ──
        // PROBLEMA ORIGINAL: no filtraba por ejercicio, sumaba todos los años
        $identificacion_totales = $db->table('identificacion')
            ->select('SUM(adulto) AS adulto, SUM(pediatrico) AS pediatrico, SUM(total) AS total')
            ->where('ejercicio', $ejercicio_actual)
            ->get()->getRowObject();

        $internacion_total      = (int)($identificacion_totales->total      ?? 0);
        $internacion_adulto     = (int)($identificacion_totales->adulto      ?? 0);
        $internacion_pediatrico = (int)($identificacion_totales->pediatrico  ?? 0);

        $pct_adultos    = ($internacion_total > 0)
            ? round(($internacion_adulto     / $internacion_total) * 100) : 0;
        $pct_pediatrico = ($internacion_total > 0)
            ? round(($internacion_pediatrico / $internacion_total) * 100) : 0;

        // ── GENERAL ────────────────────────────────────────────
        $total_departamentos = $departamentoModel->countAll();

        return view('admin/base_views', compact(
            'bases_total', 'bases_activas', 'pct_bases',
            'moviles_total', 'moviles_op', 'pct_moviles',
            'moviles_mantenimiento', 'moviles_baja',
            'total_asistencias',
            'emerg_con_medico', 'emerg_sin_medico',
            'urgencias', 'derivacion_publica', 'derivacion_privada',
            'asist_internacion',
            'datos_mensuales', 'top_bases',
            'ejercicio_actual', 'mes_desde', 'mes_hasta',
            'internacion_total', 'internacion_adulto', 'internacion_pediatrico',
            'pct_adultos', 'pct_pediatrico',
            'total_departamentos'
        ));
    }
}