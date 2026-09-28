<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?>
    <?php if ($seccion === 'categoria'): ?>
        <?= isset($categoria) ? 'EDITAR CATEGORÍA' : 'NUEVA CATEGORÍA' ?>
    <?php else: ?>
        <?= isset($sub_categoria) ? 'EDITAR SUB CATEGORÍA' : 'NUEVA SUB CATEGORÍA' ?>
    <?php endif; ?>
<?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?php if ($seccion === 'categoria'): ?>
        <?= isset($categoria) ? 'Editar Categoría' : 'Nueva Categoría' ?>
    <?php else: ?>
        <?= isset($sub_categoria) ? 'Editar Sub Categoría' : 'Nueva Sub Categoría' ?>
    <?php endif; ?>
</h2>

<?php
$actionUrl = '';
$campoOculto = '';
if ($seccion === 'categoria') {
    $actionUrl   = isset($categoria)     ? base_url(route_to('categoria_update'))     : base_url(route_to('categoria_store'));
    $campoOculto = isset($categoria)     ? $categoria->getCampoOculto()               : '';
} else {
    $actionUrl   = isset($sub_categoria) ? base_url(route_to('sub_categoria_update')) : base_url(route_to('sub_categoria_store'));
    $campoOculto = isset($sub_categoria) ? $sub_categoria->getCampoOculto()           : '';
}
$valorNombre = old('nombre',
    $seccion === 'categoria'
        ? ($categoria->nombre     ?? '')
        : ($sub_categoria->nombre ?? '')
);
?>

<form action="<?= $actionUrl ?>" method="POST">
    <?= csrf_field() ?>
    <?= $campoOculto ?>

    <div class="container is-max-widescreen">
    <div class="notification" style="background-color: #13304d;">

        <?php if (session()->has('msg')): $msg = session('msg'); ?>
            <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="notification is-danger is-light">
                <ul><?php foreach (session('errors') as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="columns">
            <div class="column is-half is-offset-one-quarter">

                <div class="field">
                    <label class="label" style="color:white;">Nombre *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.nombre') ? 'is-danger' : '' ?>"
                            type="text" name="nombre"
                            placeholder="<?= $seccion === 'categoria' ? 'Ej: ERROR DE GESTIÓN...' : 'Ej: TURNO SIN REGISTRAR EN SISTEMA' ?>"
                            maxlength="250"
                            value="<?= esc($valorNombre) ?>">
                        <span class="icon is-left"><i class="fas fa-tag"></i></span>
                    </div>
                    <?php if (session('errors.nombre')): ?>
                        <p class="help is-danger"><?= session('errors.nombre') ?></p>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <hr>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span>
                        <?php if ($seccion === 'categoria'): ?>
                            <?= isset($categoria) ? 'Actualizar' : 'Guardar' ?>
                        <?php else: ?>
                            <?= isset($sub_categoria) ? 'Actualizar' : 'Guardar' ?>
                        <?php endif; ?>
                    </span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url($seccion === 'categoria' ? route_to('categoria_list') : route_to('sub_categoria_list')) ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>

    </div>
    </div>
</form>
</section>
<?= $this->endSection() ?>