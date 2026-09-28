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
    padding: 0 0.6rem; font-size: 0.9rem; min-width: 160px;
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

.tabla-excel input[type="number"], .tabla-excel input[type="text"] {
    width: 110px; border: 1px solid #b8d0e8; border-radius: 3px; text-align: center;
    padding: 5px 4px; font-size: 0.9rem; background: #fff; outline: none;
    -moz-appearance: textfield;
}
.tabla-excel input[type="number"]::-webkit-outer-spin-button,
.tabla-excel input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.tabla-excel input:focus { border-color: #13304d; background: #fffde7; box-shadow: 0 0 0 2px rgba(19,48,77,0.15); }
.tabla-excel td.campo-nombre { text-align: left; font-weight: 600; color: #13304d; min-width: 220px; }
.tabla-excel td.campo-resultado input { background: #f4f8fc; font-weight: 700; }

.campo-observacion {
    width: 100%; min-height: 90px; border: 1px solid #b8d0e8; border-radius: 5px;
    padding: 10px; font-size: 0.9rem; font-family: inherit; resize: vertical;
}
.campo-observacion:focus { border-color: #13304d; outline: none; box-shadow: 0 0 0 2px rgba(19,48,77,0.15); }
.label-observacion { display: block; font-weight: 600; color: #13304d; margin-bottom: 6px; }

.btn-recalcular {
    background: #e8a800; color: #fff; border: none; border-radius: 5px;
    padding: 0.5rem 1.2rem; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    margin: 12px 0; transition: background 0.2s;
}
.btn-recalcular:hover { background: #c99200; }

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
    <?= !empty($quirofanoRegistro) ? 'Editar Producción Quirófano' : 'Carga de Producción Quirófano' ?>
</h2>

<form action="<?= base_url(route_to($formRoute ?? 'quirofano_store', $registro->produccion_id ?? null)) ?>" method="POST" id="formQuirofano">
<?= csrf_field() ?>

<?php if (!empty($quirofanoRegistro)): ?>
    <!-- La ruta de update es PUT; el <form> HTML solo soporta GET/POST,
         así que spoofeamos el método igual que hace ProduccionQuirofanoHosp::getCampoOculto()
         y Guardia en su vista de edición. -->
    <input type="hidden" name="_method" value="PUT">
    <input type="hidden" name="produccion_id" value="<?= esc($registro->produccion_id) ?>">
<?php endif; ?>

<!-- ── CONFIGURACIÓN ARRIBA ── -->
<div class="config-bar">

    <div class="field">
        <label>Efector *</label>
        <?php if (!empty($quirofanoRegistro)): ?>
            <input type="text" value="<?= esc($efectorActual->nombre) ?>" disabled style="min-width:220px;">
        <?php else: ?>
            <select name="efector_id" required>
                <option value="">— Seleccionar —</option>
                <?php foreach ($efectores as $e): ?>
                    <option value="<?= $e->efector_id ?>" <?= old('efector_id') == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </div>

    <div class="field">
        <label>Ejercicio *</label>
        <?php if (!empty($quirofanoRegistro)): ?>
            <input type="text" value="<?= esc($registro->ejercicio) ?>" disabled>
        <?php else: ?>
            <input type="number" name="ejercicio" id="ejercicio"
                   value="<?= old('ejercicio', date('Y')) ?>" min="2000" max="2099" required>
        <?php endif; ?>
    </div>

</div>

<!-- ── TABLA DE DATOS ── -->
<div class="tabla-excel-wrapper">
<table class="tabla-excel" id="tablaQuirofano">
    <thead>
        <tr>
            <th style="min-width:220px;">Concepto</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="campo-nombre">Quirófanos disponibles</td>
            <td><input type="number" name="quirofanos_disponibles" min="0" id="quirofanos_disponibles"
                       value="<?= esc(old('quirofanos_disponibles', $registro->quirofanos_disponibles ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Quirófanos para cirugías de urgencia</td>
            <td><input type="number" name="quirofanos_urgencias" min="0" id="quirofanos_urgencias"
                       value="<?= esc(old('quirofanos_urgencias', $registro->quirofanos_urgencias ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Quirófanos para cirugías programadas</td>
            <td><input type="number" name="quirofanos_programadas" min="0" id="quirofanos_programadas"
                       value="<?= esc(old('quirofanos_programadas', $registro->quirofanos_programadas ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Cantidad cirugías de urgencia</td>
            <td><input type="number" name="cirugias_urgencia" min="0" id="cirugias_urgencia"
                       value="<?= esc(old('cirugias_urgencia', $registro->cirugias_urgencia ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Cirugías programadas — complejidad alta</td>
            <td><input type="number" name="cirugias_prog_alta" min="0" class="campo-complejidad" id="cirugias_prog_alta"
                       value="<?= esc(old('cirugias_prog_alta', $registro->cirugias_prog_alta ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Cirugías programadas — complejidad mediana</td>
            <td><input type="number" name="cirugias_prog_mediana" min="0" class="campo-complejidad" id="cirugias_prog_mediana"
                       value="<?= esc(old('cirugias_prog_mediana', $registro->cirugias_prog_mediana ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Cirugías programadas — complejidad baja</td>
            <td><input type="number" name="cirugias_prog_baja" min="0" class="campo-complejidad" id="cirugias_prog_baja"
                       value="<?= esc(old('cirugias_prog_baja', $registro->cirugias_prog_baja ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Cirugías programadas — complejidad desconocida</td>
            <td><input type="number" name="cirugias_prog_desconocido" min="0" class="campo-complejidad" id="cirugias_prog_desconocido"
                       value="<?= esc(old('cirugias_prog_desconocido', $registro->cirugias_prog_desconocido ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Total cirugías programadas</td>
            <td class="campo-resultado"><input type="number" name="total_cirugias_programadas" min="0" id="total_cirugias_programadas"
                       value="<?= esc(old('total_cirugias_programadas', $registro->total_cirugias_programadas ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">Sub total</td>
            <td class="campo-resultado"><input type="number" name="sub_total" min="0" id="sub_total"
                       value="<?= esc(old('sub_total', $registro->sub_total ?? 0)) ?>"></td>
        </tr>
        <tr>
            <td class="campo-nombre">% en relación a la provincia</td>
            <td><input type="number" name="porcentaje_provincia" step="0.0001" min="0" max="1"
                       value="<?= esc(old('porcentaje_provincia', $registro->porcentaje_provincia ?? '')) ?>" placeholder="0.0000"></td>
        </tr>
    </tbody>
</table>
</div>

<button type="button" class="btn-recalcular" id="btnRecalcular">
    <i class="fas fa-calculator"></i> Recalcular totales
</button>
<small style="display:block; color:#5a6a7e; margin-top:-6px; margin-bottom:10px;">
    Suma las 4 complejidades para "Total cirugías programadas" y le suma la urgencia para "Sub total".
    Podés editar ambos valores a mano si tu caso tiene una excepción (ver observación).
</small>

<div style="margin-top: 1rem;">
    <label class="label-observacion">Observación</label>
    <textarea name="observacion" class="campo-observacion" placeholder="Aclaraciones, excepciones del período, etc."><?= esc(old('observacion', $registro->observacion ?? '')) ?></textarea>
</div>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= !empty($quirofanoRegistro) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('quirofano_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<script>
document.getElementById('btnRecalcular').addEventListener('click', function() {
    const val = id => parseInt(document.getElementById(id).value, 10) || 0;

    const alta   = val('cirugias_prog_alta');
    const media  = val('cirugias_prog_mediana');
    const baja   = val('cirugias_prog_baja');
    const desc   = val('cirugias_prog_desconocido');
    const urg    = val('cirugias_urgencia');

    const totalProgramadas = alta + media + baja + desc;
    document.getElementById('total_cirugias_programadas').value = totalProgramadas;
    document.getElementById('sub_total').value = totalProgramadas + urg;
});
</script>

<?= $this->endSection() ?>