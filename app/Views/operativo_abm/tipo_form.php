<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($tipo) ? 'EDITAR TIPO' : 'NUEVO TIPO' ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($tipo) ? 'Editar Tipo de Operativo' : 'Nuevo Tipo de Operativo' ?>
</h2>

<form action="<?= isset($tipo) ? base_url(route_to('tipo_operativo_update')) : base_url(route_to('tipo_operativo_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($tipo)): ?>
        <?= $tipo->getCampoOculto() ?>
    <?php endif; ?>

    <div class="container is-max-widescreen">
    <div class="notification" style="background-color: #13304d;">

        <?php if (session()->has('msg')): $msg = session('msg'); ?>
            <div class="notification is-<?= $msg['type'] ?> is-light">
                <?= $msg['body'] ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="notification is-danger is-light">
                <ul>
                <?php foreach (session('errors') as $e): ?>
                    <li><?= $e ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="columns">
            <div class="column is-half is-offset-one-quarter">

                <!-- NOMBRE -->
                <div class="field">
                    <label class="label" style="color:white;">Nombre *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.nombre') ? 'is-danger' : '' ?>"
                            type="text"
                            name="nombre"
                            placeholder="Ej: GLOBULOS ROJOS"
                            maxlength="100"
                            value="<?= old('nombre', $tipo->nombre ?? '') ?>">
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
                    <span><?= isset($tipo) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('tipo_operativo_list')) ?>" class="button is-warning">
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