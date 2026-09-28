<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> ASISTENCIA <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
/* ── Selector de configuración arriba ── */
.config-bar {
    background: #13304d;
    border-radius: 8px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.5rem;
    display: flex;
    flex-wrap: wrap;
    gap: 1.2rem;
    align-items: flex-end;
}
.config-bar .field { margin-bottom: 0; }
.config-bar label {
    color: #fff !important;
    font-weight: 600;
    font-size: 0.85rem;
    display: block;
    margin-bottom: 4px;
}
.config-bar input,
.config-bar select {
    background: #fff;
    border: none;
    border-radius: 4px;
    height: 2.2rem;
    padding: 0 0.6rem;
    font-size: 0.9rem;
    min-width: 120px;
}

/* ── Tabla tipo Excel ── */
.tabla-excel-wrapper {
    overflow-x: auto;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.tabla-excel {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    min-width: 750px;
}
.tabla-excel thead tr th {
    background: #13304d;
    color: #fff;
    text-align: center;
    padding: 10px 8px;
    font-weight: 700;
    white-space: nowrap;
    border: 1px solid #0d2035;
    position: sticky;
    top: 0;
    z-index: 2;
}
.tabla-excel thead tr th:first-child {
    text-align: left;
    min-width: 160px;
}
.tabla-excel tbody tr { background: #fff; }
.tabla-excel tbody tr:nth-child(even) { background: #f4f8fc; }
.tabla-excel tbody tr:hover { background: #ddeeff; }
.tabla-excel tbody td {
    border: 1px solid #cde0f5;
    padding: 4px 4px;
    text-align: center;
    vertical-align: middle;
}
.tabla-excel tbody td.nombre-celda {
    text-align: left;
    padding-left: 10px;
    font-weight: 600;
    color: #13304d;
    white-space: nowrap;
    background: #e8f0f8;
    border-right: 2px solid #13304d;
}
/* Inputs tipo celda Excel */
.tabla-excel input[type="number"] {
    width: 68px;
    border: 1px solid #b8d0e8;
    border-radius: 3px;
    text-align: center;
    padding: 4px 2px;
    font-size: 0.88rem;
    background: #fff;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
    -moz-appearance: textfield;
}
.tabla-excel input[type="number"]::-webkit-outer-spin-button,
.tabla-excel input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
.tabla-excel input[type="number"]:focus {
    border-color: #13304d;
    background: #fffde7;
    box-shadow: 0 0 0 2px rgba(19,48,77,0.15);
}
/* Celda total */
.total-celda {
    font-weight: 700;
    color: #13304d;
    font-size: 0.95rem;
    background: #d0e8ff !important;
    min-width: 60px;
}
/* Botones */
.btn-guardar {
    background: #13304d;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 0.6rem 1.8rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-guardar:hover { background: #1a4a70; }
.btn-volver {
    background: #e8a800;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 0.6rem 1.8rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s;
    display: inline-block;
}
.btn-volver:hover { background: #c99200; color: #fff; }
.acciones-bar {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-clipboard-list"></i></span>
    <?= isset($asistencia) ? 'Editar Asistencia' : 'Carga de Asistencia' ?>
</h2>

<form action="<?= base_url(route_to($formRoute ?? 'asistencia_store')) ?>" method="POST" id="formAsistencia">
<?= csrf_field() ?>
<?= isset($asistencia) ? $asistencia->getCampoOculto() : '' ?>

<!-- ── CONFIGURACIÓN ARRIBA ── -->
<div class="config-bar">
    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" id="ejercicio"
               value="<?= old('ejercicio', $asistencia->ejercicio ?? date('Y')) ?>"
               min="2000" max="2099" required
               <?= isset($asistencia) ? 'readonly style="background:#e9ecef;"' : '' ?>>
    </div>
    <div class="field">
        <label>Mes *</label>
        <select name="mes" id="mes" required
                <?= isset($asistencia) ? 'disabled' : '' ?>>
            <?php
            $meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
                      'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
            $mesActual = old('mes', $asistencia->mes ?? strtoupper(date('F')));
            foreach ($meses as $m):
            ?>
            <option value="<?= $m ?>" <?= $mesActual == $m ? 'selected' : '' ?>><?= $m ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($asistencia)): ?>
            <!-- Re-enviar el valor porque disabled no se envía por POST -->
            <input type="hidden" name="mes" value="<?= $asistencia->mes ?>">
        <?php endif; ?>
    </div>
    <div class="field">
        <label>Operativa *</label>
        <select name="operativa" id="operativa" required
                <?= isset($asistencia) ? 'disabled' : 'onchange="cambiarOperativa(this.value)"' ?>>
            <option value="BASE" <?= old('operativa', $asistencia->operativa ?? 'BASE') == 'BASE' ? 'selected' : '' ?>>BASE</option>
            <option value="USE"  <?= old('operativa', $asistencia->operativa ?? '')     == 'USE'  ? 'selected' : '' ?>>USE</option>
        </select>
        <?php if (isset($asistencia)): ?>
            <!-- Re-enviar el valor porque disabled no se envía por POST -->
            <input type="hidden" name="operativa" value="<?= $asistencia->operativa ?>">
        <?php endif; ?>
    </div>
</div>

<!-- ── TABLA TIPO EXCEL ── -->
<div class="tabla-excel-wrapper">
<table class="tabla-excel" id="tablaAsistencia">
    <thead>
        <tr>
            <th style="min-width:170px;">BASE / USE</th>
            <th>Con<br>médico</th>
            <th>Sin<br>médico</th>
            <th>Urgencias</th>
            <th>Pública</th>
            <th>Privada</th>
            <th>Internación</th>
            <th style="background:#1a4a70;">Total</th>
        </tr>
    </thead>
    <tbody id="cuerpoTabla">
        <!-- Se llena por JS -->
    </tbody>
</table>
</div>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($asistencia) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('asistencia_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<script>
const BASES = {
    BASE: [
        'SS DE JUJUY','SAN PEDRO','LEDESMA','PURMAMARCA','PERICO','PALPALA',
        'HUMAHUACA','LA QUIACA','ABRA PAMPA','MONTERRICO','YUTO'
    ],
    USE: [
        'VOLCAN','SALINAS','SAN ANTONIO','PAMPA BLANCA','PUESTO VIEJO',
        'SANTA CATALINA','CIENEGUILLAS','CUSI CUSI','OLAROZ','AGUAS CALIENTES',
        'CAIMANCITO','CASPALA','EL TALAR','EL CARMEN','HORNOCAL',
        'JAMA','PALMA SOLA','CIUDAD CULTURAL Y LOS CAMPOS'
    ]
};

const COLS = ['em_con','em_sin','urg','pub','priv','int'];

// ── Modo edición: datos precargados desde PHP ──
const ES_EDICION = <?= isset($asistencia) ? 'true' : 'false' ?>;

<?php if (isset($asistencia)): ?>
const EDICION_DATOS = {
    nombre : '<?= addslashes($asistencia->nombre) ?>',
    em_con : <?= $asistencia->emergencias_con_medico ?>,
    em_sin : <?= $asistencia->emergencias_sin_medico ?>,
    urg    : <?= $asistencia->urgencias ?>,
    pub    : <?= $asistencia->derivacion_publica ?>,
    priv   : <?= $asistencia->derivacion_privada ?>,
    int    : <?= $asistencia->internacion_domiciliaria ?>,
    total  : <?= $asistencia->total ?>,
};
<?php endif; ?>

// ── Construir tabla completa (modo crear) ──
function buildTable(operativa) {
    const tbody = document.getElementById('cuerpoTabla');
    tbody.innerHTML = '';
    const nombres = BASES[operativa] || [];

    nombres.forEach(function(nombre, i) {
        tbody.appendChild(crearFila(i, nombre, {
            em_con: 0, em_sin: 0, urg: 0, pub: 0, priv: 0, int: 0, total: 0
        }, false));
    });
}

// ── Construir fila única (modo editar) ──
function buildFilaEdicion() {
    const tbody = document.getElementById('cuerpoTabla');
    tbody.innerHTML = '';
    tbody.appendChild(crearFila(0, EDICION_DATOS.nombre, EDICION_DATOS, true));
    calcularTotal(0);
}

// ── Crear una fila ──
function crearFila(index, nombre, datos, esEdicion) {
    const tr = document.createElement('tr');

    // Celda nombre
    const tdNombre = document.createElement('td');
    tdNombre.className = 'nombre-celda';
    tdNombre.textContent = nombre;

    const hiddenNombre = document.createElement('input');
    hiddenNombre.type  = 'hidden';
    // En edición va como campo simple; en crear va como array
    hiddenNombre.name  = esEdicion ? 'nombre' : 'nombre[]';
    hiddenNombre.value = nombre;
    tdNombre.appendChild(hiddenNombre);
    tr.appendChild(tdNombre);

    // Celdas numéricas
    COLS.forEach(function(col) {
        const td    = document.createElement('td');
        const input = document.createElement('input');
        input.type  = 'number';
        // En edición va como campo simple; en crear va como array
        input.name  = esEdicion ? col : col + '[]';
        input.value = datos[col] ?? 0;
        input.min   = '0';
        input.setAttribute('data-row', index);
        input.setAttribute('data-col', col);
        input.addEventListener('input', function() { calcularTotal(index); });
        input.addEventListener('focus', function() { this.select(); });
        td.appendChild(input);
        tr.appendChild(td);
    });

    // Celda total
    const tdTotal = document.createElement('td');
    tdTotal.className   = 'total-celda';
    tdTotal.id          = 'total_' + index;
    tdTotal.textContent = datos.total ?? 0;
    tr.appendChild(tdTotal);

    return tr;
}

function calcularTotal(rowIndex) {
    const inputs = document.querySelectorAll('[data-row="' + rowIndex + '"]');
    let suma = 0;
    inputs.forEach(function(inp) { suma += parseInt(inp.value || 0); });
    document.getElementById('total_' + rowIndex).textContent = suma;
}

function cambiarOperativa(val) {
    buildTable(val);
}

// ── Inicializar según modo ──
if (ES_EDICION) {
    buildFilaEdicion();
} else {
    buildTable(document.getElementById('operativa').value);
}

// ── Validar antes de enviar ──
document.getElementById('formAsistencia').addEventListener('submit', function(e) {
    const inputs = document.querySelectorAll('#cuerpoTabla input[type="number"]');
    let hayDatos = false;
    inputs.forEach(function(inp) { if (parseInt(inp.value) > 0) hayDatos = true; });
    if (!hayDatos) {
        e.preventDefault();
        alert('⚠️ Debés ingresar al menos un valor mayor a 0 antes de guardar.');
    }
});
</script>

<?= $this->endSection() ?>