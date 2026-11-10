<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> EJESA · Electrodependientes <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="/css/panel-pestanas.css">
<link rel="stylesheet" href="/css/ejesa.css">

<?php
use App\Libraries\Ejesa\Calculo;

$num  = static fn ($n, int $dec = 0) => number_format((float) $n, $dec, ',', '.');
$base = [
    'desde' => $f['desde'], 'hasta' => $f['hasta'],
    'localidad' => $f['localidad'] ?: null, 'estado' => $f['estado'] ?: null,
];
$url = static function (array $extra = []) use ($base) {
    return base_url(route_to('ejesa_views')) . '?' . http_build_query(array_filter($extra + $base, static fn ($v) => $v !== null && $v !== ''));
};
$urlCorte = static fn ($id, $volver) => base_url(route_to('ejesa_corte', $id)) . '?volver=' . $volver;

$chipEstado = static fn (string $e) => '<span class="ej-chip ' . $e . '">' . esc(Calculo::ESTADOS_VISIBLES[$e] ?? $e) . '</span>';

// Afectados: total + desglose por riesgo; "a revisar" aparte. Sin protocolo no hay dato (no se muestra 0).
$afectados = static function (array $a, bool $corto = false) use ($num) {
    if ($a['total'] === null) {
        return '<span class="ej-sin-dato">Sin dato: activar protocolo en sistemabasec</span>';
    }
    $h = '<div class="ej-riesgos"><strong style="margin-right:4px;">' . $num($a['total']) . '</strong>';
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

$datosAl = 'Datos al ' . Calculo::fecha($ahora);
$coloresRiesgo = ['ALTO' => '#D96C6C', 'MEDIANO' => '#E9B949', 'BAJO' => '#5BB8A6', 'PENDIENTE' => '#9AA5B5'];
?>

<div class="ej-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Salud mental — Electrodependientes</a> ›
    <strong>EJESA</strong>
</div>

<div class="ej-panel">
    <div class="ej-panel-header">
        <span class="ej-panel-title"><i class="fas fa-bolt"></i> ELECTRODEPENDIENTES · EJESA</span>
        <span class="ej-datos-al"><i class="far fa-clock"></i> <?= esc($datosAl) ?></span>
    </div>

    <div class="ej-panel-body">

        <?= view('_messages') ?>

        <div class="ej-toolbar">
            <a href="<?= base_url(route_to('saludmental_views')); ?>" class="bl-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
            <a href="<?= base_url(route_to('electrodependiente_list')); ?>" class="bl-btn ghost"><i class="fas fa-users"></i> Pacientes</a>
            <?php if (!isset($error)): ?>
            <a href="<?= base_url(route_to('ejesa_export')) . '?' . http_build_query(array_filter($base)) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Excel del historial
            </a>
            <?php endif; ?>
        </div>

        <?php if (isset($error)): ?>
            <div class="ej-banda rojo"><i class="fas fa-exclamation-triangle"></i> <span><?= esc($error) ?></span></div>
        <?php else: ?>

        <!-- ── Banda: en curso / vencidos sin finalizar (texto, no solo color) ── -->
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

        <!-- ── Filtros del período ── -->
        <form method="GET" action="<?= base_url(route_to('ejesa_views')); ?>" class="ej-filtros" id="ejFiltros">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ej-filtro">
                <label for="f-desde">Desde</label>
                <input type="date" id="f-desde" name="desde" value="<?= esc($f['desde']) ?>">
            </div>
            <div class="ej-filtro">
                <label for="f-hasta">Hasta</label>
                <input type="date" id="f-hasta" name="hasta" value="<?= esc($f['hasta']) ?>">
            </div>
            <div class="ej-filtro">
                <label for="f-loc">Localidad</label>
                <select id="f-loc" name="localidad">
                    <option value="">Todas</option>
                    <?php foreach ($localidades as $l): ?>
                        <option value="<?= (int) $l['localidad_id'] ?>" <?= (int) $f['localidad'] === (int) $l['localidad_id'] ? 'selected' : '' ?>><?= esc($l['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ej-filtro">
                <label for="f-estado">Estado</label>
                <select id="f-estado" name="estado">
                    <option value="">Todos</option>
                    <?php foreach (Calculo::ESTADOS_VISIBLES as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $f['estado'] === $k ? 'selected' : '' ?>><?= esc($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ej-filtro">
                <label>Período</label>
                <select onchange="ejRango(this.value)" aria-label="Período rápido">
                    <option value="">Elegir…</option>
                    <option value="mes">Este mes</option>
                    <option value="mes_ant">Mes anterior</option>
                    <option value="90">Últimos 90 días</option>
                    <option value="anio">Este año</option>
                </select>
            </div>
            <div class="ej-filtro-acciones">
                <button type="submit" class="bl-btn teal" title="Filtrar"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="<?= base_url(route_to('ejesa_views')) . '?tab=' . esc($tab, 'url') ?>" class="bl-btn ghost" title="Limpiar"><i class="fas fa-times"></i></a>
            </div>
        </form>

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

        <?php $k = $kpis; $periodoTxt = date('d/m/Y', strtotime($f['desde'])) . ' al ' . date('d/m/Y', strtotime($f['hasta'])); ?>

        <!-- ══════════════ 1. RESUMEN ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'resumen' ? 'is-active' : '' ?>" data-pane="resumen">

            <?php $prox = $enCurso[0] ?? $proximos[0] ?? null; ?>
            <div class="ej-proximo">
                <?php if ($prox): ?>
                    <div class="ej-proximo-titulo"><?= $prox['estado_visible'] === 'EN_CURSO' ? 'Corte en curso' : 'Próximo corte programado' ?></div>
                    <div class="ej-proximo-fecha"><?= esc(Calculo::rango($prox['inicio_at'], $prox['fin_estimado_at'])) ?></div>
                    <?php if ($prox['estado_visible'] === 'EN_CURSO'): ?>
                        <div class="ej-proximo-faltan"><i class="fas fa-bolt"></i> En curso: lleva <?= esc(Calculo::horas($prox['horas']['sin_luz'])) ?> sin luz</div>
                    <?php else: ?>
                        <div class="ej-proximo-faltan"><i class="far fa-hourglass"></i> Faltan <?= esc($prox['faltan']) ?></div>
                    <?php endif; ?>
                    <div class="ej-proximo-datos">
                        <div><span>Localidad</span><?= esc($prox['localidad'] ?: 'Localidad sin indicar') ?></div>
                        <div><span>Zona</span><?= esc($prox['zona_descripcion'] ?: '—') ?></div>
                        <div><span>Duración programada</span><?= esc(Calculo::horas($prox['horas']['programadas'])) ?> (<?= Calculo::hhmm($prox['horas']['programadas']) ?>)</div>
                        <div><span>Afectados</span><?= $afectados($prox['afectados'], true) ?></div>
                    </div>
                    <p style="margin-top:10px;"><a href="<?= $urlCorte($prox['corte_id'], 'resumen') ?>" class="bl-btn navy sm"><i class="fas fa-search"></i> Ver detalle y afectados</a></p>
                <?php else: ?>
                    <div class="ej-proximo-titulo">Próximo corte programado</div>
                    <div class="ej-proximo-fecha" style="font-size:16px;">No hay cortes programados por delante.</div>
                <?php endif; ?>
            </div>

            <div class="ej-kpis">
                <div class="ej-kpi navy"><div class="lbl">Cortes publicados</div><div class="val"><?= $num($k['cortes']) ?></div>
                    <div class="sub"><?= $num($k['ejecutados']) ?> con horas sin luz · <?= $num($k['proximos']) ?> por venir</div></div>
                <div class="ej-kpi azul"><div class="lbl">Horas sin luz</div><div class="val"><?= Calculo::horas($k['horas_sin_luz']) ?></div>
                    <div class="sub"><?= Calculo::hhmm($k['horas_sin_luz']) ?> en total · programadas <?= Calculo::horas($k['horas_programadas']) ?></div></div>
                <div class="ej-kpi azul"><div class="lbl">Duración promedio</div><div class="val"><?= $k['duracion_media'] === null ? '—' : Calculo::horas($k['duracion_media']) ?></div>
                    <div class="sub"><?= $k['duracion_media'] === null ? 'Sin cortes ejecutados' : Calculo::hhmm($k['duracion_media']) . ' por corte' ?><?= $k['corte_mas_largo'] ? ' · máx. ' . Calculo::horas($k['corte_mas_largo']['horas']) : '' ?></div></div>
                <div class="ej-kpi rojo"><div class="lbl">Personas afectadas</div><div class="val"><?= $k['personas_distintas'] === null ? '—' : $num($k['personas_distintas']) ?></div>
                    <div class="sub"><?= $k['personas_distintas'] === null ? 'Sin dato: ningún corte con protocolo' : 'personas distintas en el período' ?></div></div>
                <div class="ej-kpi rojo"><div class="lbl">Afectados riesgo ALTO</div><div class="val"><?= $num($k['afectados_alto']) ?></div>
                    <div class="sub">suma por corte · <?= $num($k['a_revisar']) ?> a revisar aparte</div></div>
                <div class="ej-kpi ambar"><div class="lbl">Persona-horas sin luz</div><div class="val"><?= $num($k['persona_horas'], 1) ?></div>
                    <div class="sub">ALTO: <?= $num($k['persona_horas_alto'], 1) ?><?= $k['sin_dato_afectados'] ? ' · ' . $k['sin_dato_afectados'] . ' corte(s) sin dato' : '' ?></div></div>
                <div class="ej-kpi gris"><div class="lbl">Cancelados</div><div class="val"><?= $num($k['cancelados']) ?></div>
                    <div class="sub"><?= $num($k['cancelados_antes']) ?> antes de empezar (no cuentan)</div></div>
                <div class="ej-kpi ambar"><div class="lbl">Cierre a tiempo</div><div class="val"><?= $k['cumplimiento_cierre'] === null ? '—' : $num($k['cumplimiento_cierre'], 0) . '%' ?></div>
                    <div class="sub"><?= $num($k['finalizados_a_tiempo']) ?> a tiempo · <?= $num($k['finalizados_tarde']) ?> tarde · <?= $num($k['vencidos']) ?> sin finalizar</div></div>
            </div>

            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="fas fa-users"></i> Afectados del período por riesgo (suma por corte)</span><span class="ej-chip neutro"><?= esc($periodoTxt) ?></span></div>
                <div class="ej-bloque-body">
                    <?php $totR = array_sum($k['afectados_por_riesgo']); ?>
                    <?php if ($totR > 0): ?>
                        <div class="proporcion-barra" role="img" aria-label="Distribución por riesgo">
                            <?php foreach ($k['afectados_por_riesgo'] as $r => $n): if (!$n) continue; ?>
                                <div class="proporcion-seg" style="width:<?= $n * 100 / $totR ?>%; background:<?= $coloresRiesgo[$r] ?>;"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="proporcion-leyenda">
                            <?php foreach ($k['afectados_por_riesgo'] as $r => $n): ?>
                                <span><span class="proporcion-punto" style="background:<?= $coloresRiesgo[$r] ?>;"></span><?= $r ?>: <strong><?= $num($n) ?></strong> (<?= $num($n * 100 / $totR, 0) ?>%)</span>
                            <?php endforeach; ?>
                            <span>A revisar (aparte): <strong><?= $num($k['a_revisar']) ?></strong></span>
                        </div>
                    <?php else: ?>
                        <p class="ej-vacio">Sin afectados registrados en el período<?= $k['sin_dato_afectados'] ? ' (' . $k['sin_dato_afectados'] . ' corte(s) sin protocolo: sin dato)' : '' ?>.</p>
                    <?php endif; ?>
                    <?= $leyenda ?>
                </div>
            </div>
        </div>

        <!-- ══════════════ 2. PRÓXIMOS CORTES ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'proximos' ? 'is-active' : '' ?>" data-pane="proximos">
            <?php $lista = array_merge($enCurso, $proximos); ?>
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
                    <?php if (!$lista): ?><tr><td colspan="9" class="ej-vacio">No hay cortes en curso ni programados.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>

            <?php if ($lista): ?>
            <div class="ej-bloque">
                <div class="ej-bloque-titulo"><span><i class="far fa-calendar"></i> Calendario</span></div>
                <div class="ej-bloque-body">
                    <?php
                    $porDia = [];
                    foreach ($lista as $c) { $porDia[substr($c['inicio_at'], 0, 10)][] = $c; }
                    ?>
                    <?php foreach ($porDia as $dia => $cs): ?>
                        <p style="margin:0 0 6px;"><strong><?= esc(date('d/m/Y', strtotime($dia))) ?></strong>
                        <?php foreach ($cs as $c): ?>
                            · <a href="<?= $urlCorte($c['corte_id'], 'proximos') ?>"><?= substr($c['inicio_at'], 11, 5) ?>–<?= substr($c['fin_estimado_at'], 11, 5) ?> <?= esc($c['localidad'] ?: '') ?></a>
                        <?php endforeach; ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 3. HISTORIAL DE CORTES ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'historial' ? 'is-active' : '' ?>" data-pane="historial">
            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-history"></i> Cortes con inicio en el período (<?= count($cortes) ?>)</span>
                    <span class="ej-chip neutro"><?= esc($periodoTxt) ?></span>
                </div>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas">
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
                        <td></td>
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
                <div class="ej-banda gris"><i class="fas fa-info-circle"></i> <span>El período elegido tiene un solo mes. Para ver la evolución, elegí "Últimos 90 días" o "Este año".</span></div>
            <?php endif; ?>

            <div class="ej-grid-2">
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-clock"></i> Horas sin luz por mes</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart"><canvas id="chHoras" aria-label="Horas sin luz por mes"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Mes</th><th class="num">Cortes</th><th class="num">Horas</th><th class="num">Promedio</th></tr></thead>
                            <tbody><?php foreach ($serie as $s): ?><tr>
                                <td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td><td class="num"><?= $s['cortes'] ?></td>
                                <td class="num"><?= Calculo::horas($s['horas']) ?></td>
                                <td class="num"><?= $s['cortes'] ? Calculo::horas($s['horas'] / $s['cortes']) : '—' ?></td>
                            </tr><?php endforeach; ?></tbody>
                        </table></div>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-users"></i> Personas afectadas por riesgo y mes</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart"><canvas id="chRiesgo" aria-label="Afectados por riesgo y mes"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Mes</th><?php foreach (Calculo::RIESGOS as $r): ?><th class="num"><?= $r ?></th><?php endforeach; ?><th class="num">Sin dato</th></tr></thead>
                            <tbody><?php foreach ($serie as $s): ?><tr>
                                <td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td>
                                <?php foreach (Calculo::RIESGOS as $r): ?><td class="num"><?= $s[$r] ?></td><?php endforeach; ?>
                                <td class="num"><?= $s['sin_dato'] ? $s['sin_dato'] . ' corte(s)' : '—' ?></td>
                            </tr><?php endforeach; ?></tbody>
                        </table></div>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-map-marker-alt"></i> Top 10 localidades por persona-horas</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart alto"><canvas id="chLocalidad" aria-label="Localidades por persona-horas"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Localidad</th><th class="num">Cortes</th><th class="num">Horas</th><th class="num">Afectados</th><th class="num">ALTO</th><th class="num">Persona-horas</th></tr></thead>
                            <tbody><?php foreach ($localidadesTop as $l): ?><tr>
                                <td><?= esc($l['localidad']) ?></td><td class="num"><?= $l['cortes'] ?></td><td class="num"><?= Calculo::horas($l['horas']) ?></td>
                                <td class="num"><?= $num($l['afectados']) ?><?= $l['sin_dato'] ? ' <small>(' . $l['sin_dato'] . ' s/d)</small>' : '' ?></td>
                                <td class="num"><?= $num($l['alto']) ?></td><td class="num"><?= $num($l['persona_horas'], 1) ?></td>
                            </tr><?php endforeach; ?>
                            <?php if (!$localidadesTop): ?><tr><td colspan="6" class="ej-vacio">Sin cortes ejecutados en el período.</td></tr><?php endif; ?></tbody>
                        </table></div>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-chart-line"></i> Persona-horas sin luz por mes</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart"><canvas id="chPersonaHoras" aria-label="Persona-horas por mes"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Mes</th><th class="num">Persona-horas</th></tr></thead>
                            <tbody><?php foreach ($serie as $s): ?><tr><td><?= esc($s['mes'] . ' ' . $s['ejercicio']) ?></td><td class="num"><?= $num($s['persona_horas'], 1) ?></td></tr><?php endforeach; ?></tbody>
                        </table></div>
                    </div>
                </div>
            </div>

            <!-- Quiénes fueron afectados -->
            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-user-injured"></i> Quiénes quedaron sin luz en el período (<?= count($acumulado) ?> personas)</span>
                    <span class="ej-chip neutro">ordenado por horas acumuladas</span>
                </div>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas">
                    <thead><tr><th>Persona</th><th>Riesgo</th><th>Localidad</th><th>Equipos</th><th class="num">Cortes</th><th class="num">Horas sin luz</th><th>Estado</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach (array_slice($acumulado, 0, 200, true) as $id => $a): $p = $personas[$id] ?? null; ?>
                        <tr class="<?= $a['riesgo'] === 'ALTO' ? 'destacado' : '' ?>">
                            <td class="ej-td-titulo" data-label="Persona"><strong><?= esc($p['paciente'] ?? ('Persona #' . $id)) ?></strong><?= $p && $p['edad'] ? ' <small>' . (int) $p['edad'] . ' años · ' . esc($p['tipo'] ?? '') . '</small>' : '' ?></td>
                            <td data-label="Riesgo"><span class="ej-chip <?= $a['riesgo'] ?>"><?= $a['riesgo'] ?></span></td>
                            <td data-label="Localidad"><?= esc($p['localidad'] ?? '—') ?></td>
                            <td data-label="Equipos"><?php if (!empty($p['equipos'])): foreach ($p['equipos'] as $eq): ?>
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
                            <td data-label=""><?php if ($puedeVerFicha && $p): ?><a class="bl-btn ghost sm" href="<?= base_url(route_to('electrodependiente_show', $id)) ?>">Ficha</a><?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$acumulado): ?><tr><td colspan="8" class="ej-vacio">Sin personas registradas: solo se conocen los afectados de cortes con protocolo activado.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
                <div class="ej-bloque-body" style="padding-top:0;">
                    <p class="ej-leyenda" style="margin-bottom:0;">El riesgo es el del momento en que se activó el protocolo de su último corte. Horas acumuladas = suma de las horas sin luz de cada corte en que figura. No se incluyen las personas "a revisar" (sin coordenadas).</p>
                </div>
            </div>
            <?= $leyenda ?>
        </div>

        <!-- ══════════════ 5. AUDITORÍA ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'auditoria' ? 'is-active' : '' ?>" data-pane="auditoria">
            <?php $au = $auditoria; ?>

            <form method="GET" action="<?= base_url(route_to('ejesa_views')); ?>" class="ej-filtros">
                <input type="hidden" name="tab" value="auditoria">
                <input type="hidden" name="desde" value="<?= esc($f['desde']) ?>">
                <input type="hidden" name="hasta" value="<?= esc($f['hasta']) ?>">
                <div class="ej-filtro">
                    <label for="a-usuario">Usuario</label>
                    <select id="a-usuario" name="usuario">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?= (int) $u['usuario_id'] ?>" <?= (int) $f['usuario'] === (int) $u['usuario_id'] ? 'selected' : '' ?>><?= esc($u['nombre'] ?: ('Usuario #' . $u['usuario_id'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro">
                    <label for="a-mod">Cómo consultó</label>
                    <select id="a-mod" name="modalidad">
                        <option value="">Todas</option>
                        <?php foreach ($modalidades as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['modalidad'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro">
                    <label for="a-res">Resultado</label>
                    <select id="a-res" name="resultado">
                        <option value="">Todos</option>
                        <?php foreach ($resultados as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['resultado'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro">
                    <label for="a-acc">Cambio de corte</label>
                    <select id="a-acc" name="accion">
                        <option value="">Todos</option>
                        <?php foreach ($acciones as $kk => $v): ?><option value="<?= $kk ?>" <?= $f['accion'] === $kk ? 'selected' : '' ?>><?= esc($v) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="ej-filtro"><label>Período</label><input type="text" value="<?= esc($periodoTxt) ?>" readonly></div>
                <div class="ej-filtro-acciones">
                    <button type="submit" class="bl-btn teal"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="<?= $url(['tab' => 'auditoria']) ?>" class="bl-btn ghost" title="Limpiar"><i class="fas fa-times"></i></a>
                </div>
            </form>

            <div class="ej-kpis">
                <div class="ej-kpi navy"><div class="lbl">Consultas de EJESA</div><div class="val"><?= $num($au['total']) ?></div><div class="sub">en el período</div></div>
                <div class="ej-kpi azul"><div class="lbl">Personas mostradas</div><div class="val"><?= $num($au['personas']) ?></div><div class="sub">suma de resultados (puede repetir personas)</div></div>
                <div class="ej-kpi rojo"><div class="lbl">Denegadas / con error</div><div class="val"><?= $num($au['denegadas']) ?> / <?= $num($au['errores']) ?></div><div class="sub">revisar si son muchas</div></div>
                <div class="ej-kpi ambar"><div class="lbl">Fuera de horario</div><div class="val"><?= $num($au['fuera_horario']) ?></div><div class="sub">entre las <?= Calculo::HORA_NOCHE_DESDE ?> y las 0<?= Calculo::HORA_NOCHE_HASTA ?> h</div></div>
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
                        <div class="ej-chart"><canvas id="chAudDia" aria-label="Consultas por día"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Día</th><th class="num">Consultas</th></tr></thead>
                            <tbody><?php foreach ($au['por_dia'] as $dia => $n): ?><tr><td><?= esc(date('d/m/Y', strtotime($dia))) ?></td><td class="num"><?= $n ?></td></tr><?php endforeach; ?>
                            <?php if (!$au['por_dia']): ?><tr><td colspan="2" class="ej-vacio">Sin consultas en el período.</td></tr><?php endif; ?></tbody>
                        </table></div>
                    </div>
                </div>
                <div class="ej-bloque">
                    <div class="ej-bloque-titulo"><span><i class="fas fa-chart-pie"></i> Cómo consultó y con qué resultado</span></div>
                    <div class="ej-bloque-body">
                        <div class="ej-chart"><canvas id="chAudModalidad" aria-label="Consultas por modalidad"></canvas></div>
                        <div class="ej-tabla-wrap"><table class="ej-tabla" style="margin-top:10px;">
                            <thead><tr><th>Modalidad</th><th class="num">Consultas</th><th class="num">%</th></tr></thead>
                            <tbody><?php foreach ($au['por_modalidad'] as $m => $n): ?><tr><td><?= esc($modalidades[$m] ?? $m) ?></td><td class="num"><?= $n ?></td><td class="num"><?= $num($n * 100 / max(1, $au['total']), 0) ?>%</td></tr><?php endforeach; ?></tbody>
                            <thead><tr><th>Resultado</th><th class="num">Consultas</th><th class="num">%</th></tr></thead>
                            <tbody><?php foreach ($au['por_resultado'] as $r => $n): ?><tr class="<?= in_array($r, ['DENEGADA', 'ERROR'], true) ? 'destacado' : '' ?>"><td><?= esc($resultados[$r] ?? $r) ?></td><td class="num"><?= $n ?></td><td class="num"><?= $num($n * 100 / max(1, $au['total']), 0) ?>%</td></tr><?php endforeach; ?></tbody>
                        </table></div>
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
                        <?php if ($f['cursor']): ?><a class="bl-btn ghost sm" href="<?= $url(['tab' => 'auditoria', 'usuario' => $f['usuario'], 'modalidad' => $f['modalidad'], 'resultado' => $f['resultado'], 'accion' => $f['accion']]) ?>"><i class="fas fa-angle-double-left"></i> Más nuevas</a><?php endif; ?>
                        <?php if ($siguiente): ?><a class="bl-btn navy sm" href="<?= $url(['tab' => 'auditoria', 'usuario' => $f['usuario'], 'modalidad' => $f['modalidad'], 'resultado' => $f['resultado'], 'accion' => $f['accion'], 'cursor' => $siguiente]) ?>">Más viejas <i class="fas fa-angle-right"></i></a><?php endif; ?>
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
<script>
(function () {
    // ── Rangos rápidos de período ──
    window.ejRango = function (v) {
        if (!v) return;
        var hoy = new Date(), d, h;
        var iso = function (x) { return x.getFullYear() + '-' + String(x.getMonth() + 1).padStart(2, '0') + '-' + String(x.getDate()).padStart(2, '0'); };
        if (v === 'mes')     { d = new Date(hoy.getFullYear(), hoy.getMonth(), 1); h = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0); }
        if (v === 'mes_ant') { d = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1); h = new Date(hoy.getFullYear(), hoy.getMonth(), 0); }
        if (v === '90')      { d = new Date(hoy); d.setDate(d.getDate() - 89); h = hoy; }
        if (v === 'anio')    { d = new Date(hoy.getFullYear(), 0, 1); h = new Date(hoy.getFullYear(), 11, 31); }
        document.getElementById('f-desde').value = iso(d);
        document.getElementById('f-hasta').value = iso(h);
        document.getElementById('ejFiltros').submit();
    };

    // ── Datos (solo cantidades; sin datos personales) ──
    var serie = <?= json_encode(array_values($serie), JSON_UNESCAPED_UNICODE) ?>;
    var locs  = <?= json_encode(array_values($localidadesTop), JSON_UNESCAPED_UNICODE) ?>;
    var audDia = <?= json_encode($auditoria['por_dia']) ?>;
    var audMod = <?= json_encode(array_combine(array_map(fn ($m) => $modalidades[$m] ?? $m, array_keys($auditoria['por_modalidad'])), array_values($auditoria['por_modalidad'])) ?: new stdClass(), JSON_UNESCAPED_UNICODE) ?>;
    var colR = <?= json_encode($coloresRiesgo) ?>;
    var meses = serie.map(function (s) { return s.mes.substring(0, 3) + ' ' + s.ejercicio; });

    var hechos = {};
    var comun = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } };

    var graficos = {
        impacto: function () {
            new Chart(document.getElementById('chHoras'), { type: 'bar',
                data: { labels: meses, datasets: [{ label: 'Horas sin luz', data: serie.map(function (s) { return Math.round(s.horas * 10) / 10; }), backgroundColor: '#2F80C8' }] },
                options: Object.assign({}, comun, { plugins: { legend: { display: false } } }) });
            new Chart(document.getElementById('chRiesgo'), { type: 'bar',
                data: { labels: meses, datasets: ['ALTO', 'MEDIANO', 'BAJO', 'PENDIENTE'].map(function (r) { return { label: r, data: serie.map(function (s) { return s[r]; }), backgroundColor: colR[r] }; }) },
                options: Object.assign({}, comun, { scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } }) });
            new Chart(document.getElementById('chLocalidad'), { type: 'bar',
                data: { labels: locs.map(function (l) { return l.localidad; }), datasets: [{ label: 'Persona-horas', data: locs.map(function (l) { return Math.round(l.persona_horas * 10) / 10; }), backgroundColor: '#D96C6C' }] },
                options: Object.assign({}, comun, { indexAxis: 'y', plugins: { legend: { display: false } } }) });
            new Chart(document.getElementById('chPersonaHoras'), { type: 'line',
                data: { labels: meses, datasets: [{ label: 'Persona-horas', data: serie.map(function (s) { return Math.round(s.persona_horas * 10) / 10; }), borderColor: '#0e2a4d', backgroundColor: 'rgba(14,42,77,.12)', fill: true, tension: .25 }] },
                options: Object.assign({}, comun, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }) });
        },
        auditoria: function () {
            new Chart(document.getElementById('chAudDia'), { type: 'bar',
                data: { labels: Object.keys(audDia).map(function (d) { return d.substring(8, 10) + '/' + d.substring(5, 7); }), datasets: [{ label: 'Consultas', data: Object.values(audDia), backgroundColor: '#0e2a4d' }] },
                options: Object.assign({}, comun, { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }) });
            new Chart(document.getElementById('chAudModalidad'), { type: 'doughnut',
                data: { labels: Object.keys(audMod), datasets: [{ data: Object.values(audMod), backgroundColor: ['#2F80C8', '#D96C6C', '#5BB8A6', '#9AA5B5'] }] },
                options: comun });
        }
    };

    function dibujar(tab) {
        if (hechos[tab] || !graficos[tab] || typeof Chart === 'undefined') return;
        hechos[tab] = true;
        graficos[tab]();
    }

    // ── Pestañas: ?tab= se conserva al filtrar; los gráficos se dibujan al abrir la pestaña ──
    document.querySelectorAll('.ml-tab').forEach(function (b) {
        b.addEventListener('click', function () {
            var tab = b.dataset.tab;
            document.querySelectorAll('.ml-tab').forEach(function (x) { x.classList.toggle('is-active', x === b); x.setAttribute('aria-selected', x === b ? 'true' : 'false'); });
            document.querySelectorAll('.ml-tab-pane').forEach(function (p) { p.classList.toggle('is-active', p.dataset.pane === tab); });
            document.getElementById('inputTab').value = tab;
            var params = new URLSearchParams(window.location.search);
            params.set('tab', tab);
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());
            dibujar(tab);
        });
    });
    dibujar(<?= json_encode($tab) ?>);
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
