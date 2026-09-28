<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> PRODUCCIÓN QUIRÓFANO <?= $this->endSection() ?>
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

.tabla-excel-wrapper { overflow-x: auto; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
.tabla-excel { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 600px; }
.tabla-excel thead tr th {
    background: #13304d; color: #fff; text-align: center; padding: 10px 8px;
    font-weight: 700; white-space: nowrap; border: 1px solid #0d2035;
}
.tabla-excel tbody tr { background: #fff; }
.tabla-excel tbody tr:nth-child(even) { background: #f4f8fc; }
.tabla-excel tbody tr:hover { background: #ddeeff; }
.tabla-excel tbody td { border: 1px solid #cde0f5; padding: 6px 6px; text-align: center; vertical-align: middle; }

.tabla-excel select {
    width: 100%; min-width: 220px; border: 1px solid #b8d0e8; border-radius: 3px;
    padding: 5px 4px; font-size: 0.85rem; background: #fff; outline: none;
}
.tabla-excel input[type="number"] {
    width: 110px; border: 1px solid #b8d0e8; border-radius: 3px; text-align: center;
    padding: 5px 4px; font-size: 0.9rem; background: #fff; outline: none;
    -moz-appearance: textfield;
}
.tabla-excel input[type="number"]::-webkit-outer-spin-button,
.tabla-excel input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.tabla-excel select:focus, .tabla-excel input:focus {
    border-color: #13304d; background: #fffde7; box-shadow: 0 0 0 2px rgba(19,48,77,0.15);
}

.btn-fila {
    border: none; border-radius: 4px; width: 30px; height: 30px;
    font-size: 0.9rem; cursor: pointer; color: #fff;
}
.btn-quitar { background: #e74c3c; }
.btn-quitar:hover { background: #c0392b; }

.btn-agregar {
    background: #00b4a0; color: #fff; border: none; border-radius: 5px;
    padding: 0.55rem 1.4rem; font-size: 0.9rem; font-weight: 600; cursor: pointer;
    margin-top: 12px; transition: background 0.2s;
}
.btn-agregar:hover { background: #009484; }

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
    <?= !empty($registro) ? 'Editar Producción Quirúrgica' : 'Carga de Producción Quirúrgica' ?>
</h2>

<form action="<?= !empty($registro)
        ? base_url(route_to('produccion_quirofano_update', $registro->produccion_quirofano_id))
        : base_url(route_to('produccion_quirofano_store')) ?>" method="POST" id="formProduccion">
<?= csrf_field() ?>

<?php if (!empty($registro)): ?>
    <!-- Campo oculto: spoofeamos el método PUT igual que en Guardia/Efector,
         porque el <form> HTML solo soporta GET/POST. -->
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="id" value="<?= esc($registro->produccion_quirofano_id) ?>">
<?php endif; ?>

<?php if (!empty($registro)): ?>

<!-- ── MODO EDICIÓN: un solo registro ── -->
<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= old('efector_id', $registro->efector_id) == $e->efector_id ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" min="2000" max="2099"
               value="<?= old('ejercicio', $registro->ejercicio) ?>" required>
    </div>

    <div class="field">
        <label>Producción Quirúrgica *</label>
        <input type="number" name="produccion" min="0"
               value="<?= old('produccion', $registro->produccion) ?>" required>
    </div>
</div>

<?php else: ?>

<!-- ── MODO CREACIÓN: ejercicio único + tabla dinámica de efectores ── -->
<div class="config-bar">
    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" id="ejercicio"
               value="<?= old('ejercicio', date('Y')) ?>" min="2000" max="2099" required>
    </div>
</div>

<div class="tabla-excel-wrapper">
<table class="tabla-excel" id="tablaProduccion">
    <thead>
        <tr>
            <th style="min-width:220px;">Efector</th>
            <th>Producción Quirúrgica</th>
            <th style="width:40px;"></th>
        </tr>
    </thead>
    <tbody id="cuerpoTabla">
        <!-- Se llena por JS -->
    </tbody>
</table>
</div>

<button type="button" class="btn-agregar" id="btnAgregarFila">
    <i class="fas fa-plus"></i> Agregar fila
</button>

<?php endif; ?>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= !empty($registro) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('produccion_quirofano_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?php if (empty($registro)): ?>
<script>
const EFECTORES = <?= json_encode(array_map(fn($e) => ['id' => $e->efector_id, 'nombre' => $e->nombre], $efectores)) ?>;

function crearFila() {
    const tr = document.createElement('tr');

    // Select Efector
    const tdEfector = document.createElement('td');
    const selEfector = document.createElement('select');
    selEfector.name = 'efector_id[]';
    selEfector.required = true;
    selEfector.innerHTML = '<option value="">— Seleccionar —</option>' +
        EFECTORES.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    tdEfector.appendChild(selEfector);

    // Input Producción
    const tdProduccion = document.createElement('td');
    const inpProduccion = document.createElement('input');
    inpProduccion.type = 'number';
    inpProduccion.name = 'produccion[]';
    inpProduccion.min = '0';
    inpProduccion.value = '0';
    inpProduccion.required = true;
    tdProduccion.appendChild(inpProduccion);

    // Botón quitar
    const tdAccion = document.createElement('td');
    const btnQuitar = document.createElement('button');
    btnQuitar.type = 'button';
    btnQuitar.className = 'btn-fila btn-quitar';
    btnQuitar.innerHTML = '<i class="fas fa-times"></i>';
    btnQuitar.addEventListener('click', function() {
        tr.remove();
    });
    tdAccion.appendChild(btnQuitar);

    tr.appendChild(tdEfector);
    tr.appendChild(tdProduccion);
    tr.appendChild(tdAccion);

    return tr;
}

document.getElementById('btnAgregarFila').addEventListener('click', function() {
    document.getElementById('cuerpoTabla').appendChild(crearFila());
});

// Arrancar con 3 filas vacías
for (let i = 0; i < 3; i++) {
    document.getElementById('cuerpoTabla').appendChild(crearFila());
}

// Validar antes de enviar
document.getElementById('formProduccion').addEventListener('submit', function(e) {
    const filas = document.querySelectorAll('#cuerpoTabla tr');
    if (filas.length === 0) {
        e.preventDefault();
        alert('⚠️ Debés agregar al menos una fila antes de guardar.');
    }
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>