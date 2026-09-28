<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> SALUD MENTAL - CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.config-bar { background:#13304d; border-radius:8px; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:1.2rem; align-items:flex-end; }
.config-bar .field { margin-bottom:0; }
.config-bar label { color:#fff !important; font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px; }
.config-bar input, .config-bar select { background:#fff; border:none; border-radius:4px; height:2.2rem; padding:0 0.6rem; font-size:0.9rem; min-width:180px; }

.smc-seccion { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:1.2rem 1.5rem; margin-bottom:1.2rem; }
.smc-seccion h3 { color:#13304d; font-size:1rem; font-weight:700; margin-bottom:1rem; border-bottom:2px solid #00b4a0; padding-bottom:6px; }
.smc-campos { display:flex; flex-wrap:wrap; gap:1rem; }
.smc-campo { display:flex; flex-direction:column; gap:4px; min-width:170px; }
.smc-campo label { font-size:0.82rem; font-weight:600; color:#5a6a7e; }
.smc-campo input[type="number"] { border:1px solid #b8d0e8; border-radius:4px; padding:8px 10px; font-size:0.95rem; }
.smc-campo input:focus { border-color:#13304d; outline:none; box-shadow:0 0 0 2px rgba(19,48,77,0.15); }

.btn-guardar { background:#13304d; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; }
.btn-guardar:hover { background:#1a4a70; }
.btn-volver { background:#e8a800; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-volver:hover { background:#c99200; color:#fff; }
.acciones-bar { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-brain"></i></span>
    <?= isset($saludMentalCamas) ? 'Editar Camas de Salud Mental' : 'Nueva Cama de Salud Mental' ?>
</h2>

<form action="<?= base_url(route_to(isset($saludMentalCamas) ? 'salud_mental_camas_update' : 'salud_mental_camas_store')) ?>" method="POST" id="formSaludMentalCamas">
<?= csrf_field() ?>

<?php if (isset($saludMentalCamas)): ?>
    <!-- Spoofeamos el método igual que Guardia::getCampoOculto() / CapacidadCamas -->
    <?= $saludMentalCamas->getCampoOculto() ?>
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= (isset($saludMentalCamas) && $saludMentalCamas->efector_id == $e->efector_id) ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Modalidad *</label>
        <select name="modalidad" required>
            <?php foreach ($modalidades as $m): ?>
                <option value="<?= $m ?>" <?= (isset($saludMentalCamas) && $saludMentalCamas->modalidad == $m) ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Tipo *</label>
        <select name="tipo" required>
            <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>" <?= (isset($saludMentalCamas) && $saludMentalCamas->tipo == $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php
$campos = [
    'cb_adultos'     => 'Cama Básica Adultos',
    'cb_pediatricos' => 'Cama Básica Pediátricos',
    'total_basicas'  => 'Total Básicas',
];
?>

<div class="smc-seccion">
    <h3>Camas Básicas</h3>
    <div class="smc-campos">
        <?php foreach ($campos as $name => $label): ?>
            <div class="smc-campo">
                <label><?= esc($label) ?></label>
                <input type="number" name="<?= $name ?>" min="0"
                       value="<?= isset($saludMentalCamas) ? esc($saludMentalCamas->$name) : old($name, 0) ?>">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($saludMentalCamas) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('salud_mental_camas_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?= $this->endSection() ?>