<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Panel de resumen Hospitalario: una introducción de cada módulo
 * (Ambulatorio, Guardia, Internación y Quirófano) con sus indicadores principales.
 *
 * Reglas de período:
 *  - Ambulatorio y capacidad de camas no tienen período: muestran la oferta vigente.
 *  - Guardia, rendimiento, lista de espera y quirófano se filtran por ejercicio.
 *  - Ejercicio "Todos" (vacío) suma todos los años cargados.
 */
class HospitalarioVController extends BaseController
{
    // Tablas con ejercicio: [tabla, campo de año]
    private const TABLAS_EJERCICIO = [
        ['guardia', 'anio'],
        ['rendimiento_hospitalario', 'ejercicio'],
        ['rh_materno', 'ejercicio'],
        ['lista_espera', 'ejercicio'],
        ['produccion_quirofano_hosp', 'ejercicio'],
    ];

    public function hospitalariov()
    {
        helper('auth');

        $db   = \Config\Database::connect();
        $db2  = \Config\Database::connect('base2');

        // ── Opciones de período ──
        $ejercicios = [];
        foreach (self::TABLAS_EJERCICIO as [$tabla, $campo]) {
            $ejercicios = array_merge($ejercicios, array_column(
                $db->table($tabla)->select("{$campo} AS ejercicio")->distinct()->get()->getResultArray(),
                'ejercicio'
            ));
        }
        $ejercicios = array_values(array_unique(array_map('intval', array_filter($ejercicios))));
        rsort($ejercicios);

        // Si "ejercicio" no vino en la URL: último año cerrado con datos (el año en curso suele estar incompleto).
        // Si vino vacío: "Todos" los ejercicios.
        $ejercicioGet = $this->request->getGet('ejercicio');
        if ($ejercicioGet === null) {
            $cerrados  = array_filter($ejercicios, function ($a) { return $a < (int) date('Y'); });
            $ejercicio = !empty($cerrados) ? max($cerrados) : (!empty($ejercicios) ? max($ejercicios) : (int) date('Y'));
        } elseif ($ejercicioGet === '') {
            $ejercicio = '';
        } else {
            $ejercicio = (int) $ejercicioGet;
        }
        $todos = $ejercicio === '';

        // Aplica el ejercicio (si no es "Todos")
        $periodo = function ($builder, string $tabla, string $campoAnio = 'ejercicio') use ($ejercicio, $todos) {
            if (!$todos) $builder->where("{$tabla}.{$campoAnio}", $ejercicio);
            return $builder;
        };

        // ══ AMBULATORIO (oferta vigente, base2) ═════════════════════════
        $especialidadSql = "COALESCE(NULLIF(TRIM(profesion), ''), 'SIN DATO')";
        $amb = $db2->table('carta_servicio')
            ->select("
                COALESCE(SUM(cant_turnos), 0)        AS turnos,
                COUNT(DISTINCT profesional)          AS profesionales,
                COUNT(DISTINCT {$especialidadSql})   AS especialidades,
                COUNT(DISTINCT hospital)             AS hospitales
            ", false)
            ->get()->getRowArray();
        $amb = array_map('intval', $amb ?? []);
        $amb['rrhh'] = (int) ($db2->table('rrhh_carta_servicio')
            ->select("COUNT(DISTINCT COALESCE(NULLIF(TRIM(dni), ''), nombre_apellido)) AS total", false)
            ->get()->getRow()->total ?? 0);

        $ambPorEspecialidad = $db2->table('carta_servicio')
            ->select("{$especialidadSql} AS etiqueta, COALESCE(SUM(cant_turnos), 0) AS valor", false)
            ->groupBy($especialidadSql, false)
            ->orderBy('valor', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        $tipoProfSql = "COALESCE(NULLIF(TRIM(tipo_profesional), ''), 'SIN DATO')";
        $ambPorTipo = $db2->table('carta_servicio')
            ->select("{$tipoProfSql} AS etiqueta, COALESCE(SUM(cant_turnos), 0) AS valor", false)
            ->groupBy($tipoProfSql, false)
            ->orderBy('valor', 'DESC')
            ->get()->getResultArray();

        // ══ GUARDIA ═════════════════════════════════════════════════════
        $gua = $periodo($db->table('guardia'), 'guardia', 'anio')
            ->select('
                COALESCE(SUM(guardia.cantidad), 0)  AS total,
                COUNT(DISTINCT guardia.efector_id)  AS hospitales,
                COUNT(DISTINCT guardia.servicio_id) AS servicios
            ', false)
            ->get()->getRowArray();
        $gua = array_map('intval', $gua ?? []);
        $gua['promedio'] = $gua['hospitales'] > 0 ? (int) round($gua['total'] / $gua['hospitales']) : 0;

        $guaTopHospitales = $periodo($db->table('guardia'), 'guardia', 'anio')
            ->join('efector', 'efector.efector_id = guardia.efector_id', 'left')
            ->select('guardia.efector_id, efector.nombre AS etiqueta, SUM(guardia.cantidad) AS valor')
            ->groupBy('guardia.efector_id, efector.nombre')
            ->orderBy('valor', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Con "Todos": desglose por año de los 5 hospitales principales (barras apiladas por ejercicio)
        $guaTopHospitalesAnio = [];
        if ($todos && !empty($guaTopHospitales)) {
            $guaTopHospitalesAnio = $db->table('guardia')
                ->select('efector_id, anio AS ejercicio, SUM(cantidad) AS valor')
                ->whereIn('efector_id', array_column($guaTopHospitales, 'efector_id'))
                ->groupBy('efector_id, anio')
                ->get()->getResultArray();
        }

        $guaPorServicio = $periodo($db->table('guardia'), 'guardia', 'anio')
            ->join('servicio', 'servicio.servicio_id = guardia.servicio_id', 'left')
            ->select('servicio.nombre AS etiqueta, SUM(guardia.cantidad) AS valor')
            ->groupBy('guardia.servicio_id, servicio.nombre')
            ->orderBy('valor', 'DESC')
            ->get()->getResultArray();
        $guaPorServicio = $this->agruparOtros($guaPorServicio, 6);
        $gua['servicio_principal'] = $guaPorServicio[0]['etiqueta'] ?? '—';

        // Total por ejercicio (todos los años o solo el ejercicio elegido)
        $guaPorEjercicio = $periodo($db->table('guardia'), 'guardia', 'anio')
            ->select('anio AS ejercicio, SUM(cantidad) AS valor')
            ->groupBy('anio')
            ->orderBy('anio', 'ASC')
            ->get()->getResultArray();

        // Comparación con el año anterior, solo si ambos tienen la misma carga
        $gua['variacion']     = null;
        $gua['no_comparable'] = false;
        if ($todos) {
            $gua['cobertura'] = ['clave' => '', 'texto' => count($guaPorEjercicio) . ' ejercicio(s) cargado(s)'];
        } else {
            $gua['cobertura']  = $this->coberturaGuardia($db, $ejercicio);
            $coberturaAnterior = $this->coberturaGuardia($db, $ejercicio - 1);

            if ($coberturaAnterior['clave'] !== '') {
                if ($coberturaAnterior['clave'] === $gua['cobertura']['clave']) {
                    $guaAnterior = (int) ($db->table('guardia')
                        ->select('COALESCE(SUM(cantidad), 0) AS total', false)
                        ->where('anio', $ejercicio - 1)
                        ->get()->getRow()->total ?? 0);
                    $gua['variacion'] = $guaAnterior > 0 ? round(($gua['total'] - $guaAnterior) * 100 / $guaAnterior, 1) : null;
                } else {
                    $gua['no_comparable'] = true;
                }
            }
        }

        // ══ INTERNACIÓN ═════════════════════════════════════════════════
        $rend = $periodo($db->table('rendimiento_hospitalario'), 'rendimiento_hospitalario')
            ->select('
                COALESCE(SUM(total_egresos), 0)   AS egresos,
                COALESCE(SUM(defuncion), 0)       AS defuncion,
                COALESCE(SUM(dias_estada), 0)     AS dias_estada,
                COALESCE(SUM(paciente_dia), 0)    AS paciente_dia,
                COALESCE(SUM(cama_disponible), 0) AS cama_disponible
            ', false)
            ->get()->getRowArray() ?? [];

        $int = [
            'egresos'    => (int) ($rend['egresos'] ?? 0),
            'ocupacion'  => ($rend['cama_disponible'] ?? 0) > 0 ? round($rend['paciente_dia'] * 100 / $rend['cama_disponible'], 1) : 0,
            'estada'     => ($rend['egresos'] ?? 0) > 0 ? round($rend['dias_estada'] / $rend['egresos'], 1) : 0,
            'mortalidad' => ($rend['egresos'] ?? 0) > 0 ? round($rend['defuncion'] * 100 / $rend['egresos'], 2) : 0,
        ];

        $cap = $db->table('capacidad_camas')
            ->select('
                COALESCE(SUM(camas_disponibles), 0) AS disponibles,
                COALESCE(SUM(total_uti), 0)         AS uti,
                COALESCE(SUM(total_utin), 0)        AS utin,
                COALESCE(SUM(total_basicas), 0)     AS basicas
            ', false)
            ->get()->getRowArray();
        $int['camas'] = array_map('intval', $cap ?? []);

        $int['espera'] = (int) ($periodo($db->table('lista_espera'), 'lista_espera')
            ->select('COALESCE(SUM(cantidad_pacientes), 0) AS total', false)
            ->get()->getRow()->total ?? 0);

        $int['salud_mental'] = (int) ($db->table('salud_mental_camas')
            ->select('COALESCE(SUM(COALESCE(cb_adultos, 0) + COALESCE(cb_pediatricos, 0)), 0) AS total', false)
            ->get()->getRow()->total ?? 0);

        // Evolución anual de egresos y % ocupacional (todos los ejercicios o solo el elegido)
        $intEvolucion = $periodo($db->table('rendimiento_hospitalario'), 'rendimiento_hospitalario')
            ->select('
                ejercicio,
                ejercicio AS etiqueta,
                SUM(total_egresos) AS egresos,
                ROUND(SUM(paciente_dia) * 100 / NULLIF(SUM(cama_disponible), 0), 1) AS ocupacion
            ', false)
            ->groupBy('ejercicio')
            ->orderBy('ejercicio', 'ASC')
            ->get()->getResultArray();

        // ══ QUIRÓFANO (anual) ═══════════════════════════════════════════
        $qui = $periodo($db->table('produccion_quirofano_hosp'), 'produccion_quirofano_hosp')
            ->select('
                COALESCE(SUM(sub_total), 0)                  AS total,
                COALESCE(SUM(cirugias_urgencia), 0)          AS urgencia,
                COALESCE(SUM(total_cirugias_programadas), 0) AS programadas,
                COALESCE(SUM(quirofanos_disponibles), 0)     AS quirofanos,
                COALESCE(SUM(cirugias_prog_alta), 0)         AS alta,
                COALESCE(SUM(cirugias_prog_mediana), 0)      AS mediana,
                COALESCE(SUM(cirugias_prog_baja), 0)         AS baja,
                COALESCE(SUM(cirugias_prog_desconocido), 0)  AS desconocido,
                COUNT(DISTINCT efector_id)                   AS hospitales
            ', false)
            ->get()->getRowArray();
        $qui = array_map('intval', $qui ?? []);

        $quiTopHospitales = $periodo($db->table('produccion_quirofano_hosp'), 'produccion_quirofano_hosp')
            ->join('efector', 'efector.efector_id = produccion_quirofano_hosp.efector_id', 'left')
            ->select('
                efector.nombre AS etiqueta,
                SUM(produccion_quirofano_hosp.cirugias_urgencia)          AS urgencia,
                SUM(produccion_quirofano_hosp.total_cirugias_programadas) AS programadas,
                SUM(produccion_quirofano_hosp.sub_total)                  AS valor
            ')
            ->groupBy('produccion_quirofano_hosp.efector_id, efector.nombre')
            ->orderBy('valor', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        $quiPorEjercicio = $periodo($db->table('produccion_quirofano_hosp'), 'produccion_quirofano_hosp')
            ->select('ejercicio, SUM(sub_total) AS valor')
            ->groupBy('ejercicio')
            ->orderBy('ejercicio', 'ASC')
            ->get()->getResultArray();

        // Con un ejercicio filtrado: comparativo por tipo de cirugía contra el año anterior
        $quiComparativo = [];
        if (!$todos) {
            $quiComparativo = $db->table('produccion_quirofano_hosp')
                ->select('
                    ejercicio,
                    COALESCE(SUM(cirugias_urgencia), 0)         AS urgencia,
                    COALESCE(SUM(cirugias_prog_alta), 0)        AS alta,
                    COALESCE(SUM(cirugias_prog_mediana), 0)     AS mediana,
                    COALESCE(SUM(cirugias_prog_baja), 0)        AS baja,
                    COALESCE(SUM(cirugias_prog_desconocido), 0) AS desconocido,
                    COALESCE(SUM(sub_total), 0)                 AS total,
                    COUNT(DISTINCT efector_id)                  AS hospitales
                ', false)
                ->whereIn('ejercicio', [$ejercicio - 1, $ejercicio])
                ->groupBy('ejercicio')
                ->orderBy('ejercicio', 'ASC')
                ->get()->getResultArray();
        }

        // Pestaña visible
        $tab = in_array($this->request->getGet('tab'), ['amb', 'gua', 'int', 'qui'], true)
            ? $this->request->getGet('tab') : 'amb';

        return view('admin/hospitalario_views', [
            'tab'                  => $tab,
            'ejercicios'           => $ejercicios,
            'ejercicio'            => $ejercicio,

            'amb'                  => $amb,
            'ambPorEspecialidad'   => $ambPorEspecialidad,
            'ambPorTipo'           => $ambPorTipo,

            'gua'                  => $gua,
            'guaTopHospitales'     => $guaTopHospitales,
            'guaTopHospitalesAnio' => $guaTopHospitalesAnio,
            'guaPorServicio'       => $guaPorServicio,
            'guaPorEjercicio'      => $guaPorEjercicio,

            'int'                  => $int,
            'intEvolucion'         => $intEvolucion,

            'qui'                  => $qui,
            'quiTopHospitales'     => $quiTopHospitales,
            'quiPorEjercicio'      => $quiPorEjercicio,
            'quiComparativo'       => $quiComparativo,
        ]);
    }

    // Semestres y meses con datos de guardia en un año.
    // 'clave' sirve para saber si dos años son comparables; 'texto' se muestra en pantalla.
    private function coberturaGuardia($db, int $anio): array
    {
        $filas = $db->table('guardia')
            ->select('semestre, mes')->distinct()
            ->where('anio', $anio)
            ->get()->getResultArray();

        $semestres = [];
        $meses     = [];
        foreach ($filas as $f) {
            $semestres[$f['semestre']] = true;
            if (!empty($f['mes'])) $meses[strtoupper(trim($f['mes']))] = true;
        }

        $ordenMeses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO',
                       'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        $meses = array_values(array_filter($ordenMeses, function ($m) use ($meses) { return isset($meses[$m]); }));
        $semestres = array_keys($semestres);
        sort($semestres);

        if (empty($filas)) {
            $texto = 'Sin datos cargados';
        } elseif (!empty($meses)) {
            $texto = 'Cargado: ' . implode(', ', array_map(function ($m) { return ucfirst(strtolower(substr($m, 0, 3))); }, $meses));
        } else {
            $texto = 'Cargado: ' . implode(', ', $semestres) . ' semestre (total)';
        }

        return [
            'clave' => empty($filas) ? '' : implode(',', $semestres) . '|' . implode(',', $meses),
            'texto' => $texto,
        ];
    }

    // Deja las primeras $max categorías y suma el resto en "OTROS"
    private function agruparOtros(array $filas, int $max): array
    {
        if (count($filas) <= $max) return $filas;

        $principales = array_slice($filas, 0, $max);
        $resto       = array_sum(array_column(array_slice($filas, $max), 'valor'));
        $principales[] = ['etiqueta' => 'OTROS', 'valor' => $resto];

        return $principales;
    }
}
