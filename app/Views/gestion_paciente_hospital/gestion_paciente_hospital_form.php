<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GESTIÓN PACIENTE HOSPITAL <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    $editar = isset($registro);
    $titulo = $editar ? 'Editar Registro' : 'Nuevo Registro';
    $action = $editar ? route_to('gestion_paciente_hospital_update') : route_to('gestion_paciente_hospital_store');
?>

<div class="container is-fluid">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">GESTIÓN PACIENTE HOSPITAL — <?= $titulo ?></h1>
    <hr/>

    <!-- Flash errores -->
    <?php if (session()->has('errors')): ?>
        <div class="notification is-danger is-light">
            <ul>
                <?php foreach (session('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <!-- Botón volver -->
    <div class="columns is-vcentered" style="margin-bottom:1rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('gestion_paciente_hospital_list')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- FORMULARIO -->
    <form action="<?= base_url($action) ?>" method="post">
        <?= csrf_field() ?>
        <?php if ($editar): ?>
            <?= $registro->getCampoOculto() ?>
        <?php endif; ?>

        <div class="box" style="background-color:#fff;">

            <div class="columns is-multiline">

                <!-- Efector -->
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Hospital / Efector <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.efector_id') ? 'is-danger' : '' ?>">
                                <select name="efector_id">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($efectores as $e): ?>
                                        <option value="<?= $e->efector_id ?>"
                                            <?= old('efector_id', $editar ? $registro->efector_id : '') == $e->efector_id ? 'selected' : '' ?>>
                                            <?= esc($e->nombre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.efector_id')): ?>
                            <p class="help is-danger"><?= session('errors.efector_id') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Departamento -->
                <div class="column is-6">
                    <div class="field">
                        <label class="label">Departamento <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.departamento_id') ? 'is-danger' : '' ?>">
                                <select name="departamento_id">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($departamentos as $d): ?>
                                        <option value="<?= $d->departamento_id ?>"
                                            <?= old('departamento_id', $editar ? $registro->departamento_id : '') == $d->departamento_id ? 'selected' : '' ?>>
                                            <?= esc($d->nombre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.departamento_id')): ?>
                            <p class="help is-danger"><?= session('errors.departamento_id') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Zona -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Zona <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.zona') ? 'is-danger' : '' ?>">
                                <select name="zona">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($zonas as $z): ?>
                                        <option value="<?= $z ?>"
                                            <?= old('zona', $editar ? $registro->zona : '') == $z ? 'selected' : '' ?>>
                                            <?= $z ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.zona')): ?>
                            <p class="help is-danger"><?= session('errors.zona') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cuidados -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Cuidados <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.cuidados') ? 'is-danger' : '' ?>">
                                <select name="cuidados">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($cuidados as $c): ?>
                                        <option value="<?= $c ?>"
                                            <?= old('cuidados', $editar ? $registro->cuidados : '') == $c ? 'selected' : '' ?>>
                                            <?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.cuidados')): ?>
                            <p class="help is-danger"><?= session('errors.cuidados') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Nivel de Riesgo -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Nivel de Riesgo <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.nivel_riesgo') ? 'is-danger' : '' ?>">
                                <select name="nivel_riesgo">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($nivelesRiesgo as $n): ?>
                                        <option value="<?= $n ?>"
                                            <?= old('nivel_riesgo', $editar ? $registro->nivel_riesgo : '') == $n ? 'selected' : '' ?>>
                                            <?= $n ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.nivel_riesgo')): ?>
                            <p class="help is-danger"><?= session('errors.nivel_riesgo') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Proceso -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Proceso <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.proceso') ? 'is-danger' : '' ?>">
                                <select name="proceso">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($procesos as $p): ?>
                                        <option value="<?= $p ?>"
                                            <?= old('proceso', $editar ? $registro->proceso : '') == $p ? 'selected' : '' ?>>
                                            <?= $p ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.proceso')): ?>
                            <p class="help is-danger"><?= session('errors.proceso') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tipo de Cama -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Tipo de Cama <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <div class="select is-fullwidth <?= session('errors.tipo_cama') ? 'is-danger' : '' ?>">
                                <select name="tipo_cama">
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($tiposCama as $t): ?>
                                        <option value="<?= $t ?>"
                                            <?= old('tipo_cama', $editar ? $registro->tipo_cama : '') == $t ? 'selected' : '' ?>>
                                            <?= $t ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (session('errors.tipo_cama')): ?>
                            <p class="help is-danger"><?= session('errors.tipo_cama') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tiempo de Estancia -->
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Tiempo de Estancia <span class="has-text-danger">*</span></label>
                        <div class="control">
                            <input type="text"
                                   name="tiempo_estancia"
                                   class="input <?= session('errors.tiempo_estancia') ? 'is-danger' : '' ?>"
                                   placeholder="Ej: 6 HS, DE 7 A 15 DÍAS..."
                                   maxlength="50"
                                   value="<?= old('tiempo_estancia', $editar ? esc($registro->tiempo_estancia) : '') ?>">
                        </div>
                        <?php if (session('errors.tiempo_estancia')): ?>
                            <p class="help is-danger"><?= session('errors.tiempo_estancia') ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Estado (solo en edición) -->
                <?php if ($editar): ?>
                <div class="column is-4">
                    <div class="field">
                        <label class="label">Estado</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="estado">
                                    <option value="activo"      <?= $registro->estado === 'activo'      ? 'selected' : '' ?>>Activo</option>
                                    <option value="desactivado" <?= $registro->estado === 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Observación -->
                <div class="column is-12">
                    <div class="field">
                        <label class="label">Observación</label>
                        <div class="control">
                            <textarea name="observacion"
                                      class="textarea"
                                      rows="2"
                                      maxlength="255"><?= old('observacion', $editar ? esc($registro->observacion) : '') ?></textarea>
                        </div>
                    </div>
                </div>

            </div><!-- /columns -->

            <!-- Botones -->
            <div class="columns is-vcentered" style="margin-top:1rem;">
                <div class="column is-narrow">
                    <a class="button is-light" href="<?= base_url(route_to('gestion_paciente_hospital_list')) ?>">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>Cancelar</span>
                    </a>
                </div>
                <div class="column is-narrow">
                    <button type="submit" class="button is-primary">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span><?= $editar ? 'Actualizar' : 'Guardar' ?></span>
                    </button>
                </div>
            </div>

        </div><!-- /box -->

    </form>

</div>
</div>

<?= $this->endSection() ?>