<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> CHAT BOT <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Registro Chat Bot' : 'Nuevo Registro Chat Bot' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('chat_bot_update')) : base_url(route_to('chat_bot_store')) ?>" method="POST">
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
                            <select name="mes" id="sel-mes">
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

                <!-- FECHA -->
                <div class="field">
                    <label class="label" style="color:white;">
                        Fecha
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(opcional — puede quedar vacía)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input"
                            type="date" name="fecha" id="inp-fecha"
                            value="<?= old('fecha', $registro->fecha ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-calendar-day"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">Al seleccionar el mes se completa automáticamente.</p>
                    <?php if (session('errors.fecha')): ?>
                        <p class="help is-danger" style="background:#f8d7da; padding:4px 8px; border-radius:4px; margin-top:4px;">
                            ⚠️ <?= session('errors.fecha') ?>
                        </p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Columna derecha -->
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
                                        <?= esc($ef->nombre) ?> — <?= esc($ef->region ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.efector_id')): ?>
                        <p class="help is-danger"><?= session('errors.efector_id') ?></p>
                    <?php endif; ?>
                </div>

                <!-- TURNOS OTORGADOS -->
                <div class="field">
                    <label class="label" style="color:white;">Turnos Otorgados *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.turnos_otorgados') ? 'is-danger' : '' ?>"
                            type="number" name="turnos_otorgados"
                            placeholder="0" min="0"
                            value="<?= old('turnos_otorgados', $registro->turnos_otorgados ?? 0) ?>">
                        <span class="icon is-left"><i class="fas fa-robot"></i></span>
                    </div>
                    <?php if (session('errors.turnos_otorgados')): ?>
                        <p class="help is-danger"><?= session('errors.turnos_otorgados') ?></p>
                    <?php endif; ?>
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
                <a href="<?= base_url(route_to('chat_bot_list')) ?>" class="button is-warning">
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
// Al cambiar el mes, pre-completar la fecha con el primer día del mes del ejercicio
document.getElementById('sel-mes').addEventListener('change', function () {
    var ejercicio = document.querySelector('input[name="ejercicio"]').value;
    var mes       = this.value;
    if (!ejercicio || !mes) return;

    var mesesMap = {
        'ENERO':'01','FEBRERO':'02','MARZO':'03','ABRIL':'04',
        'MAYO':'05','JUNIO':'06','JULIO':'07','AGOSTO':'08',
        'SEPTIEMBRE':'09','OCTUBRE':'10','NOVIEMBRE':'11','DICIEMBRE':'12'
    };

    var numMes = mesesMap[mes];
    if (numMes) {
        var inpFecha = document.getElementById('inp-fecha');
        if (!inpFecha.value) {
            inpFecha.value = ejercicio + '-' + numMes + '-01';
        }
    }
});
</script>

<?= $this->endSection() ?>