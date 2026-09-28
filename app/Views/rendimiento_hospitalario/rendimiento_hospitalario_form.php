<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RENDIMIENTO HOSPITALARIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.config-bar { background:#13304d; border-radius:8px; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:1.2rem; align-items:flex-end; }
.config-bar .field { margin-bottom:0; }
.config-bar label { color:#fff !important; font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px; }
.config-bar input, .config-bar select { background:#fff; border:none; border-radius:4px; height:2.2rem; padding:0 0.6rem; font-size:0.9rem; min-width:180px; }

.rh-seccion { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:1.2rem 1.5rem; margin-bottom:1.2rem; }
.rh-seccion h3 { color:#13304d; font-size:1rem; font-weight:700; margin-bottom:1rem; border-bottom:2px solid #00b4a0; padding-bottom:6px; }
.rh-campos { display:flex; flex-wrap:wrap; gap:1rem; }
.rh-campo { display:flex; flex-direction:column; gap:4px; min-width:170px; }
.rh-campo label { font-size:0.82rem; font-weight:600; color:#5a6a7e; }
.rh-campo input { border:1px solid #b8d0e8; border-radius:4px; padding:8px 10px; font-size:0.95rem; }
.rh-campo input:focus { border-color:#13304d; outline:none; box-shadow:0 0 0 2px rgba(19,48,77,0.15); }

.btn-guardar { background:#13304d; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; }
.btn-guardar:hover { background:#1a4a70; }
.btn-volver { background:#e8a800; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-volver:hover { background:#c99200; color:#fff; }
.acciones-bar { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-chart-line"></i></span>
    <?= isset($rendimientoHospitalario) ? 'Editar Rendimiento Hospitalario' : 'Nuevo Rendimiento Hospitalario' ?>
</h2>

<form action="<?= base_url(route_to(isset($rendimientoHospitalario) ? 'rendimiento_hospitalario_update' : 'rendimiento_hospitalario_store')) ?>" method="POST" id="formRendimientoHospitalario">
<?= csrf_field() ?>

<?php if (isset($rendimientoHospitalario)): ?>
    <!-- Spoofeamos el método igual que CapacidadCamas / Guardia / Efector -->
    <?= $rendimientoHospitalario->getCampoOculto() ?>
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= (isset($rendimientoHospitalario) && $rendimientoHospitalario->efector_id == $e->efector_id) ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" min="2000" max="2099"
               value="<?= isset($rendimientoHospitalario) ? esc($rendimientoHospitalario->ejercicio) : old('ejercicio', date('Y')) ?>" required>
    </div>

    <div class="field">
        <label>Semestre *</label>
        <select name="semestre" required>
            <?php foreach ($semestres as $s): ?>
                <option value="<?= $s ?>" <?= (isset($rendimientoHospitalario) && $rendimientoHospitalario->semestre == $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php
// Definición de secciones/campos para no repetir markup
$secciones = [
    'Egresos' => [
        'dias_func_servicio' => 'Días Func. Servicio',
        'altas'               => 'Altas',
        'defuncion'           => 'Defunción',
        'total_egresos'       => 'Total Egresos',
        'pases_a_sala'        => 'Pases a Sala',
    ],
    'Estadía y Ocupación' => [
        'dias_estada'            => 'Días Estada',
        'paciente_dia'           => 'Paciente Día',
        'cama_disponible'        => 'Cama Disponible',
        'promedio_cama_disp'     => 'Prom. Cama Disponible',
        'promedio_pcte_dia'      => 'Prom. Paciente Día',
        'promedio_permanencia'   => 'Prom. Permanencia',
        'porcentaje_ocupacional' => '% Ocupacional',
    ],
    'Indicadores' => [
        'estandares'           => 'Estándares',
        'promedio_dias_estada' => 'Prom. Días Estada',
        'tasa_mortalidad'      => 'Tasa Mortalidad',
        'giro_cama'            => 'Giro Cama',
    ],
];

// Campos que son numéricos decimales (el resto -salvo 'estandares'- son enteros)
$camposFloat = ['promedio_cama_disp', 'promedio_pcte_dia', 'promedio_permanencia', 'porcentaje_ocupacional', 'promedio_dias_estada', 'tasa_mortalidad', 'giro_cama'];
?>

<?php foreach ($secciones as $titulo => $campos): ?>
    <div class="rh-seccion">
        <h3><?= esc($titulo) ?></h3>
        <div class="rh-campos">
            <?php foreach ($campos as $name => $label): ?>
                <div class="rh-campo">
                    <label><?= esc($label) ?></label>
                    <?php if ($name === 'estandares'): ?>
                        <input type="text" name="<?= $name ?>"
                               value="<?= isset($rendimientoHospitalario) ? esc($rendimientoHospitalario->$name) : old($name, '') ?>">
                    <?php else: ?>
                        <input type="number" name="<?= $name ?>" min="0" <?= in_array($name, $camposFloat) ? 'step="0.01"' : '' ?>
                               value="<?= isset($rendimientoHospitalario) ? esc($rendimientoHospitalario->$name) : old($name, 0) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($rendimientoHospitalario) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('rendimiento_hospitalario_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?= $this->endSection() ?>