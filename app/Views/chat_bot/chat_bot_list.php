<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Chat Bot - Turnos · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── CHAT_BOT_LIST SPECIFIC STYLES ── */
    .cb-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cb-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cb-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cb-breadcrumb a:hover { color: var(--teal); }
    .cb-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .cb-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .cb-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #d0d7e0;
        flex: 0 1 300px;
        max-width: 260px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 80px;
        box-sizing: border-box;
    }
    .cb-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
        align-items: center;
    }
    .kpi-teal   { border-left: 6px solid #81e6d9; } 
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-green  { border-left: 6px solid #9ae6b4; }
    .kpi-red    { border-left: 6px solid #feb2b2; } 
    .kpi-amber  { border-left: 6px solid #fbd38d; } 
    .kpi-purple { border-left: 6px solid #d6bcfa; } 
    .kpi-navy   { border-left: 6px solid #a0aec0; } 

    .cb-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cb-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cb-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cb-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .cb-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .cb-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .cb-kpi-icon { background: #eef3f8; color: var(--navy); }

    .cb-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .cb-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }
    .cb-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cb-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cb-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cb-panel-title i { color: #fff; font-size: 17px; }
    .cb-panel-body { padding: 18px 20px; }
    .cb-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .cb-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cb-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cb-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cb-filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: var(--white);
        outline: none;
        transition: border-color 0.15s;
        height: 36px;
    }
    .cb-filtro-select:focus { border-color: var(--teal); }

    .cb-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .cb-stats-table-wrap { overflow-x: auto; grid-area: table; }
    .cb-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .cb-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left;
    }
    .cb-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .cb-stats-table tbody tr:hover { background: #f8f9fb; }
    .cb-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .cb-chart-box canvas { height: 100% !important; width: 100% !important; }
    .cb-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .cb-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .cb-stats-header i { color: var(--teal); }
    .cb-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .cb-stats-panel.is-collapsed { margin-bottom: 12px; }
    .cb-stats-panel.is-collapsed .cb-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .cb-stats-body.is-hidden { display: none; }

    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }

    /* ── Formato de los paneles de Hospitalario ── */
    .cb-kpi-card  { flex: 0 1 240px; max-width: 300px; height: auto; min-height: 80px; }
    .cb-kpi-icon  { flex-shrink: 0; }
    .cb-kpi-label { font-size: 13px; text-align: center; }
    .cb-kpi-value { font-size: 23px; }
    .cb-kpi-sub   {
        font-size: 11px; color: #718096; text-align: center;
        max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .kpi-green .cb-kpi-icon { background: #f0fff4; color: #38a169; }
    .cb-filtro-group { min-width: 0; }
    .cb-filtro-select { max-width: 100%; }

    /* Estadísticas: tabla a la izquierda, gráficos a la derecha (diseño de la planilla de referencia) */
    .cb-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.7fr);
        gap: 22px;
        align-items: start;
    }
    .cb-stats-graficos { display: flex; flex-direction: column; gap: 26px; min-width: 0; }
    .cb-graficos-fila  { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 18px; }
    .cb-stats-table-wrap { overflow: auto; max-height: 760px; grid-area: auto; }
    .cb-stats-table thead th { position: sticky; top: 0; z-index: 1; white-space: nowrap; }
    .cb-stats-table .num { text-align: right; white-space: nowrap; }
    .cb-stats-table thead th.num { text-align: right; }
    .cb-stats-table tfoot td {
        position: sticky; bottom: 0; background: var(--white);
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .cb-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }
    .cb-chart-box { display: flex; flex-direction: column; min-width: 0; position: static; height: auto; padding: 0; grid-area: auto; }
    .cb-chart-canvas { position: relative; width: 100%; height: 330px; }
    .cb-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .cb-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 1200px) {
        .cb-graficos-fila { grid-template-columns: 1fr; }
    }
    @media (max-width: 992px) {
        .cb-stats-body { grid-template-columns: 1fr; }
        .cb-stats-table-wrap { max-height: 420px; }
    }
    @media (max-width: 600px) {
        .cb-panel-body { padding: 14px 12px; }
        .cb-stats-body { padding: 12px; }
        .cb-filtro-group { flex: 1 1 100%; }
        .cb-filtro-select { width: 100%; }
        .cb-kpi-card { flex: 1 1 100%; max-width: none; }
        .cb-chart-canvas { height: 280px; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $queryExport = http_build_query(array_filter([
        'ejercicio' => $filtro_ejercicio,
        'mes'       => $filtro_mes,
        'efector'   => $filtro_efector,
        'region'    => $filtro_region,
        'estado'    => $filtro_estado,
    ], 'strlen'));
?>

<!-- BREADCRUMB -->
<div class="cb-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Chat Bot — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="cb-panel">
    <div class="cb-panel-header">
        <span class="cb-panel-title">
            <i class="fas fa-robot"></i> CHATWOOT - TURNOS OTORGADOS - MENSAJES
        </span>
    </div>

    <div class="cb-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="cb-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('chat_bot_export')) . ($queryExport !== '' ? '?' . $queryExport : ''); ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('chat_bot_list')) ?>" class="cb-filters-form">
            <div class="cb-filtro-bar">

                <!-- EJERCICIO -->
                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- MES -->
                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Mes</span>
                    <select name="mes" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= esc($m) ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= esc($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- REGIÓN -->
                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Región</span>
                    <select name="region" class="cb-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $reg): ?>
                            <option value="<?= esc($reg) ?>" <?= $filtro_region == $reg ? 'selected' : '' ?>><?= esc($reg) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- HOSPITAL -->
                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Hospital</span>
                    <select name="efector" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $ef): ?>
                            <option value="<?= esc($ef->efector_id) ?>" <?= $filtro_efector == $ef->efector_id ? 'selected' : '' ?>>
                                <?= esc($ef->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ESTADO — toggle -->
                <div class="cb-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="cb-filtro-label">Estado</span>
                    <select name="estado" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('chat_bot_list')); ?>" class="bl-btn ghost"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                        <button type="button" class="bl-btn ghost" id="btnToggle"
                                style="height:36px; padding:0 14px;"
                                title="Mostrar/ocultar Estado">
                            <i class="fas fa-sliders-h" id="iconoToggle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS KPI ── -->
        <div class="cb-kpi-cards">
            <div class="cb-kpi-card kpi-navy">
                <div class="cb-kpi-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">Turnos otorgados</div>
                    <div class="cb-kpi-value"><?= $fmt($kpi['total']) ?></div>
                    <div class="cb-kpi-sub">en <?= $fmt($kpi['meses']) ?> mes(es) con datos</div>
                </div>
            </div>
            <div class="cb-kpi-card kpi-teal">
                <div class="cb-kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">Promedio mensual</div>
                    <div class="cb-kpi-value"><?= $fmt($kpi['promedio']) ?></div>
                    <div class="cb-kpi-sub">turnos por mes</div>
                </div>
            </div>
            <div class="cb-kpi-card kpi-green">
                <div class="cb-kpi-icon"><i class="fas fa-arrow-up"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">Mes con más turnos</div>
                    <div class="cb-kpi-value"><?= $kpi['mes_pico'] ? $fmt($kpi['mes_pico']['turnos']) : '—' ?></div>
                    <div class="cb-kpi-sub"><?= $kpi['mes_pico'] ? esc($kpi['mes_pico']['etiqueta']) : 'sin datos' ?></div>
                </div>
            </div>
            <div class="cb-kpi-card kpi-blue">
                <div class="cb-kpi-icon"><i class="fas fa-hospital"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">Hospitales</div>
                    <div class="cb-kpi-value"><?= $fmt($kpi['hospitales']) ?></div>
                    <div class="cb-kpi-sub">con turnos otorgados</div>
                </div>
            </div>
            <div class="cb-kpi-card kpi-purple">
                <div class="cb-kpi-icon"><i class="fas fa-trophy"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">Hospital principal</div>
                    <div class="cb-kpi-value"><?= $kpi['hospital_top'] ? $fmt($kpi['hospital_top_n']) : '—' ?></div>
                    <div class="cb-kpi-sub" title="<?= esc($kpi['hospital_top'] ?? '') ?>"><?= $kpi['hospital_top'] ? esc($kpi['hospital_top']) : 'sin datos' ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="cb-panel cb-stats-panel" id="statsPanel">
            <div class="cb-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE TURNOS OTORGADOS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="cb-stats-body">
                    <!-- Tabla: hospital / mes / turnos -->
                    <div class="cb-stats-table-wrap">
                        <div class="cb-chart-title" style="text-align:left;">Turnos otorgados por hospital y mes</div>
                        <table class="cb-stats-table">
                            <thead>
                                <tr>
                                    <th>Hospital</th>
                                    <th>Mes</th>
                                    <?php if ($variosAnios): ?><th>Ejercicio</th><?php endif; ?>
                                    <th class="num">Turnos otorgados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statsFilas)): ?>
                                    <tr><td colspan="<?= $variosAnios ? 4 : 3 ?>" class="cb-stats-vacio">Sin turnos otorgados por el chat bot para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($statsFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td><?= esc($f['mes']) ?></td>
                                    <?php if ($variosAnios): ?><td><?= esc($f['ejercicio']) ?></td><?php endif; ?>
                                    <td class="num"><?= $fmt($f['turnos']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($statsFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="<?= $variosAnios ? 3 : 2 ?>">Total</td>
                                    <td class="num"><?= $fmt($kpi['total']) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Gráficos -->
                    <div class="cb-stats-graficos">
                        <!-- Barras: turnos por mes, de mayor a menor -->
                        <div class="cb-chart-box">
                            <div class="cb-chart-title">Turnos otorgados mensualmente</div>
                            <div class="cb-chart-canvas" style="height:340px;"><canvas id="chartMeses"></canvas></div>
                        </div>

                        <div class="cb-graficos-fila">
                            <!-- Línea: evolución mensual -->
                            <div class="cb-chart-box">
                                <div class="cb-chart-title">Evolución mensual</div>
                                <div class="cb-chart-canvas"><canvas id="chartEvolucion"></canvas></div>
                            </div>

                            <!-- Torta: distribución por región -->
                            <div class="cb-chart-box">
                                <div class="cb-chart-title">Distribución por región</div>
                                <div class="cb-chart-canvas"><canvas id="chartRegion"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Librerías JS Externas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
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
    // Crea el gráfico solo si la configuración trae algún valor mayor a 0
    function crearGrafico(canvas, mensaje, config) {
        var valores = [];
        ((config.data && config.data.datasets) || []).forEach(function (ds) {
            (ds.data || []).forEach(function (v) { valores.push(v && typeof v === 'object' ? v.y : v); });
        });
        if (sinDatos(valores)) { mostrarSinDatos(canvas, mensaje); return null; }
        return new Chart(canvas, config);
    }

    function formatoNumero(n) {
        return Number(n).toLocaleString('es-AR');
    }

    // Eje en miles ("20 mil") como en la planilla de referencia
    function formatoEje(v) {
        return v >= 1000 ? formatoNumero(v / 1000) + ' mil' : formatoNumero(v);
    }

    // ── Mostrar / ocultar el filtro de estado ──
    var colEstado   = document.getElementById('col-estado');
    var btnToggle   = document.getElementById('btnToggle');
    var iconoToggle = document.getElementById('iconoToggle');
    var estadoVisible = !!new URLSearchParams(window.location.search).get('estado');

    function aplicarVisibilidadEstado() {
        if (!colEstado || !iconoToggle || !btnToggle) return;
        colEstado.style.display = estadoVisible ? '' : 'none';
        iconoToggle.className = estadoVisible ? 'fas fa-times' : 'fas fa-sliders-h';
        btnToggle.title = estadoVisible ? 'Ocultar Estado' : 'Mostrar Estado';
    }
    aplicarVisibilidadEstado();
    if (btnToggle) {
        btnToggle.addEventListener('click', function () {
            estadoVisible = !estadoVisible;
            aplicarVisibilidadEstado();
        });
    }

    // ── Datos desde PHP ──
    var porMes    = <?= json_encode($porMes) ?>;      // [{etiqueta, turnos}] en orden cronológico
    var porRegion = <?= json_encode($porRegion) ?>;   // {region: turnos}

    // Colores de la planilla de referencia
    var COLOR_BARRA = '#3aafbf';
    var COLOR_LINEA = '#263a5a';
    var coloresRegion = ['#263a5a', '#3aafbf', '#e98d69', '#9aa4ad', '#8fd3f4', '#f1c27d', '#82e0aa'];

    var chartsInicializados = false;

    function inicializarCharts() {
        if (chartsInicializados) return;
        chartsInicializados = true;

        // 1. Barras: turnos por mes, de mayor a menor
        var ordenados = porMes.slice().sort(function (a, b) { return b.turnos - a.turnos; });
        crearGrafico(document.getElementById('chartMeses'), 'Sin turnos otorgados por mes para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: ordenados.map(function (m) { return m.etiqueta; }),
                datasets: [{
                    label: 'TURNOS OTORGADOS',
                    data: ordenados.map(function (m) { return m.turnos; }),
                    backgroundColor: COLOR_BARRA,
                    borderRadius: 3,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { boxWidth: 28, boxHeight: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) { return 'Turnos: ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: {
                        anchor: 'end', align: 'end',
                        color: '#4a5568', font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return formatoNumero(v); }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 45, font: { size: 10 } } },
                    y: { beginAtZero: true, grace: '10%', ticks: { callback: formatoEje } }
                }
            }
        });

        // 2. Línea: evolución mensual en orden cronológico
        crearGrafico(document.getElementById('chartEvolucion'), 'Sin evolución mensual para los filtros elegidos', {
            type: 'line',
            data: {
                labels: porMes.map(function (m) { return m.etiqueta; }),
                datasets: [{
                    label: 'TURNOS OTORGADOS',
                    data: porMes.map(function (m) { return m.turnos; }),
                    borderColor: COLOR_LINEA,
                    backgroundColor: COLOR_LINEA,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { usePointStyle: true, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) { return 'Turnos: ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grace: '10%', ticks: { callback: formatoEje } }
                }
            }
        });

        // Plugin: total en el centro de las donas
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

        // 3. Dona: distribución por región
        var regiones = Object.keys(porRegion);
        var valoresRegion = regiones.map(function (r) { return porRegion[r]; });
        crearGrafico(document.getElementById('chartRegion'), 'Sin turnos por región para los filtros elegidos', {
            type: 'doughnut',
            plugins: [centerTextPlugin],
            data: {
                labels: regiones,
                datasets: [{
                    data: valoresRegion,
                    backgroundColor: regiones.map(function (r, i) { return coloresRegion[i % coloresRegion.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: window.matchMedia('(max-width: 600px)').matches ? 'bottom' : 'right',
                        labels: { usePointStyle: true, boxWidth: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var total = valoresRegion.reduce(function (a, b) { return a + b; }, 0);
                                return ctx.label + ': ' + formatoNumero(ctx.parsed) + ' (' + formatoNumero(Math.round(ctx.parsed * 1000 / total) / 10) + '%)';
                            }
                        }
                    },
                    datalabels: {
                        color: function (ctx) {
                            // Texto blanco sobre las porciones oscuras
                            var n = parseInt(ctx.dataset.backgroundColor[ctx.dataIndex].slice(1), 16);
                            return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 150 ? '#ffffff' : '#2d3748';
                        },
                        font: { size: 11 },
                        formatter: function (v) {
                            var total = valoresRegion.reduce(function (a, b) { return a + b; }, 0);
                            var pct = total ? v * 100 / total : 0;
                            return pct >= 4 ? pct.toLocaleString('es-AR', { maximumFractionDigits: 1 }) + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // ── Mostrar / ocultar estadísticas ──
    var statsPanel   = document.getElementById('statsPanel');
    var statsBody    = document.getElementById('statsBody');
    var btnStats     = document.getElementById('btnToggleStats');
    var iconoStats   = document.getElementById('iconoToggleStats');
    var textoStats   = document.getElementById('textoToggleStats');
    var statsVisible = true;

    if (btnStats) {
        btnStats.addEventListener('click', function () {
            statsVisible = !statsVisible;
            statsBody.classList.toggle('is-hidden', !statsVisible);
            statsPanel.classList.toggle('is-collapsed', !statsVisible);
            iconoStats.className   = statsVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
            textoStats.textContent = statsVisible ? 'Ocultar estadísticas' : 'Mostrar estadísticas';
            if (statsVisible) inicializarCharts();
        });
    }

    inicializarCharts();
});
</script>
<?= $this->endSection() ?>