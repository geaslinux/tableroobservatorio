<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RENDIMIENTO HOSPITALARIO UTI <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.config-bar { background:#13304d; border-radius:8px; padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; flex-wrap:wrap; gap:1.2rem; align-items:flex-end; }
.config-bar .field { margin-bottom:0; }
.config-bar label { color:#fff !important; font-weight:600; font-size:0.85rem; display:block; margin-bottom:4px; }
.config-bar input, .config-bar select { background:#fff; border:none; border-radius:4px; height:2.2rem; padding:0 0.6rem; font-size:0.9rem; min-width:180px; }

.rhu-seccion { background:#fff; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:1.2rem 1.5rem; margin-bottom:1.2rem; }
.rhu-seccion h3 { color:#13304d; font-size:1rem; font-weight:700; margin-bottom:1rem; border-bottom:2px solid #00b4a0; padding-bottom:6px; }
.rhu-campos { display:flex; flex-wrap:wrap; gap:1rem; }
.rhu-campo { display:flex; flex-direction:column; gap:4px; min-width:170px; }
.rhu-campo label { font-size:0.82rem; font-weight:600; color:#5a6a7e; }
.rhu-campo input[type="number"],
.rhu-campo input[type="text"] { border:1px solid #b8d0e8; border-radius:4px; padding:8px 10px; font-size:0.95rem; }
.rhu-campo input:focus { border-color:#13304d; outline:none; box-shadow:0 0 0 2px rgba(19,48,77,0.15); }

.btn-guardar { background:#13304d; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; }
.btn-guardar:hover { background:#1a4a70; }
.btn-volver { background:#e8a800; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-volver:hover { background:#c99200; color:#fff; }
.acciones-bar { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-procedures"></i></span>
    <?= isset($rendimientoHospitalarioUti) ? 'Editar Rendimiento Hospitalario UTI' : 'Nuevo Rendimiento Hospitalario UTI' ?>
</h2>

<form action="<?= base_url(route_to(isset($rendimientoHospitalarioUti) ? 'rendimiento_hospitalario_uti_update' : 'rendimiento_hospitalario_uti_store')) ?>" method="POST" id="formRendimientoHospitalarioUti">
<?= csrf_field() ?>

<?php if (isset($rendimientoHospitalarioUti)): ?>
    <!-- Spoofeamos el método igual que Guardia / CapacidadCamas -->
    <?= $rendimientoHospitalarioUti->getCampoOculto() ?>
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= (isset($rendimientoHospitalarioUti) && $rendimientoHospitalarioUti->efector_id == $e->efector_id) ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" min="2000" max="2100" required
               value="<?= isset($rendimientoHospitalarioUti) ? esc($rendimientoHospitalarioUti->ejercicio) : old('ejercicio', date('Y')) ?>">
    </div>
    <div class="field">
        <label>Semestre *</label>
        <select name="semestre" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($semestres as $s): ?>
                <option value="<?= $s ?>" <?= (isset($rendimientoHospitalarioUti) && $rendimientoHospitalarioUti->semestre == $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php
// Definición de secciones/campos para no repetir markup
$secciones = [
    'Egresos y Días de Funcionamiento' => [
        'dias_func_servicio' => ['Días Func. Servicio', 'number'],
        'altas'               => ['Altas', 'number'],
        'defuncion'           => ['Defunción', 'number'],
        'total_egresos'       => ['Total Egresos', 'number'],
        'pases_a_sala'        => ['Pases a Sala', 'number'],
    ],
    'Días de Estada y Camas' => [
        'dias_estada'      => ['Días Estada', 'number'],
        'paciente_dia'     => ['Paciente Día', 'number'],
        'cama_disponible'  => ['Cama Disponible', 'number'],
    ],
    'Promedios' => [
        'promedio_cama_disp'   => ['Prom. Cama Disp.', 'decimal'],
        'promedio_pcte_dia'    => ['Prom. Pcte. Día', 'decimal'],
        'promedio_permanencia' => ['Prom. Permanencia', 'decimal'],
        'promedio_dias_estada' => ['Prom. Días Estada', 'decimal'],
    ],
    'Indicadores' => [
        'porcentaje_ocupacional' => ['% Ocupacional', 'decimal'],
        'estandares'              => ['Estándares', 'text'],
        'tasa_mortalidad'         => ['Tasa Mortalidad', 'decimal'],
        'giro_cama'               => ['Giro Cama', 'decimal'],
    ],
];
?>

<?php foreach ($secciones as $titulo => $campos): ?>
    <div class="rhu-seccion">
        <h3><?= esc($titulo) ?></h3>
        <div class="rhu-campos">
            <?php foreach ($campos as $name => [$label, $tipo]): ?>
                <div class="rhu-campo">
                    <label><?= esc($label) ?></label>
                    <?php if ($tipo === 'text'): ?>
                        <input type="text" name="<?= $name ?>"
                               value="<?= isset($rendimientoHospitalarioUti) ? esc($rendimientoHospitalarioUti->$name) : old($name, '') ?>">
                    <?php elseif ($tipo === 'decimal'): ?>
                        <input type="number" step="0.01" name="<?= $name ?>" min="0"
                               value="<?= isset($rendimientoHospitalarioUti) ? esc($rendimientoHospitalarioUti->$name) : old($name, 0) ?>">
                    <?php else: ?>
                        <input type="number" name="<?= $name ?>" min="0"
                               value="<?= isset($rendimientoHospitalarioUti) ? esc($rendimientoHospitalarioUti->$name) : old($name, 0) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($rendimientoHospitalarioUti) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('rendimiento_hospitalario_uti_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?= $this->endSection() ?>