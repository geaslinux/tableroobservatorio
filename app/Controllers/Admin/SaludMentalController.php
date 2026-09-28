<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Panel de Salud mental: pacientes electrodependientes.
 * Pestañas: perfil de pacientes y territorio (mapa).
 */
class SaludMentalController extends BaseController
{
    private const TABS = ['perfil', 'mapa'];

    // ─── Filtros (mismos nombres que ElectrodependienteController para que el Excel respete lo filtrado)
    private function leerFiltros(): array
    {
        return [
            'filtro_efector'   => $this->request->getGet('efector')       ?? '',
            'filtro_localidad' => $this->request->getGet('localidad')     ?? '',
            'filtro_tipo'      => $this->request->getGet('tipo')          ?? '',
            'filtro_factor'    => $this->request->getGet('factor_riesgo') ?? '',
        ];
    }

    // electrodependiente + relaciones, sin los eliminados, con filtros
    private function builder($db, array $filtros)
    {
        $builder = $db->table('electrodependiente')
            ->join('localidad l',   'l.localidad_id   = electrodependiente.localidad_id',   'left')
            ->join('efector e',     'e.efector_id     = electrodependiente.efector_id',     'left')
            ->join('diagnostico d', 'd.diagnostico_id = electrodependiente.diagnostico_id', 'left')
            ->whereIn('electrodependiente.estado', ['activo', 'desactivado']);

        if ($filtros['filtro_efector'] !== '')   $builder->where('electrodependiente.efector_id', $filtros['filtro_efector']);
        if ($filtros['filtro_localidad'] !== '') $builder->where('electrodependiente.localidad_id', $filtros['filtro_localidad']);
        if ($filtros['filtro_tipo'] !== '')      $builder->where('electrodependiente.tipo', $filtros['filtro_tipo']);
        if ($filtros['filtro_factor'] !== '')    $builder->where('electrodependiente.factor_riesgo', $filtros['filtro_factor']);

        return $builder;
    }

    public function salud()
    {
        helper('auth');

        $db      = \Config\Database::connect();
        $filtros = $this->leerFiltros();

        // ── Opciones de filtros (solo valores que tienen pacientes) ──
        $efectores = $db->table('efector e')
            ->select('e.efector_id, e.nombre')->distinct()
            ->join('electrodependiente', 'electrodependiente.efector_id = e.efector_id')
            ->orderBy('e.nombre', 'ASC')->get()->getResultArray();
        $localidades = $db->table('localidad l')
            ->select('l.localidad_id, l.nombre')->distinct()
            ->join('electrodependiente', 'electrodependiente.localidad_id = l.localidad_id')
            ->orderBy('l.nombre', 'ASC')->get()->getResultArray();

        $riesgoSql = "COALESCE(NULLIF(TRIM(electrodependiente.factor_riesgo), ''), 'S/D')";

        // ── KPI ──
        $kpi = $this->builder($db, $filtros)
            ->select("
                COUNT(*)                                                         AS pacientes,
                SUM(electrodependiente.tipo = 'ADULTO')                          AS adultos,
                SUM(electrodependiente.tipo <> 'ADULTO')                         AS ninos,
                SUM(electrodependiente.factor_riesgo = 'ALTO')                   AS riesgo_alto,
                SUM(electrodependiente.factor_riesgo = 'MEDIANO')                AS riesgo_mediano,
                SUM(electrodependiente.cud = 'SI')                               AS con_cud,
                SUM(TRIM(COALESCE(electrodependiente.coordenadas, '')) <> '')    AS geolocalizados,
                COUNT(DISTINCT electrodependiente.efector_id)                    AS hospitales,
                COUNT(DISTINCT electrodependiente.localidad_id)                  AS localidades,
                SUM(TRIM(COALESCE(electrodependiente.seguimiento, '')) LIKE 'EVALUACION PENDI%') AS eval_pendiente
            ", false)
            ->get()->getRowArray();
        $kpi = array_map('intval', $kpi ?? []);

        // ══ PERFIL DE PACIENTES ═════════════════════════════════════════
        $porDiagnostico = $this->builder($db, $filtros)
            ->select("
                COALESCE(d.nombre, 'SIN DIAGNÓSTICO') AS diagnostico,
                COUNT(*)                                        AS pacientes,
                SUM(electrodependiente.factor_riesgo = 'ALTO')  AS alto,
                SUM(electrodependiente.tipo <> 'ADULTO')        AS ninos
            ", false)
            ->groupBy('electrodependiente.diagnostico_id, d.nombre')
            ->orderBy('pacientes', 'DESC')
            ->get()->getResultArray();

        $porRiesgo = $this->builder($db, $filtros)
            ->select("{$riesgoSql} AS riesgo, COUNT(*) AS pacientes", false)
            ->groupBy($riesgoSql, false)
            ->get()->getResultArray();

        // Orden fijo: ALTO, MEDIANO, BAJO, S/D
        $ordenRiesgo = array_flip(['ALTO', 'MEDIANO', 'BAJO', 'S/D']);
        usort($porRiesgo, function ($a, $b) use ($ordenRiesgo) {
            return ($ordenRiesgo[$a['riesgo']] ?? 9) <=> ($ordenRiesgo[$b['riesgo']] ?? 9);
        });

        // Riesgo según tipo de paciente (para barras apiladas)
        $riesgoPorTipo = [];
        $filasRT = $this->builder($db, $filtros)
            ->select("electrodependiente.tipo, {$riesgoSql} AS riesgo, COUNT(*) AS pacientes", false)
            ->groupBy("electrodependiente.tipo, {$riesgoSql}", false)
            ->get()->getResultArray();
        foreach ($filasRT as $f) {
            $riesgoPorTipo[$f['riesgo']][$f['tipo'] ?: 'S/D'] = (int) $f['pacientes'];
        }

        // ══ TERRITORIO Y MAPA ═══════════════════════════════════════════
        $porLocalidad = $this->builder($db, $filtros)
            ->select("
                COALESCE(l.nombre, 'SIN LOCALIDAD') AS localidad,
                COUNT(*)                                       AS pacientes,
                SUM(electrodependiente.factor_riesgo = 'ALTO') AS alto,
                SUM(TRIM(COALESCE(electrodependiente.coordenadas, '')) <> '') AS geo
            ", false)
            ->groupBy('electrodependiente.localidad_id, l.nombre')
            ->orderBy('pacientes', 'DESC')
            ->get()->getResultArray();

        $porHospital = $this->builder($db, $filtros)
            ->select("COALESCE(e.nombre, 'SIN HOSPITAL') AS hospital, COUNT(*) AS pacientes", false)
            ->groupBy('electrodependiente.efector_id, e.nombre')
            ->orderBy('pacientes', 'DESC')
            ->get()->getResultArray();

        // Puntos del mapa: solo pacientes con coordenadas válidas
        $filasGeo = $this->builder($db, $filtros)
            ->select("
                electrodependiente.electrodependiente_id AS id,
                electrodependiente.paciente, electrodependiente.edad, electrodependiente.tipo,
                electrodependiente.coordenadas, electrodependiente.factor_riesgo AS riesgo,
                l.nombre AS localidad, e.nombre AS efector, d.nombre AS diagnostico
            ", false)
            ->where("TRIM(COALESCE(electrodependiente.coordenadas, '')) <>", '')
            ->get()->getResultArray();

        $puntos = [];
        foreach ($filasGeo as $f) {
            $coords = array_map('trim', explode(',', $f['coordenadas']));
            if (count($coords) < 2) continue;
            $lat = (float) $coords[0];
            $lng = (float) $coords[1];
            if (($lat == 0 && $lng == 0) || abs($lat) > 90 || abs($lng) > 180) continue;

            unset($f['coordenadas']);
            $puntos[] = $f + ['lat' => $lat, 'lng' => $lng];
        }
        $kpi['geo_validos'] = count($puntos);

        // Pestaña visible
        $tab = in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'perfil';

        return view('admin/saludmental_views', array_merge($filtros, [
            'tab'            => $tab,
            'efectores'      => $efectores,
            'localidades'    => $localidades,
            'kpi'            => $kpi,
            'porDiagnostico' => $porDiagnostico,
            'porRiesgo'      => $porRiesgo,
            'riesgoPorTipo'  => $riesgoPorTipo,
            'porLocalidad'   => $porLocalidad,
            'porHospital'    => $porHospital,
            'puntos'         => $puntos,
        ]));
    }
}
