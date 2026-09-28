<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> HISTORIAL — <?= esc($paciente->paciente ?? $paciente['paciente']) ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .hist-header {
        background: #13304d;
        padding: 1rem 1.25rem 0.75rem;
        border-radius: 8px 8px 0 0;
    }
    .hist-header h1 { color: #fff; font-size: 1.15rem; margin: 0; }
    .hist-header p  { color: #8fb3d4; font-size: 0.82rem; margin: 0.2rem 0 0; }

    .hist-section {
        background: #fff;
        border: 1px solid #d0dbe8;
        border-radius: 0 0 8px 8px;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .hist-section-title {
        background: #e8f0fe;
        padding: 0.5rem 1rem;
        font-weight: 700;
        font-size: 0.85rem;
        color: #13304d;
        border-bottom: 1px solid #c5d4e8;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Tabla historial paciente */
    .tabla-hist td, .tabla-hist th {
        vertical-align: middle !important;
        font-size: 0.80rem;
        white-space: nowrap;
    }
    .tabla-hist td.val-texto {
        white-space: pre-wrap;
        word-break: break-word;
        max-width: 280px;
    }
    .badge-campo {
        background: #dbeafe;
        color: #1e40af;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Tarjeta equipo historial */
    .eq-card {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-bottom: 0.75rem;
        overflow: hidden;
    }
    .eq-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.45rem 0.85rem;
        font-size: 0.82rem;
        font-weight: 700;
    }
    .eq-card-header.alta       { background: #d4edda; color: #155724; }
    .eq-card-header.modificacion { background: #fff3cd; color: #856404; }
    .eq-card-header.baja       { background: #f8d7da; color: #721c24; }

    .eq-diff {
        padding: 0.5rem 0.85rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 16px;
        font-size: 0.78rem;
        background: #fafbfc;
    }
    .eq-diff-row { display: contents; }
    .eq-diff-lbl { color: #888; font-weight: 600; grid-column: 1 / -1; margin-top: 6px; font-size: 0.72rem; text-transform: uppercase; }
    .eq-diff-ant { color: #b91c1c; text-decoration: line-through; }
    .eq-diff-nvo { color: #15803d; font-weight: 600; }
    .eq-diff-sin { color: #aaa; font-style: italic; }

    .eq-simple {
        padding: 0.5rem 0.85rem;
        font-size: 0.78rem;
        color: #555;
        background: #fafbfc;
    }
    .eq-simple table { width: 100%; }
    .eq-simple td    { padding: 2px 6px; }
    .eq-simple td:first-child { color: #888; font-weight: 600; width: 38%; }

    .sin-registros {
        padding: 1.2rem;
        text-align: center;
        color: #aaa;
        font-style: italic;
        font-size: 0.85rem;
    }

    /* campos a mostrar en diff de equipos */
    /* ── mobile ── */
    @media (max-width: 640px) {
        .eq-diff { grid-template-columns: 1fr; }
        .tabla-hist { font-size: 0.75rem; }
        .hist-header h1 { font-size: 1rem; }
    }
</style>

<?php
// helper para leer indistintamente objeto o array
$get = fn($obj, $k) => is_array($obj) ? ($obj[$k] ?? '—') : ($obj->$k ?? '—');

// Campos de equipo con etiqueta legible
$camposEquipo = [
    'equipamiento'       => 'Equipamiento',
    'marca'              => 'Marca',
    'serie'              => 'Serie',
    'modelo'             => 'Modelo',
    'fecha_entrega'      => 'Fecha entrega',
    'tiempo_uso'         => 'Tiempo de uso',
    'medico_tratante'    => 'Médico tratante',
    'titular_servicio'   => 'Titular del servicio',
    'nro_servicio'       => 'Nº Servicio',
    'fecha_ingreso_recs' => 'Fecha ingreso RECS',
    'ultima_evaluacion'  => 'Última evaluación',
    'estado'             => 'Estado',
];
?>

<div class="container is-fluid">

    <!-- Encabezado -->
    <div class="hist-header" style="border-radius:8px; margin-bottom:1rem;">
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <div>
                <h1>📋 Historial de cambios</h1>
                <p>
                    <strong style="color:#ffe08a;"><?= esc($get($paciente, 'paciente')) ?></strong>
                    &nbsp;·&nbsp; DNI: <?= esc($get($paciente, 'dni') ?: '—') ?>
                    &nbsp;·&nbsp; ID #<?= esc($get($paciente, 'electrodependiente_id')) ?>
                </p>
            </div>
            <a class="button is-light is-small"
               href="<?= base_url(route_to('electrodependiente_show', $get($paciente, 'electrodependiente_id'))) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver al paciente</span>
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECCIÓN 1: Cambios de datos del paciente
    ══════════════════════════════════════════════════════ -->
    <div class="hist-section">
        <div class="hist-section-title">
            <i class="fas fa-user-edit"></i> Cambios en datos del paciente
            <span class="tag is-dark is-small" style="margin-left:auto;"><?= count($historialPaciente) ?> registro<?= count($historialPaciente) != 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($historialPaciente)): ?>
            <div class="sin-registros">Sin cambios registrados aún.</div>
        <?php else: ?>
        <div class="table-container">
        <table class="table is-fullwidth is-hoverable is-bordered tabla-hist" style="margin:0;">
            <thead>
                <tr style="background:#f0f4ff;">
                    <th>Fecha y hora</th>
                    <th>Campo modificado</th>
                    <th>Valor anterior</th>
                    <th>Valor nuevo</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($historialPaciente as $h): ?>
                <tr>
                    <td style="white-space:nowrap;">
                        <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                    </td>
                    <td>
                        <span class="badge-campo"><?= esc($h['campo_modificado']) ?></span>
                    </td>
                    <td class="val-texto" style="color:#b91c1c;">
                        <?= $h['valor_anterior'] !== null && $h['valor_anterior'] !== ''
                            ? esc($h['valor_anterior'])
                            : '<span style="color:#aaa;font-style:italic;">vacío</span>' ?>
                    </td>
                    <td class="val-texto" style="color:#15803d; font-weight:600;">
                        <?= $h['valor_nuevo'] !== null && $h['valor_nuevo'] !== ''
                            ? esc($h['valor_nuevo'])
                            : '<span style="color:#aaa;font-style:italic;">vacío</span>' ?>
                    </td>
                    
<td>
<?php
    $nombreUsuario = '—';
    if ($h['modificado_por']) {
        $u = model('UserModel')->find($h['modificado_por']);
        $nombreUsuario = $u 
            ? esc($u->username ?? $u->name ?? $u->email ?? '#' . $h['modificado_por'])
            : '#' . $h['modificado_por'];
    }
    echo $nombreUsuario;
?>
</td>

                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════
         SECCIÓN 2: Historial de equipamiento
    ══════════════════════════════════════════════════════ -->
    <div class="hist-section">
        <div class="hist-section-title">
            <i class="fas fa-plug"></i> Historial de equipamiento
            <span class="tag is-dark is-small" style="margin-left:auto;"><?= count($historialEquipos) ?> registro<?= count($historialEquipos) != 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($historialEquipos)): ?>
            <div class="sin-registros">Sin movimientos de equipos registrados aún.</div>
        <?php else: ?>
        <div style="padding: 0.75rem;">
        <?php foreach ($historialEquipos as $h):
            $accion   = strtolower($h['accion']); // alta / modificacion / baja
            $ant      = $h['datos_anteriores'];    // array o null
            $nvo      = $h['datos_nuevos'];        // array o null
            $iconos   = ['alta' => '🟢', 'modificacion' => '🟡', 'baja' => '🔴'];
            $etiquetas = ['alta' => 'ALTA', 'modificacion' => 'MODIFICACIÓN', 'baja' => 'BAJA'];
            $nombreEquipo = $nvo['equipamiento'] ?? ($ant['equipamiento'] ?? '—');
        ?>
            <div class="eq-card">
                <div class="eq-card-header <?= $accion ?>">
                    <span><?= $iconos[$accion] ?? '⚪' ?> <?= $etiquetas[$accion] ?? strtoupper($accion) ?> — <?= esc($nombreEquipo) ?></span>
                    <span style="font-weight:400; font-size:0.78rem;">
                        <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?>
                        <?= $h['modificado_por'] ? ' · #' . esc($h['modificado_por']) : '' ?>
                    </span>
                </div>

                <?php if ($accion === 'modificacion' && $ant && $nvo): ?>
                    <!-- Mostrar solo los campos que cambiaron -->
                    <div class="eq-diff">
                        <?php
                        $huboCambio = false;
                        foreach ($camposEquipo as $campo => $etiq):
                            $va = isset($ant[$campo]) ? (string)$ant[$campo] : '';
                            $vn = isset($nvo[$campo]) ? (string)$nvo[$campo] : '';
                            if ($va === $vn) continue;
                            $huboCambio = true;
                        ?>
                            <div class="eq-diff-lbl"><?= esc($etiq) ?></div>
                            <div class="eq-diff-ant"><?= $va !== '' ? esc($va) : '<span class="eq-diff-sin">vacío</span>' ?></div>
                            <div class="eq-diff-nvo"><?= $vn !== '' ? esc($vn) : '<span class="eq-diff-sin">vacío</span>' ?></div>
                        <?php endforeach; ?>
                        <?php if (!$huboCambio): ?>
                            <div style="grid-column:1/-1; color:#aaa; font-style:italic; font-size:0.78rem;">Sin diferencias detectadas.</div>
                        <?php endif; ?>
                    </div>

                <?php elseif ($accion === 'alta' && $nvo): ?>
                    <!-- Mostrar los datos del equipo dado de alta -->
                    <div class="eq-simple">
                        <table>
                        <?php foreach ($camposEquipo as $campo => $etiq):
                            $v = $nvo[$campo] ?? '';
                            if ($v === '' || $v === null) continue;
                        ?>
                            <tr>
                                <td><?= esc($etiq) ?></td>
                                <td><?= esc($v) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                    </div>

                <?php elseif ($accion === 'baja' && $ant): ?>
                    <!-- Mostrar los datos del equipo dado de baja -->
                    <div class="eq-simple" style="opacity:0.7;">
                        <table>
                        <?php foreach ($camposEquipo as $campo => $etiq):
                            $v = $ant[$campo] ?? '';
                            if ($v === '' || $v === null) continue;
                        ?>
                            <tr>
                                <td><?= esc($etiq) ?></td>
                                <td style="text-decoration:line-through; color:#b91c1c;"><?= esc($v) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<?= $this->endSection() ?>