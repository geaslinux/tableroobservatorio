<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($efector) ? 'EDITAR EFECTOR' : 'NUEVO EFECTOR' ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($efector) ? 'Editar Efector' : 'Nuevo Efector' ?>
</h2>

<form action="<?= isset($efector) ? base_url(route_to('efector_update')) : base_url(route_to('efector_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($efector)): ?>
        <?= $efector->getCampoOculto() ?>
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
            <div class="column is-half">

                <!-- NOMBRE -->
                <div class="field">
                    <label class="label" style="color:white;">Nombre *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.nombre') ? 'is-danger' : '' ?>"
                            type="text" name="nombre"
                            placeholder="Ej: PABLO SORIA"
                            maxlength="150"
                            value="<?= old('nombre', $efector->nombre ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-hospital"></i></span>
                    </div>
                    <?php if (session('errors.nombre')): ?>
                        <p class="help is-danger"><?= session('errors.nombre') ?></p>
                    <?php endif; ?>
                </div>

                <!-- NIVEL COMPLEJIDAD -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Nivel de Complejidad
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="nivel_complejidad">
                                <option value="">— Sin especificar —</option>
                                <?php
                                $niveles = ['I - BAJO RIESGO', 'II - MEDIANO RIESGO', 'III - ALTO RIESGO'];
                                $selNivel = old('nivel_complejidad', $efector->nivel_complejidad ?? '');
                                foreach ($niveles as $n):
                                ?>
                                    <option value="<?= $n ?>" <?= $selNivel == $n ? 'selected' : '' ?>><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- DEPARTAMENTO -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Departamento
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input"
                            type="text" name="departamento"
                            placeholder="Ej: DR. MANUEL BELGRANO"
                            maxlength="100"
                            value="<?= old('departamento', $efector->departamento ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-map"></i></span>
                    </div>
                </div>

            </div>
            <div class="column is-half">

                <!-- UBICACION -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Ubicación
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input"
                            type="text" name="ubicacion"
                            placeholder="Ej: SAN SALVADOR DE JUJUY"
                            maxlength="100"
                            value="<?= old('ubicacion', $efector->ubicacion ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-map-marker-alt"></i></span>
                    </div>
                </div>

                <!-- REGION -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Región
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional)</span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="region">
                                <option value="">— Sin especificar —</option>
                                <?php
                                $regs    = ['CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA'];
                                $selReg  = old('region', $efector->region ?? '');
                                foreach ($regs as $reg):
                                ?>
                                    <option value="<?= $reg ?>" <?= $selReg == $reg ? 'selected' : '' ?>><?= $reg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <hr>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($efector) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('efector_list')) ?>" class="button is-warning">
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