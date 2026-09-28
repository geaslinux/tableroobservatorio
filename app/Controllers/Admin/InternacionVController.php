<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class InternacionVController extends BaseController
{
    // Pestañas del panel (clave => tabla principal)
    private const TABS = ['camas', 'salud', 'rend', 'uti', 'materno', 'capacidad'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    // Mismos nombres de parámetro que los controllers de cada módulo,
    // así el Excel de cada pestaña respeta lo filtrado.
    private function leerFiltros(): array
    {
        return [
            'filtro_efector'   => $this->request->getGet('efector_id') ?? '',
            'filtro_region'    => $this->request->getGet('region')     ?? '',
            'filtro_ejercicio' => $this->request->getGet('ejercicio')  ?? '',
            'filtro_semestre'  => $this->request->getGet('semestre')   ?? '',
        ];
    }

    // Tabla del módulo + efector, con filtros de hospital/región y (si corresponde) de período
    private function builder($db, string $tabla, array $filtros, bool $conPeriodo = false)
    {
        $builder = $db->table($tabla)
            ->join('efector', "efector.efector_id = {$tabla}.efector_id", 'left');

        if ($filtros['filtro_efector'] !== '') $builder->where("{$tabla}.efector_id", $filtros['filtro_efector']);
        if ($filtros['filtro_region'] !== '')  $builder->where('efector.region', $filtros['filtro_region']);

        if ($conPeriodo) {
            if ($filtros['filtro_ejercicio'] !== '') $builder->where("{$tabla}.ejercicio", $filtros['filtro_ejercicio']);
            // lista_espera tiene ejercicio pero no semestre
            if ($filtros['filtro_semestre'] !== '' && $tabla !== 'lista_espera') {
                $builder->where("{$tabla}.semestre", $filtros['filtro_semestre']);
            }
        }

        return $builder;
    }

    // Indicadores calculados desde las sumas (no se promedian porcentajes)
    private function indicadores(array $f): array
    {
        $egresos     = (int) ($f['egresos'] ?? 0);
        $pacienteDia = (float) ($f['paciente_dia'] ?? 0);
        $camaDisp    = (float) ($f['cama_disponible'] ?? 0);

        return [
            'ocupacion'   => $camaDisp > 0 ? round($pacienteDia * 100 / $camaDisp, 1) : 0,
            'estada'      => $egresos > 0 ? round(($f['dias_estada'] ?? 0) / $egresos, 1) : 0,
            'mortalidad'  => $egresos > 0 ? round(($f['defuncion'] ?? 0) * 100 / $egresos, 2) : 0,
        ];
    }

    // Rango de estándar según el % ocupacional
    public static function estandar(float $ocupacion): string
    {
        if ($ocupacion <= 30) return '≤ 30%';
        if ($ocupacion < 60)  return '31 - 59%';
        if ($ocupacion < 85)  return '60 - 84%';
        return '≥ 85%';
    }

    // Rendimiento hospitalario (general y UTI comparten estructura)
    private function datosRendimiento($db, string $tabla, array $filtros): array
    {
        $sumas = "
            COALESCE(SUM({$tabla}.total_egresos), 0)   AS egresos,
            COALESCE(SUM({$tabla}.altas), 0)           AS altas,
            COALESCE(SUM({$tabla}.defuncion), 0)       AS defuncion,
            COALESCE(SUM({$tabla}.dias_estada), 0)     AS dias_estada,
            COALESCE(SUM({$tabla}.paciente_dia), 0)    AS paciente_dia,
            COALESCE(SUM({$tabla}.cama_disponible), 0) AS cama_disponible
        ";

        $total = $this->builder($db, $tabla, $filtros, true)->select($sumas, false)->get()->getRowArray() ?? [];
        $kpi   = array_merge(array_map('intval', $total), $this->indicadores($total));

        $filas = $this->builder($db, $tabla, $filtros, true)
            ->select("efector.nombre AS hospital, {$sumas}", false)
            ->groupBy("{$tabla}.efector_id, efector.nombre")
            ->get()->getResultArray();

        $porEstandar = [];
        foreach ($filas as &$f) {
            $f = array_merge($f, $this->indicadores($f));
            $f['estandar'] = self::estandar($f['ocupacion']);
            $porEstandar[$f['estandar']] = ($porEstandar[$f['estandar']] ?? 0) + 1;
        }
        unset($f);

        usort($filas, function ($a, $b) { return $b['ocupacion'] <=> $a['ocupacion']; });

        // Orden fijo de los rangos para la dona
        $estandares = [];
        foreach (['≤ 30%', '31 - 59%', '60 - 84%', '≥ 85%'] as $e) {
            if (!empty($porEstandar[$e])) $estandares[] = ['estandar' => $e, 'cantidad' => $porEstandar[$e]];
        }

        return ['kpi' => $kpi, 'filas' => $filas, 'estandares' => $estandares];
    }

    public function internacionv()
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
        $semestres  = [];
        foreach (['lista_espera', 'rendimiento_hospitalario', 'rendimiento_hospitalario_uti', 'rh_materno'] as $t) {
            $ejercicios = array_merge($ejercicios, array_column($db->table($t)->select('ejercicio')->distinct()->get()->getResultArray(), 'ejercicio'));
            if ($t !== 'lista_espera') {
                $semestres = array_merge($semestres, array_column($db->table($t)->select('semestre')->distinct()->get()->getResultArray(), 'semestre'));
            }
        }
        $ejercicios = array_values(array_unique(array_filter($ejercicios)));
        rsort($ejercicios);
        $semestres = array_values(array_unique(array_filter($semestres)));
        sort($semestres);

        // ══ 1. CAMAS HOSPITALARIAS (lista de espera quirúrgica) ═════════
        $esperaKpi = $this->builder($db, 'lista_espera', $filtros, true)
            ->select('
                COALESCE(SUM(lista_espera.cantidad_pacientes), 0)      AS pacientes,
                COALESCE(SUM(lista_espera.comp_quirurgica_alta), 0)    AS alta,
                COALESCE(SUM(lista_espera.comp_quirurgica_mediana), 0) AS mediana,
                COALESCE(SUM(lista_espera.comp_quirurgica_baja), 0)    AS baja
            ', false)
            ->get()->getRowArray();
        $esperaKpi = array_map('intval', $esperaKpi ?? []);

        $esperaFilas = $this->builder($db, 'lista_espera', $filtros, true)
            ->join('especialidad', 'especialidad.especialidad_id = lista_espera.especialidad_id', 'left')
            ->select("
                efector.nombre AS hospital,
                COALESCE(especialidad.nombre, 'SIN ESPECIALIDAD') AS especialidad,
                SUM(lista_espera.cantidad_pacientes)      AS pacientes,
                SUM(lista_espera.comp_quirurgica_alta)    AS alta,
                SUM(lista_espera.comp_quirurgica_mediana) AS mediana,
                SUM(lista_espera.comp_quirurgica_baja)    AS baja
            ", false)
            ->groupBy('lista_espera.efector_id, efector.nombre, especialidad.nombre')
            ->having('pacientes >', 0)
            ->orderBy('pacientes', 'DESC')
            ->get()->getResultArray();

        $esperaPorHospital = $this->builder($db, 'lista_espera', $filtros, true)
            ->select('efector.nombre AS hospital, SUM(lista_espera.cantidad_pacientes) AS pacientes')
            ->groupBy('lista_espera.efector_id, efector.nombre')
            ->having('pacientes >', 0)
            ->orderBy('pacientes', 'DESC')
            ->get()->getResultArray();

        // ══ 2. SALUD MENTAL CAMAS ═══════════════════════════════════════
        $saludKpi = $this->builder($db, 'salud_mental_camas', $filtros)
            ->select("
                COALESCE(SUM(salud_mental_camas.cb_adultos), 0)     AS adultos,
                COALESCE(SUM(salud_mental_camas.cb_pediatricos), 0) AS pediatricas,
                COALESCE(SUM(CASE WHEN salud_mental_camas.tipo = 'PUBLICO'
                    THEN COALESCE(salud_mental_camas.cb_adultos, 0) + COALESCE(salud_mental_camas.cb_pediatricos, 0) END), 0) AS publicas,
                COUNT(DISTINCT salud_mental_camas.efector_id)       AS hospitales
            ", false)
            ->get()->getRowArray();
        $saludKpi = array_map('intval', $saludKpi ?? []);
        $saludKpi['total'] = ($saludKpi['adultos'] ?? 0) + ($saludKpi['pediatricas'] ?? 0);

        $saludFilas = $this->builder($db, 'salud_mental_camas', $filtros)
            ->select("
                efector.nombre AS hospital,
                COALESCE(NULLIF(TRIM(salud_mental_camas.modalidad), ''), 'SIN DATO') AS modalidad,
                COALESCE(NULLIF(TRIM(salud_mental_camas.tipo), ''), 'SIN DATO')      AS tipo,
                COALESCE(salud_mental_camas.cb_adultos, 0)     AS adultos,
                COALESCE(salud_mental_camas.cb_pediatricos, 0) AS pediatricas
            ", false)
            ->orderBy('efector.nombre', 'ASC')
            ->get()->getResultArray();

        $saludPorModalidad = [];   // [tipo => [modalidad => camas]]
        $saludPorTipo      = [];
        foreach ($saludFilas as &$f) {
            $f['total'] = (int) $f['adultos'] + (int) $f['pediatricas'];
            $saludPorModalidad[$f['tipo']][$f['modalidad']] = ($saludPorModalidad[$f['tipo']][$f['modalidad']] ?? 0) + $f['total'];
            $saludPorTipo[$f['tipo']] = ($saludPorTipo[$f['tipo']] ?? 0) + $f['total'];
        }
        unset($f);

        // ══ 3 y 4. RENDIMIENTO HOSPITALARIO (general y UTI) ═════════════
        $rend = $this->datosRendimiento($db, 'rendimiento_hospitalario', $filtros);
        $uti  = $this->datosRendimiento($db, 'rendimiento_hospitalario_uti', $filtros);

        // ══ 5. RH MATERNO ═══════════════════════════════════════════════
        $sumasMaterno = '
            COALESCE(SUM(rh_materno.ingresos), 0)        AS ingresos,
            COALESCE(SUM(rh_materno.total_egresos), 0)   AS egresos,
            COALESCE(SUM(rh_materno.defuncion), 0)       AS defuncion,
            COALESCE(SUM(rh_materno.dias_estada), 0)     AS dias_estada,
            COALESCE(SUM(rh_materno.paciente_dia), 0)    AS paciente_dia,
            COALESCE(SUM(rh_materno.cama_disponible), 0) AS cama_disponible
        ';

        $maternoTotal = $this->builder($db, 'rh_materno', $filtros, true)->select($sumasMaterno, false)->get()->getRowArray() ?? [];
        $maternoKpi   = array_merge(array_map('intval', $maternoTotal), $this->indicadores($maternoTotal));

        $maternoFilas = $this->builder($db, 'rh_materno', $filtros, true)
            ->select("
                COALESCE(NULLIF(TRIM(rh_materno.servicio), ''), 'SIN DATO') AS servicio,
                COALESCE(NULLIF(TRIM(rh_materno.sector), ''), 'SIN DATO')   AS sector,
                {$sumasMaterno}
            ", false)
            ->groupBy("COALESCE(NULLIF(TRIM(rh_materno.servicio), ''), 'SIN DATO'), COALESCE(NULLIF(TRIM(rh_materno.sector), ''), 'SIN DATO')", false)
            ->orderBy('servicio', 'ASC')
            ->orderBy('egresos', 'DESC')
            ->get()->getResultArray();

        $maternoPorServicio = [];
        foreach ($maternoFilas as &$f) {
            $f = array_merge($f, $this->indicadores($f));
            $f['estandar'] = self::estandar($f['ocupacion']);
            $maternoPorServicio[$f['servicio']] = ($maternoPorServicio[$f['servicio']] ?? 0) + (int) $f['egresos'];
        }
        unset($f);

        // ══ 6. CAPACIDAD DE CAMAS ═══════════════════════════════════════
        $capacidadKpi = $this->builder($db, 'capacidad_camas', $filtros)
            ->select('
                COALESCE(SUM(capacidad_camas.camas_disponibles), 0)          AS disponibles,
                COALESCE(SUM(capacidad_camas.camas_disponibles_publicas), 0) AS publicas,
                COALESCE(SUM(capacidad_camas.total_uti), 0)                  AS uti,
                COALESCE(SUM(capacidad_camas.total_utin), 0)                 AS utin,
                COALESCE(SUM(capacidad_camas.total_basicas), 0)              AS basicas,
                COALESCE(SUM(capacidad_camas.camas_disponibles_publicas_salud_mental), 0) AS salud_mental
            ', false)
            ->get()->getRowArray();
        $capacidadKpi = array_map('intval', $capacidadKpi ?? []);

        $capacidadFilas = $this->builder($db, 'capacidad_camas', $filtros)
            ->select("
                efector.nombre AS hospital,
                COALESCE(NULLIF(TRIM(efector.region), ''), 'SIN DATO')        AS region,
                COALESCE(NULLIF(TRIM(capacidad_camas.tipo), ''), 'SIN DATO')  AS tipo,
                COALESCE(capacidad_camas.total_uti, 0)         AS uti,
                COALESCE(capacidad_camas.total_utin, 0)        AS utin,
                COALESCE(capacidad_camas.total_basicas, 0)     AS basicas,
                COALESCE(capacidad_camas.camas_disponibles, 0) AS disponibles
            ", false)
            ->orderBy('disponibles', 'DESC')
            ->get()->getResultArray();

        $capacidadPorRegion = [];   // [region => [uti, utin, basicas]]
        $capacidadPorTipo   = [];
        foreach ($capacidadFilas as $f) {
            $r = $f['region'];
            if (!isset($capacidadPorRegion[$r])) $capacidadPorRegion[$r] = ['uti' => 0, 'utin' => 0, 'basicas' => 0];
            $capacidadPorRegion[$r]['uti']     += (int) $f['uti'];
            $capacidadPorRegion[$r]['utin']    += (int) $f['utin'];
            $capacidadPorRegion[$r]['basicas'] += (int) $f['basicas'];
            $capacidadPorTipo[$f['tipo']] = ($capacidadPorTipo[$f['tipo']] ?? 0) + (int) $f['disponibles'];
        }
        ksort($capacidadPorRegion);

        // Pestaña visible
        $tab = in_array($this->request->getGet('tab'), self::TABS, true) ? $this->request->getGet('tab') : 'camas';

        return view('admin/internacion_views', array_merge($filtros, [
            'tab'                => $tab,
            'efectores'          => $efectores,
            'regiones'           => $regiones,
            'ejercicios'         => $ejercicios,
            'semestres'          => $semestres,

            'esperaKpi'          => $esperaKpi,
            'esperaFilas'        => $esperaFilas,
            'esperaPorHospital'  => $esperaPorHospital,

            'saludKpi'           => $saludKpi,
            'saludFilas'         => $saludFilas,
            'saludPorModalidad'  => $saludPorModalidad,
            'saludPorTipo'       => $saludPorTipo,

            'rend'               => $rend,
            'uti'                => $uti,

            'maternoKpi'         => $maternoKpi,
            'maternoFilas'       => $maternoFilas,
            'maternoPorServicio' => $maternoPorServicio,

            'capacidadKpi'       => $capacidadKpi,
            'capacidadFilas'     => $capacidadFilas,
            'capacidadPorRegion' => $capacidadPorRegion,
            'capacidadPorTipo'   => $capacidadPorTipo,
        ]));
    }
}
