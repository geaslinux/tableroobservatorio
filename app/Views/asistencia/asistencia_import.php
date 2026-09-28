<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> IMPORTAR ASISTENCIAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h2 class="subtitle has-text-centered has-text-weight-bold" style="color:white;">
        Importar Asistencias desde Excel
    </h2>
    <hr>

    <div class="message is-info">
        <div class="message-header"><p>Instrucciones</p></div>
        <div class="message-body">
            <ul>
                <li>El archivo debe ser <strong>.xlsx o .xls</strong></li>
                <li>La <strong>fila 1</strong> es el encabezado, los datos desde fila 2</li>
                <li>Los meses en <strong>MAYÚSCULAS</strong> (ENERO, FEBRERO...)</li>
                <li>El campo <strong>NOMBRE</strong> debe coincidir con una base registrada</li>
                <li>No se importarán duplicados (mismo ejercicio + mes + nombre)</li>
                <li>El TOTAL se calcula automáticamente al importar</li>
            </ul>
            <br>
            <a href="<?= base_url(route_to('asistencia_template')) ?>" class="button is-info is-small">
                <span class="icon"><i class="fas fa-download"></i></span>
                <span>Descargar Plantilla Excel</span>
            </a>
            <span class="has-text-info is-size-7 ml-2">
                ← La plantilla incluye una hoja con todos los nombres de bases válidos
            </span>
        </div>
    </div>

    <!-- Tabla de referencia nombres válidos -->
    <div class="message is-warning">
        <div class="message-header"><p>Nombres de bases válidos para la columna NOMBRE</p></div>
        <div class="message-body">
            <div class="tags">
                <?php foreach ($bases as $b): ?>
                    <span class="tag is-dark"><?= $b->nombre ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <form action="<?= base_url(route_to('asistencia_process_import')) ?>" method="POST" enctype="multipart/form-data">
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
                <a href="<?= base_url(route_to('asistencia_list')) ?>" class="button is-warning">
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