<?php

namespace App\Libraries\Ejesa;

use DateTime;
use DateTimeZone;

/**
 * Cálculos de la pestaña EJESA (docs/ejesa_visor/SKILL.md §4). Definiciones únicas: usar estas y no otras.
 *
 * Clase pura: no lee la base ni la sesión. Recibe filas y devuelve números, así se prueba con
 * tests unitarios (tests/unit/EjesaCalculoTest.php).
 *
 * Zonas horarias:
 *  - ejesa_corte.inicio_at / fin_estimado_at están en HORA LOCAL de Jujuy.
 *  - publicado_at, cancelado_at, historial.registrado_at, consulta.consultado_at, etc. están en UTC.
 * Toda resta de fechas se hace acá, en código (el SQL tiene que quedar portable a PostgreSQL).
 */
final class Calculo
{
    public const ZONA = 'America/Argentina/Jujuy';

    public const RIESGOS = ['ALTO', 'MEDIANO', 'BAJO', 'PENDIENTE'];

    public const MESES = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
        7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
    ];

    // Estado que se muestra (§4.1)
    public const ESTADOS_VISIBLES = [
        'PROGRAMADO' => 'Programado',
        'EN_CURSO'   => 'En curso',
        'VENCIDO'    => 'Vencido sin finalizar',
        'FINALIZADO' => 'Finalizado',
        'CANCELADO'  => 'Cancelado',
    ];

    // Marcas de las horas sin luz (§4.2)
    public const MARCAS = [
        'REAL'              => 'Fin registrado por EJESA',
        'NO_REGISTRADO'     => 'Fin real no registrado (se cerró tarde): se usa el fin estimado',
        'SIN_FINALIZAR'     => 'Sin finalizar: se usa el fin estimado',
        'EN_CURSO'          => 'En curso: horas parciales hasta ahora',
        'CANCELADO_ANTES'   => 'Cancelado antes de empezar: 0 h, no se cuenta',
        'CANCELADO_DURANTE' => 'Cancelado durante el corte: hasta la cancelación',
        'PROXIMO'           => 'Todavía no empezó',
    ];

    // Alertas de auditoría
    public const HORA_NOCHE_DESDE        = 22; // consultas fuera de horario: de 22 a 06 h
    public const HORA_NOCHE_HASTA        = 6;
    public const MAX_CONSULTAS_DIA       = 30; // más que esto por usuario y día = alerta
    public const MAX_SIN_RESULTADOS_DIA  = 5;  // búsquedas de servicio sin resultado por usuario y día

    // ─── Fechas ──────────────────────────────────────────────────────────

    /** Fecha y hora actual de Jujuy, en el mismo formato que inicio_at. */
    public static function ahora(): string
    {
        return (new DateTime('now', new DateTimeZone(self::ZONA)))->format('Y-m-d H:i:s');
    }

    /** UTC guardado ('Y-m-d H:i:s[.u]') → hora local de Jujuy 'Y-m-d H:i:s'. '' si viene vacío. */
    public static function utcALocal(?string $utc): string
    {
        if ($utc === null || trim($utc) === '') {
            return '';
        }

        return (new DateTime(substr(trim($utc), 0, 19), new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone(self::ZONA))->format('Y-m-d H:i:s');
    }

    /** Hora local de Jujuy → UTC, para filtrar columnas guardadas en UTC. */
    public static function localAUtc(string $local): string
    {
        return (new DateTime($local, new DateTimeZone(self::ZONA)))
            ->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }

    /** 'Y-m-d H:i:s' local → 'dd/mm/aaaa HH:MM'. */
    public static function fecha(?string $local): string
    {
        if ($local === null || trim($local) === '') {
            return '';
        }

        return (new DateTime($local, new DateTimeZone(self::ZONA)))->format('d/m/Y H:i');
    }

    /** "Mar 18/11 08:00 a 13:00" (o con la fecha de fin si termina otro día). */
    public static function rango(string $inicio, string $fin): string
    {
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $i    = new DateTime($inicio, new DateTimeZone(self::ZONA));
        $f    = new DateTime($fin, new DateTimeZone(self::ZONA));
        $txt  = $dias[(int) $i->format('w')] . ' ' . $i->format('d/m H:i') . ' a ';

        return $txt . ($i->format('Y-m-d') === $f->format('Y-m-d') ? $f->format('H:i') : $f->format('d/m H:i'));
    }

    /** Horas (decimales) entre dos fechas locales. Puede ser negativo. */
    public static function horasEntre(string $desde, string $hasta): float
    {
        $zona = new DateTimeZone(self::ZONA);

        return ((new DateTime($hasta, $zona))->getTimestamp() - (new DateTime($desde, $zona))->getTimestamp()) / 3600;
    }

    /** "3,5 h" */
    public static function horas(float $horas): string
    {
        return number_format(round($horas, 1), 1, ',', '.') . ' h';
    }

    /** "03:30" (horas totales : minutos) */
    public static function hhmm(float $horas): string
    {
        $min = (int) round(max(0, $horas) * 60);

        return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
    }

    /** Cuánto falta: "2 d 5 h", "3 h 20 min", "15 min". '' si ya pasó. */
    public static function faltan(string $inicio, string $ahora): string
    {
        $min = (int) floor(self::horasEntre($ahora, $inicio) * 60);
        if ($min <= 0) {
            return '';
        }
        $d = intdiv($min, 1440);
        $h = intdiv($min % 1440, 60);
        $m = $min % 60;

        if ($d > 0) {
            return $d . ' d ' . $h . ' h';
        }

        return $h > 0 ? $h . ' h ' . $m . ' min' : $m . ' min';
    }

    /** "NOVIEMBRE 2026" a partir de una fecha local. */
    public static function mes(string $local): string
    {
        $f = new DateTime($local, new DateTimeZone(self::ZONA));

        return self::MESES[(int) $f->format('n')] . ' ' . $f->format('Y');
    }

    // ─── Riesgo y servicio ───────────────────────────────────────────────

    /** NULL / vacío / desconocido = PENDIENTE. Nunca BAJO por defecto. */
    public static function riesgo(?string $riesgo): string
    {
        $r = strtoupper(trim((string) $riesgo));

        return in_array($r, ['ALTO', 'MEDIANO', 'BAJO'], true) ? $r : 'PENDIENTE';
    }

    /** Solo los 3 últimos caracteres: •••••123 */
    public static function servicio(?string $nro): string
    {
        $nro = trim((string) $nro);

        return $nro === '' ? '—' : '•••••' . mb_substr($nro, -3);
    }

    // ─── §4.1 Estado que se muestra ──────────────────────────────────────

    public static function estado(array $corte, string $ahora): string
    {
        $estado = (string) $corte['estado'];

        if ($estado === 'CANCELADO' || $estado === 'FINALIZADO') {
            return $estado;
        }

        $inicio = (string) $corte['inicio_at'];
        $fin    = (string) $corte['fin_estimado_at'];

        if ($fin < $ahora) {
            return 'VENCIDO';
        }
        if ($estado === 'EN_CURSO' || $inicio <= $ahora) {
            return 'EN_CURSO';
        }

        return 'PROGRAMADO';
    }

    // ─── §4.2 Horas sin luz ──────────────────────────────────────────────

    /**
     * @param array      $corte        fila de ejesa_corte (inicio_at, fin_estimado_at local; cancelado_at UTC)
     * @param array|null $finalizacion ['registrado_at' => UTC, 'vencido' => bool] de la acción FINALIZAR, o null
     *
     * @return array{programadas: float, sin_luz: float, marca: string, cuenta: bool, fin_efectivo: ?string}
     *         cuenta = entra en los totales de horas ejecutadas (no entran los futuros ni los cancelados antes de empezar)
     */
    public static function horasSinLuz(array $corte, ?array $finalizacion, string $ahora): array
    {
        $inicio      = (string) $corte['inicio_at'];
        $finEstimado = (string) $corte['fin_estimado_at'];
        $programadas = max(0.0, self::horasEntre($inicio, $finEstimado));
        $estado      = self::estado($corte, $ahora);

        $resultado = static fn (?string $finEfectivo, string $marca, bool $cuenta) => [
            'programadas'  => $programadas,
            'sin_luz'      => $finEfectivo === null ? 0.0 : max(0.0, self::horasEntre($inicio, $finEfectivo)),
            'marca'        => $marca,
            'cuenta'       => $cuenta,
            'fin_efectivo' => $finEfectivo,
        ];

        switch ($estado) {
            case 'FINALIZADO':
                if ($finalizacion !== null && empty($finalizacion['vencido'])) {
                    $finLocal = self::utcALocal($finalizacion['registrado_at'] ?? null);
                    // Se restableció antes del fin estimado: esa es la hora real
                    if ($finLocal !== '' && $finLocal < $finEstimado) {
                        return $resultado($finLocal, 'REAL', true);
                    }

                    return $resultado($finEstimado, 'REAL', true);
                }

                // Se finalizó tarde (o no quedó registro): no se sabe el fin real
                return $resultado($finEstimado, 'NO_REGISTRADO', true);

            case 'EN_CURSO':
                return $resultado($ahora < $finEstimado ? $ahora : $finEstimado, 'EN_CURSO', true);

            case 'VENCIDO':
                return $resultado($finEstimado, 'SIN_FINALIZAR', true);

            case 'CANCELADO':
                $cancelado = self::utcALocal($corte['cancelado_at'] ?? null);
                if ($cancelado === '' || $cancelado <= $inicio) {
                    return $resultado(null, 'CANCELADO_ANTES', false);
                }

                return $resultado($cancelado < $finEstimado ? $cancelado : $finEstimado, 'CANCELADO_DURANTE', true);

            default: // PROGRAMADO futuro: sus horas van a "próximos"
                return $resultado(null, 'PROXIMO', false);
        }
    }

    // ─── §4.3 Personas afectadas ─────────────────────────────────────────

    /**
     * @param array      $porRiesgo filas [riesgo_snapshot => ?string, cantidad => int] de ejesa_corte_afectado VIGENTE
     * @param array|null $protocolo ['created_at' UTC, 'ambito' JSON|array, 'revision' ...] o null si no se activó
     *
     * Sin protocolo no hay dato (el visor no recalcula geometría): total = null.
     */
    public static function afectados(array $porRiesgo, ?array $protocolo, int $versionCorte = 0): array
    {
        $base = ['ALTO' => 0, 'MEDIANO' => 0, 'BAJO' => 0, 'PENDIENTE' => 0];

        if ($protocolo === null) {
            return $base + [
                'total' => null, 'a_revisar' => null, 'fuente' => null,
                'calculado_at' => null, 'revision' => null, 'desactualizado' => false,
            ];
        }

        foreach ($porRiesgo as $fila) {
            $base[self::riesgo($fila['riesgo_snapshot'] ?? null)] += (int) $fila['cantidad'];
        }

        $ambito   = is_array($protocolo['ambito'] ?? null) ? $protocolo['ambito'] : (json_decode((string) ($protocolo['ambito'] ?? ''), true) ?: []);
        $revision = isset($ambito['revision']) ? (int) $ambito['revision'] : null;

        return $base + [
            'total'          => $base['ALTO'] + $base['MEDIANO'] + $base['BAJO'] + $base['PENDIENTE'],
            // "A revisar" se muestra aparte y NUNCA se suma al total
            'a_revisar'      => isset($ambito['a_revisar']) ? (int) $ambito['a_revisar'] : null,
            'fuente'         => 'PROTOCOLO',
            'calculado_at'   => self::utcALocal($protocolo['created_at'] ?? null),
            'revision'       => $revision,
            // El corte cambió después de activar el protocolo: los afectados son de una versión anterior
            'desactualizado' => $revision !== null && $versionCorte > 0 && $versionCorte > $revision
                && !empty($protocolo['reprogramado_despues']),
        ];
    }

    // ─── Corte completo ──────────────────────────────────────────────────

    /**
     * Junta todo lo de un corte en una fila lista para mostrar.
     *
     * @param array $historial filas de ejesa_corte_historial del corte (accion, cambios JSON, registrado_at UTC)
     */
    public static function corte(array $corte, array $historial, array $porRiesgo, ?array $protocolo, string $ahora): array
    {
        $finalizacion     = null;
        $reprogramaciones = 0;
        $reprogramadoDespuesProtocolo = false;
        $protocoloAt      = $protocolo['created_at'] ?? null;

        foreach ($historial as $h) {
            $cambios = is_array($h['cambios'] ?? null) ? $h['cambios'] : (json_decode((string) ($h['cambios'] ?? ''), true) ?: []);

            if ($h['accion'] === 'FINALIZAR') {
                // Si hubiera más de uno, vale el último
                if ($finalizacion === null || (string) $h['registrado_at'] > (string) $finalizacion['registrado_at']) {
                    $finalizacion = ['registrado_at' => $h['registrado_at'], 'vencido' => !empty($cambios['vencido'])];
                }
            }
            if ($h['accion'] === 'REPROGRAMAR') {
                $reprogramaciones++;
                if ($protocoloAt !== null && (string) $h['registrado_at'] > (string) $protocoloAt) {
                    $reprogramadoDespuesProtocolo = true;
                }
            }
        }

        if ($protocolo !== null) {
            $protocolo['reprogramado_despues'] = $reprogramadoDespuesProtocolo;
        }

        $horas     = self::horasSinLuz($corte, $finalizacion, $ahora);
        $afectados = self::afectados($porRiesgo, $protocolo, (int) ($corte['version'] ?? 0));
        $conDato   = $afectados['total'] !== null;

        return $corte + [
            'estado_visible'    => self::estado($corte, $ahora),
            'horas'             => $horas,
            'afectados'         => $afectados,
            'protocolo'         => $protocolo !== null,
            'protocolo_estado'  => $protocolo['estado'] ?? null,
            'reprogramaciones'  => $reprogramaciones,
            'finalizado_local'  => $finalizacion ? self::utcALocal($finalizacion['registrado_at']) : null,
            'finalizado_tarde'  => $finalizacion ? (bool) $finalizacion['vencido'] : null,
            // §4.4 persona-horas: solo con dato de afectados
            'persona_horas'      => $conDato ? $afectados['total'] * $horas['sin_luz'] : null,
            'persona_horas_alto' => $conDato ? $afectados['ALTO'] * $horas['sin_luz'] : null,
            'faltan'            => self::faltan((string) $corte['inicio_at'], $ahora),
        ];
    }

    // ─── §4.4 Indicadores del período ────────────────────────────────────

    /**
     * @param array    $cortes             filas devueltas por corte()
     * @param int|null $personasDistintas  COUNT(DISTINCT electrodependiente_id) del período (null = sin dato)
     */
    public static function kpis(array $cortes, ?int $personasDistintas): array
    {
        $k = [
            'cortes'               => count($cortes),
            'ejecutados'           => 0,
            'cancelados'           => 0,
            'cancelados_antes'     => 0,
            'vencidos'             => 0,
            'en_curso'             => 0,
            'proximos'             => 0,
            'finalizados'          => 0,
            'finalizados_a_tiempo' => 0,
            'finalizados_tarde'    => 0,
            'horas_sin_luz'        => 0.0,
            'horas_programadas'    => 0.0,
            'duracion_media'       => null,
            'duracion_media_prog'  => null,
            'corte_mas_largo'      => null,
            'afectados_suma'       => 0,
            'afectados_alto'       => 0,
            'afectados_por_riesgo' => ['ALTO' => 0, 'MEDIANO' => 0, 'BAJO' => 0, 'PENDIENTE' => 0],
            'a_revisar'            => 0,
            'personas_distintas'   => $personasDistintas,
            'persona_horas'        => 0.0,
            'persona_horas_alto'   => 0.0,
            'sin_dato_afectados'   => 0,
            'con_protocolo'        => 0,
            'reprogramaciones'     => 0,
            'cumplimiento_cierre'  => null,
        ];

        $conHoras = [];
        $progs    = [];
        $cerrables = 0;

        foreach ($cortes as $c) {
            $e = $c['estado_visible'];
            $h = $c['horas'];
            $a = $c['afectados'];

            $k['reprogramaciones'] += (int) $c['reprogramaciones'];
            if ($c['protocolo']) {
                $k['con_protocolo']++;
            }

            if ($e === 'CANCELADO') {
                $k['cancelados']++;
                if ($h['marca'] === 'CANCELADO_ANTES') {
                    $k['cancelados_antes']++;
                    continue; // fuera de todos los totales
                }
            }
            if ($e === 'VENCIDO') $k['vencidos']++;
            if ($e === 'EN_CURSO') $k['en_curso']++;
            if ($e === 'PROGRAMADO') $k['proximos']++;
            if ($e === 'FINALIZADO') {
                $k['finalizados']++;
                $c['finalizado_tarde'] ? $k['finalizados_tarde']++ : $k['finalizados_a_tiempo']++;
            }

            // Cumplimiento de cierre: cortes que ya debían estar cerrados (finalizados o vencidos)
            if ($e === 'FINALIZADO' || $e === 'VENCIDO') {
                $cerrables++;
            }

            if (!$h['cuenta']) {
                continue;
            }

            $k['ejecutados']++;
            $k['horas_sin_luz']     += $h['sin_luz'];
            $k['horas_programadas'] += $h['programadas'];
            $progs[] = $h['programadas'];
            if ($h['sin_luz'] > 0) {
                $conHoras[] = $h['sin_luz'];
                if ($k['corte_mas_largo'] === null || $h['sin_luz'] > $k['corte_mas_largo']['horas']) {
                    $k['corte_mas_largo'] = ['corte_id' => $c['corte_id'], 'horas' => $h['sin_luz'], 'inicio_at' => $c['inicio_at'], 'localidad' => $c['localidad'] ?? null];
                }
            }

            if ($a['total'] === null) {
                $k['sin_dato_afectados']++;
                continue;
            }
            $k['afectados_suma'] += $a['total'];
            $k['afectados_alto'] += $a['ALTO'];
            foreach (self::RIESGOS as $r) {
                $k['afectados_por_riesgo'][$r] += $a[$r];
            }
            $k['a_revisar']          += (int) $a['a_revisar'];
            $k['persona_horas']      += (float) $c['persona_horas'];
            $k['persona_horas_alto'] += (float) $c['persona_horas_alto'];
        }

        // Duración media y corte más largo: sobre cortes con horas > 0
        $k['duracion_media']      = $conHoras ? array_sum($conHoras) / count($conHoras) : null;
        $k['duracion_media_prog'] = $progs ? array_sum($progs) / count($progs) : null;
        $k['cumplimiento_cierre'] = $cerrables > 0 ? $k['finalizados_a_tiempo'] * 100.0 / $cerrables : null;

        return $k;
    }

    /**
     * Serie por mes (ejercicio + mes) de los cortes que cuentan. Meses del período sin cortes van en 0.
     *
     * @return array<string, array> clave 'AAAA-MM'
     */
    public static function serieMensual(array $cortes, string $desde, string $hasta): array
    {
        $serie = [];
        $zona  = new DateTimeZone(self::ZONA);
        $mes   = new DateTime(substr($desde, 0, 7) . '-01', $zona);
        $fin   = new DateTime($hasta, $zona);

        while ($mes < $fin) {
            $serie[$mes->format('Y-m')] = [
                'ejercicio' => (int) $mes->format('Y'), 'mes' => self::MESES[(int) $mes->format('n')],
                'cortes' => 0, 'horas' => 0.0, 'persona_horas' => 0.0,
                'ALTO' => 0, 'MEDIANO' => 0, 'BAJO' => 0, 'PENDIENTE' => 0, 'sin_dato' => 0,
            ];
            $mes->modify('+1 month');
        }

        foreach ($cortes as $c) {
            if (!$c['horas']['cuenta']) {
                continue;
            }
            $clave = substr((string) $c['inicio_at'], 0, 7);
            if (!isset($serie[$clave])) {
                continue;
            }
            $serie[$clave]['cortes']++;
            $serie[$clave]['horas'] += $c['horas']['sin_luz'];
            if ($c['afectados']['total'] === null) {
                $serie[$clave]['sin_dato']++;
                continue;
            }
            $serie[$clave]['persona_horas'] += (float) $c['persona_horas'];
            foreach (self::RIESGOS as $r) {
                $serie[$clave][$r] += $c['afectados'][$r];
            }
        }

        return $serie;
    }

    /** Localidades ordenadas por persona-horas (desc), las primeras $limite. */
    public static function porLocalidad(array $cortes, int $limite = 10): array
    {
        $loc = [];
        foreach ($cortes as $c) {
            if (!$c['horas']['cuenta']) {
                continue;
            }
            $nombre = $c['localidad'] ?? null ?: 'Localidad sin indicar';
            $loc[$nombre] ??= ['localidad' => $nombre, 'cortes' => 0, 'horas' => 0.0, 'afectados' => 0, 'alto' => 0, 'persona_horas' => 0.0, 'sin_dato' => 0];
            $loc[$nombre]['cortes']++;
            $loc[$nombre]['horas'] += $c['horas']['sin_luz'];
            if ($c['afectados']['total'] === null) {
                $loc[$nombre]['sin_dato']++;
                continue;
            }
            $loc[$nombre]['afectados']     += $c['afectados']['total'];
            $loc[$nombre]['alto']          += $c['afectados']['ALTO'];
            $loc[$nombre]['persona_horas'] += (float) $c['persona_horas'];
        }

        usort($loc, static fn ($a, $b) => [$b['persona_horas'], $b['horas']] <=> [$a['persona_horas'], $a['horas']]);

        return array_slice($loc, 0, $limite);
    }

    /**
     * Horas sin luz acumuladas por persona en el período (para "quiénes fueron afectados").
     *
     * @param array $vinculos filas [corte_id, electrodependiente_id] de ejesa_corte_afectado VIGENTE
     * @param array $cortes   filas devueltas por corte(), indexables por corte_id
     *
     * @return array<int, array{cortes: int, horas: float, alto: bool}> por electrodependiente_id
     */
    public static function acumuladoPorPersona(array $vinculos, array $cortes): array
    {
        $porId = [];
        foreach ($cortes as $c) {
            $porId[(int) $c['corte_id']] = $c;
        }

        $personas = [];
        foreach ($vinculos as $v) {
            $c = $porId[(int) $v['corte_id']] ?? null;
            if ($c === null || !$c['horas']['cuenta']) {
                continue;
            }
            $id = (int) $v['electrodependiente_id'];
            $personas[$id] ??= ['cortes' => 0, 'horas' => 0.0, 'riesgo' => self::riesgo($v['riesgo_snapshot'] ?? null), 'ultimo' => null];
            $personas[$id]['cortes']++;
            $personas[$id]['horas'] += $c['horas']['sin_luz'];
            if ($personas[$id]['ultimo'] === null || (string) $c['inicio_at'] > $personas[$id]['ultimo']) {
                $personas[$id]['ultimo'] = (string) $c['inicio_at'];
                $personas[$id]['riesgo'] = self::riesgo($v['riesgo_snapshot'] ?? null);
            }
        }

        uasort($personas, static fn ($a, $b) => [$b['horas'], $b['cortes']] <=> [$a['horas'], $a['cortes']]);

        return $personas;
    }

    // ─── Auditoría de consultas de EJESA ─────────────────────────────────

    /**
     * Estadísticas de ejesa_consulta. consultado_at viene en UTC: se agrupa por día y hora LOCAL.
     */
    public static function auditoria(array $consultas): array
    {
        $s = [
            'total' => count($consultas), 'personas' => 0,
            'por_dia' => [], 'por_usuario' => [], 'por_modalidad' => [], 'por_resultado' => [],
            'por_hora' => array_fill(0, 24, 0),
            'denegadas' => 0, 'errores' => 0, 'fuera_horario' => 0,
            'alertas' => [],
        ];

        $usuarioDia = [];
        foreach ($consultas as $q) {
            $local = self::utcALocal($q['consultado_at']);
            $dia   = substr($local, 0, 10);
            $hora  = (int) substr($local, 11, 2);
            $user  = (string) ($q['usuario_nombre_snapshot'] ?: ('Usuario #' . $q['usuario_id']));
            $mod   = (string) $q['modalidad'];
            $res   = (string) $q['resultado'];

            $s['personas'] += (int) $q['cantidad_personas'];
            $s['por_dia'][$dia] = ($s['por_dia'][$dia] ?? 0) + 1;
            $s['por_modalidad'][$mod] = ($s['por_modalidad'][$mod] ?? 0) + 1;
            $s['por_resultado'][$res] = ($s['por_resultado'][$res] ?? 0) + 1;
            $s['por_hora'][$hora]++;

            $s['por_usuario'][$user] ??= [
                'usuario' => $user, 'perfil' => $q['perfil_snapshot'] ?? null, 'total' => 0,
                'SERVICIO_EXACTO' => 0, 'CORTE' => 0, 'RIESGO_AMBITO' => 0,
                'SIN_RESULTADOS' => 0, 'DENEGADA' => 0, 'ERROR' => 0, 'personas' => 0, 'ultima' => '',
            ];
            $u = &$s['por_usuario'][$user];
            $u['total']++;
            $u['personas'] += (int) $q['cantidad_personas'];
            if (isset($u[$mod])) $u[$mod]++;
            if (isset($u[$res])) $u[$res]++;
            if ($local > $u['ultima']) $u['ultima'] = $local;
            unset($u);

            if ($res === 'DENEGADA') $s['denegadas']++;
            if ($res === 'ERROR') $s['errores']++;

            $nocturna = $hora >= self::HORA_NOCHE_DESDE || $hora < self::HORA_NOCHE_HASTA;
            if ($nocturna) {
                $s['fuera_horario']++;
            }

            $clave = $user . '|' . $dia;
            $usuarioDia[$clave] ??= ['usuario' => $user, 'dia' => $dia, 'total' => 0, 'sin_resultados' => 0, 'nocturnas' => 0];
            $usuarioDia[$clave]['total']++;
            if ($res === 'SIN_RESULTADOS' && $mod === 'SERVICIO_EXACTO') $usuarioDia[$clave]['sin_resultados']++;
            if ($nocturna) $usuarioDia[$clave]['nocturnas']++;
        }

        ksort($s['por_dia']);
        arsort($s['por_modalidad']);
        arsort($s['por_resultado']);
        uasort($s['por_usuario'], static fn ($a, $b) => $b['total'] <=> $a['total']);

        foreach ($usuarioDia as $ud) {
            if ($ud['nocturnas'] > 0) {
                $s['alertas'][] = ['tipo' => 'FUERA_HORARIO', 'usuario' => $ud['usuario'], 'dia' => $ud['dia'], 'cantidad' => $ud['nocturnas'],
                    'texto' => 'Consultas entre las ' . self::HORA_NOCHE_DESDE . ' y las 0' . self::HORA_NOCHE_HASTA . ' h'];
            }
            if ($ud['total'] > self::MAX_CONSULTAS_DIA) {
                $s['alertas'][] = ['tipo' => 'MUCHAS_CONSULTAS', 'usuario' => $ud['usuario'], 'dia' => $ud['dia'], 'cantidad' => $ud['total'],
                    'texto' => 'Más de ' . self::MAX_CONSULTAS_DIA . ' consultas en el día'];
            }
            if ($ud['sin_resultados'] >= self::MAX_SIN_RESULTADOS_DIA) {
                $s['alertas'][] = ['tipo' => 'SIN_RESULTADOS', 'usuario' => $ud['usuario'], 'dia' => $ud['dia'], 'cantidad' => $ud['sin_resultados'],
                    'texto' => 'Muchas búsquedas de servicio sin resultado (posible búsqueda al azar)'];
            }
        }
        usort($s['alertas'], static fn ($a, $b) => [$b['dia'], $b['cantidad']] <=> [$a['dia'], $a['cantidad']]);

        return $s;
    }

    /** Texto legible de una fila de historial: "Reprogramado: inicio 08:00 → 10:00". */
    public static function describirCambio(string $accion, $cambios): string
    {
        $c = is_array($cambios) ? $cambios : (json_decode((string) $cambios, true) ?: []);

        $etiquetas = [
            'inicio_at' => 'inicio', 'fin_estimado_at' => 'fin estimado', 'localidad_id' => 'localidad',
            'zona_descripcion' => 'zona', 'geometria' => 'zona dibujada', 'referencia' => 'referencia', 'estado' => 'estado',
        ];

        switch ($accion) {
            case 'CREAR':
                $d = $c['datos'] ?? [];
                $t = 'Creado';
                if (!empty($d['inicio_at']) && !empty($d['fin_estimado_at'])) {
                    $t .= ' para el ' . self::fecha($d['inicio_at']) . ' a ' . substr((string) $d['fin_estimado_at'], 11, 5);
                }

                return $t . (!empty($c['estado']) ? ' (' . strtolower((string) $c['estado']) . ')' : '');
            case 'ACTIVAR_PROTOCOLO':
                $p = $c['protocolo'] ?? [];

                return 'Protocolo activado: ' . (int) ($p['en_zona'] ?? 0) . ' afectados en la zona, '
                    . (int) ($p['a_revisar'] ?? 0) . ' a revisar (sin coordenadas)';
            case 'FINALIZAR':
                return !empty($c['vencido']) ? 'Finalizado tarde (después del fin estimado)' : 'Finalizado';
            case 'CANCELAR':
                return 'Cancelado' . (!empty($c['motivo']) ? ': ' . $c['motivo'] : '');
            case 'BAJA_BORRADOR':
                return 'Borrador dado de baja';
        }

        $partes = [];
        foreach ($c as $campo => $v) {
            if (!is_array($v) || !array_key_exists('antes', $v)) {
                continue;
            }
            $nombre = $etiquetas[$campo] ?? $campo;
            if ($campo === 'geometria') {
                $partes[] = 'cambió la zona dibujada';
                continue;
            }
            $antes   = (string) ($v['antes'] ?? '—');
            $despues = (string) ($v['despues'] ?? '—');
            if (in_array($campo, ['inicio_at', 'fin_estimado_at'], true)) {
                $antes   = $antes !== '' && $antes !== '—' ? self::fecha($antes) : '—';
                $despues = $despues !== '' && $despues !== '—' ? self::fecha($despues) : '—';
            }
            $partes[] = $nombre . ' ' . $antes . ' → ' . $despues;
        }

        $titulo = ['PUBLICAR' => 'Publicado', 'REPROGRAMAR' => 'Reprogramado', 'EDITAR' => 'Editado'][$accion] ?? ucfirst(strtolower($accion));

        return $titulo . ($partes ? ': ' . implode('; ', $partes) : '');
    }
}
