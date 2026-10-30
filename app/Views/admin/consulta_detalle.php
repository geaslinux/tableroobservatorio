<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle de atención de consulta<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('css/panel.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/consulta-tablero.css') ?>?v=<?= @filemtime(FCPATH . 'css/consulta-tablero.css') ?: '1' ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$volver = base_url(route_to('consulta_tablero')) . '?' . http_build_query($filtros) . '#consulta-detalle';
$id = (int) $registro['atencion_id'];
$fecha = $registro['fecha'] ? date('d/m/Y', strtotime($registro['fecha'])) : null;
$hora = $registro['hora'] ? substr($registro['hora'], 0, 5) . ' h' : null;

// "Servicio de Oftalmología - Hospital Materno Infantil": servicio arriba y efector abajo
$formulario = trim((string) $registro['formulario']);
$partes = $formulario !== '' ? explode(' - ', $formulario, 2) : [];
$servicioAtencion = $partes[0] ?? null;
$efectorAtencion = $partes[1] ?? null;

$sinDato = '<span class="ct-sin-dato">Sin informar</span>';
$texto = static fn ($v) => trim((string) $v) !== '' ? esc(trim((string) $v)) : null;

// Registro clínico: diagnóstico y procedimiento siempre; indicaciones y observaciones solo si tienen texto
$clinicos = [
    'diagnostico'   => ['Diagnóstico', 'fa-stethoscope'],
    'procedimiento' => ['Procedimiento', 'fa-file-medical'],
    'indicaciones'  => ['Indicaciones', 'fa-prescription'],
    'observaciones' => ['Observaciones', 'fa-comment-medical'],
];
$vacios = [];
foreach (['indicaciones', 'observaciones'] as $key) {
    if ($texto($registro[$key]) === null) $vacios[] = mb_strtolower($clinicos[$key][0]);
}
?>
<div class="base-list-wrap consulta-tablero">

    <?= view('partials/breadcrumb', ['nodo' => 'consulta_tablero', 'actual' => 'Atención ' . $id]) ?>

    <div class="bl-toolbar">
        <a class="bl-btn ghost" href="<?= esc($volver, 'attr') ?>"><i class="fas fa-arrow-left"></i> Volver al tablero con los mismos filtros</a>
        <span class="ct-toolbar-nota"><i class="fas fa-user-shield" aria-hidden="true"></i> Valores históricos de la atención. Se omiten identificables de pacientes.</span>
    </div>

    <section class="bl-panel ct-atencion" aria-labelledby="titulo-atencion">
        <div class="bl-panel-header">
            <h1 class="bl-panel-title" id="titulo-atencion"><i class="fas fa-notes-medical" aria-hidden="true"></i> Atención <?= $id ?></h1>
            <?php if ($fecha): ?>
                <span class="bl-panel-meta"><i class="fas fa-calendar-day" aria-hidden="true"></i> <?= esc($fecha) ?><?= $hora ? ' · ' . esc($hora) : '' ?></span>
            <?php endif ?>
        </div>
        <div class="bl-panel-body">

            <!-- Resumen: cuándo, dónde y quién -->
            <dl class="ct-resumen-atencion">
                <div class="ct-dato">
                    <span class="ct-dato-icono" aria-hidden="true"><i class="fas fa-calendar-day"></i></span>
                    <div>
                        <dt>Fecha y hora</dt>
                        <dd><?= $fecha ? esc($fecha) : $sinDato ?><?php if ($hora): ?><small><i class="fas fa-clock" aria-hidden="true"></i> <?= esc($hora) ?></small><?php endif ?></dd>
                    </div>
                </div>
                <div class="ct-dato">
                    <span class="ct-dato-icono" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                    <div>
                        <dt>Servicio de atención</dt>
                        <dd><?= $servicioAtencion ? esc($servicioAtencion) : $sinDato ?><?php if ($efectorAtencion): ?><small><i class="fas fa-hospital" aria-hidden="true"></i> <?= esc($efectorAtencion) ?></small><?php endif ?></dd>
                    </div>
                </div>
                <div class="ct-dato">
                    <span class="ct-dato-icono" aria-hidden="true"><i class="fas fa-user-doctor"></i></span>
                    <div>
                        <dt>Profesional registrado</dt>
                        <dd><?= $texto($registro['profesional']) ?? $sinDato ?></dd>
                    </div>
                </div>
            </dl>

            <!-- Origen de la interconsulta -->
            <h2 class="ct-ficha-seccion"><i class="fas fa-route" aria-hidden="true"></i> Origen de la interconsulta</h2>
            <dl class="ct-origen">
                <div>
                    <dt><i class="fas fa-hospital" aria-hidden="true"></i> Hospital</dt>
                    <dd><?= $texto($registro['hospital']) ?? $sinDato ?></dd>
                </div>
                <span class="ct-origen-flecha" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                <div>
                    <dt><i class="fas fa-door-open" aria-hidden="true"></i> Servicio</dt>
                    <dd><?= $texto($registro['servicio']) ?? $sinDato ?></dd>
                </div>
            </dl>

            <!-- Registro clínico -->
            <h2 class="ct-ficha-seccion"><i class="fas fa-file-waveform" aria-hidden="true"></i> Registro clínico</h2>
            <div class="ct-clinico">
                <?php foreach ($clinicos as $key => [$label, $icono]):
                    $valor = $texto($registro[$key]);
                    if ($valor === null && in_array($key, ['indicaciones', 'observaciones'], true)) continue;
                ?>
                    <div class="ct-clinico-item">
                        <h3><i class="fas <?= $icono ?>" aria-hidden="true"></i> <?= esc($label) ?></h3>
                        <p><?= $valor ?? $sinDato ?></p>
                    </div>
                <?php endforeach ?>
                <?php if ($vacios): ?>
                    <p class="ct-clinico-vacio"><i class="fas fa-circle-minus" aria-hidden="true"></i> Sin <?= esc(implode(' ni ', $vacios)) ?> registradas.</p>
                <?php endif ?>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection() ?>