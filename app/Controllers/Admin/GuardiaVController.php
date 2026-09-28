<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class GuardiaVController extends BaseController
{
    private $semestresValidos = ['PRIMER', 'SEGUNDO'];

    // ─── Filtros (mismos nombres que GuardiaController para que el Excel respete lo filtrado)
    private function leerFiltros(): array
    {
        return [
            'filtro_anio'     => $this->request->getGet('anio')        ?? '',
            'filtro_semestre' => $this->request->getGet('semestre')    ?? '',
            'filtro_efector'  => $this->request->getGet('efector_id')  ?? '',
            'filtro_servicio' => $this->request->getGet('servicio_id') ?? '',
        ];
    }

    // guardia + efector + servicio con los filtros aplicados
    private function builder($db, array $filtros)
    {
        $builder = $db->table('guardia')
            ->join('efector', 'efector.efector_id = guardia.efector_id')
            ->join('servicio', 'servicio.servicio_id = guardia.servicio_id');

        if ($filtros['filtro_anio'] !== '')     $builder->where('guardia.anio', $filtros['filtro_anio']);
        if ($filtros['filtro_semestre'] !== '') $builder->where('guardia.semestre', $filtros['filtro_semestre']);
        if ($filtros['filtro_efector'] !== '')  $builder->where('guardia.efector_id', $filtros['filtro_efector']);
        if ($filtros['filtro_servicio'] !== '') $builder->where('guardia.servicio_id', $filtros['filtro_servicio']);

        return $builder;
    }

    public function guardiav()
    {
        helper('auth');

        $db      = \Config\Database::connect();
        $filtros = $this->leerFiltros();

        // ── Opciones de filtros ──
        $anios = array_column(
            $db->table('guardia')->select('anio')->distinct()->orderBy('anio', 'DESC')->get()->getResultArray(),
            'anio'
        );
        $efectores = $db->table('efector')->select('efector_id, nombre')->orderBy('nombre', 'ASC')->get()->getResultArray();
        $servicios = $db->table('servicio')->select('servicio_id, nombre')->orderBy('orden', 'ASC')->get()->getResultArray();

        // ── KPI ──
        $kpi = $this->builder($db, $filtros)
            ->select('
                COALESCE(SUM(guardia.cantidad), 0)  AS total,
                COUNT(DISTINCT guardia.efector_id)  AS hospitales,
                COUNT(DISTINCT guardia.servicio_id) AS servicios
            ', false)
            ->get()->getRowArray();
        $kpi = array_map('intval', $kpi ?? []);
        $kpi['promedio'] = ($kpi['hospitales'] ?? 0) > 0 ? (int) round($kpi['total'] / $kpi['hospitales']) : 0;

        // Años a comparar en los gráficos: el filtrado o todos
        $aniosGrafico = ($filtros['filtro_anio'] !== '') ? [(int) $filtros['filtro_anio']] : array_map('intval', $anios);
        sort($aniosGrafico);

        // ══ POR SERVICIO ══════════════════════════════════════════════
        $porServicioAnio = $this->builder($db, $filtros)
            ->select('servicio.nombre AS servicio, servicio.orden, guardia.anio, SUM(guardia.cantidad) AS cantidad')
            ->groupBy('servicio.servicio_id, servicio.nombre, servicio.orden, guardia.anio')
            ->orderBy('servicio.orden', 'ASC')
            ->orderBy('guardia.anio', 'ASC')
            ->get()->getResultArray();

        // Pivot servicio → [anio => cantidad, total]
        $tablaServicios = [];
        foreach ($porServicioAnio as $f) {
            $s = $f['servicio'];
            if (!isset($tablaServicios[$s])) {
                $tablaServicios[$s] = ['servicio' => $s, 'anios' => [], 'total' => 0];
            }
            $tablaServicios[$s]['anios'][$f['anio']] = (int) $f['cantidad'];
            $tablaServicios[$s]['total'] += (int) $f['cantidad'];
        }
        $tablaServicios = array_values($tablaServicios);

        // ══ POR HOSPITAL ══════════════════════════════════════════════
        $tablaHospitales = $this->builder($db, $filtros)
            ->select("efector.nombre AS hospital, COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO') AS region, SUM(guardia.cantidad) AS cantidad", false)
            ->groupBy('efector.efector_id, efector.nombre, efector.region')
            ->orderBy('cantidad', 'DESC')
            ->get()->getResultArray();

        $porRegion = $this->builder($db, $filtros)
            ->select("COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO') AS region, SUM(guardia.cantidad) AS cantidad", false)
            ->groupBy("COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO')", false)
            ->orderBy('cantidad', 'DESC')
            ->get()->getResultArray();

        // Pestaña visible: por servicio o por hospital
        $tab = $this->request->getGet('tab') === 'hospital' ? 'hospital' : 'servicio';

        return view('admin/guardia_panel_views', array_merge($filtros, [
            'tab'             => $tab,
            'anios'           => $anios,
            'semestres'       => $this->semestresValidos,
            'efectores'       => $efectores,
            'servicios'       => $servicios,
            'kpi'             => $kpi,
            'aniosGrafico'    => $aniosGrafico,
            'tablaServicios'  => $tablaServicios,
            'tablaHospitales' => $tablaHospitales,
            'porRegion'       => $porRegion,
        ]));
    }
}
