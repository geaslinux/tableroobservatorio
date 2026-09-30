<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Ambulatorio · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── CARTA_SERVICIO_PANEL SPECIFIC STYLES ── */
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
    .ml-panel-title i { color: #fff; font-size: 17px; }
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
        padding: 8px 10px; text-align: left;
    }
    .ml-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .ml-stats-table tbody tr:hover { background: #f8f9fb; }
    .ml-stats-table .num { text-align: right; }
    .ml-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .ml-stats-table + .ml-chart-title { margin-top: 18px; }
    .ml-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    .ml-chart-box { display: flex; flex-direction: column; min-width: 0; }
    .ml-sin-datos {
        height: 330px;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
        color: var(--text-muted); font-size: 13px; font-style: italic; text-align: center;
        border: 1px dashed var(--border); border-radius: 10px;
    }
    .ml-sin-datos i { font-size: 28px; opacity: 0.4; font-style: normal; }
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

    /* ── PESTAÑAS (Carta / RRHH) ── */
    .ml-tabs {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 6px;
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 12px;
    }
    .ml-tab {
        flex: 1 1 200px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 11px 16px;
        border: none; border-radius: 9px;
        background: transparent;
        font-size: 13.5px; font-weight: 700; letter-spacing: 0.3px;
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
    .ml-tab.is-active i { color: #fff; }
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
        .ml-chart-canvas, .ml-sin-datos { height: 280px; }
    }
</style>

<?php
    // Parámetros de filtro para los links de exportación
    $queryFiltros = http_build_query(array_filter([
        'hospital'          => $filtro_hospital,
        'region'            => $filtro_region,
        'nivel_complejidad' => $filtro_nivel,
    ], 'strlen'));
    $queryFiltros = $queryFiltros !== '' ? '?' . $queryFiltros : '';

    $totalTurnosTabla = array_sum(array_column($cartaPorEspecialidad, 'turnos'));
    $totalAgendasTabla = array_sum(array_column($cartaPorEspecialidad, 'agendas'));
    $totalRrhhTabla   = array_sum(array_column($rrhhPorEspecialidad, 'cantidad'));
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
    <strong>Ambulatorio — Carta de servicio</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-file-contract"></i> AMBULATORIO · CARTA DE SERVICIO
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('carta_servicio_export')) . $queryFiltros; ?>" class="bl-btn green"
               data-tab-only="carta" <?= $tab !== 'carta' ? 'style="display:none;"' : '' ?>>
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
            <a href="<?= base_url(route_to('rrhh_carta_servicio_export')) . $queryFiltros; ?>" class="bl-btn green"
               data-tab-only="rrhh" <?= $tab !== 'rrhh' ? 'style="display:none;"' : '' ?>>
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('ambulatorio_list')); ?>" id="searchForm">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ml-filtro-bar">
                <!-- HOSPITAL -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Hospital</span>
                    <select name="hospital" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($hospitales as $h): ?>
                            <option value="<?= esc($h) ?>" <?= $filtro_hospital == $h ? 'selected' : '' ?>><?= esc($h) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- REGIÓN -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Región</span>
                    <select name="region" class="ml-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- NIVEL DE COMPLEJIDAD -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Nivel de complejidad</span>
                    <select name="nivel_complejidad" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($niveles as $n): ?>
                            <option value="<?= esc($n) ?>" <?= $filtro_nivel == $n ? 'selected' : '' ?>><?= esc($n) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ACCIONES -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('ambulatorio_list')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── PESTAÑAS ── -->
        <div class="ml-tabs" role="tablist">
            <button type="button" class="ml-tab <?= $tab === 'carta' ? 'is-active' : '' ?>" data-tab="carta" role="tab">
                <i class="fas fa-calendar-check"></i> Carta de servicio
            </button>
            <button type="button" class="ml-tab <?= $tab === 'rrhh' ? 'is-active' : '' ?>" data-tab="rrhh" role="tab">
                <i class="fas fa-users"></i> RRHH carta de servicio
            </button>
        </div>

        <!-- ══════════════ CARTA DE SERVICIO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'carta' ? 'is-active' : '' ?>" data-pane="carta">

        <div class="ml-kpi-cards">
            <div class="ml-kpi-card kpi-teal">
                <div class="ml-kpi-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Turnos ofrecidos</div>
                    <div class="ml-kpi-value"><?= number_format($cartaKpi['turnos'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-blue">
                <div class="ml-kpi-icon"><i class="fas fa-user-md"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Profesionales</div>
                    <div class="ml-kpi-value"><?= number_format($cartaKpi['profesionales'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-purple">
                <div class="ml-kpi-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Especialidades</div>
                    <div class="ml-kpi-value"><?= number_format($cartaKpi['especialidades'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-navy">
                <div class="ml-kpi-icon"><i class="fas fa-hospital"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Hospitales</div>
                    <div class="ml-kpi-value"><?= number_format($cartaKpi['hospitales'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── ESTADÍSTICAS CARTA DE SERVICIO ── -->
        <div class="ml-panel ml-stats-panel" data-stats-panel>
            <div class="ml-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE CARTA DE SERVICIO</span>
                <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                </button>
            </div>
            <div class="ml-stats-body">
                <!-- Tabla detalle -->
                <div class="ml-stats-table-wrap">
                    <div class="ml-chart-title" style="text-align:left;">Turnos por especialidad</div>
                    <table class="ml-stats-table">
                        <thead>
                            <tr><th>Especialidad</th><th class="num">Agendas</th><th class="num">Turnos</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cartaPorEspecialidad)): ?>
                                <tr><td colspan="3" class="ml-stats-vacio">Sin turnos por especialidad para los filtros elegidos</td></tr>
                            <?php endif; ?>
                            <?php foreach ($cartaPorEspecialidad as $f): ?>
                            <tr>
                                <td><?= esc($f['especialidad']) ?></td>
                                <td class="num"><?= esc($f['agendas']) ?></td>
                                <td class="num"><?= esc($f['turnos']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if (!empty($cartaPorEspecialidad)): ?>
                        <tfoot>
                            <tr><td>Total</td><td class="num"><?= $totalAgendasTabla ?></td><td class="num"><?= $totalTurnosTabla ?></td></tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Barras por día -->
                <div class="ml-chart-box">
                    <div class="ml-chart-title">Turnos por día de atención</div>
                    <div class="ml-chart-canvas"><canvas id="chartCartaDias"></canvas></div>
                </div>

                <!-- Dona por tipo de profesional -->
                <div class="ml-chart-box">
                    <div class="ml-chart-title">Turnos por tipo de profesional</div>
                    <div class="ml-chart-canvas"><canvas id="chartCartaTipo"></canvas></div>
                </div>
            </div>
        </div>

        </div>

        <!-- ══════════════ RRHH CARTA DE SERVICIO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'rrhh' ? 'is-active' : '' ?>" data-pane="rrhh">

        <div class="ml-kpi-cards">
            <div class="ml-kpi-card kpi-teal">
                <div class="ml-kpi-icon"><i class="fas fa-users"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Profesionales</div>
                    <div class="ml-kpi-value"><?= number_format($rrhhKpi['profesionales'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-blue">
                <div class="ml-kpi-icon"><i class="fas fa-id-badge"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">De planta</div>
                    <div class="ml-kpi-value"><?= number_format($rrhhKpi['planta'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-purple">
                <div class="ml-kpi-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Especialidades</div>
                    <div class="ml-kpi-value"><?= number_format($rrhhKpi['especialidades'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-navy">
                <div class="ml-kpi-icon"><i class="fas fa-hospital"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Hospitales</div>
                    <div class="ml-kpi-value"><?= number_format($rrhhKpi['hospitales'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── ESTADÍSTICAS RRHH ── -->
        <div class="ml-panel ml-stats-panel" data-stats-panel>
            <div class="ml-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE RRHH</span>
                <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                </button>
            </div>
            <div class="ml-stats-body">
                <!-- Tablas detalle -->
                <div class="ml-stats-table-wrap">
                    <div class="ml-chart-title" style="text-align:left;">Profesionales por especialidad</div>
                    <table class="ml-stats-table">
                        <thead>
                            <tr><th>Especialidad</th><th class="num">Profesionales</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rrhhPorEspecialidad)): ?>
                                <tr><td colspan="2" class="ml-stats-vacio">Sin profesionales por especialidad para los filtros elegidos</td></tr>
                            <?php endif; ?>
                            <?php foreach ($rrhhPorEspecialidad as $f): ?>
                            <tr>
                                <td><?= esc($f['especialidad']) ?></td>
                                <td class="num"><?= esc($f['cantidad']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <?php if (!empty($rrhhPorEspecialidad)): ?>
                        <tfoot>
                            <tr><td>Total</td><td class="num"><?= $totalRrhhTabla ?></td></tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>

                    <div class="ml-chart-title" style="text-align:left;">Actividades asignadas</div>
                    <table class="ml-stats-table">
                        <thead>
                            <tr><th>Actividad</th><th class="num">Profesionales</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rrhhActividades)): ?>
                                <tr><td colspan="2" class="ml-stats-vacio">Sin actividades asignadas para los filtros elegidos</td></tr>
                            <?php endif; ?>
                            <?php foreach ($rrhhActividades as $a): ?>
                            <tr>
                                <td><?= esc($a['actividad']) ?></td>
                                <td class="num"><?= esc($a['cantidad']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Barras por especialidad -->
                <div class="ml-chart-box">
                    <div class="ml-chart-title">Profesionales por especialidad</div>
                    <div class="ml-chart-canvas"><canvas id="chartRrhhEspecialidad"></canvas></div>
                </div>

                <!-- Dona por situación de revista -->
                <div class="ml-chart-box">
                    <div class="ml-chart-title">Situación de revista</div>
                    <div class="ml-chart-canvas"><canvas id="chartRrhhRevista"></canvas></div>
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
<script>

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    var cartaPorDia             = <?= json_encode($cartaPorDia) ?>;
    var diasGrafico             = <?= json_encode($diasGrafico) ?>;
    var cartaPorTipoProfesional = <?= json_encode($cartaPorTipoProfesional) ?>;
    var rrhhPorEspecialidad     = <?= json_encode($rrhhPorEspecialidad) ?>;
    var rrhhPorRevista          = <?= json_encode($rrhhPorRevista) ?>;

    // Paleta pastel (misma línea que Móviles)
    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99',
        '#bb8fce', '#7fb3d5', '#b5ead7', '#e6b0aa', '#c7ceea', '#ffdac1'
    ];

    // Colores fijos por turno
    var coloresTurno = {
        'MAÑANA':   '#f8c471',
        'TARDE':    '#90cdf4',
        'NOCHE':    '#d6bcfa',
        'SIN DATO': '#cbd5e0'
    };

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
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
        });
    });

    // ── Plugins para las donas ──
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function (chart) {
            if (chart.config.type !== 'doughnut') return;
            var arco = chart.getDatasetMeta(0).data[0];
            if (!arco) return;
            // Total fijado por la vista (p. ej. dona paginada) o suma de las secciones visibles en la leyenda
            var total = chart.options.plugins.centerTotal;
            if (typeof total !== 'number') {
                total = chart.data.datasets[0].data.reduce(function (a, v, i) {
                    return a + (chart.getDataVisibility(i) ? Number(v) || 0 : 0);
                }, 0);
            }
            // Letra proporcional al hueco: se ve igual en donas grandes y chicas
            var tamNumero = Math.round(Math.max(12, Math.min(24, arco.innerRadius * 0.34)));
            var tamTitulo = Math.round(Math.max(10, Math.min(16, arco.innerRadius * 0.22)));
            var ctx = chart.ctx;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = 'bold ' + tamTitulo + 'px sans-serif';
            ctx.fillStyle = '#555555';
            ctx.fillText('TOTAL', arco.x, arco.y - tamNumero * 0.55);
            ctx.font = 'bold ' + tamNumero + 'px sans-serif';
            ctx.fillStyle = '#222222';
            ctx.fillText(total.toLocaleString('es-AR'), arco.x, arco.y + tamTitulo * 0.65);
            ctx.restore();
        }
    };

    // Reemplaza el canvas por el aviso de "sin datos"
    function mostrarSinDatos(canvasId, mensaje) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var caja = canvas.parentNode;
        caja.className = 'ml-sin-datos';
        caja.innerHTML = '<i class="fas fa-chart-bar"></i>';
        caja.appendChild(document.createTextNode(mensaje));
    }

    function crearDona(canvasId, etiquetas, valores, mensajeSinDatos) {
        var seccionesConDatos = valores.filter(function (v) { return Number(v) > 0; }).length;
        if (seccionesConDatos === 0) { mostrarSinDatos(canvasId, mensajeSinDatos); return; }

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: etiquetas.map(function (e, i) { return paleta[i % paleta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    spacing: seccionesConDatos > 1 ? 8 : 0,
                    offset: seccionesConDatos > 1 ? 12 : 0,
                    borderRadius: 5
                }]
            },
            plugins: [centerTextPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: esMovil() ? 'bottom' : 'right',
                        labels: { boxWidth: 15, padding: 10 }
                    },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 14 },
                        formatter: function (value, ctx) {
                            var total = ctx.chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            if (total === 0) return '';
                            var pct = Math.round((value / total) * 100);
                            return pct > 3 ? pct + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // ══ Gráficos de CARTA DE SERVICIO ══
    function graficosCarta() {
    // ── 1. Turnos por día (barras apiladas por turno) ──
    (function () {
        var turnos = Object.keys(cartaPorDia);

        // Solo los días que tienen turnos cargados
        var dias = diasGrafico.filter(function (dia) {
            return turnos.some(function (t) { return (cartaPorDia[t][dia] || 0) > 0; });
        });
        if (dias.length === 0) { mostrarSinDatos('chartCartaDias', 'Sin turnos por día de atención para los filtros elegidos'); return; }

        var datasets = turnos.map(function (t, i) {
            return {
                label: t,
                backgroundColor: coloresTurno[t] || paleta[i % paleta.length],
                maxBarThickness: 60,
                borderRadius: 4,
                data: dias.map(function (dia) { return cartaPorDia[t][dia] || 0; })
            };
        });

        new Chart(document.getElementById('chartCartaDias'), {
            type: 'bar',
            data: { labels: dias, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function (value) { return value > 0 ? value : ''; }
                    }
                },
                scales: {
                    x: { stacked: true, ticks: { autoSkip: false } },
                    y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                },
                layout: { padding: 10 }
            }
        });
    })();

    // ── 2. Turnos por tipo de profesional ──
    crearDona(
        'chartCartaTipo',
        cartaPorTipoProfesional.map(function (d) { return d.tipo_profesional; }),
        cartaPorTipoProfesional.map(function (d) { return parseInt(d.turnos, 10); }),
        'Sin turnos por tipo de profesional para los filtros elegidos'
    );
    }

    // ══ Gráficos de RRHH ══
    function graficosRrhh() {
    // ── 3. Profesionales por especialidad (barras horizontales) ──
    (function () {
        if (rrhhPorEspecialidad.length === 0) { mostrarSinDatos('chartRrhhEspecialidad', 'Sin profesionales por especialidad para los filtros elegidos'); return; }
        new Chart(document.getElementById('chartRrhhEspecialidad'), {
            type: 'bar',
            data: {
                labels: rrhhPorEspecialidad.map(function (d) { return d.especialidad; }),
                datasets: [{
                    label: 'Profesionales',
                    data: rrhhPorEspecialidad.map(function (d) { return parseInt(d.cantidad, 10); }),
                    backgroundColor: rrhhPorEspecialidad.map(function (d, i) { return paleta[i % paleta.length]; }),
                    borderRadius: 4,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333333',
                        font: { weight: 'bold', size: 11 }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grace: '10%' },
                    y: { ticks: { autoSkip: false } }
                },
                layout: { padding: { right: 20 } }
            }
        });
    })();

    // ── 4. Situación de revista ──
    crearDona(
        'chartRrhhRevista',
        rrhhPorRevista.map(function (d) { return d.revista; }),
        rrhhPorRevista.map(function (d) { return parseInt(d.cantidad, 10); }),
        'Sin profesionales por situación de revista para los filtros elegidos'
    );
    }

    // ══ Pestañas: Carta de servicio / RRHH ══
    // Los gráficos se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
    (function () {
        var dibujar   = { carta: graficosCarta, rrhh: graficosRrhh };
        var dibujados = {};
        var inputTab  = document.getElementById('inputTab');
        var btnLimpiar = document.getElementById('btnLimpiar');

        function mostrar(tab) {
            document.querySelectorAll('.ml-tab').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.tab === tab);
            });
            document.querySelectorAll('.ml-tab-pane').forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.pane === tab);
            });
            document.querySelectorAll('[data-tab-only]').forEach(function (el) {
                el.style.display = el.dataset.tabOnly === tab ? '' : 'none';
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
            }
        }

        document.querySelectorAll('.ml-tab').forEach(function (b) {
            b.addEventListener('click', function () { mostrar(b.dataset.tab); });
        });

        mostrar(inputTab.value === 'rrhh' ? 'rrhh' : 'carta');
    })();
</script>
<?= $this->endSection() ?>
