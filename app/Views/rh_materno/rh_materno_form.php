<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RENDIMIENTO HOSPITALARIO MATERNO INFANTIL <?= $this->endSection() ?>
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
.rhu-campo input[readonly] { background:#eef3f8; color:#13304d; font-weight:600; }

.btn-guardar { background:#13304d; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; }
.btn-guardar:hover { background:#1a4a70; }
.btn-volver { background:#e8a800; color:#fff; border:none; border-radius:5px; padding:0.6rem 1.8rem; font-size:1rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
.btn-volver:hover { background:#c99200; color:#fff; }
.acciones-bar { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d; font-size:1.4rem;">
    <span class="icon"><i class="fas fa-baby"></i></span>
    <?= isset($rhMaterno) ? 'Editar Rendimiento Hospitalario Materno Infantil' : 'Nuevo Rendimiento Hospitalario Materno Infantil' ?>
</h2>

<form action="<?= base_url(route_to(isset($rhMaterno) ? 'rh_materno_update' : 'rh_materno_store')) ?>" method="POST" id="formRhMaterno">
<?= csrf_field() ?>

<?php if (isset($rhMaterno)): ?>
    <!-- Spoofeamos el método igual que Guardia / CapacidadCamas -->
    <?= $rhMaterno->getCampoOculto() ?>
<?php endif; ?>

<div class="config-bar">
    <div class="field">
        <label>Efector *</label>
        <select name="efector_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($efectores as $e): ?>
                <option value="<?= $e->efector_id ?>" <?= (isset($rhMaterno) && $rhMaterno->efector_id == $e->efector_id) ? 'selected' : '' ?>>
                    <?= esc($e->nombre) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Ejercicio *</label>
        <input type="number" name="ejercicio" min="2000" max="2100" required
               value="<?= isset($rhMaterno) ? esc($rhMaterno->ejercicio) : old('ejercicio', date('Y')) ?>">
    </div>
    <div class="field">
        <label>Semestre *</label>
        <select name="semestre" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($semestres as $s): ?>
                <option value="<?= $s ?>" <?= (isset($rhMaterno) && $rhMaterno->semestre == $s) ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="field">
        <label>Servicio *</label>
        <input type="text" name="servicio" required list="listaServicios"
               value="<?= isset($rhMaterno) ? esc($rhMaterno->servicio) : old('servicio', '') ?>">
        <datalist id="listaServicios">
            <option value="PEDIATRIA">
            <option value="MATERNIDAD">
            <option value="NEONATOLOGIA">
        </datalist>
    </div>
    <div class="field">
        <label>Sector *</label>
        <input type="text" name="sector" required
               value="<?= isset($rhMaterno) ? esc($rhMaterno->sector) : old('sector', '') ?>">
    </div>
</div>

<?php
// Secciones de campos editables (los calculados van aparte, más abajo)
$secciones = [
    'Movimiento del Servicio' => [
        'dias_funcionamiento_servicio' => 'Días Func. Servicio',
        'ingresos'                     => 'Ingresos',
        'pases_de'                     => 'Pases De',
        'altas'                        => 'Altas',
        'defuncion'                    => 'Defunción',
        'total_egresos'                => 'Total Egresos',
        'pases_a'                      => 'Pases A',
    ],
    'Días de Estada y Camas' => [
        'paciente_dia'     => 'Paciente Día',
        'cama_disponible'  => 'Cama Disponible',
        'dias_estada'      => 'Días Estada',
    ],
];
?>

<?php foreach ($secciones as $titulo => $campos): ?>
    <div class="rhu-seccion">
        <h3><?= esc($titulo) ?></h3>
        <div class="rhu-campos">
            <?php foreach ($campos as $name => $label): ?>
                <div class="rhu-campo">
                    <label><?= esc($label) ?></label>
                    <input type="number" name="<?= $name ?>" min="0" id="campo_<?= $name ?>"
                           value="<?= isset($rhMaterno) ? esc($rhMaterno->$name) : old($name, 0) ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="rhu-seccion">
    <h3>Promedios (calculado automáticamente)</h3>
    <div class="rhu-campos">
        <?php
        $promedios = [
            'promedio_cama_disponible' => 'Prom. Cama Disp.',
            'promedio_paciente_dia'    => 'Prom. Pcte. Día',
            'promedio_dias_estada'     => 'Prom. Días Estada',
            'promedio_permanencia'     => 'Prom. Permanencia',
        ];
        foreach ($promedios as $name => $label):
        ?>
            <div class="rhu-campo">
                <label><?= esc($label) ?></label>
                <input type="text" id="campo_<?= $name ?>" readonly
                       value="<?= isset($rhMaterno) ? esc($rhMaterno->$name) : '0' ?>">
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="rhu-seccion">
    <h3>Indicadores</h3>
    <div class="rhu-campos">
        <div class="rhu-campo">
            <label>% Ocupacional</label>
            <input type="text" id="campo_porcentaje_ocupacional" readonly
                   value="<?= isset($rhMaterno) ? esc(round($rhMaterno->porcentaje_ocupacional * 100, 2)) . '%' : '0%' ?>">
        </div>
        <div class="rhu-campo">
            <label>Estándares</label>
            <input type="text" name="estandares"
                   value="<?= isset($rhMaterno) ? esc($rhMaterno->estandares) : old('estandares', '') ?>">
        </div>
        <div class="rhu-campo">
            <label>Tasa Mortalidad</label>
            <input type="text" id="campo_tasa_mortalidad" readonly
                   value="<?= isset($rhMaterno) ? esc(round($rhMaterno->tasa_mortalidad * 100, 2)) . '%' : '0%' ?>">
        </div>
        <div class="rhu-campo">
            <label>Giro Cama</label>
            <input type="text" id="campo_giro_cama" readonly
                   value="<?= isset($rhMaterno) ? esc($rhMaterno->giro_cama) : '0' ?>">
        </div>
        <div class="rhu-campo">
            <label>Giro Sustitución</label>
            <input type="text" id="campo_giro_sustitucion" readonly
                   value="<?= isset($rhMaterno) ? esc($rhMaterno->giro_sustitucion) : '0' ?>">
        </div>
        <div class="rhu-campo">
            <label>Egresos por Día</label>
            <input type="text" id="campo_egresos_por_dia" readonly
                   value="<?= isset($rhMaterno) ? esc($rhMaterno->egresos_por_dia) : '0' ?>">
        </div>
    </div>
</div>

<div class="acciones-bar">
    <button type="submit" class="btn-guardar">
        <i class="fas fa-save"></i> <?= isset($rhMaterno) ? 'Guardar Cambios' : 'Guardar' ?>
    </button>
    <a href="<?= base_url(route_to('rh_materno_list')) ?>" class="btn-volver">
        <i class="fas fa-list"></i> Volver
    </a>
</div>

</form>
</section>

<script>
(function () {
    const idsEntrada = [
        'dias_funcionamiento_servicio', 'total_egresos', 'pases_a',
        'paciente_dia', 'cama_disponible', 'dias_estada', 'defuncion'
    ];

    function num(id) {
        const el = document.getElementById('campo_' + id);
        return el ? (parseFloat(el.value) || 0) : 0;
    }

    function recalcular() {
        const diasFuncion    = num('dias_funcionamiento_servicio');
        const totalEgresos   = num('total_egresos');
        const pasesA         = num('pases_a');
        const pacienteDia    = num('paciente_dia');
        const camaDisponible = num('cama_disponible');
        const defuncion      = num('defuncion');

        const egresosAmpliado = totalEgresos + pasesA;

        const promedioCamaDisponible = diasFuncion > 0 ? camaDisponible / diasFuncion : 0;
        const promedioPacienteDia    = diasFuncion > 0 ? pacienteDia / diasFuncion : 0;
        const promedioDiasEstada     = egresosAmpliado > 0 ? pacienteDia / egresosAmpliado : 0;
        const promedioPermanencia    = promedioDiasEstada;
        const porcentajeOcupacional  = camaDisponible > 0 ? pacienteDia / camaDisponible : 0;
        const tasaMortalidad         = totalEgresos > 0 ? defuncion / totalEgresos : 0;
        const giroCama               = promedioCamaDisponible > 0 ? egresosAmpliado / promedioCamaDisponible : 0;
        const giroSustitucion        = totalEgresos > 0 ? (camaDisponible - pacienteDia) / totalEgresos : 0;
        const egresosPorDia          = diasFuncion > 0 ? totalEgresos / diasFuncion : 0;

        document.getElementById('campo_promedio_cama_disponible').value = promedioCamaDisponible.toFixed(1);
        document.getElementById('campo_promedio_paciente_dia').value    = promedioPacienteDia.toFixed(1);
        document.getElementById('campo_promedio_dias_estada').value     = promedioDiasEstada.toFixed(2);
        document.getElementById('campo_promedio_permanencia').value     = promedioPermanencia.toFixed(1);
        document.getElementById('campo_porcentaje_ocupacional').value   = (porcentajeOcupacional * 100).toFixed(2) + '%';
        document.getElementById('campo_tasa_mortalidad').value          = (tasaMortalidad * 100).toFixed(2) + '%';
        document.getElementById('campo_giro_cama').value                = giroCama.toFixed(1);
        document.getElementById('campo_giro_sustitucion').value         = giroSustitucion.toFixed(1);
        document.getElementById('campo_egresos_por_dia').value          = egresosPorDia.toFixed(1);
    }

    idsEntrada.forEach(function (id) {
        const el = document.getElementById('campo_' + id);
        if (el) el.addEventListener('input', recalcular);
    });

    recalcular();
})();
</script>

<?= $this->endSection() ?>