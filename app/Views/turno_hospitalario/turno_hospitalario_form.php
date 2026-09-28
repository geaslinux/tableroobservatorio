<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> TURNO HOSPITALARIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Turno Hospitalario' : 'Nuevo Turno Hospitalario' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('turno_hospitalario_update')) : base_url(route_to('turno_hospitalario_store')) ?>" method="POST">
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

        <!-- EJERCICIO + MES + EFECTOR -->
        <div class="columns">
            <div class="column is-2">
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
                    <p class="help" style="color:#ffe08a;">Hasta <?= date('Y') ?>.</p>
                    <?php if (session('errors.ejercicio')): ?>
                        <p class="help is-danger"><?= session('errors.ejercicio') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column is-3">
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
                                    <option value="<?= $m ?>" <?= $selMes==$m?'selected':'' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.mes')): ?>
                        <p class="help is-danger"><?= session('errors.mes') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Efector *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.efector_id') ? 'is-danger' : '' ?>">
                            <select name="efector_id">
                                <option value="">Seleccionar efector...</option>
                                <?php
                                $selEf = old('efector_id', $registro->efector_id ?? '');
                                foreach ($efectores as $ef):
                                ?>
                                    <option value="<?= $ef->efector_id ?>" <?= $selEf==$ef->efector_id?'selected':'' ?>>
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
            </div>

            <?php if (isset($registro)): ?>
            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="estado">
                                <option value="activo"      <?= ($registro->estado??'activo')=='activo'?'selected':'' ?>>Activo</option>
                                <option value="desactivado" <?= ($registro->estado??'')=='desactivado'?'selected':'' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="estado" value="activo">
            <?php endif; ?>
        </div>

        <hr>

        <!-- CAMPOS NUMÉRICOS EN GRILLA -->
        <div class="columns">

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Turnos Atendidos</label>
                    <div class="control has-icons-left">
                        <input class="input" type="number" id="f_turnos_atendidos" name="turnos_atendidos"
                            placeholder="0" min="0"
                            value="<?= old('turnos_atendidos', $registro->turnos_atendidos ?? 0) ?>"
                            oninput="calcularTotal()">
                        <span class="icon is-left"><i class="fas fa-check-circle"></i></span>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Ausentes</label>
                    <div class="control has-icons-left">
                        <input class="input" type="number" id="f_ausentes" name="ausentes"
                            placeholder="0" min="0"
                            value="<?= old('ausentes', $registro->ausentes ?? 0) ?>"
                            oninput="calcularTotal()">
                        <span class="icon is-left"><i class="fas fa-user-times"></i></span>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Cancelados</label>
                    <div class="control has-icons-left">
                        <input class="input" type="number" id="f_cancelados" name="cancelados"
                            placeholder="0" min="0"
                            value="<?= old('cancelados', $registro->cancelados ?? 0) ?>"
                            oninput="calcularTotal()">
                        <span class="icon is-left"><i class="fas fa-ban"></i></span>
                    </div>
                </div>
            </div>

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Sin Codificar</label>
                    <div class="control has-icons-left">
                        <input class="input" type="number" id="f_sin_codificar" name="sin_codificar"
                            placeholder="0" min="0"
                            value="<?= old('sin_codificar', $registro->sin_codificar ?? 0) ?>"
                            oninput="calcularTotal()">
                        <span class="icon is-left"><i class="fas fa-question-circle"></i></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- TOTAL + % -->
        <div class="columns">
            <div class="column is-3">
                <div class="field">
                    <label class="label" style="color:white;">
                        Total Otorgados
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(suma automática)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input" type="number" id="total_otorgados" name="total_otorgados"
                            value="<?= old('total_otorgados', $registro->total_otorgados ?? 0) ?>"
                            style="background:#e8f5e9; font-weight:bold; color:#1a1a1a;"
                            readonly>
                        <span class="icon is-left"><i class="fas fa-calculator"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">
                        % Atendidos
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(calculado)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" id="pct_atendidos"
                            style="background:#e8f0fe; font-weight:bold; color:#1a1a1a;"
                            readonly>
                        <span class="icon is-left"><i class="fas fa-percent"></i></span>
                    </div>
                </div>
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
                <a href="<?= base_url(route_to('turno_hospitalario_list')) ?>" class="button is-warning">
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
    var atendidos    = parseInt(document.getElementById('f_turnos_atendidos').value) || 0;
    var ausentes     = parseInt(document.getElementById('f_ausentes').value)         || 0;
    var cancelados   = parseInt(document.getElementById('f_cancelados').value)       || 0;
    var sinCodificar = parseInt(document.getElementById('f_sin_codificar').value)    || 0;

    var total = atendidos + ausentes + cancelados + sinCodificar;
    document.getElementById('total_otorgados').value = total;

    var pct = total > 0 ? ((atendidos / total) * 100).toFixed(1) : '0.0';
    document.getElementById('pct_atendidos').value = pct + '%';
}

document.addEventListener('DOMContentLoaded', calcularTotal);
</script>

<?= $this->endSection() ?>