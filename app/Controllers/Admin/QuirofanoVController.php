<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class QuirofanoVController extends BaseController
{
    // Pestañas del panel
    private const TABS = ['prod', 'hosp'];

    // ─── Filtros (mismos nombres que los controllers de cada módulo) ─────
    private function leerFiltros(): array
    {
        return [
            'filtro_efector'   => $this->request->getGet('efector_id') ?? '',
            'filtro_region'    => $this->request->getGet('region')     ?? '',
            'filtro_ejercicio' => $this->request->getGet('ejercicio')  ?? '',
        ];
    }

    // Tabla del módulo + efector, con los filtros aplicados
    private function builder($db, string $tabla, array $filtros)
    {
        $builder = $db->table($tabla)
            ->join('efector', "efector.efector_id = {$tabla}.efector_id", 'left');

        if ($filtros['filtro_efector'] !== '')   $builder->where("{$tabla}.efector_id", $filtros['filtro_efector']);
        if ($filtros['filtro_region'] !== '')    $builder->where('efector.region', $filtros['filtro_region']);
        if ($filtros['filtro_ejercicio'] !== '') $builder->where("{$tabla}.ejercicio", $filtros['filtro_ejercicio']);

        return $builder;
    }

    public function quirofanov()
    {
        helper('auth');

        $db      = \Config\Database::connect();
        $filtros = $this->leerFiltros();

        // ── Opciones de filtros ──
        $efectores = $db->table('efector')->select('efector_id, nombre')->orderBy('nombre', 'ASC')->get()->getResultArray();
        $regiones  = array_column(
            $db->table('efector')->select('region')->distinct()
               ->where('region IS NOT NULL')->where('region !=', '')
               ->orderBy('region', 'ASC')->get()->getResultArray(),
            'region'
        );

        $ejercicios = [];
        foreach (['produccion_quirofano', 'produccion_quirofano_hosp'] as $t) {
            $ejercicios = array_merge($ejercicios, array_column($db->table($t)->select('ejercicio')->distinct()->get()->getResultArray(), 'ejercicio'));
        }
        $ejercicios = array_values(array_unique(array_filter($ejercicios)));
        rsort($ejercicios);

        $regionSql = "COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO')";

        // ══ 1. PRODUCCIÓN QUIRÓFANO ═════════════════════════════════════
        $prodKpi = $this->builder($db, 'produccion_quirofano', $filtros)
            ->select('
                COALESCE(SUM(produccion_quirofano.produccion), 0)    AS produccion,
                COUNT(DISTINCT produccion_quirofano.efector_id)      AS hospitales,
                COUNT(DISTINCT produccion_quirofano.ejercicio)       AS ejercicios
            ', false)
            ->get()->getRowArray();
        $prodKpi = array_map('intval', $prodKpi ?? []);
        $prodKpi['promedio'] = ($prodKpi['hospitales'] ?? 0) > 0 ? round($prodKpi['produccion'] / $prodKpi['hospitales'], 1) : 0;

        $prodFilas = $this->builder($db, 'produccion_quirofano', $filtros)
            ->select("efector.nombre AS hospital, {$regionSql} AS region, produccion_quirofano.ejercicio, SUM(produccion_quirofano.produccion) AS produccion", false)
            ->groupBy("produccion_quirofano.efector_id, efector.nombre, efector.region, produccion_quirofano.ejercicio")
            ->orderBy('produccion', 'DESC')
            ->get()->getResultArray();

        $prodPorHospital = $this->builder($db, 'produccion_quirofano', $filtros)
            ->select('efector.nombre AS hospital, SUM(produccion_quirofano.produccion) AS produccion')
            ->groupBy('produccion_quirofano.efector_id, efector.nombre')
            ->orderBy('produccion', 'DESC')
            ->get()->getResultArray();

        $prodPorRegion = $this->builder($db, 'produccion_quirofano', $filtros)
            ->select("{$regionSql} AS region, SUM(produccion_quirofano.produccion) AS produccion", false)
            ->groupBy($regionSql, false)
            ->orderBy('produccion', 'DESC')
            ->get()->getResultArray();

        // ══ 2. PRODUCCIÓN QUIRÓFANO HOSPITALARIA ════════════════════════
        $sumasHosp = '
            COALESCE(SUM(produccion_quirofano_hosp.quirofanos_disponibles), 0)     AS quirofanos,
            COALESCE(SUM(produccion_quirofano_hosp.cirugias_urgencia), 0)          AS urgencia,
            COALESCE(SUM(produccion_quirofano_hosp.total_cirugias_programadas), 0) AS programadas,
            COALESCE(SUM(produccion_quirofano_hosp.sub_total), 0)                  AS total,
            COALESCE(SUM(produccion_quirofano_hosp.cirugias_prog_alta), 0)         AS alta,
            COALESCE(SUM(produccion_quirofano_hosp.cirugias_prog_mediana), 0)      AS mediana,
            COALESCE(SUM(produccion_quirofano_hosp.cirugias_prog_baja), 0)         AS baja,
            COALESCE(SUM(produccion_quirofano_hosp.cirugias_prog_desconocido), 0)  AS desconocido
        ';

        $hospKpi = $this->builder($db, 'produccion_quirofano_hosp', $filtros)
            ->select($sumasHosp, false)
            ->get()->getRowArray();
        $hospKpi = array_map('intval', $hospKpi ?? []);

        $hospFilas = $this->builder($db, 'produccion_quirofano_hosp', $filtros)
            ->select("
                efector.nombre AS hospital,
                produccion_quirofano_hosp.ejercicio,
                {$sumasHosp},
                GROUP_CONCAT(NULLIF(TRIM(produccion_quirofano_hosp.observacion), '') SEPARATOR ' | ') AS observacion
            ", false)
            ->groupBy('produccion_quirofano_hosp.efector_id, efector.nombre, produccion_quirofano_hosp.ejercicio')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        // % sobre el total de la provincia (según lo filtrado)
        foreach ($hospFilas as &$f) {
            $f['porcentaje'] = $hospKpi['total'] > 0 ? round($f['total'] * 100 / $hospKpi['total'], 1) : 0;
        }
        unset($f);

        // Pestaña visible
        $tab = in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'prod';

        return view('admin/quirofano_panel_views', array_merge($filtros, [
            'tab'             => $tab,
            'efectores'       => $efectores,
            'regiones'        => $regiones,
            'ejercicios'      => $ejercicios,

            'prodKpi'         => $prodKpi,
            'prodFilas'       => $prodFilas,
            'prodPorHospital' => $prodPorHospital,
            'prodPorRegion'   => $prodPorRegion,

            'hospKpi'         => $hospKpi,
            'hospFilas'       => $hospFilas,
        ]));
    }
}
