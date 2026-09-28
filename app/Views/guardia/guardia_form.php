<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GUARDIA <?= $this->endSection() ?>
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
    padding: 0 0.6rem; font-size: 0.9rem; min-width: 140px;
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
    width: 100%; min-width: 170px; border: 1px solid #b8d0e8; border-radius: 3px;
    padding: 5px 4px; font-size: 0.85rem; background: #fff; outline: none;
}
.tabla-excel input[type="number"] {
    width: 90px; border: 1px solid #b8d0e8; border-radius: 3px; text-align: center;
    padding: 5px 4px; font-size: 0.9rem; background: #fff; outline: none;
    -moz-appearance: textfield;
}
.tabla-excel input[type="number"]::-webkit-outer-spin-button,
.tabla-excel input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.tabla-excel select:focus, .tabla-excel input:focus {
    border-color: #13304d; background: #fffde7; box-shadow: 0 0 0 2px rgba(19,48,77,0.15);
}
.tabla-excel td.servicio-nombre { text-align: left; font-weight: 600; color: #13304d; }

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
    <span class="icon"><i class="fas fa-user-shield"></i></span>
    <?= !empty($guardiaGrupo) ? 'Editar Guardia' : 'Carga de Guardias' ?>
</h2>

<form action="<?= base_url(route_to($formRoute ?? 'guardia_store')) ?>" method="POST" id="formGuardia">
<?= csrf_field() ?>

<?php if (!empty($guardiaGrupo)): ?>
    <!-- La ruta de update es PUT; el <form> HTML solo soporta GET/POST,
         así que spoofeamos el método igual que hace Guardia::getCampoOculto()
         y Efector en su vista de edición. -->
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="clave" value="<?= esc($clave) ?>">
<?php endif; ?>

<!-- ── CONFIGURACIÓN ARRIBA ── -->
<div class="config-bar">

    <div class="field">
        <label>Año *</label>
        <?php if (!empty($guardiaGrupo)): ?>
            <input type="text" value="<?= esc($anio) ?>" disabled>
        <?php else: ?>
            <input type="number" name="anio" id="anio"
                   value="<?= old('anio', date('Y')) ?>" min="2000" max="2099" required>
        <?php endif; ?>
    </div>

    <div class="field">
        <label>Semestre *</label>
        <?php if (!empty($guardiaGrupo)): ?>
            <input type="text" value="<?= esc($semestre) ?>" disabled>
        <?php else: ?>
            <select name="semestre" required>
                <?php foreach ($semestres as $s): ?>
                    <option value="<?= $s ?>" <?= old('semestre') == $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>

    <div class="field">
        <label>Mes<?= empty($guardiaGrupo) ? ' (opcional)' : '' ?></label>
        <?php if (!empty($guardiaGrupo)): ?>
            <input type="text" value="<?= esc($mes ?? '— Sin mes —') ?>" disabled>
        <?php else: ?>
            <select name="mes">
                <option value="">— Sin mes —</option>
                <?php foreach ($meses as $m): ?>
                    <option value="<?= $m ?>" <?= old('mes') == $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>

    <?php if (!empty($guardiaGrupo)): ?>
    <div class="field">
        <label>Efector</label>
        <input type="text" value="<?= esc($efectorActual->nombre) ?>" disabled style="min-width:220px;">
    </div>
    <?php endif; ?>

</div>

<?php if (!empty($guardiaGrupo)): ?>

<!-- ── MODO EDICIÓN: tabla fija, un renglón por servicio ── -->
<div class="tabla-excel-wrapper">
<table class="tabla-excel">
    <thead>
        <tr>
            <th style="min-width:220px;">Servicio</th>
            <th>Cantidad</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($servicios as $s): ?>
        <tr>
            <td class="servicio-nombre">
                <?= esc($s->nombre) ?>
                <input type="hidden" name="servicio_id[]" value="<?= $s->servicio_id ?>">
            </td>
            <td>
                <input type="number" name="cantidad[]" min="0"
                       value="<?= esc($cantidadesPorServicio[$s->servicio_id] ?? 0) ?>">
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php else: ?>

<!-- ── MODO CREACIÓN: tabla dinámica de filas ── -->
<div class="tabla-excel-wrapper">
<table class="tabla-excel" id="tablaGuardia">
    <thead>
        <tr>
            <th style="min-width:180px;">Efector</th>
            <th style="min-width:180px;">Servicio</th>
            <th>Cantidad</th>
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
        <i class="fas fa-save"></i> <?= !empty($guardiaGrupo) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('guardia_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<?php if (empty($guardiaGrupo)): ?>
<script>
const EFECTORES = <?= json_encode(array_map(fn($e) => ['id' => $e->efector_id, 'nombre' => $e->nombre], $efectores)) ?>;
const SERVICIOS = <?= json_encode(array_map(fn($s) => ['id' => $s->servicio_id, 'nombre' => $s->nombre], $servicios)) ?>;

let contadorFilas = 0;

function crearFila() {
    const tr = document.createElement('tr');
    contadorFilas++;

    // Select Efector
    const tdEfector = document.createElement('td');
    const selEfector = document.createElement('select');
    selEfector.name = 'efector_id[]';
    selEfector.required = true;
    selEfector.innerHTML = '<option value="">— Seleccionar —</option>' +
        EFECTORES.map(e => `<option value="${e.id}">${e.nombre}</option>`).join('');
    tdEfector.appendChild(selEfector);

    // Select Servicio
    const tdServicio = document.createElement('td');
    const selServicio = document.createElement('select');
    selServicio.name = 'servicio_id[]';
    selServicio.required = true;
    selServicio.innerHTML = '<option value="">— Seleccionar —</option>' +
        SERVICIOS.map(s => `<option value="${s.id}">${s.nombre}</option>`).join('');
    tdServicio.appendChild(selServicio);

    // Input Cantidad
    const tdCantidad = document.createElement('td');
    const inpCantidad = document.createElement('input');
    inpCantidad.type = 'number';
    inpCantidad.name = 'cantidad[]';
    inpCantidad.min = '0';
    inpCantidad.value = '0';
    inpCantidad.required = true;
    tdCantidad.appendChild(inpCantidad);

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
    tr.appendChild(tdServicio);
    tr.appendChild(tdCantidad);
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
document.getElementById('formGuardia').addEventListener('submit', function(e) {
    const filas = document.querySelectorAll('#cuerpoTabla tr');
    if (filas.length === 0) {
        e.preventDefault();
        alert('⚠️ Debés agregar al menos una fila antes de guardar.');
    }
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>