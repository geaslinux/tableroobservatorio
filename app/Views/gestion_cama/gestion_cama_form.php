<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> GESTIÓN DE CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Registro — Gestión de Camas' : 'Nuevo Registro — Gestión de Camas' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('gestion_cama_update')) : base_url(route_to('gestion_cama_store')) ?>" method="POST">
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

            <!-- Columna izquierda -->
            <div class="column is-half">

                <!-- HOSPITAL -->
                <div class="field">
                    <label class="label" style="color:white;">Hospital *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.efector_id') ? 'is-danger' : '' ?>">
                            <select name="efector_id">
                                <option value="">Seleccionar hospital...</option>
                                <?php
                                $selEf = old('efector_id', $registro->efector_id ?? '');
                                foreach ($efectores as $ef):
                                ?>
                                    <option value="<?= $ef->efector_id ?>" <?= $selEf == $ef->efector_id ? 'selected' : '' ?>>
                                        <?= esc($ef->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.efector_id')): ?>
                        <p class="help is-danger"><?= session('errors.efector_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TIPO ESTABLECIMIENTO -->
                <div class="field">
                    <label class="label" style="color:white;">Tipo de Establecimiento *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.tipo_establecimiento') ? 'is-danger' : '' ?>">
                            <select name="tipo_establecimiento">
                                <option value="">Seleccionar tipo...</option>
                                <?php
                                $selTipoEst = old('tipo_establecimiento', $registro->tipo_establecimiento ?? '');
                                foreach ($tiposEstablecimiento as $te):
                                ?>
                                    <option value="<?= $te ?>" <?= $selTipoEst == $te ? 'selected' : '' ?>><?= $te ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.tipo_establecimiento')): ?>
                        <p class="help is-danger"><?= session('errors.tipo_establecimiento') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TIPO GESTIÓN -->
                <div class="field">
                    <label class="label" style="color:white;">Tipo de Gestión *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.tipo_gestion') ? 'is-danger' : '' ?>">
                            <select name="tipo_gestion">
                                <option value="">Seleccionar gestión...</option>
                                <?php
                                $selTipoGes = old('tipo_gestion', $registro->tipo_gestion ?? '');
                                foreach ($tiposGestion as $tg):
                                ?>
                                    <option value="<?= $tg ?>" <?= $selTipoGes == $tg ? 'selected' : '' ?>><?= $tg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.tipo_gestion')): ?>
                        <p class="help is-danger"><?= session('errors.tipo_gestion') ?></p>
                    <?php endif; ?>
                </div>

                <!-- REGIÓN -->
                <div class="field">
                    <label class="label" style="color:white;">Región *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.region') ? 'is-danger' : '' ?>">
                            <select name="region">
                                <option value="">Seleccionar región...</option>
                                <?php
                                $selReg = old('region', $registro->region ?? '');
                                foreach ($regiones as $reg):
                                ?>
                                    <option value="<?= $reg ?>" <?= $selReg == $reg ? 'selected' : '' ?>><?= $reg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.region')): ?>
                        <p class="help is-danger"><?= session('errors.region') ?></p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Columna derecha -->
            <div class="column is-half">

                <!-- CUIDADOS BÁSICOS ADULTOS -->
                <div class="field">
                    <label class="label" style="color:white;">Cuidados Básicos — Adultos *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.cuidados_basicos_adultos') ? 'is-danger' : '' ?>"
                            type="number" name="cuidados_basicos_adultos" id="inp-adultos"
                            placeholder="0" min="0"
                            value="<?= old('cuidados_basicos_adultos', $registro->cuidados_basicos_adultos ?? 0) ?>">
                        <span class="icon is-left"><i class="fas fa-bed"></i></span>
                    </div>
                    <?php if (session('errors.cuidados_basicos_adultos')): ?>
                        <p class="help is-danger"><?= session('errors.cuidados_basicos_adultos') ?></p>
                    <?php endif; ?>
                </div>

                <!-- CUIDADOS BÁSICOS PEDIÁTRICOS -->
                <div class="field">
                    <label class="label" style="color:white;">Cuidados Básicos — Pediátricos *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.cuidados_basicos_pediatricos') ? 'is-danger' : '' ?>"
                            type="number" name="cuidados_basicos_pediatricos" id="inp-pediatricos"
                            placeholder="0" min="0"
                            value="<?= old('cuidados_basicos_pediatricos', $registro->cuidados_basicos_pediatricos ?? 0) ?>">
                        <span class="icon is-left"><i class="fas fa-child"></i></span>
                    </div>
                    <?php if (session('errors.cuidados_basicos_pediatricos')): ?>
                        <p class="help is-danger"><?= session('errors.cuidados_basicos_pediatricos') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TOTAL CALCULADO (solo lectura) -->
                <div class="field">
                    <label class="label" style="color:white;">Total Cuidados Básicos</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" id="inp-total"
                            readonly
                            style="background:#1e4a75; color:#fff; font-weight:bold; cursor:default;"
                            value="0">
                        <span class="icon is-left"><i class="fas fa-calculator"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">Calculado automáticamente: Adultos + Pediátricos.</p>
                </div>

                <!-- OBSERVACION -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Observación
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <input class="input"
                            type="text" name="observacion"
                            placeholder="Observación..."
                            maxlength="255"
                            value="<?= old('observacion', $registro->observacion ?? '') ?>">
                    </div>
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
                <a href="<?= base_url(route_to('gestion_cama_list')) ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>

    </div>
    </div>
</form>
</section>

<script>
function calcularTotal() {
    var adultos     = parseInt(document.getElementById('inp-adultos').value)     || 0;
    var pediatricos = parseInt(document.getElementById('inp-pediatricos').value) || 0;
    document.getElementById('inp-total').value = adultos + pediatricos;
}
document.getElementById('inp-adultos').addEventListener('input', calcularTotal);
document.getElementById('inp-pediatricos').addEventListener('input', calcularTotal);
calcularTotal();
</script>

<?= $this->endSection() ?>