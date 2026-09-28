<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> OPERATIVO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Registro' : 'Nuevo Registro de Operativo' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('cantidad_operativo_update')) : base_url(route_to('cantidad_operativo_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($registro)): ?>
        <?= $registro->getCampoOculto() ?>
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

        <!-- Columna 1 -->
        <div class="column is-half">

            <!-- EJERCICIO -->
            <div class="field">
                <label class="label" style="color:white;">Ejercicio *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['ejercicio']) ? 'is-danger' : '' ?>"
                        type="number"
                        name="ejercicio"
                        placeholder="<?= date('Y') ?>"
                        max="<?= date('Y') ?>"
                        value="<?= old('ejercicio', $registro->ejercicio ?? date('Y')) ?>">
                    <span class="icon is-left"><i class="fas fa-calendar"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">
                    <span class="icon is-small"><i class="fas fa-info-circle"></i></span>
                    Solo se permiten ejercicios hasta el año actual (<?= date('Y') ?>).
                </p>
                <?php if (isset($errors['ejercicio'])): ?>
                    <p class="help is-danger" style="font-weight:bold; background:#fff0f0; padding:6px 10px; border-radius:4px; border-left:4px solid #ff3860;">
                        <?= $errors['ejercicio'] ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- OPERATIVO -->
            <div class="field">
                <label class="label" style="color:white;">Operativo *</label>
                <div class="control">
                    <div class="select is-fullwidth <?= isset($errors['operativo_id']) ? 'is-danger' : '' ?>">
                        <select name="operativo_id" id="operativo_id" onchange="mostrarTipo(this)">
                            <option value="">Seleccionar operativo...</option>
                            <?php
                            $selOp = old('operativo_id', $registro->operativo_id ?? '');
                            foreach ($operativos as $op):
                            ?>
                                <option value="<?= $op->operativo_id ?>"
                                    data-tipo="<?= esc($op->tipo_nombre ?? '') ?>"
                                    <?= $selOp == $op->operativo_id ? 'selected' : '' ?>>
                                    <?= esc($op->nombre) ?>
                                    <?php if (!empty($op->tipo_nombre)): ?>
                                        — <?= esc($op->tipo_nombre) ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php if (isset($errors['operativo_id'])): ?>
                    <p class="help is-danger"><?= $errors['operativo_id'] ?></p>
                <?php endif; ?>
            </div>

            <!-- TIPO (solo lectura, se completa automático) -->
            <div class="field" id="campo-tipo" style="<?= empty($registro->tipo_nombre ?? '') ? 'display:none;' : '' ?>">
                <label class="label" style="color:white;">Tipo</label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="text"
                        id="tipo_display"
                        placeholder="Sin tipo"
                        value="<?= esc($registro->tipo_nombre ?? '') ?>"
                        readonly
                        style="background:#e8f5e9; color:#1a1a1a;">
                    <span class="icon is-left"><i class="fas fa-tag"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">Se completa automáticamente según el operativo seleccionado.</p>
            </div>

        </div>

        <!-- Columna 2 -->
        <div class="column is-half">

            <!-- VÍA PÚBLICA -->
            <div class="field">
                <label class="label" style="color:white;">Vía Pública</label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="number"
                        id="via_publica"
                        name="via_publica"
                        placeholder="0"
                        min="0"
                        value="<?= old('via_publica', $registro->via_publica ?? 0) ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-road"></i></span>
                </div>
            </div>

            <!-- VÍA HOSPITALARIA -->
            <div class="field">
                <label class="label" style="color:white;">Vía Hospitalaria</label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="number"
                        id="via_hospitalaria"
                        name="via_hospitalaria"
                        placeholder="0"
                        min="0"
                        value="<?= old('via_hospitalaria', $registro->via_hospitalaria ?? 0) ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-hospital"></i></span>
                </div>
            </div>

            <!-- TOTAL (calculado) -->
            <div class="field">
                <label class="label" style="color:white;">
                    Total
                    <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">
                        (Vía Pública + Vía Hospitalaria)
                    </span>
                </label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="number"
                        id="total"
                        name="total"
                        placeholder="0"
                        min="0"
                        value="<?= old('total', $registro->total ?? 0) ?>"
                        style="background:#e8f5e9; font-weight:bold; color:#1a1a1a;"
                        readonly>
                    <span class="icon is-left"><i class="fas fa-calculator"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">Se calcula automáticamente.</p>
            </div>

            <?php if (isset($registro)): ?>
            <!-- ESTADO (solo visible al editar) -->
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
            <a href="<?= base_url(route_to('cantidad_operativo_list')) ?>" class="button is-warning">
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
    const pub  = parseInt(document.getElementById('via_publica').value)      || 0;
    const hosp = parseInt(document.getElementById('via_hospitalaria').value) || 0;
    document.getElementById('total').value = pub + hosp;
}

function mostrarTipo(sel) {
    const tipo       = sel.options[sel.selectedIndex].getAttribute('data-tipo') || '';
    const campoTipo  = document.getElementById('campo-tipo');
    const inputTipo  = document.getElementById('tipo_display');

    if (tipo && tipo.trim() !== '') {
        inputTipo.value     = tipo;
        campoTipo.style.display = '';
    } else {
        inputTipo.value     = '';
        campoTipo.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    calcularTotal();
    // Disparar para mostrar tipo si hay valor pre-seleccionado (edición o withInput)
    const sel = document.getElementById('operativo_id');
    if (sel) mostrarTipo(sel);
});
</script>

<?= $this->endSection() ?>