<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> INTERNACION DOMICILIARIA <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    INTERNACION DOMICILIARIA
</h2>

<form action="<?= base_url(route_to('identificacion_store')) ?>" method="POST">
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

            <!-- DEPARTAMENTO -->
            <div class="field">
                <label class="label" style="color:white;">Departamento *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['departamento']) ? 'is-danger' : '' ?>"
                        type="text"
                        name="departamento"
                        placeholder="DEPARTAMENTO"
                        style="text-transform:uppercase;"
                        oninput="this.value=this.value.toUpperCase()"
                        value="<?= old('departamento') ?? '' ?>">
                    <span class="icon is-left"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <p class="help" style="color:#ffe08a;">
                    <span class="icon is-small"><i class="fas fa-info-circle"></i></span>
                    Ingresar en MAYÚSCULAS.
                </p>
                <?php if (isset($errors['departamento'])): ?>
                    <p class="help is-danger" style="font-weight:bold; background:#fff0f0; padding:6px 10px; border-radius:4px; border-left:4px solid #ff3860;">
                        <span class="icon is-small"><i class="fas fa-exclamation-circle"></i></span>
                        <?= $errors['departamento'] ?>
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Columna 2 -->
        <div class="column is-half">

            <!-- ADULTO -->
            <div class="field">
                <label class="label" style="color:white;">Adulto *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['adulto']) ? 'is-danger' : '' ?>"
                        type="number"
                        id="adulto"
                        name="adulto"
                        placeholder="0"
                        min="0"
                        value="<?= old('adulto') ?? 0 ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-user"></i></span>
                </div>
                <?php if (isset($errors['adulto'])): ?>
                    <p class="help is-danger"><?= $errors['adulto'] ?></p>
                <?php endif; ?>
            </div>

            <!-- PEDIÁTRICO -->
            <div class="field">
                <label class="label" style="color:white;">Pediátrico *</label>
                <div class="control has-icons-left">
                    <input class="input <?= isset($errors['pediatrico']) ? 'is-danger' : '' ?>"
                        type="number"
                        id="pediatrico"
                        name="pediatrico"
                        placeholder="0"
                        min="0"
                        value="<?= old('pediatrico') ?? 0 ?>"
                        oninput="calcularTotal()">
                    <span class="icon is-left"><i class="fas fa-child"></i></span>
                </div>
                <?php if (isset($errors['pediatrico'])): ?>
                    <p class="help is-danger"><?= $errors['pediatrico'] ?></p>
                <?php endif; ?>
            </div>

            <!-- TOTAL (automático) -->
            <div class="field">
                <label class="label" style="color:white;">
                    Total
                    <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">
                        (Adulto + Pediátrico)
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
            <a href="<?= base_url(route_to('identificacion_list')) ?>" class="button is-warning">
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
    const adulto     = parseInt(document.getElementById('adulto').value)     || 0;
    const pediatrico = parseInt(document.getElementById('pediatrico').value) || 0;
    document.getElementById('total').value = adulto + pediatrico;
}
document.addEventListener('DOMContentLoaded', calcularTotal);
</script>

<?= $this->endSection() ?>