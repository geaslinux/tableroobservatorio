<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> CONSULTA/RECLAMO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Registro' : 'Nuevo Registro de Consulta / Reclamo' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('consulta_reclamo_update')) : base_url(route_to('consulta_reclamo_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($registro)): ?>
        <?= $registro->getCampoOculto() ?>
    <?php endif; ?>

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

            <!-- Columna 1 -->
            <div class="column is-half">

                <!-- EJERCICIO -->
                <div class="field">
                    <label class="label" style="color:white;">Ejercicio *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.ejercicio') ? 'is-danger' : '' ?>"
                            type="number" name="ejercicio"
                            placeholder="<?= date('Y') ?>"
                            max="<?= date('Y') ?>"
                            value="<?= old('ejercicio', $registro->ejercicio ?? date('Y')) ?>">
                        <span class="icon is-left"><i class="fas fa-calendar"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">Solo hasta el año actual (<?= date('Y') ?>).</p>
                    <?php if (session('errors.ejercicio')): ?>
                        <p class="help is-danger"><?= session('errors.ejercicio') ?></p>
                    <?php endif; ?>
                </div>

                <!-- MES -->
                <div class="field">
                    <label class="label" style="color:white;">Mes *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.mes') ? 'is-danger' : '' ?>">
                            <select name="mes">
                                <option value="">Seleccionar mes...</option>
                                <?php
                                $selMes = old('mes', $registro->mes ?? '');
                                foreach ($meses as $m):
                                ?>
                                    <option value="<?= $m ?>" <?= $selMes == $m ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.mes')): ?>
                        <p class="help is-danger"><?= session('errors.mes') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TIPO DE LLAMADO -->
                <div class="field">
                    <label class="label" style="color:white;">Tipo de Llamado *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.tipo_llamado') ? 'is-danger' : '' ?>">
                            <select name="tipo_llamado">
                                <option value="">Seleccionar tipo...</option>
                                <?php
                                $selTipo = old('tipo_llamado', $registro->tipo_llamado ?? '');
                                foreach ($tipos as $t):
                                ?>
                                    <option value="<?= $t ?>" <?= $selTipo == $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.tipo_llamado')): ?>
                        <p class="help is-danger"><?= session('errors.tipo_llamado') ?></p>
                    <?php endif; ?>
                </div>

                <!-- ATENCIONES -->
                <div class="field">
                    <label class="label" style="color:white;">Atenciones *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.atencion') ? 'is-danger' : '' ?>"
                            type="number" name="atencion"
                            placeholder="0" min="0"
                            value="<?= old('atencion', $registro->atencion ?? 0) ?>">
                        <span class="icon is-left"><i class="fas fa-phone"></i></span>
                    </div>
                    <?php if (session('errors.atencion')): ?>
                        <p class="help is-danger"><?= session('errors.atencion') ?></p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Columna 2 -->
            <div class="column is-half">

                <!-- CATEGORIA -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Categoría
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="categoria_id">
                                <option value="">— Sin categoría —</option>
                                <?php
                                $selCat = old('categoria_id', $registro->categoria_id ?? '');
                                foreach ($categorias as $cat):
                                ?>
                                    <option value="<?= $cat->categoria_id ?>" <?= $selCat == $cat->categoria_id ? 'selected' : '' ?>>
                                        <?= esc($cat->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="help" style="color:#ffe08a;">Puede quedar en blanco.</p>
                </div>

                <!-- SUB CATEGORIA -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Sub Categoría
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="sub_categoria_id">
                                <option value="">— Sin sub categoría —</option>
                                <?php
                                $selSub = old('sub_categoria_id', $registro->sub_categoria_id ?? '');
                                foreach ($sub_categorias as $sub):
                                ?>
                                    <option value="<?= $sub->sub_categoria_id ?>" <?= $selSub == $sub->sub_categoria_id ? 'selected' : '' ?>>
                                        <?= esc($sub->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <p class="help" style="color:#ffe08a;">Puede quedar en blanco.</p>
                </div>

                <?php if (isset($registro)): ?>
                <!-- ESTADO -->
                <div class="field">
                    <label class="label" style="color:white;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="estado">
                                <option value="activo"      <?= ($registro->estado ?? 'activo') == 'activo'      ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= ($registro->estado ?? '') == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <input type="hidden" name="estado" value="activo">
                <?php endif; ?>

            </div>
        </div>

        <hr>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($registro) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('consulta_reclamo_list')) ?>" class="button is-warning">
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