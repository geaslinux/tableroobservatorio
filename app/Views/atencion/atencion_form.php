<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> ATENCIÓN <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    Nueva Atención
</h2>

<form action="<?= base_url(route_to('atencion_store')) ?>" method="POST">
    <?= csrf_field() ?>

    <div class="container is-max-widescreen">
    <div class="notification" style="background-color: #13304d;">

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
                        value="<?= old('ejercicio') ?? date('Y') ?>">
                    <span class="icon is-left"><i class="fas fa-calendar"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">
                    <span class="icon is-small"><i class="fas fa-info-circle"></i></span>
                    Solo se permiten ejercicios hasta el año actual (<?= date('Y') ?>).
                </p>
                <?php if (isset($errors['ejercicio'])): ?>
                    <p class="help is-danger" style="font-weight:bold; background:#fff0f0; padding:6px 10px; border-radius:4px; border-left:4px solid #ff3860;">
                        <span class="icon is-small"><i class="fas fa-exclamation-circle"></i></span>
                        <?= $errors['ejercicio'] ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- MES -->
            <div class="field">
                <label class="label" style="color:white;">Mes *</label>
                <div class="control">
                    <div class="select is-fullwidth <?= isset($errors['mes']) ? 'is-danger' : '' ?>">
                        <select name="mes">
                            <option value="">Seleccionar mes...</option>
                            <?php
                            $mesesOpciones = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
                                             'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
                            $mesActual = old('mes') ?? '';
                            foreach ($mesesOpciones as $m):
                            ?>
                                <option value="<?= $m ?>" <?= $mesActual == $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php if (isset($errors['mes'])): ?>
                    <p class="help is-danger"><?= $errors['mes'] ?></p>
                <?php endif; ?>
            </div>

            <!-- ATENCIONES BASE/USE -->
            <div class="field">
                <label class="label" style="color:white;">Atenciones en Base/USE *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['atenciones_base']) ? 'is-danger' : '' ?>"
                        type="number"
                        id="atenciones_base"
                        name="atenciones_base"
                        placeholder="0"
                        min="0"
                        value="<?= old('atenciones_base') ?? 0 ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-ambulance"></i></span>
                </div>
                <?php if (isset($errors['atenciones_base'])): ?>
                    <p class="help is-danger"><?= $errors['atenciones_base'] ?></p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Columna 2 -->
        <div class="column is-half">

            <!-- ASISTIDOS COBERTURAS -->
            <div class="field">
                <label class="label" style="color:white;">Asistidos en Coberturas *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['asistidos_coberturas']) ? 'is-danger' : '' ?>"
                        type="number"
                        id="asistidos_coberturas"
                        name="asistidos_coberturas"
                        placeholder="0"
                        min="0"
                        value="<?= old('asistidos_coberturas') ?? 0 ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-user-injured"></i></span>
                </div>
                <?php if (isset($errors['asistidos_coberturas'])): ?>
                    <p class="help is-danger"><?= $errors['asistidos_coberturas'] ?></p>
                <?php endif; ?>
            </div>

            <!-- CANTIDAD COBERTURAS -->
            <div class="field">
                <label class="label" style="color:white;">Cantidad de Coberturas en Eventos</label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="number"
                        name="cantidad_coberturas"
                        placeholder="0"
                        min="0"
                        value="<?= old('cantidad_coberturas') ?? 0 ?>">
                    <span class="icon is-left"><i class="fas fa-calendar-check"></i></span>
                </div>
            </div>

            <!-- TOTAL (calculado automáticamente) -->
            <div class="field">
                <label class="label" style="color:white;">
                    Total
                    <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">
                        (Atenciones Base + Asistidos Coberturas)
                    </span>
                </label>
                <div class="control has-icons-left">
                    <input class="input"
                        type="number"
                        id="total"
                        name="total"
                        placeholder="0"
                        min="0"
                        value="<?= old('total') ?? 0 ?>"
                        style="background:#e8f5e9; font-weight:bold; color:#1a1a1a;"
                        readonly>
                    <span class="icon is-left"><i class="fas fa-calculator"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">
                    Se calcula automáticamente al ingresar los valores anteriores.
                </p>
            </div>

            <!-- ESTADO -->
            <!-- ESTADO (bloqueado) -->
<div class="field">
    <label class="label" style="color:white;">Estado</label>
    <div class="control">
        <div class="select is-fullwidth">
            <select name="estado" disabled style="background:#e5e5e5; cursor:not-allowed;">
                <option value="activo" selected>Activo</option>
                <option value="desactivado">Desactivado</option>
            </select>
        </div>
    </div>
</div>

<!-- input oculto para que el valor viaje en el POST -->
<input type="hidden" name="estado" value="<?= old('estado') ?? 'activo' ?>">

        </div>
    </div>

    <hr>
    <div class="field is-grouped is-grouped-centered">
        <div class="control">
            <button class="button is-warning" type="submit">
                <span class="icon"><i class="fas fa-save"></i></span>
                <span>Guardar</span>
            </button>
        </div>
        <div class="control">
            <a href="<?= base_url(route_to('atencion_list')) ?>" class="button is-warning">
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
    const atenciones = parseInt(document.getElementById('atenciones_base').value)      || 0;
    const asistidos  = parseInt(document.getElementById('asistidos_coberturas').value) || 0;
    document.getElementById('total').value = atenciones + asistidos;
}
// Calcular al cargar si hay valores previos (ej: error de validación con withInput)
document.addEventListener('DOMContentLoaded', calcularTotal);
</script>

<?= $this->endSection() ?>