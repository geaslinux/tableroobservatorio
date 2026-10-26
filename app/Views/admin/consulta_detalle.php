<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle de atención de consulta<?= $this->endSection() ?>
<?= $this->section('css') ?><link rel="stylesheet" href="<?= base_url('css/panel.css') ?>"><?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="base-list-wrap">
    <a class="button mb-4" href="<?= esc(base_url(route_to('consulta_tablero')) . '?' . http_build_query($filtros), 'attr') ?>#consulta-detalle">Volver al tablero con los mismos filtros</a>
    <section class="bl-panel"><div class="bl-panel-header"><h1 class="bl-panel-title">Atención <?= (int) $registro['atencion_id'] ?></h1></div>
        <div class="bl-panel-body"><p class="mb-4">Valores históricos conservados en la atención. Se omiten identificables de pacientes.</p>
            <dl><?php foreach (['fecha' => 'Fecha', 'hora' => 'Hora', 'formulario' => 'Servicio/formulario de atención', 'profesional' => 'Profesional registrado', 'diagnostico' => 'Diagnóstico registrado', 'procedimiento' => 'Procedimiento registrado', 'hospital' => 'Hospital de origen de interconsulta', 'servicio' => 'Servicio de origen de interconsulta', 'indicaciones' => 'Indicaciones', 'observaciones' => 'Observaciones'] as $key => $label): ?>
                <dt class="has-text-weight-bold mt-3"><?= esc($label) ?></dt><dd style="white-space:pre-wrap;overflow-wrap:anywhere"><?= esc($registro[$key] ?: 'Sin informar') ?></dd>
            <?php endforeach ?></dl>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
