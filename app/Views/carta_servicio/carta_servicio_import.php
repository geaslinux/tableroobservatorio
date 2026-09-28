<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> IMPORTAR CARTA DE SERVICIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h2 class="subtitle has-text-centered has-text-weight-bold" style="color:white;">
        Importar Carta de Servicio desde Excel
    </h2>
    <hr>

    <div class="message is-info">
        <div class="message-header"><p>Instrucciones</p></div>
        <div class="message-body">
            <ul>
                <li>El archivo debe ser <strong>.xlsx o .xls</strong></li>
                <li>La <strong>fila 1</strong> debe ser el encabezado</li>
                <li>Al importar se <strong>reemplaza todo el listado</strong> anterior</li>
            </ul>
            <br>
            <a href="<?= base_url(route_to('carta_servicio_template')) ?>" class="button is-info is-small">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Descargar Plantilla Excel</span>
            </a>
        </div>
    </div>

    <form action="<?= base_url(route_to('carta_servicio_process_import')) ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="field">
            <label class="label" style="color:white;">Seleccionar archivo Excel</label>
            <div class="control">
                <input class="input" type="file" name="archivo" accept=".xlsx,.xls">
            </div>
        </div>
        <br>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-upload"></i></span>
                    <span>Importar</span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('carta_servicio_list')) ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>
    </form>

</div>
</div>
</section>
<?= $this->endSection() ?>