<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Panel de Inicio <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb i { color: var(--teal); font-size: 13px; }
    .breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .section-header {
        font-size: 10.5px; font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 1px;
        margin-bottom: 10px; padding-bottom: 6px;
        border-bottom: 2px solid var(--teal); display: inline-block;
    }
    .inicio-nota {
        font-size: 11.5px; color: var(--text-muted);
        display: inline-flex; align-items: center; gap: 6px;
        margin-left: 12px;
    }
    .inicio-nota i { color: var(--teal); }

    /* ── MODULE GRID ── */
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 14px; margin-bottom: 28px;
        max-width: 100%;
    }

    /* ── CARD DE MÓDULO CON INDICADORES ── */
    .module-card-prehosp {
        background: var(--white); border-radius: 12px;
        text-decoration: none; color: var(--text-main);
        border: 0.5px solid var(--border);
        position: relative; overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex; flex-direction: column;
        --c-main: #3489ff;
        --c-dark: #206bd9;
    }
    .module-card-prehosp.card-hospi     { --c-main: #e74c3c; --c-dark: #c0392b; }
    .module-card-prehosp.card-virtual   { --c-main: #3498db; --c-dark: #2980b9; }
    .module-card-prehosp.card-prehosp   { --c-main: #27ae60; --c-dark: #219150; }
    .module-card-prehosp.card-paciente  { --c-main: #9b59b6; --c-dark: #8e44ad; }
    .module-card-prehosp.card-salud     { --c-main: #e67e22; --c-dark: #d35400; }
    .module-card-prehosp.card-servicios { --c-main: #00bcd4; --c-dark: #0097a7; }
    .module-card-prehosp.disabled {
        opacity: 0.55; cursor: default; pointer-events: none;
        --c-main: #6c757d; --c-dark: #495057;
    }

    .module-card-prehosp::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; background: var(--c-dark);
        transform: scaleX(0); transition: transform 0.2s ease;
    }
    .module-card-prehosp:hover { transform: translateY(-4px); box-shadow: 0 8px 22px rgba(0,0,0,0.11); text-decoration: none; color: var(--text-main); }
    .module-card-prehosp:hover::before { transform: scaleX(1); }

    .mcp-header {
        padding: 16px 16px 12px; text-align: center;
        border-bottom: 1px solid var(--border);
        background: color-mix(in srgb, var(--c-main), transparent 85%);
    }
    .module-card-prehosp.disabled .mcp-header { padding: 30px 16px 24px; border-bottom: none; }
    .mcp-header .mc-icon  { font-size: 26px; color: var(--c-dark); margin-bottom: 6px; display: block; }
    .mcp-header .mc-title { font-size: 12px; font-weight: 700; color: var(--c-dark); text-transform: uppercase; letter-spacing: 0.5px; }
    .mcp-header .mc-sub   { font-size: 10.5px; color: var(--text-muted); margin-top: 2px; }

    .mcp-stats { padding: 12px 14px 14px; display: flex; flex-direction: column; flex: 1; }
    .mcp-row { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .mcp-stat {
        background: #f4f6f9; border-radius: 8px;
        padding: 8px 8px; text-align: center; min-width: 0;
    }
    .mcp-stat-val {
        font-size: 19px; font-weight: 700;
        color: var(--c-dark); line-height: 1.05;
        font-variant-numeric: tabular-nums;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .mcp-stat-val small { font-size: 12px; color: var(--text-muted); font-weight: 600; }
    .mcp-stat-label {
        font-size: 9px; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.5px;
        margin-top: 4px; line-height: 1.25;
    }
    .mcp-anio {
        display: inline-block; margin-top: 3px;
        font-size: 9px; font-weight: 700; color: var(--c-dark);
        background: color-mix(in srgb, var(--c-main), transparent 85%);
        padding: 1px 6px; border-radius: 6px;
    }
    .mcp-pill {
        display: flex; align-items: center; justify-content: center; gap: 5px;
        margin-top: auto; padding-top: 10px;
    }
    .mcp-pill span {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 10.5px; font-weight: 600;
        padding: 4px 10px; border-radius: 10px;
        background: color-mix(in srgb, var(--c-main), transparent 88%);
        color: var(--c-dark);
        border: 1px solid color-mix(in srgb, var(--c-main), transparent 70%);
    }
    .mcp-pill i { font-size: 9px; }

    /* ── ADMIN GRID ── */
    .admin-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        max-width: 100%;
    }
    .admin-card {
        background: var(--white); border-radius: 10px; padding: 14px 16px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; color: var(--text-main);
        border: 0.5px solid var(--border); transition: box-shadow 0.2s, background 0.15s;
    }
    .admin-card:hover { box-shadow: 0 3px 10px rgba(0,0,0,0.09); background: #f5faff; text-decoration: none; color: var(--text-main); }
    .ac-icon { width: 36px; height: 36px; border-radius: 8px; background: var(--teal-bg); display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--teal); flex-shrink: 0; }
    .ac-title { font-size: 12px; font-weight: 700; color: var(--text-main); }
    .ac-desc  { font-size: 10.5px; color: var(--text-muted); margin-top: 2px; }

    @media (max-width: 600px) {
        .inicio-nota { margin-left: 0; margin-bottom: 8px; display: flex; }
        .module-grid { grid-template-columns: 1fr; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $dec = function ($n, $d = 1) { return number_format((float) $n, $d, ',', '.'); };

    // Indicador chico: valor, etiqueta y año (o "vigente")
    $stat = function ($valor, $etiqueta, $anio = null) {
        return '<div class="mcp-stat">
                    <div class="mcp-stat-val">' . $valor . '</div>
                    <div class="mcp-stat-label">' . $etiqueta . '</div>
                    <span class="mcp-anio">' . ($anio === null ? 'vigente' : esc($anio)) . '</span>
                </div>';
    };
?>

<div class="breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a> › <strong>Panel de inicio</strong>
</div>

<!-- MÓDULOS -->
<div class="section-header">Módulos del sistema</div>
<span class="inicio-nota"><i class="fas fa-info-circle"></i> Cada dato indica su año: se usa el último año cerrado con información cargada.</span>
<div class="module-grid" style="margin-top:10px;">

    <!-- HOSPITALARIO -->
    <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="module-card-prehosp card-hospi">
        <div class="mcp-header">
            <i class="fas fa-hospital mc-icon"></i>
            <div class="mc-title">Hospitalario</div>
            <div class="mc-sub">Guardia · Internación · Quirófano</div>
        </div>
        <div class="mcp-stats">
            <div class="mcp-row">
                <?= $stat($fmt($hosp['guardia']), 'Atenciones<br>de guardia', $hosp['anio_guardia']) ?>
                <?= $stat($fmt($hosp['egresos']), 'Egresos<br>hospitalarios', $hosp['anio_rend']) ?>
                <?= $stat($fmt($hosp['cirugias']), 'Cirugías<br>realizadas', $hosp['anio_qui']) ?>
                <?= $stat($fmt($hosp['camas']), 'Camas<br>disponibles') ?>
            </div>
            <div class="mcp-pill">
                <span><i class="fas fa-circle"></i> <?= $dec($hosp['ocupacion']) ?>% ocupación (<?= esc($hosp['anio_rend']) ?>)</span>
            </div>
        </div>
    </a>

    <!-- PREHOSPITALARIO -->
    <a href="<?= base_url(route_to('base_views')); ?>" class="module-card-prehosp card-prehosp">
        <div class="mcp-header">
            <i class="fas fa-ambulance mc-icon"></i>
            <div class="mc-title">Prehospitalario</div>
            <div class="mc-sub">Bases · Móviles · Asistencias</div>
        </div>
        <div class="mcp-stats">
            <div class="mcp-row">
                <?= $stat($fmt($pre['bases_activas']) . '<small>/' . $fmt($pre['bases_total']) . '</small>', 'Bases<br>activas') ?>
                <?= $stat($fmt($pre['moviles_op']) . '<small>/' . $fmt($pre['moviles_flota']) . '</small>', 'Móviles<br>operativos', $pre['anio_movil']) ?>
                <?= $stat($fmt($pre['asistencias']), 'Asistencias<br>realizadas', $pre['anio_asist']) ?>
                <?= $stat($fmt($pre['atenciones']), 'Atenciones<br>en base', $pre['anio_atenc']) ?>
            </div>
            <div class="mcp-pill">
                <span><i class="fas fa-circle"></i> <?= $pre['pct_flota'] ?>% de la flota operativa (<?= esc($pre['anio_movil']) ?>)</span>
            </div>
        </div>
    </a>

    <!-- GESTIÓN PACIENTE -->
    <a href="<?= base_url(route_to('paciente_views')); ?>" class="module-card-prehosp card-paciente">
        <div class="mcp-header">
            <i class="fas fa-user-injured mc-icon"></i>
            <div class="mc-title">Gestión paciente</div>
            <div class="mc-sub">Turnos · 0800 · Chat bot · Reclamos</div>
        </div>
        <div class="mcp-stats">
            <div class="mcp-row">
                <?= $stat($fmt($pac['turnos']), 'Turnos<br>otorgados', $pac['anio_turnos']) ?>
                <?= $stat($fmt($pac['llamadas']), 'Llamadas<br>0800', $pac['anio_call']) ?>
                <?= $stat($fmt($pac['chat_bot']), 'Turnos por<br>chat bot', $pac['anio_chat']) ?>
                <?= $stat($fmt($pac['consultas']), 'Consultas y<br>reclamos', $pac['anio_consulta']) ?>
            </div>
            <div class="mcp-pill">
                <span><i class="fas fa-circle"></i> <?= $pac['pct_atendidas'] ?>% llamadas atendidas · <?= $pac['pct_ausentes'] ?>% ausentismo</span>
            </div>
        </div>
    </a>

    <!-- SALUD MENTAL -->
    <a href="<?= base_url(route_to('saludmental_views')); ?>" class="module-card-prehosp card-salud">
        <div class="mcp-header">
            <i class="fas fa-brain mc-icon"></i>
            <div class="mc-title">Salud mental</div>
            <div class="mc-sub">Electrodependientes · Camas</div>
        </div>
        <div class="mcp-stats">
            <div class="mcp-row">
                <?= $stat($fmt($sm['pacientes']), 'Pacientes<br>electrodep.') ?>
                <?= $stat($fmt($sm['riesgo_alto']), 'Riesgo<br>alto') ?>
                <?= $stat($fmt($sm['geo']), 'Ubicados<br>en mapa') ?>
                <?= $stat($fmt($sm['camas']), 'Camas<br>salud mental') ?>
            </div>
            <div class="mcp-pill">
                <span><i class="fas fa-circle"></i> <?= $dec($sm['pct_alto']) ?>% de pacientes con riesgo alto</span>
            </div>
        </div>
    </a>

    <!-- SERVICIOS TRANSVERSALES -->
    <a href="<?= base_url(route_to('servicio_views')); ?>" class="module-card-prehosp card-servicios">
        <div class="mcp-header">
            <i class="fas fa-project-diagram mc-icon"></i>
            <div class="mc-title">Servicios transversales</div>
            <div class="mc-sub">Operativos · Transfusión</div>
        </div>
        <div class="mcp-stats">
            <div class="mcp-row">
                <?= $stat($fmt($ser['transfusiones']), 'Transfusiones', $ser['anio_transf']) ?>
                <?= $stat($fmt($ser['hospitales']), 'Hospitales<br>que transfunden', $ser['anio_transf']) ?>
                <?= $stat($fmt($ser['operativos']), 'Operativos<br>realizados', $ser['anio_oper']) ?>
                <?= $stat($fmt($ser['via_publica']), 'En vía<br>pública', $ser['anio_oper']) ?>
            </div>
            <div class="mcp-pill">
                <?php if ($ser['variacion'] === null): ?>
                    <span><i class="fas fa-circle"></i> Sin datos de <?= esc($ser['anio_transf'] - 1) ?> para comparar</span>
                <?php else: ?>
                    <span><i class="fas fa-<?= $ser['variacion'] >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= $dec(abs($ser['variacion'])) ?>% transfusiones vs <?= esc($ser['anio_transf'] - 1) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </a>

    <!-- HOSPITAL VIRTUAL (próximamente) -->
    <div class="module-card-prehosp disabled card-virtual">
        <div class="mcp-header">
            <i class="fas fa-laptop-medical mc-icon"></i>
            <div class="mc-title">Hospital virtual</div>
            <div class="mc-sub">Próximamente</div>
        </div>
    </div>

</div>

<!-- ADMINISTRACIÓN -->
<div class="section-header">Administración</div>
<div class="admin-grid" style="margin-top:10px;">
    <a href="<?= base_url(route_to('list_users')); ?>" class="admin-card">
        <div class="ac-icon"><i class="fas fa-user-cog"></i></div>
        <div><div class="ac-title">Admin usuarios</div><div class="ac-desc">Gestión de cuentas</div></div>
    </a>
    <a href="<?= base_url(route_to('groups_list')); ?>" class="admin-card">
        <div class="ac-icon"><i class="fas fa-users-cog"></i></div>
        <div><div class="ac-title">Admin grupos</div><div class="ac-desc">Roles y permisos</div></div>
    </a>
</div>
<?= $this->endSection() ?>
