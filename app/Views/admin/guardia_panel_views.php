<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Guardia · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── GUARDIA_PANEL SPECIFIC STYLES ── */
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
        grid-template-columns: 1fr 1.4fr;
        gap: 18px;
        align-items: start;
    }
    /* Columna derecha: los dos gráficos uno debajo del otro */
    .ml-stats-graficos { display: flex; flex-direction: column; gap: 28px; min-width: 0; }
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

    /* ── PESTAÑAS (Por servicio / Por hospital) ── */
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
        .ml-chart-canvas { height: 280px; }
    }
</style>

<?php
    // Parámetros de filtro para el link de exportación
    $queryFiltros = http_build_query(array_filter([
        'anio'        => $filtro_anio,
        'semestre'    => $filtro_semestre,
        'efector_id'  => $filtro_efector,
        'servicio_id' => $filtro_servicio,
    ], 'strlen'));
    $queryFiltros = $queryFiltros !== '' ? '?' . $queryFiltros : '';

    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };

    // Totales por año para el pie de la tabla de servicios
    $totalesAnio = [];
    foreach ($aniosGrafico as $a) {
        $totalesAnio[$a] = array_sum(array_map(function ($s) use ($a) { return $s['anios'][$a] ?? 0; }, $tablaServicios));
    }
    $totalHospitales = array_sum(array_column($tablaHospitales, 'cantidad'));

    // Alto del gráfico de hospitales según la cantidad de barras
    $altoHospitales = max(330, count($tablaHospitales) * 26 + 40);
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
    <strong>Guardia — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-briefcase-medical"></i> GUARDIA
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('guardia_export')) . $queryFiltros; ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('guardia_views')); ?>" id="searchForm">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ml-filtro-bar">
                <!-- AÑO -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Año</span>
                    <select name="anio" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($anios as $a): ?>
                            <option value="<?= esc($a) ?>" <?= $filtro_anio == $a ? 'selected' : '' ?>><?= esc($a) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- SEMESTRE -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Semestre</span>
                    <select name="semestre" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($semestres as $s): ?>
                            <option value="<?= esc($s) ?>" <?= $filtro_semestre == $s ? 'selected' : '' ?>><?= esc($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
                <!-- SERVICIO -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Servicio</span>
                    <select name="servicio_id" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= esc($s['servicio_id']) ?>" <?= $filtro_servicio == $s['servicio_id'] ? 'selected' : '' ?>><?= esc($s['nombre']) ?></option>
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
                        <a href="<?= base_url(route_to('guardia_views')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS KPI ── -->
        <div class="ml-kpi-cards">
            <div class="ml-kpi-card kpi-teal">
                <div class="ml-kpi-icon"><i class="fas fa-briefcase-medical"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Atenciones</div>
                    <div class="ml-kpi-value"><?= $fmt($kpi['total'] ?? 0) ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-blue">
                <div class="ml-kpi-icon"><i class="fas fa-hospital"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Hospitales</div>
                    <div class="ml-kpi-value"><?= $fmt($kpi['hospitales'] ?? 0) ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-purple">
                <div class="ml-kpi-icon"><i class="fas fa-stethoscope"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Servicios</div>
                    <div class="ml-kpi-value"><?= $fmt($kpi['servicios'] ?? 0) ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-navy">
                <div class="ml-kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">Promedio x hospital</div>
                    <div class="ml-kpi-value"><?= $fmt($kpi['promedio'] ?? 0) ?></div>
                </div>
            </div>
        </div>

        <!-- ── PESTAÑAS ── -->
        <div class="ml-tabs" role="tablist">
            <button type="button" class="ml-tab <?= $tab === 'servicio' ? 'is-active' : '' ?>" data-tab="servicio" role="tab">
                <i class="fas fa-stethoscope"></i> Por servicio
            </button>
            <button type="button" class="ml-tab <?= $tab === 'hospital' ? 'is-active' : '' ?>" data-tab="hospital" role="tab">
                <i class="fas fa-hospital"></i> Por hospital
            </button>
        </div>

        <!-- ══════════════ POR SERVICIO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'servicio' ? 'is-active' : '' ?>" data-pane="servicio">
            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <div class="ml-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS POR SERVICIO</span>
                    <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                    </button>
                </div>
                <div class="ml-stats-body">
                    <!-- Tabla detalle -->
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Atenciones por servicio / año</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <?php foreach ($aniosGrafico as $a): ?><th class="num"><?= esc($a) ?></th><?php endforeach; ?>
                                    <?php if (count($aniosGrafico) > 1): ?><th class="num">Total</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tablaServicios)): ?>
                                    <tr><td colspan="<?= count($aniosGrafico) + 2 ?>Sin atenciones de guardia por servicio para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($tablaServicios as $s): ?>
                                <tr>
                                    <td><?= esc($s['servicio']) ?></td>
                                    <?php foreach ($aniosGrafico as $a): ?><td class="num"><?= $fmt($s['anios'][$a] ?? 0) ?></td><?php endforeach; ?>
                                    <?php if (count($aniosGrafico) > 1): ?><td class="num"><strong><?= $fmt($s['total']) ?></strong></td><?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($tablaServicios)): ?>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <?php foreach ($aniosGrafico as $a): ?><td class="num"><?= $fmt($totalesAnio[$a]) ?></td><?php endforeach; ?>
                                    <?php if (count($aniosGrafico) > 1): ?><td class="num"><?= $fmt(array_sum($totalesAnio)) ?></td><?php endif; ?>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Gráficos apilados: barras arriba, dona abajo -->
                    <div class="ml-stats-graficos">
                        <!-- Barras comparativas por año -->
                        <div class="ml-chart-box">
                            <div class="ml-chart-title">Comparativo por servicio y año</div>
                            <div class="ml-chart-canvas" style="height:440px;"><canvas id="chartServicioAnio"></canvas></div>
                        </div>

                        <!-- Dona distribución por servicio -->
                        <div class="ml-chart-box">
                            <div class="ml-chart-title">Distribución por servicio</div>
                            <div class="ml-chart-canvas"><canvas id="chartServicioDona"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ POR HOSPITAL ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'hospital' ? 'is-active' : '' ?>" data-pane="hospital">
            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <div class="ml-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS POR HOSPITAL</span>
                    <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                    </button>
                </div>
                <div class="ml-stats-body">
                    <!-- Tabla detalle -->
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Atenciones por hospital</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th>Región</th><th class="num">Atenciones</th><th class="num">%</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($tablaHospitales)): ?>
                                    <tr><td colspan="4" class="ml-stats-vacio">Sin atenciones de guardia por hospital para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($tablaHospitales as $h): ?>
                                <tr>
                                    <td><?= esc($h['hospital']) ?></td>
                                    <td><?= esc($h['region']) ?></td>
                                    <td class="num"><?= $fmt($h['cantidad']) ?></td>
                                    <td class="num"><?= $totalHospitales > 0 ? number_format($h['cantidad'] * 100 / $totalHospitales, 1, ',', '.') : '0' ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($tablaHospitales)): ?>
                            <tfoot>
                                <tr><td colspan="2">Total</td><td class="num"><?= $fmt($totalHospitales) ?></td><td class="num">100%</td></tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Gráficos apilados: dona arriba, barras abajo -->
                    <div class="ml-stats-graficos">
                        <!-- Dona por región -->
                        <div class="ml-chart-box">
                            <div class="ml-chart-title">Distribución por región</div>
                            <div class="ml-chart-canvas"><canvas id="chartRegion"></canvas></div>
                        </div>

                        <!-- Barras horizontales por hospital -->
                        <div class="ml-chart-box">
                            <div class="ml-chart-title">Atenciones por hospital</div>
                            <div class="ml-chart-canvas" style="height:<?= $altoHospitales ?>px;"><canvas id="chartHospitales"></canvas></div>
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
<script>

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    var aniosGrafico    = <?= json_encode($aniosGrafico) ?>;
    var tablaServicios  = <?= json_encode($tablaServicios) ?>;
    var tablaHospitales = <?= json_encode($tablaHospitales) ?>;
    var porRegion       = <?= json_encode($porRegion) ?>;

    // Paleta pastel (misma línea que Móviles)
    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99',
        '#bb8fce', '#7fb3d5', '#b5ead7', '#e6b0aa', '#c7ceea', '#ffdac1'
    ];

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
    }

    function formatoNumero(n) {
        return Number(n).toLocaleString('es-AR');
    }

    // ── Gráficos sin datos: en lugar del gráfico se muestra un aviso ──
    function sinDatos(valores) {
        return !(valores || []).some(function (v) { return Number(v) > 0; });
    }
    function mostrarSinDatos(canvasId, mensaje) {
        var canvas = document.getElementById(canvasId);
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

    function crearDona(canvasId, etiquetas, valores, mensajeSinDatos) {
        if (sinDatos(valores)) { mostrarSinDatos(canvasId, mensajeSinDatos); return; }

        // Las secciones de menos del 2% se agrupan en "Otros": son ilegibles en la dona
        // y, con separación entre secciones, Chart.js las dibuja como un aro completo.
        var total = valores.reduce(function (a, v) { return a + (Number(v) || 0); }, 0);
        var etiq = [], vals = [], otros = 0;
        valores.forEach(function (v, i) {
            v = Number(v) || 0;
            if (v <= 0) return;
            if (v * 100 / total < 2) { otros += v; return; }
            etiq.push(etiquetas[i]);
            vals.push(v);
        });
        if (otros > 0) { etiq.push('OTROS'); vals.push(otros); }
        var seccionesConDatos = vals.length;

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: etiq,
                datasets: [{
                    data: vals,
                    backgroundColor: etiq.map(function (e, i) { return e === 'OTROS' ? '#cbd5e0' : paleta[i % paleta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: seccionesConDatos > 1 ? 3 : 0,
                    hoverOffset: seccionesConDatos > 1 ? 8 : 0,
                    borderRadius: 4
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

    // Colores por ejercicio: 10 tonos de azul (el más viejo primero)
    var paletaEjercicios = [
        '#4FB3E6', '#1A5FA8', '#0B2E59', '#8FD3F4', '#2F80C8',
        '#123F73', '#6EC6EA', '#1E4E8C', '#A9DDF5', '#0F5E9C'
    ];
    var todosLosAnios = <?= json_encode(array_map('intval', $anios)) ?>.sort(function (x, y) { return x - y; });

    // Color fijo por año (el más viejo primero); no cambia al filtrar un año
    function colorAnio(anio) {
        var idx = todosLosAnios.indexOf(parseInt(anio, 10));
        return paletaEjercicios[(idx < 0 ? 0 : idx) % paletaEjercicios.length];
    }

    // ══ Gráficos POR SERVICIO ══
    function graficosServicio() {
        // ── 1. Comparativo por servicio y año ──
        if (sinDatos(tablaServicios.map(function (s) { return s.total; }))) {
            mostrarSinDatos('chartServicioAnio', 'Sin atenciones de guardia por servicio para los filtros elegidos');
        } else
        new Chart(document.getElementById('chartServicioAnio'), {
            type: 'bar',
            data: {
                labels: tablaServicios.map(function (s) { return s.servicio; }),
                datasets: aniosGrafico.map(function (anio) {
                    return {
                        label: String(anio),
                        backgroundColor: colorAnio(anio),
                        borderRadius: 4,
                        maxBarThickness: 40,
                        data: tablaServicios.map(function (s) { return s.anios[anio] || 0; })
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return ctx.dataset.label + ': ' + formatoNumero(ctx.parsed.y); }
                        }
                    }
                },
                scales: {
                    x: { ticks: { autoSkip: false, maxRotation: 60, minRotation: 30, font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { callback: function (v) { return formatoNumero(v); } } }
                },
                layout: { padding: 10 }
            }
        });

        // ── 2. Distribución por servicio ──
        crearDona(
            'chartServicioDona',
            tablaServicios.map(function (s) { return s.servicio; }),
            tablaServicios.map(function (s) { return s.total; }),
            'Sin atenciones de guardia por servicio para los filtros elegidos'
        );
    }

    // ══ Gráficos POR HOSPITAL ══
    function graficosHospital() {
        // ── 3. Atenciones por hospital (barras horizontales) ──
        if (sinDatos(tablaHospitales.map(function (h) { return h.cantidad; }))) {
            mostrarSinDatos('chartHospitales', 'Sin atenciones de guardia por hospital para los filtros elegidos');
        } else
        new Chart(document.getElementById('chartHospitales'), {
            type: 'bar',
            data: {
                labels: tablaHospitales.map(function (h) { return h.hospital; }),
                datasets: [{
                    label: 'Atenciones',
                    data: tablaHospitales.map(function (h) { return parseInt(h.cantidad, 10); }),
                    backgroundColor: '#90cdf4',
                    borderRadius: 4,
                    maxBarThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return 'Atenciones: ' + formatoNumero(ctx.parsed.x); }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return formatoNumero(v); }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grace: '15%', ticks: { callback: function (v) { return formatoNumero(v); } } },
                    y: { ticks: { autoSkip: false, font: { size: 10 } } }
                },
                layout: { padding: { right: 20 } }
            }
        });

        // ── 4. Distribución por región ──
        crearDona(
            'chartRegion',
            porRegion.map(function (r) { return r.region; }),
            porRegion.map(function (r) { return parseInt(r.cantidad, 10); }),
            'Sin atenciones de guardia por región para los filtros elegidos'
        );
    }

    // ══ Pestañas: Por servicio / Por hospital ══
    // Los gráficos se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
    (function () {
        var dibujar    = { servicio: graficosServicio, hospital: graficosHospital };
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
            }
        }

        document.querySelectorAll('.ml-tab').forEach(function (b) {
            b.addEventListener('click', function () { mostrar(b.dataset.tab); });
        });

        mostrar(inputTab.value === 'hospital' ? 'hospital' : 'servicio');
    })();
</script>
<?= $this->endSection() ?>
