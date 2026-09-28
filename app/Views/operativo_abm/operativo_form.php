<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($operativo) ? 'EDITAR OPERATIVO' : 'NUEVO OPERATIVO' ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($operativo) ? 'Editar Operativo' : 'Nuevo Operativo' ?>
</h2>

<form action="<?= isset($operativo) ? base_url(route_to('operativo_update')) : base_url(route_to('operativo_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($operativo)): ?>
        <?= $operativo->getCampoOculto() ?>
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
                            placeholder="Ej: HEMOCOMPONENTES PRODUCIDOS"
                            maxlength="150"
                            value="<?= old('nombre', $operativo->nombre ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-clipboard-list"></i></span>
                    </div>
                    <?php if (session('errors.nombre')): ?>
                        <p class="help is-danger"><?= session('errors.nombre') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TIPO (opcional) -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Tipo
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tipo_id">
                                <option value="">— Sin tipo —</option>
                                <?php
                                $selTipo = old('tipo_id', $operativo->tipo_id ?? '');
                                foreach ($tipos as $t):
                                ?>
                                    <option value="<?= $t->tipo_op_id ?>" <?= $selTipo == $t->tipo_op_id ? 'selected' : '' ?>>
                                        <?= esc($t->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="help" style="color:#ffe08a;">
                        Dejá en blanco si el operativo no tiene tipo asociado.
                    </p>
                </div>

            </div>
        </div>

        <hr>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($operativo) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('operativo_list')) ?>" class="button is-warning">
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