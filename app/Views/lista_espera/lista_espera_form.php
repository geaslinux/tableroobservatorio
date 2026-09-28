<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> LISTA DE ESPERA QUIRÚRGICA <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.config-bar {
    background: #13304d; border-radius: 8px; padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1.2rem; align-items: flex-end;
}
.config-bar .field { margin-bottom: 0; }
.config-bar label { color: #fff !important; font-weight: 600; font-size: 0.85rem; display: block; margin-bottom: 4px; }
.config-bar input, .config-bar select {
    background: #fff; border: none; border-radius: 4px; height: 2.2rem;
    padding: 0 0.6rem; font-size: 0.9rem; min-width: 200px;
}
.config-bar input:disabled { background: #dfe6ee; color: #13304d; font-weight: 600; }

.btn-guardar {
    background: #13304d; color: #fff; border: none; border-radius: 5px;
    padding: 0.6rem 1.8rem; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
}
.btn-guardar:hover { background: #1a4a70; }
.btn-volver {
    background: #e8a800; color: #fff; border: none; border-radius: 5px;
    padding: 0.6rem 1.8rem; font-size: 1rem; font-weight: 600; cursor: pointer;
    text-decoration: none; display: inline-block; transition: background 0.2s;
}
.btn-volver:hover { background: #c99200; color: #fff; }
.acciones-bar { display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-procedures"></i></span>
    <?= !empty($registro) ? 'Editar Lista de Espera' : 'Carga de Lista de Espera' ?>
</h2>

<form action="<?= !empty($registro)
        ? base_url(route_to('lista_espera_update', $registro->lista_espera_id))
        : base_url(route_to('lista_espera_store')) ?>" method="POST" id="formListaEspera">
<?= csrf_field() ?>

<?php if (!empty($registro)): ?>
    <!-- Campo oculto: spoofeamos el método PUT igual que en Guardia/Efector,
         porque el <form> HTML solo soporta GET/POST. -->
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="lista_espera_id" value="<?= esc($registro->lista_espera_id) ?>">
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <?php if (!empty($registro)): ?>
                <option value="<?= $efectorActual->efector_id ?>" selected><?= esc($efectorActual->nombre) ?></option>
            <?php else: ?>
                <option value="">— Seleccionar —</option>
                <?php foreach ($efectores as $e): ?>
                    <option value="<?= $e->efector_id ?>"><?= esc($e->nombre) ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>

    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" min="2000" max="2099"
               value="<?= old('ejercicio', $registro->ejercicio ?? date('Y')) ?>" required>
    </div>

    <div class="field">
        <label>Especialidad</label>
        <select name="especialidad_id">
            <option value="">— Sin especialidad —</option>
            <?php foreach ($especialidades as $esp): ?>
                <option value="<?= $esp->especialidad_id ?>"
                    <?= old('especialidad_id', $registro->especialidad_id ?? '') == $esp->especialidad_id ? 'selected' : '' ?>>
                    <?= esc($esp->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="config-bar">
    <div class="field">
        <label>Cantidad de Pacientes *</label>
        <input type="number" name="cantidad_pacientes" min="0"
               value="<?= old('cantidad_pacientes', $registro->cantidad_pacientes ?? 0) ?>" required>
    </div>

    <div class="field">
        <label>Comp. Quirúrgica Alta</label>
        <input type="number" name="comp_quirurgica_alta" min="0"
               value="<?= old('comp_quirurgica_alta', $registro->comp_quirurgica_alta ?? 0) ?>">
    </div>

    <div class="field">
        <label>Comp. Quirúrgica Mediana</label>
        <input type="number" name="comp_quirurgica_mediana" min="0"
               value="<?= old('comp_quirurgica_mediana', $registro->comp_quirurgica_mediana ?? 0) ?>">
    </div>

    <div class="field">
        <label>Comp. Quirúrgica Baja</label>
        <input type="number" name="comp_quirurgica_baja" min="0"
               value="<?= old('comp_quirurgica_baja', $registro->comp_quirurgica_baja ?? 0) ?>">
    </div>
</div>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= !empty($registro) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('lista_espera_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?= $this->endSection() ?>