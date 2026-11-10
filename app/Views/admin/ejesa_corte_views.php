<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Corte EJESA #<?= (int) $corte['corte_id'] ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="/css/panel-pestanas.css">
<link rel="stylesheet" href="/css/ejesa.css">
<?php if ($geometria): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<?php endif; ?>

<?php
use App\Libraries\Ejesa\Calculo;

$num = static fn ($n, int $dec = 0) => number_format((float) $n, $dec, ',', '.');
$c   = $corte;
$a   = $c['afectados'];
$h   = $c['horas'];
$volverUrl = base_url(route_to('ejesa_views')) . '?tab=' . urlencode(in_array($volver, ['resumen', 'proximos', 'historial', 'impacto', 'auditoria'], true) ? $volver : 'historial')
    . '&desde=' . substr($c['inicio_at'], 0, 8) . '01&hasta=' . date('Y-m-t', strtotime($c['inicio_at']));
?>

<div class="ej-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Electrodependientes</a> ›
    <a href="<?= $volverUrl ?>">EJESA</a> ›
    <strong>Corte #<?= (int) $c['corte_id'] ?></strong>
</div>

<div class="ej-panel">
    <div class="ej-panel-header">
        <span class="ej-panel-title"><i class="fas fa-bolt"></i> CORTE #<?= (int) $c['corte_id'] ?><?= $c['referencia'] ? ' · ' . esc($c['referencia']) : '' ?></span>
        <span class="ej-datos-al"><i class="far fa-clock"></i> Datos al <?= esc(Calculo::fecha($ahora)) ?></span>
    </div>
    <div class="ej-panel-body">

        <div class="ej-toolbar">
            <a href="<?= $volverUrl ?>" class="bl-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <?php if ($c['estado_visible'] === 'EN_CURSO'): ?>
            <div class="ej-banda rojo"><i class="fas fa-bolt"></i><span><strong>Corte EN CURSO.</strong> Lleva <?= Calculo::horas($h['sin_luz']) ?> sin luz.</span></div>
        <?php elseif ($c['estado_visible'] === 'VENCIDO'): ?>
            <div class="ej-banda ambar"><i class="fas fa-exclamation-triangle"></i><span><strong>Vencido sin finalizar:</strong> ya pasó el fin estimado y EJESA no lo cerró. Las horas se cuentan hasta el fin estimado.</span></div>
        <?php elseif ($c['estado_visible'] === 'CANCELADO'): ?>
            <div class="ej-banda gris"><i class="fas fa-ban"></i><span><strong>Cancelado</strong> el <?= esc(Calculo::fecha(Calculo::utcALocal($c['cancelado_at']))) ?><?= $c['motivo_cancelacion'] ? ': ' . esc($c['motivo_cancelacion']) : '' ?>.</span></div>
        <?php endif; ?>

        <!-- ── Datos del corte ── -->
        <div class="ej-bloque">
            <div class="ej-bloque-titulo"><span><i class="fas fa-info-circle"></i> Datos del corte</span><span class="ej-chip <?= $c['estado_visible'] ?>"><?= esc(Calculo::ESTADOS_VISIBLES[$c['estado_visible']]) ?></span></div>
            <div class="ej-bloque-body">
                <div class="ej-ficha">
                    <div><span>Fecha y hora</span><strong><?= esc(Calculo::rango($c['inicio_at'], $c['fin_estimado_at'])) ?></strong></div>
                    <div><span><?= $c['estado_visible'] === 'PROGRAMADO' ? 'Faltan' : 'Inicio / fin estimado' ?></span><?= $c['estado_visible'] === 'PROGRAMADO' ? esc($c['faltan']) : esc(Calculo::fecha($c['inicio_at'])) . ' → ' . esc(Calculo::fecha($c['fin_estimado_at'])) ?></div>
                    <div><span>Localidad</span><?= esc($c['localidad'] ?: 'Localidad sin indicar') ?></div>
                    <div><span>Zona</span><?= esc($c['zona_descripcion'] ?: '—') ?></div>
                    <div><span>Motivo</span><?= esc($c['motivo'] ?: '—') ?></div>
                    <div><span>Horas programadas</span><?= Calculo::horas($h['programadas']) ?> (<?= Calculo::hhmm($h['programadas']) ?>)</div>
                    <div><span>Horas sin luz</span><?php if ($h['cuenta']): ?><strong><?= Calculo::horas($h['sin_luz']) ?></strong> (<?= Calculo::hhmm($h['sin_luz']) ?>)<?php else: ?>0 h<?php endif; ?>
                        <span class="ej-marca" style="text-transform:none; letter-spacing:0;"><?= esc(Calculo::MARCAS[$h['marca']]) ?></span></div>
                    <div><span>Persona-horas</span><?= $c['persona_horas'] === null ? '—' : $num($c['persona_horas'], 1) . ' <small>(ALTO ' . $num($c['persona_horas_alto'], 1) . ')</small>' ?></div>
                    <div><span>Publicado</span><?= esc(Calculo::fecha(Calculo::utcALocal($c['publicado_at'])) ?: '—') ?></div>
                    <div><span>Finalizado</span><?= $c['finalizado_local'] ? esc(Calculo::fecha($c['finalizado_local'])) . ($c['finalizado_tarde'] ? ' <small>(tarde)</small>' : '') : '—' ?></div>
                    <div><span>Reprogramaciones</span><?= (int) $c['reprogramaciones'] ?></div>
                    <div><span>Versión</span><?= (int) $c['version'] ?></div>
                </div>
                <p class="ej-leyenda" style="margin-bottom:0;">No existe "inicio real": las horas sin luz se cuentan desde el inicio programado.</p>
            </div>
        </div>

        <!-- ── Afectados ── -->
        <div class="ej-bloque">
            <div class="ej-bloque-titulo">
                <span><i class="fas fa-users"></i> Personas electrodependientes afectadas</span>
                <?php if ($a['total'] !== null): ?><span class="ej-chip neutro">Al activar el protocolo (<?= esc(Calculo::fecha($a['calculado_at'])) ?>)</span><?php endif; ?>
            </div>
            <div class="ej-bloque-body">
                <?php if ($a['total'] === null): ?>
                    <div class="ej-banda gris"><i class="fas fa-info-circle"></i><span><strong>Sin dato:</strong> este corte no tiene el protocolo activado. Los afectados se guardan cuando se activa el protocolo en sistemabasec; este panel no los recalcula.</span></div>
                <?php else: ?>
                    <div class="ej-kpis">
                        <div class="ej-kpi navy"><div class="lbl">Afectados</div><div class="val"><?= $num($a['total']) ?></div><div class="sub">en la zona del corte</div></div>
                        <div class="ej-kpi rojo"><div class="lbl">Riesgo ALTO</div><div class="val"><?= $num($a['ALTO']) ?></div><div class="sub">MEDIANO <?= $num($a['MEDIANO']) ?> · BAJO <?= $num($a['BAJO']) ?></div></div>
                        <div class="ej-kpi gris"><div class="lbl">Riesgo PENDIENTE</div><div class="val"><?= $num($a['PENDIENTE']) ?></div><div class="sub">sin evaluación de riesgo</div></div>
                        <div class="ej-kpi ambar"><div class="lbl">A revisar (aparte)</div><div class="val"><?= $a['a_revisar'] === null ? '—' : $num($a['a_revisar']) ?></div><div class="sub">de la localidad, sin coordenadas: no se suman</div></div>
                    </div>
                    <?php if (!empty($a['desactualizado'])): ?>
                        <div class="ej-banda ambar"><i class="fas fa-exclamation-triangle"></i><span>El corte se <strong>reprogramó después</strong> de activar el protocolo: la lista corresponde a la versión <?= (int) $a['revision'] ?> del corte.</span></div>
                    <?php endif; ?>

                    <div class="ej-tabla-wrap">
                    <table class="ej-tabla tarjetas">
                        <thead><tr><th>Persona</th><th>Riesgo</th><th>Localidad</th><th>Hospital</th><th>Equipos</th><th>Servicio</th><th>Estado hoy</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($afectados as $p): $r = Calculo::riesgo($p['riesgo_snapshot']); ?>
                            <tr class="<?= $r === 'ALTO' ? 'destacado' : '' ?>">
                                <td class="ej-td-titulo" data-label="Persona"><strong><?= esc($p['paciente'] ?? ('Persona #' . $p['electrodependiente_id'])) ?></strong><?= $p['edad'] ? ' <small>' . (int) $p['edad'] . ' años · ' . esc($p['tipo'] ?? '') . '</small>' : '' ?></td>
                                <td data-label="Riesgo"><span class="ej-chip <?= $r ?>"><?= $r ?></span>
                                    <?php if (Calculo::riesgo($p['factor_riesgo'] ?? null) !== $r): ?><br><small title="Riesgo actual en la ficha">hoy: <?= Calculo::riesgo($p['factor_riesgo'] ?? null) ?></small><?php endif; ?></td>
                                <td data-label="Localidad"><?= esc($p['localidad'] ?? '—') ?></td>
                                <td data-label="Hospital"><?= esc($p['efector'] ?? '—') ?></td>
                                <td data-label="Equipos"><?php if (!empty($p['equipos'])): foreach ($p['equipos'] as $eq): ?>
                                    <div><?= esc($eq['equipamiento']) ?><?= $eq['tiempo_uso'] ? ' <small>(' . esc($eq['tiempo_uso']) . ')</small>' : '' ?></div>
                                <?php endforeach; else: ?>—<?php endif; ?></td>
                                <td data-label="Servicio"><?= esc(Calculo::servicio($p['nro_servicio_snapshot'] ?: $p['nro_servicio'])) ?></td>
                                <td data-label="Estado hoy"><?php
                                    if (!isset($p['paciente'])) echo '<span class="ej-chip neutro">Sin ficha</span>';
                                    elseif (($p['estado_vital'] ?? '') === 'fallecido') echo '<span class="ej-chip CANCELADO">Fallecido</span>';
                                    elseif (($p['estado'] ?? '') !== 'activo') echo '<span class="ej-chip neutro">' . esc(ucfirst((string) $p['estado'])) . '</span>';
                                    else echo '<span class="ej-chip ok">Activo</span>';
                                ?></td>
                                <td data-label=""><?php if ($puedeVerFicha && isset($p['paciente'])): ?><a class="bl-btn ghost sm" href="<?= base_url(route_to('electrodependiente_show', $p['electrodependiente_id'])) ?>">Ficha</a><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$afectados): ?><tr><td colspan="8" class="ej-vacio">El protocolo se activó sin personas en la zona del corte.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                    </div>
                    <p class="ej-leyenda"><i class="fas fa-info-circle"></i> Afectados <strong>orientativos</strong>: el domicilio no prueba de qué red eléctrica depende la persona. El riesgo es el guardado al activar el protocolo. Las personas "a revisar" no quedan guardadas en la base: solo se conoce la cantidad.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($geometria): ?>
        <div class="ej-bloque">
            <div class="ej-bloque-titulo"><span><i class="fas fa-draw-polygon"></i> Zona del corte</span><span class="ej-chip neutro">sin ubicación de personas</span></div>
            <div class="ej-bloque-body"><div id="ej-mapa"></div></div>
        </div>
        <?php endif; ?>

        <!-- ── Línea de tiempo ── -->
        <div class="ej-bloque">
            <div class="ej-bloque-titulo"><span><i class="fas fa-stream"></i> Historial del corte (<?= count($historial) ?>)</span></div>
            <div class="ej-bloque-body">
                <?php if ($historial): ?>
                <ul class="ej-timeline">
                    <?php foreach ($historial as $ev): ?>
                        <li class="<?= esc($ev['accion'], 'attr') ?>">
                            <strong><?= esc($acciones[$ev['accion']] ?? $ev['accion']) ?></strong> · versión <?= (int) $ev['version'] ?>
                            <div><?= esc(Calculo::describirCambio($ev['accion'], $ev['cambios'])) ?></div>
                            <div class="cuando"><?= esc(Calculo::fecha(Calculo::utcALocal($ev['registrado_at']))) ?> · <?= esc($ev['username'] ?: 'usuario desconocido') ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?><p class="ej-vacio">Sin historial registrado.</p><?php endif; ?>
            </div>
        </div>

        <!-- ── Quién consultó este corte ── -->
        <div class="ej-bloque">
            <div class="ej-bloque-titulo"><span><i class="fas fa-user-shield"></i> Consultas de EJESA sobre este corte (<?= count($consultas) ?>)</span></div>
            <div class="ej-tabla-wrap"><table class="ej-tabla tarjetas">
                <thead><tr><th>Fecha y hora</th><th>Usuario</th><th>Cómo</th><th>Resultado</th><th class="num">Personas</th><th>IP</th></tr></thead>
                <tbody><?php foreach ($consultas as $q): ?><tr>
                    <td class="ej-td-titulo" data-label="Fecha"><?= esc(Calculo::fecha(Calculo::utcALocal($q['consultado_at']))) ?></td>
                    <td data-label="Usuario"><?= esc($q['usuario_nombre_snapshot'] ?: ('#' . $q['usuario_id'])) ?></td>
                    <td data-label="Cómo"><?= esc($modalidades[$q['modalidad']] ?? $q['modalidad']) ?></td>
                    <td data-label="Resultado"><?= esc($resultados[$q['resultado']] ?? $q['resultado']) ?></td>
                    <td data-label="Personas" class="num"><?= (int) $q['cantidad_personas'] ?></td>
                    <td data-label="IP"><?= esc($q['ip'] ?: '—') ?></td>
                </tr><?php endforeach; ?>
                <?php if (!$consultas): ?><tr><td colspan="6" class="ej-vacio">Nadie consultó los afectados de este corte.</td></tr><?php endif; ?></tbody>
            </table></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<?php if ($geometria): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var zona = <?= json_encode(json_decode((string) $geometria, true)) ?>;
    if (!zona || zona.type !== 'Polygon') return;
    var mapa = L.map('ej-mapa');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
    var capa = L.geoJSON(zona, { style: { color: '#c53030', weight: 2, fillOpacity: .2 } }).addTo(mapa);
    mapa.fitBounds(capa.getBounds(), { padding: [20, 20] });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
