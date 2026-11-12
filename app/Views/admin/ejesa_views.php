<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> EJESA · Electrodependientes <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="/css/panel-pestanas.css">
<link rel="stylesheet" href="/css/ejesa.css?v=<?= @filemtime(FCPATH . 'css/ejesa.css') ?: '1' ?>">

<?php
use App\Libraries\Ejesa\Calculo;

$num = static fn ($n, int $dec = 0) => number_format((float) $n, $dec, ',', '.');

// Todos los filtros del panel (período, localidad, estado y los de auditoría) viajan juntos:
// cambiar uno no borra los demás.
$base = [
    'desde'     => $f['desde'],
    'hasta'     => $f['hasta'],
    'localidad' => $f['localidad'] ?: null,
    'estado'    => $f['estado'] ?: null,
    'usuario'   => $f['usuario'] ?: null,
    'modalidad' => $f['modalidad'] ?: null,
    'resultado' => $f['resultado'] ?: null,
    'accion'    => $f['accion'] ?: null,
];
$url = static function (array $extra = [], array $quitar = []) use ($base, $tab) {
    $q = array_diff_key($extra + $base + ['tab' => $tab], array_flip($quitar));

    return base_url(route_to('ejesa_views')) . '?' . http_build_query(array_filter($q, static fn ($v) => $v !== null && $v !== ''));
};
$urlCorte = static fn ($id, $volver) => base_url(route_to('ejesa_corte', $id)) . '?volver=' . $volver;

$chipEstado = static fn (string $e) => '<span class="ej-chip ' . $e . '">' . esc(Calculo::ESTADOS_VISIBLES[$e] ?? $e) . '</span>';

// Afectados: total + desglose por riesgo; "a revisar" aparte. Sin protocolo no hay dato (no se muestra 0).
$afectados = static function (array $a, bool $corto = false) use ($num) {
    if ($a['total'] === null) {
        return '<span class="ej-sin-dato">Sin dato: activar protocolo en sistemabasec</span>';
    }
    $h = '<div class="ej-riesgos"><span class="ej-total-afectados"><strong>' . $num($a['total']) . '</strong> en total</span>';
    foreach (Calculo::RIESGOS as $r) {
        if ($corto && $a[$r] === 0) continue;
        $h .= '<span class="ej-chip ' . $r . '">' . $r . ' ' . $num($a[$r]) . '</span>';
    }
    if ($a['a_revisar'] !== null) {
        $h .= '<span class="ej-chip REVISAR" title="Personas de la localidad sin coordenadas: no se suman a los afectados">a revisar ' . $num($a['a_revisar']) . '</span>';
    }
    if (!empty($a['desactualizado'])) {
        $h .= '<span class="ej-chip VENCIDO" title="El corte se reprogramó después de activar el protocolo">versión anterior</span>';
    }

    return $h . '</div>';
};

$horas = static function (array $h) {
    if (!$h['cuenta']) {
        return $h['marca'] === 'CANCELADO_ANTES'
            ? '<span class="ej-sin-dato">0 h · cancelado antes</span>'
            : '<span class="ej-sin-dato">Aún no empezó</span>';
    }
    $t = '<strong>' . Calculo::horas($h['sin_luz']) . '</strong> <small>(' . Calculo::hhmm($h['sin_luz']) . ')</small>';
    if ($h['marca'] !== 'REAL') {
        $t .= '<span class="ej-marca">' . esc(Calculo::MARCAS[$h['marca']]) . '</span>';
    }

    return $t;
};

$leyenda = '<p class="ej-leyenda"><i class="fas fa-info-circle"></i> Afectados <strong>orientativos</strong>: el domicilio no prueba de qué red eléctrica depende la persona. '
    . 'Se guardan al <strong>activar el protocolo</strong> en sistemabasec; "a revisar" (sin coordenadas) va aparte y no se suma. '
    . 'No existe "inicio real": las horas se cuentan desde el inicio programado.</p>';

$datosAl       = 'Datos al ' . Calculo::fecha($ahora);
$coloresRiesgo = ['ALTO' => '#D96C6C', 'MEDIANO' => '#E9B949', 'BAJO' => '#5BB8A6', 'PENDIENTE' => '#9AA5B5'];
$periodoTxt    = date('d/m/Y', strtotime($f['desde'])) . ' al ' . date('d/m/Y', strtotime($f['hasta']));

// Períodos rápidos (hora de Jujuy): se marca el que coincide con el filtro aplicado
$hoy      = new DateTime($ahora);
$periodos = [
    'Este mes'         => [(clone $hoy)->modify('first day of this month')->format('Y-m-d'), (clone $hoy)->modify('last day of this month')->format('Y-m-d')],
    'Mes anterior'     => [(clone $hoy)->modify('first day of last month')->format('Y-m-d'), (clone $hoy)->modify('last day of last month')->format('Y-m-d')],
    'Últimos 90 días'  => [(clone $hoy)->modify('-89 days')->format('Y-m-d'), $hoy->format('Y-m-d')],
    'Últimos 12 meses' => [(clone $hoy)->modify('first day of this month')->modify('-11 months')->format('Y-m-d'), (clone $hoy)->modify('last day of this month')->format('Y-m-d')],
    'Este año'         => [$hoy->format('Y') . '-01-01', $hoy->format('Y') . '-12-31'],
];
$esPeriodoDefecto = $f['desde'] === $periodos['Este mes'][0] && $f['hasta'] === $periodos['Este mes'][1];

// Nombre de la localidad elegida (para los filtros aplicados)
$nombreLocalidad = '';
foreach ($localidades ?? [] as $l) {
    if ((int) $l['localidad_id'] === (int) $f['localidad']) $nombreLocalidad = $l['nombre'];
}
?>

<div class="ej-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Salud mental — Electrodependientes</a> ›
    <strong>EJESA</strong>
</div>

<div class="ej-panel">
    <div class="ej-panel-header">
        <span class="ej-panel-title"><i class="fas fa-bolt"></i> ELECTRODEPENDIENTES · CORTES PROGRAMADOS EJESA</span>
        <span class="ej-datos-al"><i class="far fa-clock"></i> <?= esc($datosAl) ?></span>
    </div>

    <div class="ej-panel-body">

        <?= view('_messages') ?>

        <div class="ej-toolbar">
            <a href="<?= base_url(route_to('saludmental_views')); ?>" class="bl-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
            <a href="<?= base_url(route_to('electrodependiente_list')); ?>" class="bl-btn ghost"><i class="fas fa-users"></i> Pacientes</a>
            <?php if (!isset($error)): ?>
            <a href="<?= base_url(route_to('ejesa_export')) . '?' . http_build_query(array_filter(['desde' => $f['desde'], 'hasta' => $f['hasta'], 'localidad' => $f['localidad'], 'estado' => $f['estado']])) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Excel del historial
            </a>
            <?php endif; ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="ej-banda rojo"><i class="fas fa-exclamation-triangle"></i> <span><?= esc($error) ?></span></div>
        <?php else: ?>

        <!-- ── Bandas: en curso / vencidos sin finalizar (texto, no solo color) ── -->
        <?php foreach ($enCurso as $c): ?>
            <div class="ej-banda rojo">
                <i class="fas fa-bolt"></i>
                <span><strong>Corte EN CURSO</strong> · <?= esc(Calculo::rango($c['inicio_at'], $c['fin_estimado_at'])) ?> ·
                    <?= esc($c['localidad'] ?: 'Localidad sin indicar') ?> ·
                    <?= $c['afectados']['total'] === null ? 'afectados: sin dato' : $num($c['afectados']['total']) . ' afectados (' . $num($c['afectados']['ALTO']) . ' de riesgo ALTO)' ?>
                    · <a href="<?= $urlCorte($c['corte_id'], 'resumen') ?>">ver detalle</a></span>
            </div>
        <?php endforeach; ?>
        <?php if ($vencidos): ?>
            <div class="ej-banda ambar">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong><?= count($vencidos) ?> corte<?= count($vencidos) > 1 ? 's' : '' ?> vencido<?= count($vencidos) > 1 ? 's' : '' ?> sin finalizar</strong>:
                    ya pasó su fin estimado y EJESA no lo cerró.
                    <?php foreach ($vencidos as $i => $c): ?>
                        <?= $i ? ' · ' : '' ?><a href="<?= $urlCorte($c['corte_id'], 'resumen') ?>"><?= esc(Calculo::fecha($c['inicio_at'])) ?> (<?= esc($c['localidad'] ?: 's/localidad') ?>)</a>
                    <?php endforeach; ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- ── Filtros: un solo formulario para todo el panel ── -->
        <form method="GET" action="<?= base_url(route_to('ejesa_views')); ?>" class="ej-filtro-bar" id="ejFiltros">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">

            <div class="ej-filtro-fila">
                <div class="ej-periodos">
                    <span class="ej-filtro-label">Período rápido</span>
                    <?php foreach ($periodos as $nombre => [$d, $h]): ?>
                        <button type="button" class="ej-periodo <?= $f['desde'] === $d && $f['hasta'] === $h ? 'is-active' : '' ?>"
                                data-desde="<?= $d ?>" data-hasta="<?= $h ?>"><?= esc($nombre) ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="ej-filtro <?= $esPeriodoDefecto ? '' : 'activo' ?>">
                    <label for="f-desde">Desde</label>
                    <input type="date" id="f-desde" name="desde" value="<?= esc($f['desde']) ?>">
                </div>
                <div class="ej-filtro <?= $esPeriodoDefecto ? '' : 'activo' ?>">
                    <label for="f-hasta">Hasta</label>
                    <input type="date" id="f-hasta" name="hasta" value="<?= esc($f['hasta']) ?>">
                </div>
                <div class="ej-filtro <?= $f['localidad'] ? 'activo' : '' ?>">
                    <label for="f-loc">Localidad</label>
                    <select id="f-loc" name="localidad">
                        <option value="">Todas</option>
                        <?php foreach ($localidades as $l): ?>
                            <option value="<?= (int) $l['localidad_id'] ?>" <?= (int) $f['localidad'] === (int) $l['localidad_id'] ? 'selected' : '' ?>><?= esc($l['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro <?= $f['estado'] ? 'activo' : '' ?>">
                    <label for="f-estado">Estado del corte</label>
                    <select id="f-estado" name="estado">
                        <option value="">Todos</option>
                        <?php foreach (Calculo::ESTADOS_VISIBLES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $f['estado'] === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro-acciones">
                    <button type="submit" class="bl-btn teal" title="Filtrar"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="<?= base_url(route_to('ejesa_views')) . '?tab=' . esc($tab, 'url') ?>" class="bl-btn ghost" id="btnLimpiar" title="Limpiar filtros"><i class="fas fa-times"></i> Limpiar</a>
                </div>
            </div>

            <!-- Filtros propios de Auditoría: se ven solo en esa pestaña, pero siempre se conservan -->
            <div class="ej-filtro-fila" data-solo-tab="auditoria" <?= $tab === 'auditoria' ? '' : 'hidden' ?>>
                <div class="ej-filtro-sub-titulo"><i class="fas fa-user-shield"></i> Filtros de auditoría</div>
                <div class="ej-filtro <?= $f['usuario'] ? 'activo' : '' ?>">
                    <label for="a-usuario">Usuario</label>
                    <select id="a-usuario" name="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['usuario_id'] ?>" <?= (int) $f['usuario'] === (int) $u['usuario_id'] ? 'selected' : '' ?>><?= esc($u['nombre'] ?: ('Usuario #' . $u['usuario_id'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro <?= $f['modalidad'] ? 'activo' : '' ?>">
                    <label for="a-mod">Cómo consultó</label>
                    <select id="a-mod" name="modalidad">
                        <option value="">Todas</option>
                        <?php foreach ($modalidades as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['modalidad'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro <?= $f['resultado'] ? 'activo' : '' ?>">
                    <label for="a-res">Resultado</label>
                    <select id="a-res" name="resultado">
                        <option value="">Todos</option>
                        <?php foreach ($resultados as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['resultado'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro <?= $f['accion'] ? 'activo' : '' ?>">
                    <label for="a-acc">Cambio de corte</label>
                    <select id="a-acc" name="accion">
                        <option value="">Todos</option>
                        <?php foreach ($acciones as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['accion'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <!-- Filtros aplicados: cada uno se quita con un clic -->
        <?php
        $activos = [];
        if (!$esPeriodoDefecto) $activos[] = ['Período ' . $periodoTxt, $url([], ['desde', 'hasta'])];
        if ($f['localidad'])    $activos[] = ['Localidad: ' . ($nombreLocalidad ?: '#' . $f['localidad']), $url([], ['localidad'])];
        if ($f['estado'])       $activos[] = ['Estado: ' . Calculo::ESTADOS_VISIBLES[$f['estado']], $url([], ['estado'])];
        if ($f['usuario']) {
            $nu = '';
            foreach ($usuarios as $u) if ((int) $u['usuario_id'] === (int) $f['usuario']) $nu = $u['nombre'];
            $activos[] = ['Usuario: ' . ($nu ?: '#' . $f['usuario']), $url([], ['usuario'])];
        }
        if ($f['modalidad'])    $activos[] = ['Cómo: ' . $modalidades[$f['modalidad']], $url([], ['modalidad'])];
        if ($f['resultado'])    $activos[] = ['Resultado: ' . $resultados[$f['resultado']], $url([], ['resultado'])];
        if ($f['accion'])       $activos[] = ['Cambio: ' . $acciones[$f['accion']], $url([], ['accion'])];
        ?>
        <?php if ($activos): ?>
            <div class="ej-activos" aria-label="Filtros aplicados">
                <span><i class="fas fa-filter"></i> Filtros aplicados:</span>
                <?php foreach ($activos as [$texto, $href]): ?>
                    <a class="ej-activo" href="<?= $href ?>" data-quitar title="Quitar este filtro"><?= esc($texto) ?> <span><i class="fas fa-times"></i></span></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ── Sub-pestañas ── -->
        <div class="ml-tabs" role="tablist">
            <?php foreach ([
                'resumen'   => ['fa-tachometer-alt', 'Resumen'],
                'proximos'  => ['fa-calendar-alt', 'Próximos cortes'],
                'historial' => ['fa-history', 'Historial de cortes'],
                'impacto'   => ['fa-chart-bar', 'Impacto'],
                'auditoria' => ['fa-user-shield', 'Auditoría'],
            ] as $clave => [$icono, $titulo]): ?>
                <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab" aria-selected="<?= $tab === $clave ? 'true' : 'false' ?>">
                    <i class="fas <?= $icono ?>"></i> <?= $titulo ?>
                </button>
            <?php endforeach; ?>
        </div>

        <?php $k = $kpis; ?>

        <!-- ══════════════ 1. RESUMEN ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'resumen' ? 'is-active' : '' ?>" data-pane="resumen">

            <?php $prox = $enCurso[0] ?? $proximos[0] ?? null; ?>
            <?php if ($prox): $enCursoProx = $prox['estado_visible'] === 'EN_CURSO'; ?>
                <div class="ej-proximo <?= $enCursoProx ? 'en-curso' : '' ?>">
                    <div class="ej-proximo-titulo"><?= $enCursoProx ? 'Corte en curso' : 'Próximo corte programado' ?></div>
                    <div class="ej-proximo-fecha"><?= esc(Calculo::rango($prox['inicio_at'], $prox['fin_estimado_at'])) ?></div>
                    <?php if ($enCursoProx): ?>
                        <span class="ej-proximo-faltan"><i class="fas fa-bolt"></i> En curso: lleva <?= esc(Calculo::horas($prox['horas']['sin_luz'])) ?> sin luz</span>
                    <?php else: ?>
                        <span class="ej-proximo-faltan"><i class="far fa-hourglass"></i> Faltan <?= esc($prox['faltan']) ?></span>
                    <?php endif; ?>
                    <div class="ej-proximo-datos">
                        <div><span class="lbl">Localidad</span><?= esc($prox['localidad'] ?: 'Localidad sin indicar') ?></div>
                        <div><span class="lbl">Zona</span><?= esc($prox['zona_descripcion'] ?: '—') ?></div>
                        <div><span class="lbl">Duración programada</span><?= esc(Calculo::horas($prox['horas']['programadas'])) ?> (<?= Calculo::hhmm($prox['horas']['programadas']) ?>)</div>
                        <div><span class="lbl">Afectados</span><?= $afectados($prox['afectados'], true) ?></div>
                    </div>
                    <div class="ej-proximo-acciones">
                        <a href="<?= $urlCorte($prox['corte_id'], 'resumen') ?>" class="bl-btn sm"><i class="fas fa-search"></i> Ver detalle y afectados</a>
                        <?php if (count($enCurso) + count($proximos) > 1): ?>
                            <button type="button" class="bl-btn sm" data-ir-tab="proximos"><i class="fas fa-calendar-alt"></i> Ver los <?= count($enCurso) + count($proximos) ?> próximos</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="ej-proximo vacio">
                    <div class="ej-proximo-titulo">Próximo corte programado</div>
                    <div class="ej-proximo-fecha" style="font-size:16px;">No hay cortes programados por delante<?= $nombreLocalidad ? ' en ' . esc($nombreLocalidad) : '' ?>.</div>
                </div>
            <?php endif; ?>

            <div class="ej-kpi-cards">
                <button type="button" class="ej-kpi-card navy" data-ir-tab="historial" title="Ver el historial de cortes">
                    <div class="ej-kpi-icono"><i class="fas fa-bolt"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Cortes publicados</div>
                        <div class="ej-kpi-val"><?= $num($k['cortes']) ?></div>
                        <div class="ej-kpi-sub"><?= $num($k['ejecutados']) ?> con horas sin luz · <?= $num($k['proximos']) ?> por venir</div>
                    </div>
                </button>
                <div class="ej-kpi-card azul">
                    <div class="ej-kpi-icono"><i class="fas fa-clock"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Horas sin luz</div>
                        <div class="ej-kpi-val"><?= Calculo::horas($k['horas_sin_luz']) ?></div>
                        <div class="ej-kpi-sub"><?= $k['duracion_media'] === null ? 'Sin cortes ejecutados' : 'Promedio ' . Calculo::horas($k['duracion_media']) . ' por corte' ?><?= $k['corte_mas_largo'] ? ' · máx. ' . Calculo::horas($k['corte_mas_largo']['horas']) : '' ?></div>
                    </div>
                </div>
                <button type="button" class="ej-kpi-card teal" data-ir-tab="impacto" data-ir-tabla="1" title="Ver quiénes quedaron sin luz">
                    <div class="ej-kpi-icono"><i class="fas fa-user-friends"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Personas afectadas</div>
                        <div class="ej-kpi-val"><?= $k['personas_distintas'] === null ? '—' : $num($k['personas_distintas']) ?></div>
                        <div class="ej-kpi-sub"><?= $k['personas_distintas'] === null ? 'Sin dato: ningún corte con protocolo' : 'personas distintas en el período' ?></div>
                    </div>
                </button>
                <div class="ej-kpi-card ambar">
                    <div class="ej-kpi-icono"><i class="fas fa-user-clock"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Persona-horas sin luz</div>
                        <div class="ej-kpi-val"><?= $num($k['persona_horas'], 1) ?></div>
                        <div class="ej-kpi-sub">Riesgo ALTO: <?= $num($k['persona_horas_alto'], 1) ?><?= $k['sin_dato_afectados'] ? ' · ' . $k['sin_dato_afectados'] . ' corte(s) sin dato' : '' ?></div>
                    </div>
                </div>
                <button type="button" class="ej-kpi-card rojo" data-ir-tab="impacto" data-ir-riesgo="ALTO" title="Ver las personas de riesgo ALTO">
                    <div class="ej-kpi-icono"><i class="fas fa-heartbeat"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Afectados riesgo ALTO</div>
                        <div class="ej-kpi-val"><?= $num($k['afectados_alto']) ?></div>
                        <div class="ej-kpi-sub">suma por corte · <?= $num($k['a_revisar']) ?> a revisar aparte</div>
                    </div>
                </button>
                <div class="ej-kpi-card gris">
                    <div class="ej-kpi-icono"><i class="fas fa-ban"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Cancelados</div>
                        <div class="ej-kpi-val"><?= $num($k['cancelados']) ?></div>
                        <div class="ej-kpi-sub"><?= $num($k['cancelados_antes']) ?> antes de empezar (no cuentan)</div>
                    </div>
                </div>
                <div class="ej-kpi-card ambar <?= $k['vencidos'] ? 'alerta' : '' ?>">
                    <div class="ej-kpi-icono"><i class="fas fa-hourglass-end"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Vencidos sin finalizar</div>
                        <div class="ej-kpi-val"><?= $num($k['vencidos']) ?></div>
                        <div class="ej-kpi-sub"><?= $k['vencidos'] ? 'EJESA no los cerró: revisar' : 'Ninguno pendiente de cierre' ?></div>
                    </div>
                </div>
                <div class="ej-kpi-card verde">
                    <div class="ej-kpi-icono"><i class="fas fa-check-circle"></i></div>
                    <div class="ej-kpi-contenido">
                        <div class="ej-kpi-lbl">Cierre a tiempo</div>
                        <div class="ej-kpi-val"><?= $k['cumplimiento_cierre'] === null ? '—' : $num($k['cumplimiento_cierre'], 0) . '%' ?></div>
                        <div class="ej-kpi-sub"><?= $num($k['finalizados_a_tiempo']) ?> a tiempo · <?= $num($k['finalizados_tarde']) ?> tarde · <?= $num($k['vencidos']) ?> sin finalizar</div>
                    </div>
                </div>
            </div>

            <div class="ej-grid-2">
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-chart-pie"></i> Cortes por estado</span><span class="ej-chip neutro"><?= $num($k['cortes']) ?> cortes</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas bajo"><canvas id="chEstados" aria-label="Cortes del período por estado"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un estado para ver esos cortes en el historial.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla"><thead><tr><th>Estado</th><th class="num">Cortes</th></tr></thead>
                            <tbody><?php foreach ($porEstado as $e => $n): ?><tr><td><?= $chipEstado($e) ?></td><td class="num"><?= $n ?></td></tr><?php endforeach; ?></tbody></table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-users"></i> Afectados por riesgo</span><span class="ej-chip neutro">suma por corte</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas bajo"><canvas id="chRiesgoResumen" aria-label="Afectados del período por riesgo"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un riesgo para ver quiénes son.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <?php $totR = array_sum($k['afectados_por_riesgo']); ?>
                            <table class="ej-tabla"><thead><tr><th>Riesgo</th><th class="num">Afectados</th><th class="num">%</th></tr></thead>
                            <tbody><?php foreach ($k['afectados_por_riesgo'] as $r => $n): ?><tr><td><span class="ej-chip <?= $r ?>"><?= $r ?></span></td><td class="num"><?= $num($n) ?></td><td class="num"><?= $totR ? $num($n * 100 / $totR, 0) . '%' : '—' ?></td></tr><?php endforeach; ?>
                            <tr><td><span class="ej-chip REVISAR">A revisar (aparte)</span></td><td class="num"><?= $num($k['a_revisar']) ?></td><td class="num">—</td></tr></tbody></table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-clock"></i> Horas sin luz por mes</span><span class="ej-chip neutro"><?= esc($periodoTxt) ?></span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas bajo"><canvas id="chHorasResumen" aria-label="Horas sin luz por mes"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un mes para filtrar el período.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla"><thead><tr><th>Mes</th><th class="num">Cortes</th><th class="num">Horas</th></tr></thead>
                            <tbody><?php foreach ($serie as $s): ?><tr><td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td><td class="num"><?= $s['cortes'] ?></td><td class="num"><?= Calculo::horas($s['horas']) ?></td></tr><?php endforeach; ?></tbody></table>
                        </details>
                    </div>
                </div>
            </div>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 2. PRÓXIMOS CORTES ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'proximos' ? 'is-active' : '' ?>" data-pane="proximos">
            <?php $lista = array_merge($enCurso, $proximos); ?>

            <?php if ($lista): ?>
            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="far fa-calendar"></i> Agenda de cortes</span><span class="ej-chip neutro">no depende del período<?= $nombreLocalidad ? ' · ' . esc($nombreLocalidad) : '' ?></span></div>
                <div class="ej-bloque-body">
                    <?php
                    $porDia = [];
                    foreach ($lista as $c) { $porDia[substr($c['inicio_at'], 0, 10)][] = $c; }
                    $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    ?>
                    <div class="ej-cal">
                        <?php foreach ($porDia as $dia => $cs): ?>
                            <div class="ej-cal-dia">
                                <div class="ej-cal-fecha"><span><?= $diasSemana[(int) date('w', strtotime($dia))] ?> <?= esc(date('d/m/Y', strtotime($dia))) ?></span><span><?= count($cs) ?> corte<?= count($cs) > 1 ? 's' : '' ?></span></div>
                                <?php foreach ($cs as $c): ?>
                                    <a class="ej-cal-corte" href="<?= $urlCorte($c['corte_id'], 'proximos') ?>">
                                        <span class="ej-cal-hora"><?= substr($c['inicio_at'], 11, 5) ?>–<?= substr($c['fin_estimado_at'], 11, 5) ?></span>
                                        <span><?= esc($c['localidad'] ?: 'Localidad sin indicar') ?><?= $c['afectados']['total'] !== null ? ' · <strong>' . $num($c['afectados']['total']) . '</strong> afect.' : '' ?></span>
                                        <?= $chipEstado($c['estado_visible']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-calendar-alt"></i> En curso y programados (<?= count($lista) ?>)</span><span class="ej-chip neutro">no depende del período</span></div>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas">
                    <thead><tr>
                        <th>Fecha y hora</th><th>Faltan</th><th class="num">Duración</th><th>Localidad / zona</th><th>Referencia</th>
                        <th>Afectados por riesgo</th><th>Protocolo</th><th class="num">Reprog.</th><th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($lista as $c): ?>
                        <tr class="<?= $c['estado_visible'] === 'EN_CURSO' ? 'destacado' : '' ?>">
                            <td class="ej-td-titulo" data-label="Fecha"><strong><?= esc(Calculo::rango($c['inicio_at'], $c['fin_estimado_at'])) ?></strong><br><?= $chipEstado($c['estado_visible']) ?></td>
                            <td data-label="Faltan"><?= $c['estado_visible'] === 'EN_CURSO' ? '<strong style="color:var(--ej-alto)">En curso</strong>' : esc($c['faltan']) ?></td>
                            <td data-label="Duración" class="num"><?= Calculo::horas($c['horas']['programadas']) ?><br><small><?= Calculo::hhmm($c['horas']['programadas']) ?></small></td>
                            <td data-label="Localidad"><?= esc($c['localidad'] ?: 'Localidad sin indicar') ?><?= $c['zona_descripcion'] ? '<br><small>' . esc($c['zona_descripcion']) . '</small>' : '' ?></td>
                            <td data-label="Referencia"><?= esc($c['referencia'] ?: '—') ?></td>
                            <td data-label="Afectados"><?= $afectados($c['afectados']) ?></td>
                            <td data-label="Protocolo"><?= $c['protocolo'] ? '<span class="ej-chip mal">Activado</span>' : '<span class="ej-chip neutro">No</span>' ?></td>
                            <td data-label="Reprogramaciones" class="num"><?= (int) $c['reprogramaciones'] ?></td>
                            <td data-label=""><a href="<?= $urlCorte($c['corte_id'], 'proximos') ?>" class="bl-btn ghost sm">Detalle</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$lista): ?><tr><td colspan="9" class="ej-vacio">No hay cortes en curso ni programados<?= $nombreLocalidad ? ' en ' . esc($nombreLocalidad) : '' ?>.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 3. HISTORIAL DE CORTES ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'historial' ? 'is-active' : '' ?>" data-pane="historial">
            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-stream"></i> Horas programadas y horas sin luz de cada corte</span>
                    <span class="ej-chip neutro"><?= esc($periodoTxt) ?></span>
                </div>
                <div class="ej-bloque-body">
                    <?php $cortesGraf = array_slice(array_values(array_filter($cortes, static fn ($c) => $c['horas']['cuenta'] || $c['estado_visible'] === 'PROGRAMADO')), -40); ?>
                    <div class="ej-chart ej-chart-canvas" style="height:<?= max(220, min(900, 60 + count($cortesGraf) * 34)) ?>px;"><canvas id="chCortes" aria-label="Horas por corte"></canvas></div>
                    <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en una barra para abrir el detalle del corte.<?= count($cortesGraf) === 40 ? ' Se muestran los últimos 40.' : '' ?></p>
                </div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-history"></i> Cortes con inicio en el período (<?= count($cortes) ?>)</span>
                    <span class="ej-chip neutro"><?= esc($periodoTxt) ?></span>
                </div>
                <?php if ($cortes): ?>
                <div class="ej-tabla-filtros">
                    <div class="ej-buscar"><i class="fas fa-search"></i><input type="search" placeholder="Buscar localidad, referencia, fecha…" data-buscar="tablaHistorial" aria-label="Buscar en el historial"></div>
                    <span class="ej-contador" data-contador="tablaHistorial"></span>
                </div>
                <?php endif; ?>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas" id="tablaHistorial">
                    <thead><tr>
                        <th>Fecha</th><th>Localidad</th><th>Estado</th><th class="num">Programadas</th><th>Horas sin luz</th>
                        <th>Afectados</th><th class="num">Persona-horas</th><th>Protocolo</th><th class="num">Reprog.</th><th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($cortes as $c): ?>
                        <tr>
                            <td class="ej-td-titulo" data-label="Fecha"><strong><?= esc(Calculo::fecha($c['inicio_at'])) ?></strong> a <?= substr($c['fin_estimado_at'], 11, 5) ?><?= $c['referencia'] ? '<br><small>' . esc($c['referencia']) . '</small>' : '' ?></td>
                            <td data-label="Localidad"><?= esc($c['localidad'] ?: 'Localidad sin indicar') ?></td>
                            <td data-label="Estado"><?= $chipEstado($c['estado_visible']) ?></td>
                            <td data-label="Programadas" class="num"><?= Calculo::horas($c['horas']['programadas']) ?></td>
                            <td data-label="Sin luz"><?= $horas($c['horas']) ?></td>
                            <td data-label="Afectados"><?= $afectados($c['afectados'], true) ?></td>
                            <td data-label="Persona-horas" class="num"><?= $c['persona_horas'] === null ? '—' : $num($c['persona_horas'], 1) ?></td>
                            <td data-label="Protocolo"><?= $c['protocolo'] ? '<span class="ej-chip mal">Sí</span>' : '<span class="ej-chip neutro">No</span>' ?></td>
                            <td data-label="Reprogramaciones" class="num"><?= (int) $c['reprogramaciones'] ?></td>
                            <td data-label=""><a href="<?= $urlCorte($c['corte_id'], 'historial') ?>" class="bl-btn ghost sm">Detalle</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$cortes): ?><tr><td colspan="10" class="ej-vacio">Sin cortes publicados con inicio en el período.</td></tr><?php endif; ?>
                    </tbody>
                    <?php if ($cortes): ?>
                    <tfoot><tr>
                        <td data-label="Total" class="ej-td-titulo">Total del período</td>
                        <td data-label="Cortes"><?= $num($k['cortes']) ?> cortes</td>
                        <td data-label="Cancelados"><?= $num($k['cancelados']) ?> cancel.</td>
                        <td data-label="Programadas" class="num"><?= Calculo::horas($k['horas_programadas']) ?></td>
                        <td data-label="Sin luz"><?= Calculo::horas($k['horas_sin_luz']) ?> (<?= Calculo::hhmm($k['horas_sin_luz']) ?>)<br><small>promedio <?= $k['duracion_media'] === null ? '—' : Calculo::horas($k['duracion_media']) ?></small></td>
                        <td data-label="Afectados"><?= $num($k['afectados_suma']) ?> (<?= $k['personas_distintas'] === null ? 's/d' : $num($k['personas_distintas']) ?> distintas)</td>
                        <td data-label="Persona-horas" class="num"><?= $num($k['persona_horas'], 1) ?></td>
                        <td data-label="Con protocolo"><?= $num($k['con_protocolo']) ?></td>
                        <td data-label="Reprogramaciones" class="num"><?= $num($k['reprogramaciones']) ?></td>
                        <td data-label=""></td>
                    </tr></tfoot>
                    <?php endif; ?>
                </table>
                </div>
            </div>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 4. IMPACTO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'impacto' ? 'is-active' : '' ?>" data-pane="impacto">
            <?php if (count($serie) <= 1): ?>
                <div class="ej-banda gris"><i class="fas fa-info-circle"></i> <span>El período elegido tiene un solo mes. Para ver la evolución, elegí <a href="#" data-periodo="Últimos 90 días">Últimos 90 días</a> o <a href="#" data-periodo="Este año">Este año</a>.</span></div>
            <?php endif; ?>

            <div class="ej-grid-2">
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-clock"></i> Horas sin luz por mes</span><span class="ej-chip neutro">número arriba: horas · dentro de la barra celeste: cortes</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas"><canvas id="chHoras" aria-label="Horas sin luz por mes"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un mes para filtrar el período.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Mes</th><th class="num">Cortes</th><th class="num">Horas</th><th class="num">Promedio</th></tr></thead>
                                <tbody><?php foreach ($serie as $s): ?><tr>
                                    <td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td><td class="num"><?= $s['cortes'] ?></td>
                                    <td class="num"><?= Calculo::horas($s['horas']) ?></td>
                                    <td class="num"><?= $s['cortes'] ? Calculo::horas($s['horas'] / $s['cortes']) : '—' ?></td>
                                </tr><?php endforeach; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-users"></i> Personas afectadas por riesgo y mes</span><span class="ej-chip neutro">suma por corte</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas"><canvas id="chRiesgo" aria-label="Afectados por riesgo y mes"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un color para ver las personas de ese riesgo.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Mes</th><?php foreach (Calculo::RIESGOS as $r): ?><th class="num"><?= $r ?></th><?php endforeach; ?><th class="num">Sin dato</th></tr></thead>
                                <tbody><?php foreach ($serie as $s): ?><tr>
                                    <td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td>
                                    <?php foreach (Calculo::RIESGOS as $r): ?><td class="num"><?= $s[$r] ?></td><?php endforeach; ?>
                                    <td class="num"><?= $s['sin_dato'] ? $s['sin_dato'] . ' corte(s)' : '—' ?></td>
                                </tr><?php endforeach; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-map-marker-alt"></i> Top 10 localidades por persona-horas</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas" style="height:<?= max(200, 60 + count($localidadesTop) * 34) ?>px;"><canvas id="chLocalidad" aria-label="Localidades por persona-horas"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en una localidad para filtrar todo el panel.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Localidad</th><th class="num">Cortes</th><th class="num">Horas</th><th class="num">Afectados</th><th class="num">ALTO</th><th class="num">Persona-horas</th></tr></thead>
                                <tbody><?php foreach ($localidadesTop as $l): ?><tr>
                                    <td><?= esc($l['localidad']) ?></td><td class="num"><?= $l['cortes'] ?></td><td class="num"><?= Calculo::horas($l['horas']) ?></td>
                                    <td class="num"><?= $num($l['afectados']) ?><?= $l['sin_dato'] ? ' <small>(' . $l['sin_dato'] . ' s/d)</small>' : '' ?></td>
                                    <td class="num"><?= $num($l['alto']) ?></td><td class="num"><?= $num($l['persona_horas'], 1) ?></td>
                                </tr><?php endforeach; ?>
                                <?php if (!$localidadesTop): ?><tr><td colspan="6" class="ej-vacio">Sin cortes ejecutados en el período.</td></tr><?php endif; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-chart-line"></i> Persona-horas sin luz por mes</span><span class="ej-chip neutro">total y riesgo ALTO</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas"><canvas id="chPersonaHoras" aria-label="Persona-horas por mes"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un mes para filtrar el período.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Mes</th><th class="num">Persona-horas</th><th class="num">De riesgo ALTO</th></tr></thead>
                                <tbody><?php foreach ($serie as $s): ?><tr><td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td><td class="num"><?= $num($s['persona_horas'], 1) ?></td><td class="num"><?= $num($s['persona_horas_alto'], 1) ?></td></tr><?php endforeach; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-lungs"></i> Equipos de las personas que quedaron sin luz</span><span class="ej-chip neutro">personas por tipo de equipo</span></div>
                <div class="ej-bloque-body">
                    <div class="ej-chart ej-chart-canvas" style="height:<?= max(180, 50 + count($equipos) * 30) ?>px;"><canvas id="chEquipos" aria-label="Personas afectadas por tipo de equipo"></canvas></div>
                    <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un equipo para ver quiénes lo usan. Una persona con varios equipos cuenta en cada tipo.</p>
                    <details class="ej-datos"><summary>Ver tabla de datos</summary>
                        <table class="ej-tabla">
                            <thead><tr><th>Tipo de equipo</th><th class="num">Personas</th></tr></thead>
                            <tbody><?php foreach ($equipos as $e): ?><tr><td><?= esc($e['tipo']) ?></td><td class="num"><?= $e['personas'] ?></td></tr><?php endforeach; ?>
                            <?php if (!$equipos): ?><tr><td colspan="2" class="ej-vacio">Sin personas registradas en el período.</td></tr><?php endif; ?></tbody>
                        </table>
                    </details>
                </div>
            </div>

            <!-- Quiénes fueron afectados -->
            <?php
            $cantRiesgo = array_fill_keys(Calculo::RIESGOS, 0);
            foreach (array_slice($acumulado, 0, 200, true) as $a) $cantRiesgo[$a['riesgo']]++;
            ?>
            <div class="ej-bloque" id="bloqueQuienes">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-user-injured"></i> Quiénes quedaron sin luz en el período (<?= count($acumulado) ?> personas)</span>
                    <span class="ej-chip neutro">ordenado por horas acumuladas</span>
                </div>
                <?php if ($acumulado): ?>
                <div class="ej-tabla-filtros">
                    <div class="grupo" role="group" aria-label="Filtrar por riesgo">
                        <?php foreach (Calculo::RIESGOS as $r): ?>
                            <button type="button" class="ej-filtro-riesgo" data-riesgo="<?= $r ?>" aria-pressed="false" <?= $cantRiesgo[$r] ? '' : 'disabled' ?>>
                                <span class="punto" style="background:<?= $coloresRiesgo[$r] ?>"></span><?= $r ?> <span class="cant"><?= $cantRiesgo[$r] ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="ej-buscar"><i class="fas fa-search"></i><input type="search" placeholder="Buscar persona, localidad o equipo…" data-buscar="tablaQuienes" aria-label="Buscar personas"></div>
                    <span class="ej-contador" data-contador="tablaQuienes"></span>
                </div>
                <?php endif; ?>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas" id="tablaQuienes">
                    <thead><tr><th>Persona</th><th>Riesgo</th><th>Localidad</th><th>Equipos</th><th class="num">Cortes</th><th class="num">Horas sin luz</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($acumulado, 0, 200, true) as $id => $a): $p = $personas[$id] ?? null; ?>
                        <tr class="<?= $a['riesgo'] === 'ALTO' ? 'destacado' : '' ?>">
                            <td class="ej-td-titulo" data-label="Persona"><strong><?= esc($p['paciente'] ?? ('Persona #' . $id)) ?></strong><?= $p && $p['edad'] ? ' <small>' . (int) $p['edad'] . ' años · ' . esc($p['tipo'] ?? '') . '</small>' : '' ?></td>
                            <td data-label="Riesgo"><span class="ej-chip <?= $a['riesgo'] ?>"><?= $a['riesgo'] ?></span></td>
                            <td data-label="Localidad"><?= esc($p['localidad'] ?? '—') ?></td>
                            <td data-label="Equipos" class="ej-td-equipos"><?php if (!empty($p['equipos'])): foreach ($p['equipos'] as $eq): ?>
                                <div><?= esc($eq['equipamiento']) ?><?= $eq['tiempo_uso'] ? ' <small>(' . esc($eq['tiempo_uso']) . ')</small>' : '' ?></div>
                            <?php endforeach; else: ?>—<?php endif; ?></td>
                            <td data-label="Cortes" class="num"><?= $a['cortes'] ?></td>
                            <td data-label="Horas sin luz" class="num"><strong><?= Calculo::horas($a['horas']) ?></strong><br><small><?= Calculo::hhmm($a['horas']) ?></small></td>
                            <td data-label="Estado"><?php
                                if (!$p) echo '<span class="ej-chip neutro">Sin ficha</span>';
                                elseif (($p['estado_vital'] ?? '') === 'fallecido') echo '<span class="ej-chip CANCELADO">Fallecido</span>';
                                elseif (($p['estado'] ?? '') !== 'activo') echo '<span class="ej-chip neutro">' . esc(ucfirst((string) $p['estado'])) . '</span>';
                                else echo '<span class="ej-chip ok">Activo</span>';
                            ?></td>
                            <td data-label=""><?php if ($puedeVerFicha && $p): ?><a class="bl-btn ghost sm" href="<?= base_url(route_to('electrodependiente_historial', $id)) ?>" title="Ver el historial del paciente (solo lectura)"><i class="fas fa-history"></i> Historial</a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$acumulado): ?><tr><td colspan="8" class="ej-vacio">Sin personas registradas: solo se conocen los afectados de cortes con protocolo activado.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
                <div class="ej-bloque-body" style="padding-top:8px;">
                    <p class="ej-leyenda" style="margin-bottom:0;">El riesgo es el del momento en que se activó el protocolo de su último corte. Horas acumuladas = suma de las horas sin luz de cada corte en que figura. No se incluyen las personas "a revisar" (sin coordenadas).<?= count($acumulado) > 200 ? ' Se muestran las primeras 200.' : '' ?></p>
                </div>
            </div>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 5. AUDITORÍA ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'auditoria' ? 'is-active' : '' ?>" data-pane="auditoria">
            <?php $au = $auditoria; ?>

            <div class="ej-kpi-cards">
                <div class="ej-kpi-card navy">
                    <div class="ej-kpi-icono"><i class="fas fa-search"></i></div>
                    <div class="ej-kpi-contenido"><div class="ej-kpi-lbl">Consultas de EJESA</div><div class="ej-kpi-val"><?= $num($au['total']) ?></div><div class="ej-kpi-sub">en el período · <?= count($au['por_usuario']) ?> usuario(s)</div></div>
                </div>
                <div class="ej-kpi-card azul">
                    <div class="ej-kpi-icono"><i class="fas fa-user-friends"></i></div>
                    <div class="ej-kpi-contenido"><div class="ej-kpi-lbl">Personas mostradas</div><div class="ej-kpi-val"><?= $num($au['personas']) ?></div><div class="ej-kpi-sub">suma de resultados (puede repetir personas)</div></div>
                </div>
                <div class="ej-kpi-card rojo <?= $au['denegadas'] + $au['errores'] ? 'alerta' : '' ?>">
                    <div class="ej-kpi-icono"><i class="fas fa-times-circle"></i></div>
                    <div class="ej-kpi-contenido"><div class="ej-kpi-lbl">Denegadas / con error</div><div class="ej-kpi-val"><?= $num($au['denegadas']) ?> / <?= $num($au['errores']) ?></div><div class="ej-kpi-sub">revisar si son muchas</div></div>
                </div>
                <div class="ej-kpi-card ambar <?= $au['fuera_horario'] ? 'alerta' : '' ?>">
                    <div class="ej-kpi-icono"><i class="fas fa-moon"></i></div>
                    <div class="ej-kpi-contenido"><div class="ej-kpi-lbl">Fuera de horario</div><div class="ej-kpi-val"><?= $num($au['fuera_horario']) ?></div><div class="ej-kpi-sub">entre las <?= Calculo::HORA_NOCHE_DESDE ?> y las 0<?= Calculo::HORA_NOCHE_HASTA ?> h</div></div>
                </div>
            </div>

            <?php if ($au['alertas']): ?>
            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-bell" style="color:var(--ej-alto)"></i> Alertas (<?= count($au['alertas']) ?>)</span></div>
                <div class="ej-tabla-wrap"><table class="ej-tabla tarjetas">
                    <thead><tr><th>Día</th><th>Usuario</th><th>Alerta</th><th class="num">Cantidad</th></tr></thead>
                    <tbody><?php foreach ($au['alertas'] as $al): ?><tr class="destacado">
                        <td class="ej-td-titulo" data-label="Día"><?= esc(date('d/m/Y', strtotime($al['dia']))) ?></td>
                        <td data-label="Usuario"><?= esc($al['usuario']) ?></td>
                        <td data-label="Alerta"><?= esc($al['texto']) ?></td>
                        <td data-label="Cantidad" class="num"><?= (int) $al['cantidad'] ?></td>
                    </tr><?php endforeach; ?></tbody>
                </table></div>
            </div>
            <?php endif; ?>

            <div class="ej-grid-2">
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-calendar-day"></i> Consultas por día</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas"><canvas id="chAudDia" aria-label="Consultas por día"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un día para ver solo las consultas de ese día.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Día</th><th class="num">Consultas</th></tr></thead>
                                <tbody><?php foreach ($au['por_dia'] as $dia => $n): ?><tr><td><?= esc(date('d/m/Y', strtotime($dia))) ?></td><td class="num"><?= $n ?></td></tr><?php endforeach; ?>
                                <?php if (!$au['por_dia']): ?><tr><td colspan="2" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-business-time"></i> Consultas por hora del día</span><span class="ej-chip neutro">hora de Jujuy</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas"><canvas id="chAudHora" aria-label="Consultas por hora del día"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-moon"></i> En rojo, las horas fuera de horario (<?= Calculo::HORA_NOCHE_DESDE ?> a 0<?= Calculo::HORA_NOCHE_HASTA ?> h).</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Hora</th><th class="num">Consultas</th></tr></thead>
                                <tbody><?php foreach ($au['por_hora'] as $h => $n): if (!$n) continue; ?><tr><td><?= sprintf('%02d:00 a %02d:59', $h, $h) ?></td><td class="num"><?= $n ?></td></tr><?php endforeach; ?>
                                <?php if (!$au['total']): ?><tr><td colspan="2" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-chart-pie"></i> Cómo consultó</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas bajo"><canvas id="chAudModalidad" aria-label="Consultas por modalidad"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en una modalidad para filtrar.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Modalidad</th><th class="num">Consultas</th><th class="num">%</th></tr></thead>
                                <tbody><?php foreach ($au['por_modalidad'] as $m => $n): ?><tr><td><?= esc($modalidades[$m] ?? $m) ?></td><td class="num"><?= $n ?></td><td class="num"><?= $num($n * 100 / max(1, $au['total']), 0) ?>%</td></tr><?php endforeach; ?>
                                <?php if (!$au['por_modalidad']): ?><tr><td colspan="3" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-clipboard-check"></i> Con qué resultado</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart ej-chart-canvas bajo"><canvas id="chAudResultado" aria-label="Consultas por resultado"></canvas></div>
                        <p class="ej-chart-ayuda"><i class="fas fa-mouse-pointer"></i> Clic en un resultado para filtrar. Denegadas y errores en rojo.</p>
                        <details class="ej-datos"><summary>Ver tabla de datos</summary>
                            <table class="ej-tabla">
                                <thead><tr><th>Resultado</th><th class="num">Consultas</th><th class="num">%</th></tr></thead>
                                <tbody><?php foreach ($au['por_resultado'] as $r => $n): ?><tr class="<?= in_array($r, ['DENEGADA', 'ERROR'], true) ? 'destacado' : '' ?>"><td><?= esc($resultados[$r] ?? $r) ?></td><td class="num"><?= $n ?></td><td class="num"><?= $num($n * 100 / max(1, $au['total']), 0) ?>%</td></tr><?php endforeach; ?>
                                <?php if (!$au['por_resultado']): ?><tr><td colspan="3" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                            </table>
                        </details>
                    </div>
                </div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-user"></i> Quién consultó</span></div>
                <div class="ej-tabla-wrap"><table class="ej-tabla tarjetas">
                    <thead><tr><th>Usuario</th><th>Perfil</th><th class="num">Total</th><th class="num">Por servicio</th><th class="num">Por corte</th><th class="num">Listado</th><th class="num">Sin result.</th><th class="num">Deneg./Error</th><th class="num">Personas</th><th>Última</th></tr></thead>
                    <tbody><?php foreach ($au['por_usuario'] as $u): ?><tr>
                        <td class="ej-td-titulo" data-label="Usuario"><strong><?= esc($u['usuario']) ?></strong></td>
                        <td data-label="Perfil"><?= esc($u['perfil'] ?? '—') ?></td>
                        <td data-label="Total" class="num"><strong><?= $u['total'] ?></strong></td>
                        <td data-label="Por servicio" class="num"><?= $u['SERVICIO_EXACTO'] ?></td>
                        <td data-label="Por corte" class="num"><?= $u['CORTE'] ?></td>
                        <td data-label="Listado" class="num"><?= $u['RIESGO_AMBITO'] ?></td>
                        <td data-label="Sin resultados" class="num"><?= $u['SIN_RESULTADOS'] ?></td>
                        <td data-label="Deneg./Error" class="num"><?= $u['DENEGADA'] ?> / <?= $u['ERROR'] ?></td>
                        <td data-label="Personas" class="num"><?= $num($u['personas']) ?></td>
                        <td data-label="Última"><?= esc(Calculo::fecha($u['ultima'])) ?></td>
                    </tr><?php endforeach; ?>
                    <?php if (!$au['por_usuario']): ?><tr><td colspan="10" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                </table></div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-list"></i> Detalle de consultas (más nuevas primero)</span><span class="ej-chip neutro">Nº de servicio enmascarado</span></div>
                <div class="ej-tabla-wrap"><table class="ej-tabla tarjetas">
                    <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Cómo</th><th>Resultado</th><th class="num">Personas</th><th>Servicio</th><th>Corte</th><th>IP</th></tr></thead>
                    <tbody><?php foreach ($consultas as $q): ?><tr class="<?= in_array($q['resultado'], ['DENEGADA', 'ERROR'], true) ? 'destacado' : '' ?>">
                        <td class="ej-td-titulo" data-label="Fecha"><?= esc(Calculo::fecha(Calculo::utcALocal($q['consultado_at']))) ?></td>
                        <td data-label="Usuario"><?= esc($q['usuario_nombre_snapshot'] ?: ('#' . $q['usuario_id'])) ?><?= $q['perfil_snapshot'] ? ' <small>(' . esc($q['perfil_snapshot']) . ')</small>' : '' ?></td>
                        <td data-label="Cómo"><?= esc($modalidades[$q['modalidad']] ?? $q['modalidad']) ?></td>
                        <td data-label="Resultado"><span class="ej-chip <?= $q['resultado'] === 'CON_RESULTADOS' ? 'ok' : (in_array($q['resultado'], ['DENEGADA', 'ERROR'], true) ? 'mal' : 'neutro') ?>"><?= esc($resultados[$q['resultado']] ?? $q['resultado']) ?></span></td>
                        <td data-label="Personas" class="num"><?= (int) $q['cantidad_personas'] ?></td>
                        <td data-label="Servicio"><?= esc(Calculo::servicio($q['nro_servicio_consultado'])) ?></td>
                        <td data-label="Corte"><?= $q['corte_id'] ? '<a href="' . $urlCorte($q['corte_id'], 'auditoria') . '">#' . (int) $q['corte_id'] . ($q['corte_referencia'] ? ' ' . esc($q['corte_referencia']) : '') . '</a>' : '—' ?></td>
                        <td data-label="IP"><?= esc($q['ip'] ?: '—') ?></td>
                    </tr><?php endforeach; ?>
                    <?php if (!$consultas): ?><tr><td colspan="8" class="ej-vacio">Sin consultas para estos filtros.</td></tr><?php endif; ?></tbody>
                </table></div>
                <div class="ej-pager" style="padding:0 12px 12px;">
                    <span><?= count($consultas) ?> consultas en esta página</span>
                    <span style="display:flex; gap:6px;">
                        <?php if ($f['cursor']): ?><a class="bl-btn ghost sm" href="<?= $url(['tab' => 'auditoria']) ?>"><i class="fas fa-angle-double-left"></i> Más nuevas</a><?php endif; ?>
                        <?php if ($siguiente): ?><a class="bl-btn navy sm" href="<?= $url(['tab' => 'auditoria', 'cursor' => $siguiente]) ?>">Más viejas <i class="fas fa-angle-right"></i></a><?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-exchange-alt"></i> Cambios de cortes (<?= count($cambios) ?>)</span><span class="ej-chip neutro">publicaciones, reprogramaciones, cancelaciones, finalizaciones, protocolo</span></div>
                <div class="ej-tabla-wrap"><table class="ej-tabla tarjetas">
                    <thead><tr><th>Fecha y hora</th><th>Corte</th><th>Acción</th><th>Qué cambió</th><th>Quién</th></tr></thead>
                    <tbody><?php foreach ($cambios as $h): ?><tr class="<?= in_array($h['accion'], ['REPROGRAMAR', 'CANCELAR'], true) ? 'destacado' : '' ?>">
                        <td class="ej-td-titulo" data-label="Fecha"><?= esc(Calculo::fecha(Calculo::utcALocal($h['registrado_at']))) ?></td>
                        <td data-label="Corte"><a href="<?= $urlCorte($h['corte_id'], 'auditoria') ?>">#<?= (int) $h['corte_id'] ?></a> <small><?= esc(Calculo::fecha($h['inicio_at'])) ?> · <?= esc($h['localidad'] ?: 's/localidad') ?></small></td>
                        <td data-label="Acción"><span class="ej-chip neutro"><?= esc($acciones[$h['accion']] ?? $h['accion']) ?></span></td>
                        <td data-label="Qué cambió" style="text-align:left;"><?= esc(Calculo::describirCambio($h['accion'], $h['cambios'])) ?></td>
                        <td data-label="Quién"><?= esc($h['username'] ?: '—') ?></td>
                    </tr><?php endforeach; ?>
                    <?php if (!$cambios): ?><tr><td colspan="5" class="ej-vacio">Sin cambios de cortes en el período.</td></tr><?php endif; ?></tbody>
                </table></div>
            </div>
            <p class="ej-leyenda"><i class="fas fa-lock"></i> El detalle de qué personas vio EJESA en cada consulta queda solo en sistemabasec (no se muestra acá). Los números de servicio se muestran enmascarados.</p>
        </div>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?php if (!isset($error)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
(function () {
    'use strict';

    // ── Datos de los gráficos: SOLO cantidades (sin nombres ni datos personales) ──
    var D = {
        serie:      <?= json_encode($serie ?: new stdClass(), JSON_UNESCAPED_UNICODE) ?>,
        locs:       <?= json_encode(array_values($localidadesTop), JSON_UNESCAPED_UNICODE) ?>,
        locIds:     <?= json_encode(array_column($localidades, 'localidad_id', 'nombre') ?: new stdClass(), JSON_UNESCAPED_UNICODE) ?>,
        estados:    <?= json_encode($porEstado) ?>,
        estadosTxt: <?= json_encode(Calculo::ESTADOS_VISIBLES, JSON_UNESCAPED_UNICODE) ?>,
        riesgo:     <?= json_encode($k['afectados_por_riesgo']) ?>,
        equipos:    <?= json_encode(array_map(static fn ($e) => ['tipo' => $e['tipo'], 'clave' => $e['clave'], 'personas' => $e['personas']], $equipos), JSON_UNESCAPED_UNICODE) ?>,
        cortes:     <?= json_encode(array_map(static fn ($c) => [
            'etiqueta' => date('d/m H:i', strtotime($c['inicio_at'])) . ' · ' . ($c['localidad'] ?: 's/localidad'),
            'prog'     => round($c['horas']['programadas'], 2),
            'luz'      => $c['horas']['cuenta'] ? round($c['horas']['sin_luz'], 2) : 0,
            'estado'   => $c['estado_visible'],
            'afect'    => $c['afectados']['total'],
            'url'      => $urlCorte($c['corte_id'], 'historial'),
        ], $cortesGraf), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        audDia:     <?= json_encode($auditoria['por_dia'] ?: new stdClass()) ?>,
        audHora:    <?= json_encode(array_values($auditoria['por_hora'])) ?>,
        audMod:     <?= json_encode($auditoria['por_modalidad'] ?: new stdClass()) ?>,
        audRes:     <?= json_encode($auditoria['por_resultado'] ?: new stdClass()) ?>,
        modTxt:     <?= json_encode($modalidades, JSON_UNESCAPED_UNICODE) ?>,
        resTxt:     <?= json_encode($resultados, JSON_UNESCAPED_UNICODE) ?>,
        nocheDesde: <?= Calculo::HORA_NOCHE_DESDE ?>,
        nocheHasta: <?= Calculo::HORA_NOCHE_HASTA ?>
    };

    var COL = window.dashboardColors || { primary: '#39A9C6', secondary: '#243B63', danger: '#D96C6C', warning: '#F2A272', success: '#5BB8A6', slate: '#708090', tooltipDark: 'rgba(11,19,34,.96)' };
    var alfa = window.colorAlfa || function (h) { return h; };
    var colR = <?= json_encode($coloresRiesgo) ?>;
    var colEstado = { PROGRAMADO: COL.primary, EN_CURSO: COL.danger, VENCIDO: COL.warning, FINALIZADO: COL.success, CANCELADO: '#9AA5B5' };
    var colResultado = { CON_RESULTADOS: COL.success, SIN_RESULTADOS: '#9AA5B5', DENEGADA: COL.danger, ERROR: '#a33a3a' };

    var form = document.getElementById('ejFiltros');
    var nf1 = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 1 });
    var nf0 = new Intl.NumberFormat('es-AR', { maximumFractionDigits: 0 });
    var horasTxt = function (h) {
        var min = Math.round(Math.max(0, h) * 60);
        return nf1.format(h) + ' h (' + String(Math.floor(min / 60)).padStart(2, '0') + ':' + String(min % 60).padStart(2, '0') + ')';
    };
    var mesCorto = function (s) { return s.mes.charAt(0) + s.mes.substring(1, 3).toLowerCase() + ' ' + s.ejercicio; };
    var serieClaves = Object.keys(D.serie);
    var serie = serieClaves.map(function (k) { return D.serie[k]; });
    var meses = serie.map(mesCorto);

    // ── Filtros ──
    function enviar(cambios, tab) {
        Object.keys(cambios).forEach(function (n) {
            var el = form.elements[n];
            if (el) el.value = cambios[n];
        });
        if (tab) document.getElementById('inputTab').value = tab;
        form.submit();
    }
    function filtrarMes(i, tab) {
        var clave = serieClaves[i];
        if (!clave) return;
        var p = clave.split('-'), ultimo = new Date(+p[0], +p[1], 0).getDate();
        enviar({ desde: clave + '-01', hasta: clave + '-' + String(ultimo).padStart(2, '0') }, tab);
    }

    document.querySelectorAll('.ej-periodo').forEach(function (b) {
        b.addEventListener('click', function () { enviar({ desde: b.dataset.desde, hasta: b.dataset.hasta }); });
    });
    document.querySelectorAll('[data-periodo]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var b = Array.prototype.find.call(document.querySelectorAll('.ej-periodo'), function (x) { return x.textContent.trim() === a.dataset.periodo; });
            if (b) b.click();
        });
    });
    // Los desplegables se aplican apenas se cambian
    ['f-loc', 'f-estado', 'a-usuario', 'a-mod', 'a-res', 'a-acc'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', function () { form.submit(); });
    });
    // Desde > hasta: se acomodan antes de enviar
    form.addEventListener('submit', function () {
        var d = form.elements.desde, h = form.elements.hasta;
        if (d.value && h.value && d.value > h.value) { var t = d.value; d.value = h.value; h.value = t; }
    });

    // ── Pestañas: ?tab= se conserva al filtrar; los gráficos se dibujan al abrir la pestaña ──
    var hechos = {};
    var graficos = null;

    function abrirTab(tab) {
        document.querySelectorAll('.ml-tab').forEach(function (x) {
            var activa = x.dataset.tab === tab;
            x.classList.toggle('is-active', activa);
            x.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
        document.querySelectorAll('.ml-tab-pane').forEach(function (p) { p.classList.toggle('is-active', p.dataset.pane === tab); });
        document.querySelectorAll('[data-solo-tab]').forEach(function (el) { el.hidden = el.dataset.soloTab !== tab; });
        document.getElementById('inputTab').value = tab;

        // La dirección guarda la pestaña (si el navegador no deja cambiarla, el panel sigue igual)
        try {
            var params = new URLSearchParams(window.location.search);
            params.set('tab', tab);
            params.delete('cursor');
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());
        } catch (e) { /* sin historial: no afecta al panel */ }

        // "Limpiar" y "quitar filtro" vuelven a la pestaña abierta
        document.querySelectorAll('#btnLimpiar, [data-quitar]').forEach(function (a) { a.href = a.href.replace(/tab=[^&]*/, 'tab=' + tab); });

        dibujar(tab);
    }
    function dibujar(tab) {
        if (hechos[tab] || !graficos || !graficos[tab]) return;
        hechos[tab] = true;
        graficos[tab]();
    }
    document.querySelectorAll('.ml-tab').forEach(function (b) {
        b.addEventListener('click', function () { abrirTab(b.dataset.tab); });
    });
    document.querySelectorAll('[data-ir-tab]').forEach(function (b) {
        b.addEventListener('click', function () {
            abrirTab(b.dataset.irTab);
            if (b.dataset.irRiesgo) verQuienes({ riesgo: b.dataset.irRiesgo });
            else if (b.dataset.irTabla) verQuienes({});
            else window.scrollTo({ top: document.querySelector('.ml-tabs').getBoundingClientRect().top + window.scrollY - 70, behavior: 'smooth' });
        });
    });

    // ── Tablas: búsqueda y filtro por riesgo (lee el texto ya visible en la tabla) ──
    // Marcas diacríticas U+0300 a U+036F (se arma con fromCharCode: la salida de la vista convierte los escapes \u)
    var diacriticos = new RegExp('[' + String.fromCharCode(0x300) + '-' + String.fromCharCode(0x36f) + ']', 'g');
    var sinTildes = function (t) { return (t || '').normalize('NFD').replace(diacriticos, '').toLowerCase(); };
    var filtroTabla = { tablaQuienes: { riesgo: null, texto: '', equipo: null }, tablaHistorial: { texto: '' } };

    function aplicarFiltroTabla(id) {
        var tabla = document.getElementById(id);
        if (!tabla) return;
        var f = filtroTabla[id], total = 0, visibles = 0;
        Array.prototype.forEach.call(tabla.tBodies[0].rows, function (tr) {
            if (tr.querySelector('.ej-vacio')) return;
            total++;
            var ok = true;
            if (f.texto) ok = sinTildes(tr.textContent).indexOf(sinTildes(f.texto)) !== -1;
            if (ok && f.riesgo) ok = !!tr.querySelector('td[data-label="Riesgo"] .ej-chip.' + f.riesgo);
            if (ok && f.equipo !== null && f.equipo !== undefined) {
                var eq = tr.querySelector('.ej-td-equipos');
                var txt = sinTildes(eq ? eq.textContent : '').trim();
                ok = f.equipo === '' ? (txt === '—' || txt === '') : txt.indexOf(f.equipo) !== -1;
            }
            tr.hidden = !ok;
            if (ok) visibles++;
        });
        var cont = document.querySelector('[data-contador="' + id + '"]');
        if (!cont) return;
        cont.innerHTML = visibles === total ? '<strong>' + total + '</strong> en total'
            : 'Mostrando <strong>' + visibles + '</strong> de ' + total + ' · <a href="#" data-limpiar-tabla>ver todos</a>';
        var limpiar = cont.querySelector('[data-limpiar-tabla]');
        if (limpiar) limpiar.addEventListener('click', function (e) { e.preventDefault(); limpiarTabla(id); });
    }
    function limpiarTabla(id) {
        var f = filtroTabla[id];
        Object.keys(f).forEach(function (k) { f[k] = k === 'texto' ? '' : null; });
        var input = document.querySelector('[data-buscar="' + id + '"]');
        if (input) input.value = '';
        document.querySelectorAll('.ej-filtro-riesgo').forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
        aplicarFiltroTabla(id);
    }
    document.querySelectorAll('[data-buscar]').forEach(function (input) {
        input.addEventListener('input', function () {
            filtroTabla[input.dataset.buscar].texto = input.value;
            aplicarFiltroTabla(input.dataset.buscar);
        });
        aplicarFiltroTabla(input.dataset.buscar);
    });
    document.querySelectorAll('.ej-filtro-riesgo').forEach(function (b) {
        b.addEventListener('click', function () {
            var f = filtroTabla.tablaQuienes, activo = f.riesgo === b.dataset.riesgo;
            f.riesgo = activo ? null : b.dataset.riesgo;
            document.querySelectorAll('.ej-filtro-riesgo').forEach(function (x) { x.setAttribute('aria-pressed', !activo && x === b ? 'true' : 'false'); });
            aplicarFiltroTabla('tablaQuienes');
        });
    });

    // Abre Impacto en la tabla de personas, con el filtro pedido
    function verQuienes(opc) {
        var f = filtroTabla.tablaQuienes;
        if ('riesgo' in opc) {
            f.riesgo = opc.riesgo;
            document.querySelectorAll('.ej-filtro-riesgo').forEach(function (x) { x.setAttribute('aria-pressed', x.dataset.riesgo === opc.riesgo ? 'true' : 'false'); });
        }
        if ('equipo' in opc) f.equipo = opc.equipo;
        aplicarFiltroTabla('tablaQuienes');
        var bloque = document.getElementById('bloqueQuienes');
        if (bloque) setTimeout(function () { window.scrollTo({ top: bloque.getBoundingClientRect().top + window.scrollY - 70, behavior: 'smooth' }); }, 60);
    }

    // ── Gráficos ──
    if (typeof Chart === 'undefined') return;
    if (window.ChartDataLabels) Chart.register(ChartDataLabels);
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#5a6a7e';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.tooltip.backgroundColor = COL.tooltipDark;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    if (Chart.defaults.plugins.datalabels) Chart.defaults.plugins.datalabels.display = false;

    var esMovil = function () { return window.matchMedia('(max-width: 767px)').matches; };
    var grilla = { color: '#eef0f3' };
    var cursorMano = function (e, el) { e.native.target.style.cursor = el.length ? 'pointer' : 'default'; };
    // El contexto de chartjs-plugin-datalabels no trae "raw": el valor se lee del dataset
    var valor = function (c) { return +c.dataset.data[c.dataIndex] || 0; };
    var etiquetasDentro = { color: window.colorEtiqueta || '#1d2b3c', font: { weight: 'bold', size: 11 } };
    var etiquetaArriba = function (formato) {
        return { display: function (c) { return valor(c) > 0; }, anchor: 'end', align: 'end', offset: 2, color: '#2d3748', font: { weight: 'bold', size: 11 }, formatter: formato || function (v) { return nf0.format(v); } };
    };

    function vacio(id, texto) {
        var canvas = document.getElementById(id);
        if (!canvas) return;
        canvas.style.display = 'none';
        var div = document.createElement('div');
        div.className = 'ej-chart-vacio';
        div.innerHTML = '<i class="fas fa-chart-bar"></i>' + texto;
        canvas.parentNode.appendChild(div);
        var ayuda = canvas.parentNode.parentNode.querySelector('.ej-chart-ayuda');
        if (ayuda && ayuda.querySelector('.fa-mouse-pointer')) ayuda.style.display = 'none';
    }
    function suma(o) { return Object.keys(o).reduce(function (a, k) { return a + (+o[k] || 0); }, 0); }
    var sinDatoAfectados = 'Sin afectados registrados.<br><small>Solo hay dato en cortes con protocolo activado.</small>';

    function dona(id, etiquetas, datos, colores, alClic, textoTotal) {
        var total = datos.reduce(function (a, b) { return a + b; }, 0);
        return new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels: etiquetas, datasets: [{ data: datos, backgroundColor: colores, borderWidth: 2, borderColor: '#fff', hoverOffset: 8 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '58%',
                onHover: cursorMano,
                onClick: function (e, el) { if (el.length) alClic(el[0].index); },
                plugins: {
                    legend: {
                        position: esMovil() ? 'bottom' : 'right',
                        labels: {
                            padding: 10, font: { size: 12 },
                            // Leyenda con cantidad y porcentaje: "Finalizado · 3 (60%)"
                            generateLabels: function (chart) {
                                return Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart).map(function (l) {
                                    var v = datos[l.index] || 0;
                                    l.text = l.text + ' · ' + nf0.format(v) + ' (' + nf0.format(v * 100 / (total || 1)) + '%)';
                                    return l;
                                });
                            }
                        }
                    },
                    tooltip: { callbacks: {
                        label: function (c) { return ' ' + c.label + ': ' + nf0.format(c.raw) + ' (' + nf0.format(c.raw * 100 / (total || 1)) + '%)'; },
                        footer: function () { return 'Clic para filtrar'; }
                    } },
                    // Porcentaje dentro de cada porción (las muy chicas no llevan etiqueta)
                    datalabels: Object.assign({}, etiquetasDentro, {
                        display: function (c) { return valor(c) / (total || 1) >= 0.05; },
                        font: { weight: 'bold', size: 12 },
                        formatter: function (v) { return nf0.format(v * 100 / (total || 1)) + '%'; }
                    })
                }
            },
            plugins: [{
                id: 'totalCentro',
                afterDraw: function (chart) {
                    var a = chart.chartArea, ctx = chart.ctx;
                    var x = (a.left + a.right) / 2, y = (a.top + a.bottom) / 2;
                    // El texto crece con el hueco de la dona (grande en pantallas anchas, sin salirse en celular)
                    var arco = chart.getDatasetMeta(0).data[0];
                    if (arco) { x = arco.x; y = arco.y; }
                    var hueco = arco && arco.innerRadius ? arco.innerRadius : 50;
                    // Etiqueta arriba (mayúsculas, gris) y número abajo (negrita), como en los demás paneles
                    var tNum = Math.max(18, Math.min(26, Math.round(hueco * 0.3)));
                    var tTxt = Math.max(11, Math.min(14, Math.round(hueco * 0.15)));
                    ctx.save();
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#4a5568'; ctx.font = '700 ' + tTxt + 'px ' + Chart.defaults.font.family;
                    ctx.fillText(String(textoTotal || 'total').toUpperCase(), x, y - tNum * 0.62);
                    ctx.fillStyle = '#1a202c'; ctx.font = '800 ' + tNum + 'px ' + Chart.defaults.font.family;
                    ctx.fillText(nf0.format(total), x, y + tTxt * 1.0);
                    ctx.restore();
                }
            }]
        });
    }

    graficos = {
        resumen: function () {
            var est = Object.keys(D.estados);
            if (!suma(D.estados)) vacio('chEstados', 'Sin cortes publicados en el período.');
            else dona('chEstados', est.map(function (e) { return D.estadosTxt[e]; }), est.map(function (e) { return D.estados[e]; }),
                est.map(function (e) { return colEstado[e]; }), function (i) { enviar({ estado: est[i] }, 'historial'); }, 'cortes');

            var rs = Object.keys(D.riesgo);
            if (!suma(D.riesgo)) vacio('chRiesgoResumen', sinDatoAfectados);
            else dona('chRiesgoResumen', rs, rs.map(function (r) { return D.riesgo[r]; }), rs.map(function (r) { return colR[r]; }),
                function (i) { abrirTab('impacto'); verQuienes({ riesgo: rs[i] }); }, 'afectados');

            if (!serie.some(function (s) { return s.cortes > 0; })) { vacio('chHorasResumen', 'Sin cortes ejecutados en el período.'); return; }
            new Chart(document.getElementById('chHorasResumen'), {
                type: 'bar',
                data: { labels: meses, datasets: [{ label: 'Horas sin luz', data: serie.map(function (s) { return Math.round(s.horas * 10) / 10; }),
                    backgroundColor: serie.map(function (s, i) { return i === serie.length - 1 ? COL.primary : COL.secondary; }), borderRadius: 6, maxBarThickness: 40,
                    datalabels: etiquetaArriba(function (v) { return nf1.format(v) + ' h'; }) }] },
                options: {
                    responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 } },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) filtrarMes(el[0].index, 'resumen'); },
                    plugins: { legend: { display: false },
                        tooltip: { callbacks: { label: function (c) { return ' ' + horasTxt(c.raw) + ' · ' + serie[c.dataIndex].cortes + ' corte(s)'; }, footer: function () { return 'Clic para filtrar el mes'; } } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: grilla } }
                }
            });
        },

        historial: function () {
            if (!D.cortes.length) { vacio('chCortes', 'Sin cortes en el período.'); return; }
            new Chart(document.getElementById('chCortes'), {
                type: 'bar',
                data: {
                    labels: D.cortes.map(function (c) { return c.etiqueta; }),
                    datasets: [
                        { label: 'Horas programadas', data: D.cortes.map(function (c) { return c.prog; }), backgroundColor: alfa(COL.primary, .3), borderColor: COL.primary, borderWidth: 1, borderRadius: 5, barPercentage: .9, categoryPercentage: .8 },
                        { label: 'Horas sin luz', data: D.cortes.map(function (c) { return c.luz; }),
                          backgroundColor: D.cortes.map(function (c) { return c.estado === 'VENCIDO' ? COL.warning : (c.estado === 'CANCELADO' ? '#9AA5B5' : COL.secondary); }),
                          borderRadius: 5, barPercentage: .9, categoryPercentage: .8, datalabels: etiquetaArriba(function (v) { return nf1.format(v) + ' h'; }) }
                    ]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, layout: { padding: { right: 44 } },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) window.location = D.cortes[el[0].index].url; },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: {
                            label: function (c) { return ' ' + c.dataset.label + ': ' + horasTxt(c.raw); },
                            footer: function (items) {
                                var c = D.cortes[items[0].dataIndex];
                                return D.estadosTxt[c.estado] + ' · ' + (c.afect === null ? 'afectados: sin dato' : c.afect + ' afectado(s)') + '\nClic para ver el detalle';
                            }
                        } }
                    },
                    scales: { x: { beginAtZero: true, grid: grilla, title: { display: true, text: 'horas' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } }
                }
            });
        },

        impacto: function () {
            if (!serie.some(function (s) { return s.cortes > 0; })) vacio('chHoras', 'Sin cortes ejecutados en el período.');
            else new Chart(document.getElementById('chHoras'), {
                data: {
                    labels: meses,
                    datasets: [
                        { type: 'bar', label: 'Horas sin luz', data: serie.map(function (s) { return Math.round(s.horas * 10) / 10; }),
                          backgroundColor: COL.secondary, borderRadius: 6, maxBarThickness: 40, barPercentage: .8, categoryPercentage: .7, yAxisID: 'y',
                          datalabels: etiquetaArriba(function (v) { return nf1.format(v) + ' h'; }) },
                        { type: 'bar', label: 'Cortes', data: serie.map(function (s) { return s.cortes; }),
                          backgroundColor: COL.primary, borderRadius: 6, maxBarThickness: 40, barPercentage: .8, categoryPercentage: .7, yAxisID: 'y2',
                          datalabels: { display: function (c) { return valor(c) > 0; }, anchor: 'end', align: 'start', offset: 4, color: '#fff', font: { weight: 'bold', size: 11 }, formatter: function (v) { return nf0.format(v); } } }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 } },
                    interaction: { mode: 'index', intersect: false },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) filtrarMes(el[0].index, 'impacto'); },
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: {
                            label: function (c) { return c.dataset.yAxisID === 'y' ? ' Horas sin luz: ' + horasTxt(c.raw) : ' Cortes: ' + c.raw; },
                            footer: function (items) { var s = serie[items[0].dataIndex]; return s.cortes ? 'Promedio: ' + horasTxt(s.horas / s.cortes) + ' por corte' : ''; }
                        } }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: grilla, grace: '15%', title: { display: !esMovil(), text: 'horas' } },
                        y2: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 }, grace: '15%', title: { display: !esMovil(), text: 'cortes' } }
                    }
                }
            });

            var rs = ['ALTO', 'MEDIANO', 'BAJO', 'PENDIENTE'];
            if (!serie.some(function (s) { return rs.some(function (r) { return s[r] > 0; }); })) vacio('chRiesgo', sinDatoAfectados);
            else new Chart(document.getElementById('chRiesgo'), {
                type: 'bar',
                data: { labels: meses, datasets: rs.map(function (r) {
                    return { label: r, data: serie.map(function (s) { return s[r]; }), backgroundColor: colR[r], borderRadius: 4, maxBarThickness: 36,
                             barPercentage: .9, categoryPercentage: .7,
                             datalabels: Object.assign({}, etiquetaArriba(), { display: function (c) { return valor(c) > 0; } }) };
                }) },
                options: {
                    responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 } },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) verQuienes({ riesgo: rs[el[0].datasetIndex] }); },
                    plugins: { legend: { position: 'bottom' },
                        tooltip: { mode: 'index', callbacks: { footer: function (items) { var s = serie[items[0].dataIndex]; return 'Total: ' + rs.reduce(function (a, r) { return a + s[r]; }, 0) + (s.sin_dato ? '\n' + s.sin_dato + ' corte(s) sin dato' : ''); } } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: grilla, grace: '10%', ticks: { precision: 0 } } }
                }
            });

            if (!D.locs.length) vacio('chLocalidad', 'Sin cortes ejecutados en el período.');
            else new Chart(document.getElementById('chLocalidad'), {
                type: 'bar',
                data: { labels: D.locs.map(function (l) { return l.localidad; }), datasets: [{ label: 'Persona-horas', data: D.locs.map(function (l) { return Math.round(l.persona_horas * 10) / 10; }),
                    backgroundColor: D.locs.map(function (l, i) { return i === 0 ? COL.danger : alfa(COL.danger, .6); }), borderRadius: 6, maxBarThickness: 26,
                    datalabels: etiquetaArriba(function (v) { return nf1.format(v); }) }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, layout: { padding: { right: 44 } },
                    onHover: cursorMano,
                    onClick: function (e, el) { if (!el.length) return; var id = D.locIds[D.locs[el[0].index].localidad]; if (id) enviar({ localidad: id }, 'impacto'); },
                    plugins: { legend: { display: false },
                        tooltip: { callbacks: { label: function (c) { return ' Persona-horas: ' + nf1.format(c.raw); },
                            footer: function (items) { var l = D.locs[items[0].dataIndex]; return l.cortes + ' corte(s) · ' + horasTxt(l.horas) + '\n' + l.afectados + ' afectados (' + l.alto + ' ALTO)' + (l.sin_dato ? ' · ' + l.sin_dato + ' s/d' : '') + '\nClic para filtrar la localidad'; } } } },
                    scales: { x: { beginAtZero: true, grid: grilla }, y: { grid: { display: false } } }
                }
            });

            if (!serie.some(function (s) { return s.persona_horas > 0; })) vacio('chPersonaHoras', 'Sin persona-horas en el período.<br><small>Solo hay dato en cortes con protocolo activado.</small>');
            else new Chart(document.getElementById('chPersonaHoras'), {
                type: 'line',
                data: { labels: meses, datasets: [
                    { label: 'Persona-horas', data: serie.map(function (s) { return Math.round(s.persona_horas * 10) / 10; }), borderColor: COL.secondary, backgroundColor: alfa(COL.secondary, .12), fill: true, tension: .3, pointRadius: 5, pointHoverRadius: 7,
                      datalabels: { display: function (c) { return valor(c) > 0; }, align: 'top', offset: 4, color: COL.secondary, font: { weight: 'bold', size: 11 }, formatter: function (v) { return nf1.format(v); } } },
                    { label: 'De riesgo ALTO', data: serie.map(function (s) { return Math.round(s.persona_horas_alto * 10) / 10; }), borderColor: COL.danger, backgroundColor: COL.danger, borderDash: [5, 4], tension: .3, pointRadius: 3 }
                ] },
                options: {
                    responsive: true, maintainAspectRatio: false, layout: { padding: { top: 24, left: 6, right: 14 } },
                    interaction: { mode: 'index', intersect: false },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) filtrarMes(el[0].index, 'impacto'); },
                    plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + nf1.format(c.raw); } } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: grilla } }
                }
            });

            if (!D.equipos.length) vacio('chEquipos', 'Sin personas registradas en el período.');
            else new Chart(document.getElementById('chEquipos'), {
                type: 'bar',
                data: { labels: D.equipos.map(function (e) { return e.tipo; }), datasets: [{ label: 'Personas', data: D.equipos.map(function (e) { return e.personas; }),
                    backgroundColor: D.equipos.map(function (e, i) { return e.clave === '' ? '#cbd5e0' : (window.colorPaleta ? colorPaleta(i) : COL.secondary); }), borderRadius: 6, maxBarThickness: 24,
                    datalabels: etiquetaArriba() }] },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false, layout: { padding: { right: 30 } },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) verQuienes({ equipo: D.equipos[el[0].index].clave }); },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return ' ' + c.raw + ' persona(s)'; }, footer: function () { return 'Clic para ver quiénes'; } } } },
                    scales: { x: { beginAtZero: true, grid: grilla, ticks: { precision: 0 } }, y: { grid: { display: false } } }
                }
            });
        },

        auditoria: function () {
            var dias = Object.keys(D.audDia);
            if (!dias.length) vacio('chAudDia', 'Sin consultas en el período.');
            else new Chart(document.getElementById('chAudDia'), {
                type: 'bar',
                data: { labels: dias.map(function (d) { return d.substring(8, 10) + '/' + d.substring(5, 7); }), datasets: [{ label: 'Consultas', data: dias.map(function (d) { return D.audDia[d]; }),
                    backgroundColor: COL.secondary, borderRadius: 5, maxBarThickness: 34, datalabels: etiquetaArriba() }] },
                options: {
                    responsive: true, maintainAspectRatio: false, layout: { padding: { top: 20 } },
                    onHover: cursorMano, onClick: function (e, el) { if (el.length) enviar({ desde: dias[el[0].index], hasta: dias[el[0].index] }, 'auditoria'); },
                    plugins: { legend: { display: false }, tooltip: { callbacks: {
                        title: function (i) { var d = dias[i[0].dataIndex]; return d.substring(8, 10) + '/' + d.substring(5, 7) + '/' + d.substring(0, 4); },
                        label: function (c) { return ' ' + c.raw + ' consulta(s)'; }, footer: function () { return 'Clic para ver ese día'; } } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: grilla, ticks: { precision: 0 } } }
                }
            });

            var noche = function (h) { return h >= D.nocheDesde || h < D.nocheHasta; };
            if (!D.audHora.some(function (n) { return n > 0; })) vacio('chAudHora', 'Sin consultas en el período.');
            else new Chart(document.getElementById('chAudHora'), {
                type: 'bar',
                data: { labels: D.audHora.map(function (n, h) { return String(h).padStart(2, '0'); }), datasets: [{ label: 'Consultas', data: D.audHora,
                    backgroundColor: D.audHora.map(function (n, h) { return noche(h) ? COL.danger : COL.primary; }), borderRadius: 4 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: {
                        title: function (i) { var h = String(i[0].dataIndex).padStart(2, '0'); return h + ':00 a ' + h + ':59'; },
                        label: function (c) { return ' ' + c.raw + ' consulta(s)' + (noche(c.dataIndex) ? ' · fuera de horario' : ''); } } } },
                    scales: { x: { grid: { display: false }, ticks: { autoSkip: true, maxRotation: 0 } }, y: { beginAtZero: true, grid: grilla, ticks: { precision: 0 } } }
                }
            });

            var mods = Object.keys(D.audMod);
            if (!mods.length) vacio('chAudModalidad', 'Sin consultas en el período.');
            else dona('chAudModalidad', mods.map(function (m) { return D.modTxt[m] || m; }), mods.map(function (m) { return D.audMod[m]; }),
                mods.map(function (m, i) { return window.colorPaleta ? colorPaleta(i) : COL.secondary; }), function (i) { enviar({ modalidad: mods[i] }, 'auditoria'); }, 'consultas');

            var res = Object.keys(D.audRes);
            if (!res.length) vacio('chAudResultado', 'Sin consultas en el período.');
            else dona('chAudResultado', res.map(function (r) { return D.resTxt[r] || r; }), res.map(function (r) { return D.audRes[r]; }),
                res.map(function (r) { return colResultado[r] || COL.slate; }), function (i) { enviar({ resultado: res[i] }, 'auditoria'); }, 'consultas');
        }
    };

    dibujar(<?= json_encode($tab) ?>);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>