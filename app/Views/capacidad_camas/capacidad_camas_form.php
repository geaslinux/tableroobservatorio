<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> CAPACIDAD DE CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.config-bar { background:#13304d; border-radius:8px; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:1.2rem; align-items:flex-end; }
.config-bar .field { margin-bottom:0; }
.config-bar label { color:#fff !important; font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px; }
.config-bar input, .config-bar select { background:#fff; border:none; border-radius:4px; height:2.2rem; padding:0 0.6rem; font-size:0.9rem; min-width:180px; }

.cc-seccion { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:1.2rem 1.5rem; margin-bottom:1.2rem; }
.cc-seccion h3 { color:#13304d; font-size:1rem; font-weight:700; margin-bottom:1rem; border-bottom:2px solid #00b4a0; padding-bottom:6px; }
.cc-campos { display:flex; flex-wrap:wrap; gap:1rem; }
.cc-campo { display:flex; flex-direction:column; gap:4px; min-width:170px; }
.cc-campo label { font-size:0.82rem; font-weight:600; color:#5a6a7e; }
.cc-campo input[type="number"] { border:1px solid #b8d0e8; border-radius:4px; padding:8px 10px; font-size:0.95rem; }
.cc-campo input:focus { border-color:#13304d; outline:none; box-shadow:0 0 0 2px rgba(19,48,77,0.15); }

.btn-guardar { background:#13304d; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; }
.btn-guardar:hover { background:#1a4a70; }
.btn-volver { background:#e8a800; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-volver:hover { background:#c99200; color:#fff; }
.acciones-bar { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-bed"></i></span>
    <?= isset($capacidadCamas) ? 'Editar Capacidad de Camas' : 'Nueva Capacidad de Camas' ?>
</h2>

<form action="<?= base_url(route_to(isset($capacidadCamas) ? 'capacidad_camas_update' : 'capacidad_camas_store')) ?>" method="POST" id="formCapacidadCamas">
<?= csrf_field() ?>

<?php if (isset($capacidadCamas)): ?>
    <!-- Spoofeamos el método igual que Guardia::getCampoOculto() / Efector -->
    <?= $capacidadCamas->getCampoOculto() ?>
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= (isset($capacidadCamas) && $capacidadCamas->efector_id == $e->efector_id) ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Tipo *</label>
        <select name="tipo" required>
            <?php foreach ($tipos as $t): ?>
                <option value="<?= $t ?>" <?= (isset($capacidadCamas) && $capacidadCamas->tipo == $t) ? 'selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php
// Definición de secciones/campos para no repetir markup
$secciones = [
    'UTI' => [
        'uti_adulto'          => 'Adulto',
        'uti_coronario'       => 'Coronario',
        'uti_pediatrico'      => 'Pediátrico',
        'uti_neonatal'        => 'Neonatal',
        'total_uti'           => 'Total UTI',
        'total_uti_publicos'  => 'Total UTI Públicos',
    ],
    'UTIN' => [
        'utin_adulto'          => 'Adulto',
        'utin_pediatrico'      => 'Pediátrico',
        'utin_neonatal'        => 'Neonatal',
        'total_utin'           => 'Total UTIN',
        'total_utin_publicos'  => 'Total UTIN Públicos',
    ],
    'Camas Básicas' => [
        'cb_adultos'                 => 'Adultos',
        'cb_pediatricos'             => 'Pediátricos',
        'cb_neonatales'              => 'Neonatales',
        'total_basicas'              => 'Total Básicas',
        'total_basicas_publicos'     => 'Total Básicas Públicos',
    ],
    'Camas Disponibles' => [
        'camas_disponibles'                       => 'Camas Disponibles',
        'camas_disponibles_publicas'                => 'Camas Disponibles Públicas',
        'camas_disponibles_publicas_salud_mental'  => 'Camas Disponibles Públicas Salud Mental',
    ],
];
?>

<?php foreach ($secciones as $titulo => $campos): ?>
    <div class="cc-seccion">
        <h3><?= esc($titulo) ?></h3>
        <div class="cc-campos">
            <?php foreach ($campos as $name => $label): ?>
                <div class="cc-campo">
                    <label><?= esc($label) ?></label>
                    <input type="number" name="<?= $name ?>" min="0"
                           value="<?= isset($capacidadCamas) ? esc($capacidadCamas->$name) : old($name, 0) ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($capacidadCamas) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('capacidad_camas_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?= $this->endSection() ?>