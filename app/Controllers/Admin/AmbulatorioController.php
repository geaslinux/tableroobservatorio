<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AmbulatorioController extends BaseController
{
    // Orden fijo de los días de atención para el gráfico semanal
    private const DIAS = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'];

    // ─── Filtros comunes a carta_servicio y rrhh_carta_servicio ──────────
    private function leerFiltros(): array
    {
        return [
            'filtro_hospital' => $this->request->getGet('hospital')          ?? '',
            'filtro_region'   => $this->request->getGet('region')            ?? '',
            'filtro_nivel'    => $this->request->getGet('nivel_complejidad') ?? '',
        ];
    }

    private function builder($db, string $tabla, array $filtros)
    {
        $builder = $db->table($tabla);

        if ($filtros['filtro_hospital'] !== '') $builder->where('hospital', $filtros['filtro_hospital']);
        if ($filtros['filtro_region'] !== '')   $builder->where('region', $filtros['filtro_region']);
        if ($filtros['filtro_nivel'] !== '')    $builder->where('nivel_complejidad', $filtros['filtro_nivel']);

        return $builder;
    }

    // Valores distintos de un campo en ambas tablas, para los select de filtro
    private function opciones($db, string $campo): array
    {
        $valores = [];
        foreach (['carta_servicio', 'rrhh_carta_servicio'] as $tabla) {
            $filas = $db->table($tabla)
                ->select($campo)->distinct()
                ->where("{$campo} IS NOT NULL")
                ->where("{$campo} !=", '')
                ->get()->getResultArray();
            $valores = array_merge($valores, array_column($filas, $campo));
        }
        $valores = array_values(array_unique($valores));
        sort($valores);

        return $valores;
    }

    // Quita acentos y espacios para comparar días (SÁBADO → SABADO)
    private function normalizarDia(?string $dia): string
    {
        $dia = mb_strtoupper(trim($dia ?? ''), 'UTF-8');
        return strtr($dia, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U']);
    }

    public function index()
    {
        helper('auth');

        $db      = \Config\Database::connect('base2');
        $filtros = $this->leerFiltros();

        // ── Opciones de filtros ──
        $hospitales = $this->opciones($db, 'hospital');
        $regiones   = $this->opciones($db, 'region');
        $niveles    = $this->opciones($db, 'nivel_complejidad');

        // La profesión es la que identifica la especialidad (el campo especialidad viene vacío o como subespecialidad)
        $campoEspecialidad = "COALESCE(NULLIF(TRIM(profesion), ''), 'SIN DATO')";

        // ══ CARTA DE SERVICIO ══════════════════════════════════════════
        $cartaKpi = $this->builder($db, 'carta_servicio', $filtros)
            ->select("
                COALESCE(SUM(cant_turnos), 0)              AS turnos,
                COUNT(*)                                   AS agendas,
                COUNT(DISTINCT profesional)                AS profesionales,
                COUNT(DISTINCT {$campoEspecialidad})       AS especialidades,
                COUNT(DISTINCT hospital)                   AS hospitales
            ", false)
            ->get()->getRowArray();

        $cartaPorEspecialidad = $this->builder($db, 'carta_servicio', $filtros)
            ->select("{$campoEspecialidad} AS especialidad, COUNT(*) AS agendas, COALESCE(SUM(cant_turnos), 0) AS turnos", false)
            ->groupBy($campoEspecialidad, false)
            ->orderBy('turnos', 'DESC')
            ->get()->getResultArray();

        // Turnos por día de la semana, separados por turno (MAÑANA / TARDE / ...)
        $filasDia = $this->builder($db, 'carta_servicio', $filtros)
            ->select("dia_atencion, COALESCE(NULLIF(TRIM(turno), ''), 'SIN DATO') AS turno, COALESCE(SUM(cant_turnos), 0) AS turnos", false)
            ->groupBy("dia_atencion, COALESCE(NULLIF(TRIM(turno), ''), 'SIN DATO')", false)
            ->get()->getResultArray();

        $cartaPorDia = [];   // [turno => [dia => turnos]]
        $diasExtra   = [];
        foreach ($filasDia as $f) {
            $dia = $this->normalizarDia($f['dia_atencion']);
            if ($dia === '') $dia = 'SIN DATO';
            if (!in_array($dia, self::DIAS, true) && !in_array($dia, $diasExtra, true)) {
                $diasExtra[] = $dia;
            }
            $cartaPorDia[$f['turno']][$dia] = ($cartaPorDia[$f['turno']][$dia] ?? 0) + (int) $f['turnos'];
        }
        $diasGrafico = array_merge(self::DIAS, $diasExtra);

        $cartaPorTipoProfesional = $this->builder($db, 'carta_servicio', $filtros)
            ->select("COALESCE(NULLIF(TRIM(tipo_profesional), ''), 'SIN DATO') AS tipo_profesional, COALESCE(SUM(cant_turnos), 0) AS turnos", false)
            ->groupBy("COALESCE(NULLIF(TRIM(tipo_profesional), ''), 'SIN DATO')", false)
            ->orderBy('turnos', 'DESC')
            ->get()->getResultArray();

        // ══ RRHH CARTA DE SERVICIO ═════════════════════════════════════
        // Se cuenta por nombre porque el DNI puede venir vacío
        $rrhhKpi = $this->builder($db, 'rrhh_carta_servicio', $filtros)
            ->select("
                COUNT(DISTINCT COALESCE(NULLIF(TRIM(dni), ''), nombre_apellido)) AS profesionales,
                COUNT(DISTINCT {$campoEspecialidad})                              AS especialidades,
                COUNT(DISTINCT hospital)                                          AS hospitales,
                SUM(UPPER(TRIM(revista)) = 'PLANTA')                              AS planta
            ", false)
            ->get()->getRowArray();

        $rrhhPorEspecialidad = $this->builder($db, 'rrhh_carta_servicio', $filtros)
            ->select("{$campoEspecialidad} AS especialidad, COUNT(*) AS cantidad", false)
            ->groupBy($campoEspecialidad, false)
            ->orderBy('cantidad', 'DESC')
            ->get()->getResultArray();

        $rrhhPorRevista = $this->builder($db, 'rrhh_carta_servicio', $filtros)
            ->select("COALESCE(NULLIF(TRIM(revista), ''), 'SIN DATO') AS revista, COUNT(*) AS cantidad", false)
            ->groupBy("COALESCE(NULLIF(TRIM(revista), ''), 'SIN DATO')", false)
            ->orderBy('cantidad', 'DESC')
            ->get()->getResultArray();

        // Actividades: se cuenta al profesional si el campo tiene dato y no es "NO"
        $actividades = [
            'consultorio'      => 'Consultorio',
            'guardia_cargo'    => 'Guardia a cargo',
            'telemedicina'     => 'Telemedicina',
            'prosane'          => 'PROSANE',
            'carnet_sanitario' => 'Carnet sanitario',
        ];
        $selectAct = [];
        foreach ($actividades as $campo => $label) {
            $selectAct[] = "SUM(TRIM(COALESCE({$campo}, '')) NOT IN ('', 'NO', '0')) AS {$campo}";
        }
        $filaAct = $this->builder($db, 'rrhh_carta_servicio', $filtros)
            ->select(implode(', ', $selectAct), false)
            ->get()->getRowArray();

        $rrhhActividades = [];
        foreach ($actividades as $campo => $label) {
            $rrhhActividades[] = ['actividad' => $label, 'cantidad' => (int) ($filaAct[$campo] ?? 0)];
        }

        // Pestaña visible: carta de servicio o RRHH
        $tab = $this->request->getGet('tab') === 'rrhh' ? 'rrhh' : 'carta';

        return view('admin/carta_servicio_panel_views', array_merge($filtros, [
            'tab'                     => $tab,
            'hospitales'              => $hospitales,
            'regiones'                => $regiones,
            'niveles'                 => $niveles,
            'cartaKpi'                => array_map('intval', $cartaKpi ?? []),
            'cartaPorEspecialidad'    => $cartaPorEspecialidad,
            'cartaPorDia'             => $cartaPorDia,
            'diasGrafico'             => $diasGrafico,
            'cartaPorTipoProfesional' => $cartaPorTipoProfesional,
            'rrhhKpi'                 => array_map('intval', $rrhhKpi ?? []),
            'rrhhPorEspecialidad'     => $rrhhPorEspecialidad,
            'rrhhPorRevista'          => $rrhhPorRevista,
            'rrhhActividades'         => $rrhhActividades,
        ]));
    }
}
