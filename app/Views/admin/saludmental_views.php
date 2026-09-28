<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Salud mental · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── SALUD_MENTAL_PANEL SPECIFIC STYLES ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── KPIS ── */
    .ml-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .ml-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #d0d7e0;
        flex: 0 1 260px;
        max-width: 320px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 90px;
        box-sizing: border-box;
    }
    .ml-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
        align-items: center;
    }
    .kpi-teal   { border-left: 6px solid #81e6d9; }
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-purple { border-left: 6px solid #d6bcfa; }
    .kpi-red    { border-left: 6px solid #feb2b2; }
    .kpi-navy   { border-left: 6px solid #a0aec0; }

    .ml-kpi-icon {
        width: 60px; height: 60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; flex-shrink: 0;
    }
    .kpi-teal   .ml-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue   .ml-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-purple .ml-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-red    .ml-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-navy   .ml-kpi-icon { background: #f7fafc; color: #4a5568; }

    .ml-kpi-label { font-size: 14px; font-weight: 700; color: #718096; text-transform: uppercase; text-align: center; }
    .ml-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .ml-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .ml-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .ml-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .ml-panel-title i { color: var(--teal); font-size: 17px; }
    .ml-panel-body { padding: 18px 20px; }
    .ml-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .ml-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .ml-filtro-group { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .ml-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .ml-filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: var(--white);
        outline: none;
        transition: border-color 0.15s;
        height: 36px;
        max-width: 100%;
    }
    .ml-filtro-select:focus { border-color: var(--teal); }

    .ml-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .ml-stats-table-wrap { overflow-x: auto; }
    .ml-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .ml-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left; white-space: nowrap;
    }
    .ml-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .ml-stats-table tbody tr:hover { background: #f8f9fb; }
    .ml-stats-table .num { text-align: right; white-space: nowrap; }
    .ml-stats-table thead th.num { text-align: right; }
    .ml-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .ml-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    /* Rango de estándar ocupacional */
    .ml-badge {
        display: inline-block; padding: 2px 8px; border-radius: 10px;
        font-size: 11px; font-weight: 700; color: #2d3748; white-space: nowrap;
    }

    .ml-chart-box { display: flex; flex-direction: column; min-width: 0; }
    .ml-chart-canvas { position: relative; width: 100%; height: 330px; }
    .ml-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    .ml-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .ml-stats-header i { color: var(--teal); }
    .ml-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .ml-stats-panel.is-collapsed { margin-bottom: 12px; }
    .ml-stats-panel.is-collapsed .ml-stats-header { border-radius: 12px; border-bottom: none; }
    .ml-stats-panel.is-collapsed .ml-stats-body { display: none; }

    /* ── PESTAÑAS ── */
    .ml-tabs {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 6px;
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 12px;
    }
    .ml-tab {
        flex: 1 1 160px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 11px 14px;
        border: none; border-radius: 9px;
        background: transparent;
        font-size: 12.5px; font-weight: 700; letter-spacing: 0.3px;
        text-transform: uppercase;
        color: var(--text-muted);
        cursor: pointer;
        transition: background 0.15s, color 0.15s, box-shadow 0.15s;
    }
    .ml-tab:hover { background: #e9edf2; color: var(--text-main); }
    .ml-tab.is-active {
        background: var(--navy); color: #fff;
        box-shadow: 0 2px 6px rgba(14,42,77,0.18);
    }
    .ml-tab.is-active i { color: var(--teal); }
    .ml-tab-pane { display: none; }
    .ml-tab-pane.is-active { display: block; }

    @media (max-width: 992px) {
        .ml-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .ml-panel-body { padding: 14px 12px; }
        .ml-stats-body { padding: 12px; }
        .ml-filtro-group { flex: 1 1 100%; }
        .ml-filtro-select { width: 100%; }
        .ml-kpi-card { flex: 1 1 100%; max-width: none; }
        .ml-chart-canvas { height: 280px; }
        .ml-tab { flex: 1 1 45%; font-size: 11.5px; padding: 10px 8px; }
    }
</style>

<!-- Leaflet (mapa) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ── MAPA ── */
    .sm-mapa-wrap { padding: 18px 18px 0; }
    #mapa-electro {
        height: 440px;
        width: 100%;
        border-radius: 10px;
        border: 1px solid var(--border);
        z-index: 1;
    }
    .leyenda-mapa {
        background: var(--navy);
        border-radius: 8px;
        padding: 8px 12px;
        color: #fff;
        font-size: 12px;
        display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
        margin-top: 8px;
    }
    .leyenda-dot {
        display: inline-block; width: 12px; height: 12px;
        border-radius: 50%; margin-right: 4px; vertical-align: middle;
    }
    .leyenda-nota { margin-left: auto; color: rgba(255,255,255,0.6); font-size: 11px; }
    .leaflet-popup-content strong { color: #13304d; font-size: 13px; }
    .leaflet-popup-content table  { font-size: 12px; margin-top: 4px; }
    .leaflet-popup-content td     { padding: 1px 4px; vertical-align: top; }
    .ml-stats-body.dos-col { grid-template-columns: 1.1fr 1fr; }
    .ml-stats-panel.is-collapsed .ml-stats-content { display: none; }
    @media (max-width: 992px) { .ml-stats-body.dos-col { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { #mapa-electro { height: 340px; } .sm-mapa-wrap { padding: 12px 12px 0; } }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $pct = function ($parte, $total) { return $total > 0 ? number_format($parte * 100 / $total, 1, ',', '.') . '%' : '0%'; };

    // Link de Excel con los filtros que acepta ElectrodependienteController
    $q = http_build_query(array_filter([
        'efector'       => $filtro_efector,
        'localidad'     => $filtro_localidad,
        'tipo'          => $filtro_tipo,
        'factor_riesgo' => $filtro_factor,
    ], 'strlen'));
    $urlExcel = base_url(route_to('electrodependiente_export')) . ($q !== '' ? '?' . $q : '');

    $coloresRiesgo = ['ALTO' => '#e53e3e', 'MEDIANO' => '#d69e2e', 'BAJO' => '#38a169', 'S/D' => '#718096'];

    $headerStats = function ($titulo) {
        return '<div class="ml-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ' . esc($titulo) . '</span>
                    <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                    </button>
                </div>';
    };

    $alto = function ($cantidad) { return max(330, $cantidad * 26 + 40); };
    $sinGeo = $kpi['pacientes'] - $kpi['geo_validos'];
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <strong>Salud mental — Electrodependientes</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-plug"></i> SALUD MENTAL · ELECTRODEPENDIENTES
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('inicio_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= $urlExcel ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('saludmental_views')); ?>" id="searchForm">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ml-filtro-bar">
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Hospital</span>
                    <select name="efector" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $e): ?>
                            <option value="<?= esc($e['efector_id']) ?>" <?= $filtro_efector == $e['efector_id'] ? 'selected' : '' ?>><?= esc($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Localidad</span>
                    <select name="localidad" class="ml-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($localidades as $l): ?>
                            <option value="<?= esc($l['localidad_id']) ?>" <?= $filtro_localidad == $l['localidad_id'] ? 'selected' : '' ?>><?= esc($l['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Tipo</span>
                    <select name="tipo" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach (['ADULTO' => 'Adulto', 'NIÑO' => 'Niño'] as $v => $t): ?>
                            <option value="<?= esc($v) ?>" <?= $filtro_tipo == $v ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Factor de riesgo</span>
                    <select name="factor_riesgo" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach (['ALTO', 'MEDIANO', 'BAJO'] as $r): ?>
                            <option value="<?= $r ?>" <?= $filtro_factor == $r ? 'selected' : '' ?>><?= ucfirst(strtolower($r)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('saludmental_views')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── PESTAÑAS ── -->
        <div class="ml-tabs" role="tablist">
            <button type="button" class="ml-tab <?= $tab === 'perfil' ? 'is-active' : '' ?>" data-tab="perfil" role="tab">
                <i class="fas fa-user-injured"></i> Perfil de pacientes
            </button>
            <button type="button" class="ml-tab <?= $tab === 'mapa' ? 'is-active' : '' ?>" data-tab="mapa" role="tab">
                <i class="fas fa-map-marked-alt"></i> Territorio y mapa
            </button>
        </div>

        <!-- ══════════════ PERFIL DE PACIENTES ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'perfil' ? 'is-active' : '' ?>" data-pane="perfil">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-plug"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Pacientes</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['pacientes']) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-child"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Adultos / niños</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['adultos']) ?> / <?= $fmt($kpi['ninos']) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Riesgo alto</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['riesgo_alto']) ?> <small style="font-size:13px; color:#718096;">(<?= $pct($kpi['riesgo_alto'], $kpi['pacientes']) ?>)</small></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-id-card"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Con CUD</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['con_cud']) ?> <small style="font-size:13px; color:#718096;">(<?= $pct($kpi['con_cud'], $kpi['pacientes']) ?>)</small></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE PACIENTES') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Pacientes por diagnóstico</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Diagnóstico</th><th class="num">Pacientes</th><th class="num">%</th><th class="num">Riesgo alto</th><th class="num">Niños</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($porDiagnostico)): ?>
                                    <tr><td colspan="5" class="ml-stats-vacio">Sin pacientes para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($porDiagnostico as $f): ?>
                                <tr>
                                    <td><?= esc($f['diagnostico']) ?></td>
                                    <td class="num"><?= $fmt($f['pacientes']) ?></td>
                                    <td class="num"><?= $pct($f['pacientes'], $kpi['pacientes']) ?></td>
                                    <td class="num"><?= $fmt($f['alto']) ?></td>
                                    <td class="num"><?= $fmt($f['ninos']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($porDiagnostico)): ?>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td class="num"><?= $fmt($kpi['pacientes']) ?></td>
                                    <td class="num">100%</td>
                                    <td class="num"><?= $fmt($kpi['riesgo_alto']) ?></td>
                                    <td class="num"><?= $fmt($kpi['ninos']) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Principales diagnósticos</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(min(10, count($porDiagnostico))) ?>px;"><canvas id="chartDiagnostico"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Factor de riesgo</div>
                        <div class="ml-chart-canvas"><canvas id="chartRiesgo"></canvas></div>
                        <div class="ml-chart-title" style="margin-top:16px;">Riesgo según tipo de paciente</div>
                        <div class="ml-chart-canvas" style="height:200px;"><canvas id="chartRiesgoTipo"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ TERRITORIO Y MAPA ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'mapa' ? 'is-active' : '' ?>" data-pane="mapa">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">En el mapa</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['geo_validos']) ?> <small style="font-size:13px; color:#718096;">(<?= $pct($kpi['geo_validos'], $kpi['pacientes']) ?>)</small></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-map-signs"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Sin coordenadas</div>
                        <div class="ml-kpi-value"><?= $fmt($sinGeo) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-hospital"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Hospitales</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['hospitales']) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-city"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Localidades</div>
                        <div class="ml-kpi-value"><?= $fmt($kpi['localidades']) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('DISTRIBUCIÓN TERRITORIAL') ?>
                <div class="ml-stats-content">
                    <div class="sm-mapa-wrap">
                        <div id="mapa-electro"></div>
                        <div class="leyenda-mapa">
                            <strong style="color:#ffe08a;">Riesgo:</strong>
                            <?php foreach ($coloresRiesgo as $r => $c): ?>
                                <span><span class="leyenda-dot" style="background:<?= $c ?>;"></span><?= $r ?></span>
                            <?php endforeach; ?>
                            <span class="leyenda-nota">Solo pacientes con coordenadas cargadas (<?= $fmt($kpi['geo_validos']) ?> de <?= $fmt($kpi['pacientes']) ?>).</span>
                        </div>
                    </div>

                    <div class="ml-stats-body dos-col">
                        <div class="ml-stats-table-wrap">
                            <div class="ml-chart-title" style="text-align:left;">Pacientes por localidad</div>
                            <table class="ml-stats-table">
                                <thead>
                                    <tr><th>Localidad</th><th class="num">Pacientes</th><th class="num">Riesgo alto</th><th class="num">En mapa</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porLocalidad)): ?>
                                        <tr><td colspan="4" class="ml-stats-vacio">Sin pacientes para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porLocalidad as $f): ?>
                                    <tr>
                                        <td><?= esc($f['localidad']) ?></td>
                                        <td class="num"><?= $fmt($f['pacientes']) ?></td>
                                        <td class="num"><?= $fmt($f['alto']) ?></td>
                                        <td class="num"><?= $fmt($f['geo']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porLocalidad)): ?>
                                <tfoot>
                                    <tr>
                                        <td>Total</td>
                                        <td class="num"><?= $fmt($kpi['pacientes']) ?></td>
                                        <td class="num"><?= $fmt($kpi['riesgo_alto']) ?></td>
                                        <td class="num"><?= $fmt($kpi['geolocalizados']) ?></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <div class="ml-chart-box">
                            <div class="ml-chart-title">Pacientes por hospital de referencia</div>
                            <div class="ml-chart-canvas" style="height:<?= $alto(count($porHospital)) ?>px;"><canvas id="chartHospital"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    var porDiagnostico = <?= json_encode(array_slice($porDiagnostico, 0, 10)) ?>;
    var porRiesgo      = <?= json_encode($porRiesgo) ?>;
    var riesgoPorTipo  = <?= json_encode($riesgoPorTipo) ?>;
    var porHospital    = <?= json_encode($porHospital) ?>;
    var puntos         = <?= json_encode($puntos) ?>;
    var coloresRiesgo  = <?= json_encode($coloresRiesgo) ?>;

    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99'
    ];

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
    }

    function formatoNumero(n) {
        return Number(n).toLocaleString('es-AR');
    }

    function acortar(texto, max) {
        texto = String(texto || '');
        return texto.length > max ? texto.slice(0, max - 1) + '…' : texto;
    }

    function escapar(texto) {
        return String(texto == null ? '' : texto).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // ── Colapsar / expandir paneles de estadísticas ──
    document.querySelectorAll('[data-stats-panel]').forEach(function (panel) {
        var btn   = panel.querySelector('[data-stats-toggle]');
        var icono = btn.querySelector('i');
        var texto = btn.querySelector('span');

        btn.addEventListener('click', function () {
            var colapsado = panel.classList.toggle('is-collapsed');
            icono.className   = colapsado ? 'fas fa-eye' : 'fas fa-eye-slash';
            texto.textContent = colapsado ? 'Mostrar estadísticas' : 'Ocultar estadísticas';
            if (!colapsado && mapa) setTimeout(function () { mapa.invalidateSize(); }, 150);
        });
    });

    // ── Plugin: total en el centro de las donas ──
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function (chart) {
            if (chart.config.type !== 'doughnut') return;
            var ctx = chart.ctx;
            var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
            var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
            var total = chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = 'bold 15px sans-serif';
            ctx.fillStyle = '#555555';
            ctx.fillText('TOTAL', centerX, centerY - 12);
            ctx.font = 'bold 20px sans-serif';
            ctx.fillStyle = '#222222';
            ctx.fillText(formatoNumero(total), centerX, centerY + 13);
            ctx.restore();
        }
    };

    function crearBarrasH(id, etiquetas, valores, color) {
        new Chart(document.getElementById(id), {
            type: 'bar',
            data: {
                labels: etiquetas.map(function (e) { return acortar(e, 30); }),
                datasets: [{ data: valores, backgroundColor: color, borderRadius: 4, maxBarThickness: 22 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { title: function (items) { return etiquetas[items[0].dataIndex]; } } },
                    datalabels: { anchor: 'end', align: 'end', color: '#333333', font: { weight: 'bold', size: 10 } }
                },
                scales: {
                    x: { beginAtZero: true, grace: '15%', ticks: { precision: 0 } },
                    y: { ticks: { autoSkip: false, font: { size: 10 } } }
                },
                layout: { padding: { right: 20 } }
            }
        });
    }

    // ══ Gráficos: PERFIL ══
    function graficosPerfil() {
        crearBarrasH(
            'chartDiagnostico',
            porDiagnostico.map(function (d) { return d.diagnostico; }),
            porDiagnostico.map(function (d) { return parseInt(d.pacientes, 10); }),
            '#90cdf4'
        );

        var seccionesConDatos = porRiesgo.filter(function (r) { return Number(r.pacientes) > 0; }).length;
        new Chart(document.getElementById('chartRiesgo'), {
            type: 'doughnut',
            data: {
                labels: porRiesgo.map(function (r) { return r.riesgo; }),
                datasets: [{
                    data: porRiesgo.map(function (r) { return parseInt(r.pacientes, 10); }),
                    backgroundColor: porRiesgo.map(function (r) { return coloresRiesgo[r.riesgo] || '#cbd5e0'; }),
                    borderWidth: 0,
                    spacing: seccionesConDatos > 1 ? 6 : 0,
                    offset: seccionesConDatos > 1 ? 10 : 0,
                    borderRadius: 5
                }]
            },
            plugins: [centerTextPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: esMovil() ? 'bottom' : 'right', labels: { boxWidth: 12, padding: 8 } },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 12 },
                        formatter: function (value, ctx) {
                            var total = ctx.chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            var p = total ? Math.round(value * 100 / total) : 0;
                            return p > 4 ? p + '%' : '';
                        }
                    }
                }
            }
        });

        // Riesgo según tipo: barras apiladas horizontales (ADULTO / NIÑO)
        var tipos = [];
        Object.keys(riesgoPorTipo).forEach(function (r) {
            Object.keys(riesgoPorTipo[r]).forEach(function (t) { if (tipos.indexOf(t) === -1) tipos.push(t); });
        });
        var riesgos = ['ALTO', 'MEDIANO', 'BAJO', 'S/D'].filter(function (r) { return riesgoPorTipo[r]; });
        new Chart(document.getElementById('chartRiesgoTipo'), {
            type: 'bar',
            data: {
                labels: tipos,
                datasets: riesgos.map(function (r) {
                    return {
                        label: r,
                        backgroundColor: coloresRiesgo[r],
                        data: tipos.map(function (t) { return riesgoPorTipo[r][t] || 0; }),
                        maxBarThickness: 30
                    };
                })
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12 } },
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return v > 3 ? v : ''; }
                    }
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                    y: { stacked: true }
                }
            }
        });
    }

    // ══ Gráficos y mapa: TERRITORIO ══
    var mapa = null;

    function colorRiesgo(r) {
        return coloresRiesgo[(r || '').toUpperCase()] || coloresRiesgo['S/D'];
    }

    function crearIcono(r) {
        var c = colorRiesgo(r);
        return L.divIcon({
            html: '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="36" viewBox="0 0 28 38">' +
                  '<path d="M14 0C6.27 0 0 6.27 0 14c0 9.75 14 24 14 24S28 23.75 28 14C28 6.27 21.73 0 14 0z" fill="' + c + '" stroke="#fff" stroke-width="2"/>' +
                  '<circle cx="14" cy="14" r="6" fill="white" opacity="0.9"/></svg>',
            className: '', iconSize: [26, 36], iconAnchor: [13, 36], popupAnchor: [0, -34]
        });
    }

    function graficosMapa() {
        mapa = L.map('mapa-electro', { zoomControl: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>',
            maxZoom: 18
        }).addTo(mapa);

        var grupo = L.featureGroup();
        puntos.forEach(function (p) {
            L.marker([p.lat, p.lng], { icon: crearIcono(p.riesgo) })
                .bindPopup(
                    '<strong>' + escapar(p.paciente) + '</strong><table>' +
                    '<tr><td>Edad:</td><td>' + escapar(p.edad || '—') + ' (' + escapar(p.tipo || '—') + ')</td></tr>' +
                    '<tr><td>Localidad:</td><td>' + escapar(p.localidad || '—') + '</td></tr>' +
                    '<tr><td>Hospital:</td><td>' + escapar(p.efector || '—') + '</td></tr>' +
                    '<tr><td>Diagnóstico:</td><td>' + escapar(p.diagnostico || '—') + '</td></tr>' +
                    '<tr><td>Riesgo:</td><td><strong style="color:' + colorRiesgo(p.riesgo) + '">' + escapar(p.riesgo || 'S/D') + '</strong></td></tr>' +
                    '</table>',
                    { maxWidth: 260 }
                )
                .addTo(grupo);
        });
        grupo.addTo(mapa);

        if (puntos.length > 0) mapa.fitBounds(grupo.getBounds().pad(0.15));
        else mapa.setView([-24.185, -65.299], 8);   // San Salvador de Jujuy

        crearBarrasH(
            'chartHospital',
            porHospital.map(function (h) { return h.hospital; }),
            porHospital.map(function (h) { return parseInt(h.pacientes, 10); }),
            '#81e6d9'
        );
    }

    // ══ Pestañas ══
    // Los gráficos y el mapa se dibujan la primera vez que se muestra su pestaña
    // (Chart.js y Leaflet no pueden medir un contenedor oculto).
    (function () {
        var dibujar    = { perfil: graficosPerfil, mapa: graficosMapa };
        var dibujados  = {};
        var inputTab   = document.getElementById('inputTab');
        var btnLimpiar = document.getElementById('btnLimpiar');

        function mostrar(tab) {
            document.querySelectorAll('.ml-tab').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.tab === tab);
            });
            document.querySelectorAll('.ml-tab-pane').forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.pane === tab);
            });

            // Mantener la pestaña al filtrar, limpiar o recargar
            inputTab.value = tab;
            btnLimpiar.href = btnLimpiar.href.split('?')[0] + '?tab=' + tab;
            var params = new URLSearchParams(window.location.search);
            params.set('tab', tab);
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());

            if (!dibujados[tab]) {
                dibujar[tab]();
                dibujados[tab] = true;
            } else if (tab === 'mapa' && mapa) {
                setTimeout(function () { mapa.invalidateSize(); }, 50);
            }
        }

        document.querySelectorAll('.ml-tab').forEach(function (b) {
            b.addEventListener('click', function () { mostrar(b.dataset.tab); });
        });

        mostrar(inputTab.value === 'mapa' ? 'mapa' : 'perfil');
    })();
</script>
<?= $this->endSection() ?>
