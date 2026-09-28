<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Quirófano · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── QUIROFANO_PANEL SPECIFIC STYLES ── */
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
        .ml-chart-canvas { height: 280px; }
        .ml-tab { flex: 1 1 45%; font-size: 11.5px; padding: 10px 8px; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $dec = function ($n, $d = 1) { return number_format((float) $n, $d, ',', '.'); };

    // Link de Excel de cada pestaña, con los filtros que acepta su módulo
    $linkExcel = function ($ruta, array $params) {
        $q = http_build_query(array_filter($params, 'strlen'));
        return base_url(route_to($ruta)) . ($q !== '' ? '?' . $q : '');
    };
    $excel = [
        'prod' => $linkExcel('produccion_quirofano_export', ['efector_id' => $filtro_efector, 'ejercicio' => $filtro_ejercicio, 'region' => $filtro_region]),
        'hosp' => $linkExcel('quirofano_export', ['efector_id' => $filtro_efector, 'ejercicio' => $filtro_ejercicio]),
    ];

    $tabs = [
        'prod' => ['icono' => 'fa-procedures', 'titulo' => 'Producción quirófano'],
        'hosp' => ['icono' => 'fa-hospital',   'titulo' => 'Producción hospitalaria'],
    ];

    // Botón de ocultar/mostrar estadísticas (se repite en cada pestaña)
    $headerStats = function ($titulo) {
        return '<div class="ml-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ' . esc($titulo) . '</span>
                    <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                    </button>
                </div>';
    };

    $alto = function ($cantidad) { return max(330, $cantidad * 26 + 40); };
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
    <strong>Quirófano — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-syringe"></i> QUIRÓFANO
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <?php foreach ($excel as $clave => $url): ?>
                <a href="<?= $url ?>" class="bl-btn green" data-tab-only="<?= $clave ?>" <?= $tab !== $clave ? 'style="display:none;"' : '' ?>>
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('quirofano_views')); ?>" id="searchForm">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ml-filtro-bar">
                <!-- HOSPITAL -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Hospital</span>
                    <select name="efector_id" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $e): ?>
                            <option value="<?= esc($e['efector_id']) ?>" <?= $filtro_efector == $e['efector_id'] ? 'selected' : '' ?>><?= esc($e['nombre']) ?></option>
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
                <!-- EJERCICIO -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
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
                        <a href="<?= base_url(route_to('quirofano_views')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── PESTAÑAS ── -->
        <div class="ml-tabs" role="tablist">
            <?php foreach ($tabs as $clave => $t): ?>
                <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab">
                    <i class="fas <?= $t['icono'] ?>"></i> <?= esc($t['titulo']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ══════════════ 1. PRODUCCIÓN QUIRÓFANO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'prod' ? 'is-active' : '' ?>" data-pane="prod">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-procedures"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Producción</div>
                        <div class="ml-kpi-value"><?= $fmt($prodKpi['produccion'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-hospital"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Hospitales</div>
                        <div class="ml-kpi-value"><?= $fmt($prodKpi['hospitales'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Promedio x hospital</div>
                        <div class="ml-kpi-value"><?= $dec($prodKpi['promedio'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Ejercicios</div>
                        <div class="ml-kpi-value"><?= $fmt($prodKpi['ejercicios'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE PRODUCCIÓN QUIRÓFANO') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Producción por hospital / ejercicio</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th>Región</th><th class="num">Ejercicio</th><th class="num">Producción</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($prodFilas)): ?>
                                    <tr><td colspan="4" class="ml-stats-vacio">Sin producción de quirófano para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($prodFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td><?= esc($f['region']) ?></td>
                                    <td class="num"><?= esc($f['ejercicio']) ?></td>
                                    <td class="num"><?= $fmt($f['produccion']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($prodFilas)): ?>
                            <tfoot>
                                <tr><td colspan="3">Total</td><td class="num"><?= $fmt($prodKpi['produccion']) ?></td></tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Producción por hospital</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(count($prodPorHospital)) ?>px;"><canvas id="chartProdHospital"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Distribución por región</div>
                        <div class="ml-chart-canvas"><canvas id="chartProdRegion"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ 2. PRODUCCIÓN QUIRÓFANO HOSPITALARIA ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'hosp' ? 'is-active' : '' ?>" data-pane="hosp">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-syringe"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Total cirugías</div>
                        <div class="ml-kpi-value"><?= $fmt($hospKpi['total'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-ambulance"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Urgencia</div>
                        <div class="ml-kpi-value"><?= $fmt($hospKpi['urgencia'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-calendar-check"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Programadas</div>
                        <div class="ml-kpi-value"><?= $fmt($hospKpi['programadas'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-door-open"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Quirófanos</div>
                        <div class="ml-kpi-value"><?= $fmt($hospKpi['quirofanos'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE PRODUCCIÓN HOSPITALARIA') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Cirugías por hospital</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th class="num">Quirof.</th><th class="num">Urgencia</th><th class="num">Program.</th><th class="num">Total</th><th class="num">% Prov.</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hospFilas)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin cirugías registradas para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($hospFilas as $f): ?>
                                <tr>
                                    <td>
                                        <?= esc($f['hospital']) ?>
                                        <?php if (!empty($f['observacion'])): ?>
                                            <i class="fas fa-info-circle" style="color:var(--teal); cursor:help;" title="<?= esc($f['observacion']) ?>"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="num"><?= $fmt($f['quirofanos']) ?></td>
                                    <td class="num"><?= $fmt($f['urgencia']) ?></td>
                                    <td class="num"><?= $fmt($f['programadas']) ?></td>
                                    <td class="num"><strong><?= $fmt($f['total']) ?></strong></td>
                                    <td class="num"><?= $dec($f['porcentaje']) ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($hospFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td class="num"><?= $fmt($hospKpi['quirofanos']) ?></td>
                                    <td class="num"><?= $fmt($hospKpi['urgencia']) ?></td>
                                    <td class="num"><?= $fmt($hospKpi['programadas']) ?></td>
                                    <td class="num"><?= $fmt($hospKpi['total']) ?></td>
                                    <td class="num">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Urgencia / programadas por hospital</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(count($hospFilas)) ?>px;"><canvas id="chartHospCirugias"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Cirugías por complejidad</div>
                        <div class="ml-chart-canvas"><canvas id="chartHospComplejidad"></canvas></div>
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

    var prodPorHospital = <?= json_encode($prodPorHospital) ?>;
    var prodPorRegion   = <?= json_encode($prodPorRegion) ?>;
    var hospKpi         = <?= json_encode($hospKpi) ?>;
    var hospFilas       = <?= json_encode($hospFilas) ?>;

    // Paleta pastel (misma línea que Móviles)
    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99',
        '#bb8fce', '#7fb3d5', '#b5ead7', '#e6b0aa', '#c7ceea', '#ffdac1'
    ];

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
    }

    function formatoNumero(n, decimales) {
        return Number(n).toLocaleString('es-AR', { maximumFractionDigits: decimales || 0 });
    }

    // ── Gráficos sin datos: en lugar del gráfico se muestra un aviso ──
    function sinDatos(valores) {
        return !(valores || []).some(function (v) { return Number(v) > 0; });
    }
    function mostrarSinDatos(canvas, mensaje) {
        if (typeof canvas === 'string') canvas = document.getElementById(canvas);
        if (!canvas) return;
        var aviso = document.createElement('div');
        aviso.style.cssText = 'height:100%;min-height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:#718096;font-size:13px;font-style:italic;text-align:center;border:1px dashed #d8dee6;border-radius:10px;padding:16px;box-sizing:border-box;';
        aviso.innerHTML = '<i class="fas fa-chart-bar" style="font-size:28px;opacity:.4;font-style:normal;"></i>';
        aviso.appendChild(document.createTextNode(mensaje));
        canvas.replaceWith(aviso);
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

    // ── Plugin: total en el centro de las donas ──
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

    function crearDona(canvasId, etiquetas, valores, colores, mensajeSinDatos) {
        if (sinDatos(valores)) { mostrarSinDatos(canvasId, mensajeSinDatos); return; }
        var seccionesConDatos = valores.filter(function (v) { return Number(v) > 0; }).length;

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: colores || etiquetas.map(function (e, i) { return paleta[i % paleta.length]; }),
                    borderColor: '#ffffff',
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
                    legend: {
                        position: esMovil() ? 'bottom' : 'right',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return ctx.label + ': ' + formatoNumero(ctx.parsed); }
                        }
                    },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 12 },
                        formatter: function (value, ctx) {
                            var total = ctx.chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            if (total === 0) return '';
                            var pct = Math.round((value / total) * 100);
                            return pct > 4 ? pct + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // Barras horizontales; datasets = [{ label, data, color }]. Con más de uno se apilan.
    function crearBarrasH(canvasId, etiquetas, datasets, mensajeSinDatos) {
        var todos = [];
        datasets.forEach(function (d) { todos = todos.concat(d.data); });
        if (sinDatos(todos)) { mostrarSinDatos(canvasId, mensajeSinDatos); return; }
        var apiladas = datasets.length > 1;
        new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: etiquetas,
                datasets: datasets.map(function (d) {
                    return { label: d.label, data: d.data, backgroundColor: d.color, borderRadius: 4, maxBarThickness: 22 };
                })
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: apiladas, position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return (apiladas ? ctx.dataset.label + ': ' : '') + formatoNumero(ctx.parsed.x); }
                        }
                    },
                    datalabels: apiladas ? {
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return v > 0 ? formatoNumero(v) : ''; }
                    } : {
                        anchor: 'end',
                        align: 'end',
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return formatoNumero(v); }
                    }
                },
                scales: {
                    x: { stacked: apiladas, beginAtZero: true, grace: apiladas ? 0 : '15%', ticks: { callback: function (v) { return formatoNumero(v); } } },
                    y: { stacked: apiladas, ticks: { autoSkip: false, font: { size: 10 } } }
                },
                layout: { padding: { right: 20 } }
            }
        });
    }

    // ══ Gráficos por pestaña ══
    var graficos = {
        // 1. Producción quirófano
        prod: function () {
            crearBarrasH(
                'chartProdHospital',
                prodPorHospital.map(function (h) { return h.hospital; }),
                [{ label: 'Producción', data: prodPorHospital.map(function (h) { return parseInt(h.produccion, 10); }), color: '#90cdf4' }],
                'Sin producción de quirófano por hospital para los filtros elegidos'
            );
            crearDona(
                'chartProdRegion',
                prodPorRegion.map(function (r) { return r.region; }),
                prodPorRegion.map(function (r) { return parseInt(r.produccion, 10); }),
                null,
                'Sin producción de quirófano por región para los filtros elegidos'
            );
        },

        // 2. Producción hospitalaria
        hosp: function () {
            crearBarrasH(
                'chartHospCirugias',
                hospFilas.map(function (f) { return f.hospital; }),
                [
                    { label: 'Urgencia',    data: hospFilas.map(function (f) { return parseInt(f.urgencia, 10); }),    color: '#feb2b2' },
                    { label: 'Programadas', data: hospFilas.map(function (f) { return parseInt(f.programadas, 10); }), color: '#81e6d9' }
                ],
                'Sin cirugías registradas para los filtros elegidos'
            );

            var etiquetas = ['Alta', 'Mediana', 'Baja'];
            var valores   = [hospKpi.alta || 0, hospKpi.mediana || 0, hospKpi.baja || 0];
            var colores   = ['#feb2b2', '#90cdf4', '#81e6d9'];
            if ((hospKpi.desconocido || 0) > 0) {
                etiquetas.push('Desconocida');
                valores.push(hospKpi.desconocido);
                colores.push('#cbd5e0');
            }
            crearDona('chartHospComplejidad', etiquetas, valores, colores, 'Sin cirugías programadas por complejidad para los filtros elegidos');
        }
    };

    // ══ Pestañas ══
    // Los gráficos se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
    (function () {
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
            // Excel de la pestaña activa
            document.querySelectorAll('[data-tab-only]').forEach(function (el) {
                el.style.display = el.dataset.tabOnly === tab ? '' : 'none';
            });

            // Mantener la pestaña al filtrar, limpiar o recargar
            inputTab.value = tab;
            btnLimpiar.href = btnLimpiar.href.split('?')[0] + '?tab=' + tab;
            var params = new URLSearchParams(window.location.search);
            params.set('tab', tab);
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());

            if (!dibujados[tab] && graficos[tab]) {
                graficos[tab]();
                dibujados[tab] = true;
            }
        }

        document.querySelectorAll('.ml-tab').forEach(function (b) {
            b.addEventListener('click', function () { mostrar(b.dataset.tab); });
        });

        mostrar(inputTab.value || 'prod');
    })();
</script>
<?= $this->endSection() ?>
