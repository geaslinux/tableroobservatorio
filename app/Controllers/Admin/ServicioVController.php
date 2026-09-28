<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Panel de Servicios transversales.
 * Pestañas: cantidad de operativos y transfusión mensual.
 */
class ServicioVController extends BaseController
{
    private const TABS  = ['operativos', 'transfusion'];
    private const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                           'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // ─── Filtros (mismos nombres que los controllers de cada módulo) ─────
    private function leerFiltros(): array
    {
        return [
            'filtro_ejercicio' => $this->request->getGet('ejercicio') ?? '',
            'filtro_efector'   => $this->request->getGet('efector')   ?? '',
            'filtro_region'    => $this->request->getGet('region')    ?? '',
        ];
    }

    public function serviciov()
    {
        helper('auth');

        $db      = \Config\Database::connect();
        $filtros = $this->leerFiltros();
        $ej      = $filtros['filtro_ejercicio'];

        // ── Opciones de filtros ──
        $ejercicios = [];
        foreach (['cantidad_operativo', 'transfusion'] as $t) {
            $ejercicios = array_merge($ejercicios, array_column(
                $db->table($t)->select('ejercicio')->distinct()->whereIn('estado', ['activo', 'desactivado'])->get()->getResultArray(),
                'ejercicio'
            ));
        }
        $ejercicios = array_values(array_unique(array_map('intval', array_filter($ejercicios))));
        rsort($ejercicios);

        $efectores = $db->table('efector e')
            ->select('e.efector_id, e.nombre')->distinct()
            ->join('transfusion', 'transfusion.efector_id = e.efector_id')
            ->orderBy('e.nombre', 'ASC')->get()->getResultArray();
        $regiones = array_column(
            $db->table('efector')->select('region')->distinct()
               ->where('region IS NOT NULL')->where('region !=', '')
               ->orderBy('region', 'ASC')->get()->getResultArray(),
            'region'
        );

        // ══ 1. CANTIDAD DE OPERATIVOS ═══════════════════════════════════
        $opBuilder = function () use ($db, $ej) {
            $b = $db->table('cantidad_operativo')
                ->join('operativo', 'operativo.operativo_id = cantidad_operativo.operativo_id', 'left')
                ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left')
                ->whereIn('cantidad_operativo.estado', ['activo', 'desactivado']);
            if ($ej !== '') $b->where('cantidad_operativo.ejercicio', $ej);
            return $b;
        };

        $opKpi = $opBuilder()
            ->select('
                COALESCE(SUM(cantidad_operativo.via_publica), 0)      AS via_publica,
                COALESCE(SUM(cantidad_operativo.via_hospitalaria), 0) AS via_hospitalaria,
                COALESCE(SUM(cantidad_operativo.total), 0)            AS total,
                COUNT(*)                                              AS registros,
                COUNT(DISTINCT operativo.nombre)                      AS operativos
            ', false)
            ->get()->getRowArray();
        $opKpi = array_map('intval', $opKpi ?? []);

        // Se agrupa por nombre: el mismo operativo existe con distintos tipos de hemocomponente
        $opPorOperativo = $opBuilder()
            ->select("
                COALESCE(operativo.nombre, 'SIN OPERATIVO')           AS operativo,
                SUM(cantidad_operativo.via_publica)      AS via_publica,
                SUM(cantidad_operativo.via_hospitalaria) AS via_hospitalaria,
                SUM(cantidad_operativo.total)            AS total
            ", false)
            ->groupBy('operativo.nombre')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        $opPorTipo = $opBuilder()
            ->select('tipo_operativo.nombre AS tipo, SUM(cantidad_operativo.total) AS total')
            ->where('operativo.tipo_id IS NOT NULL')
            ->groupBy('tipo_operativo.tipo_op_id, tipo_operativo.nombre')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        // ══ 2. TRANSFUSIÓN MENSUAL ══════════════════════════════════════
        $trBuilder = function (?int $anio = null) use ($db, $filtros, $ej) {
            $b = $db->table('transfusion')
                ->join('efector', 'efector.efector_id = transfusion.efector_id', 'left')
                ->whereIn('transfusion.estado', ['activo', 'desactivado']);
            if ($anio !== null)                     $b->where('transfusion.ejercicio', $anio);
            elseif ($ej !== '')                     $b->where('transfusion.ejercicio', $ej);
            if ($filtros['filtro_efector'] !== '')  $b->where('transfusion.efector_id', $filtros['filtro_efector']);
            if ($filtros['filtro_region'] !== '')   $b->where('efector.region', $filtros['filtro_region']);
            return $b;
        };

        $sumaMeses = implode(' + ', array_map(function ($m) { return "COALESCE(transfusion.{$m}, 0)"; }, self::MESES));
        $selectMeses = implode(', ', array_map(function ($m) { return "COALESCE(SUM(transfusion.{$m}), 0) AS {$m}"; }, self::MESES));

        $trKpi = $trBuilder()
            ->select("
                COALESCE(SUM(transfusion.total), 0)   AS total,
                COUNT(DISTINCT transfusion.efector_id) AS hospitales,
                COALESCE(SUM({$sumaMeses}), 0)         AS total_meses,
                {$selectMeses}
            ", false)
            ->get()->getRowArray();
        $trKpi = array_map('intval', $trKpi ?? []);
        $trKpi['promedio'] = ($trKpi['hospitales'] ?? 0) > 0 ? (int) round($trKpi['total'] / $trKpi['hospitales']) : 0;

        // Detalle mensual: solo con un ejercicio elegido y si se cargaron meses
        // (2024/2025 tienen solo el total anual)
        $trMensual = [];
        if ($ej !== '' && ($trKpi['total_meses'] ?? 0) > 0) {
            foreach (self::MESES as $m) $trMensual[] = ['mes' => ucfirst(substr($m, 0, 3)), 'total' => $trKpi[$m]];
        }

        // Variación contra el año anterior, solo si ambos años tienen la misma forma de carga
        $trKpi['variacion'] = null;
        $trKpi['no_comparable'] = false;
        if ($ej !== '') {
            $ant = $trBuilder((int) $ej - 1)
                ->select("COALESCE(SUM(transfusion.total), 0) AS total, COUNT(DISTINCT transfusion.efector_id) AS hospitales, COALESCE(SUM({$sumaMeses}), 0) AS total_meses", false)
                ->get()->getRowArray();
            if ((int) ($ant['total'] ?? 0) > 0) {
                $mismaCarga = ((int) $ant['hospitales'] === $trKpi['hospitales'])
                           && (((int) $ant['total_meses'] > 0) === ($trKpi['total_meses'] > 0));
                if ($mismaCarga) {
                    $trKpi['variacion'] = round(($trKpi['total'] - $ant['total']) * 100 / $ant['total'], 1);
                } else {
                    $trKpi['no_comparable'] = true;
                }
            }
        }

        $trPorHospital = $trBuilder()
            ->select("efector.nombre AS hospital, COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO') AS region, SUM(transfusion.total) AS total", false)
            ->groupBy('transfusion.efector_id, efector.nombre, efector.region')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        $regionSql = "COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO')";
        $trPorRegion = $trBuilder()
            ->select("{$regionSql} AS region, SUM(transfusion.total) AS total", false)
            ->groupBy($regionSql, false)
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        // Evolución anual (todos los años o el ejercicio elegido, respetando hospital/región)
        $trPorAnio = $db->table('transfusion')
            ->join('efector', 'efector.efector_id = transfusion.efector_id', 'left')
            ->whereIn('transfusion.estado', ['activo', 'desactivado'])
            ->when($ej !== '', function ($b) use ($ej) { $b->where('transfusion.ejercicio', $ej); })
            ->when($filtros['filtro_efector'] !== '', function ($b) use ($filtros) { $b->where('transfusion.efector_id', $filtros['filtro_efector']); })
            ->when($filtros['filtro_region'] !== '', function ($b) use ($filtros) { $b->where('efector.region', $filtros['filtro_region']); })
            ->select('transfusion.ejercicio, SUM(transfusion.total) AS total, COUNT(DISTINCT transfusion.efector_id) AS hospitales')
            ->groupBy('transfusion.ejercicio')
            ->orderBy('transfusion.ejercicio', 'ASC')
            ->get()->getResultArray();

        // Pestaña visible
        $tab = in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'operativos';

        return view('admin/servicio_views', array_merge($filtros, [
            'tab'            => $tab,
            'ejercicios'     => $ejercicios,
            'efectores'      => $efectores,
            'regiones'       => $regiones,

            'opKpi'          => $opKpi,
            'opPorOperativo' => $opPorOperativo,
            'opPorTipo'      => $opPorTipo,

            'trKpi'          => $trKpi,
            'trMensual'      => $trMensual,
            'trPorHospital'  => $trPorHospital,
            'trPorRegion'    => $trPorRegion,
            'trPorAnio'      => $trPorAnio,
        ]));
    }
}
