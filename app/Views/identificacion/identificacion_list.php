<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Identificación · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── IDENTIFICACION_LIST SPECIFIC STYLES ── */
    .at-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .at-breadcrumb i { color: var(--teal); font-size: 13px; }
    .at-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .at-breadcrumb a:hover { color: var(--teal); }
    .at-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .at-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .at-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #d0d7e0;
        flex: 0 1 300px;
        max-width: 270px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 80px;
        box-sizing: border-box;
    }
    .at-kpi-content {
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
    
    .at-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .at-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .at-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .at-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .at-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .at-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .at-kpi-icon { background: #eef3f8; color: var(--navy); }

    .at-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .at-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }
    .at-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .at-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .at-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .at-panel-title i { color: #fff; font-size: 17px; }
    .at-panel-body { padding: 18px 20px; }
    .at-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .at-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .at-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .at-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .at-filtro-select {
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
    .at-filtro-select:focus { border-color: var(--teal); }

    .at-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .at-stats-table-wrap { overflow-x: auto; grid-area: table; }
    .at-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .at-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left;
    }
    .at-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .at-stats-table tbody tr:hover { background: #f8f9fb; }
    .at-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .at-chart-box canvas { height: 100% !important; width: 100% !important; }
    .at-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .at-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .at-stats-header i { color: var(--teal); }
    .at-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .at-stats-panel.is-collapsed { margin-bottom: 12px; }
    .at-stats-panel.is-collapsed .at-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .at-stats-body.is-hidden { display: none; }

    @media (max-width: 992px) {
        .at-stats-body { grid-template-columns: 1fr; }
    }

    /* ── Formato de los paneles de Hospitalario ── */
    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }
    .at-kpi-card  { flex: 0 1 260px; max-width: 320px; }
    .at-kpi-icon  { flex-shrink: 0; }
    .at-kpi-label { font-size: 14px; text-align: center; }
    .at-filtro-group { min-width: 0; }
    .at-stats-header { gap: 10px; flex-wrap: wrap; }
    .at-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        grid-template-areas: none;
        gap: 18px;
        align-items: start;
    }
    /* Columna derecha: los dos gráficos uno debajo del otro */
    .at-stats-graficos { display: flex; flex-direction: column; gap: 28px; min-width: 0; }
    .at-stats-table-wrap { overflow-x: auto; grid-area: auto; }
    .at-stats-table thead th { white-space: nowrap; }
    .at-stats-table .num { text-align: right; white-space: nowrap; }
    .at-stats-table thead th.num { text-align: right; }
    .at-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .at-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }
    .at-chart-box {
        display: flex; flex-direction: column; min-width: 0;
        position: static; height: auto; padding: 0; grid-area: auto;
    }
    .at-chart-box.is-large { height: auto; }
    .at-chart-canvas { position: relative; width: 100%; height: 330px; }
    .at-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .at-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    @media (max-width: 992px) {
        .at-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .at-panel-body { padding: 14px 12px; }
        .at-stats-body { padding: 12px; }
        .at-filtro-group { flex: 1 1 100%; }
        .at-filtro-select { width: 100%; }
        .at-kpi-card { flex: 1 1 100%; max-width: none; }
        .at-chart-canvas { height: 280px; }
    }
</style>

<!-- BREADCRUMB -->
<div class="at-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('base_views')); ?>">Prehospitalario</a> ›
    <strong>Identificación — Listado</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="at-panel">
    <div class="at-panel-header">
        <span class="at-panel-title">
            <i class="fas fa-id-card"></i> IDENTIFICACIÓN
        </span>
    </div>

    <div class="at-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="at-toolbar">
            <a href="<?= base_url(route_to('base_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('identificacion_export')); ?>?<?= http_build_query([
                'ejercicio'    => $filtro_ejercicio,
                'departamento' => $filtro_departamento,
                'estado'       => $filtro_estado,
            ]) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('identificacion_list')); ?>" id="searchForm">
            <div class="at-filtro-bar">

                <!-- EJERCICIO -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- DEPARTAMENTO -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Departamento</span>
                    <select name="departamento" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($departamentos as $d): ?>
                            <option value="<?= $d ?>" <?= $filtro_departamento == $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PACIENTE -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Paciente</span>
                    <select name="paciente" class="at-filtro-select">
                        <option value="">Todos</option>
                        <option value="adulto" <?= $filtro_paciente == 'adulto' ? 'selected' : '' ?>>Adulto</option>
                        <option value="pediatrico" <?= $filtro_paciente == 'pediatrico' ? 'selected' : '' ?>>Pediatrico</option>
                    </select>
                </div>

                <!-- ESTADO — toggle -->
                <div class="at-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="at-filtro-label">Estado</span>
                    <select name="estado" class="at-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('identificacion_list')); ?>" class="bl-btn ghost"
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
        <div class="at-kpi-cards">
            <?php if ($filtro_paciente === 'adulto' || !$filtro_paciente): ?>
            <div class="at-kpi-card kpi-teal">
                <div class="at-kpi-icon"><i class="fas fa-user-tie"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">TOTAL ADULTO</div>
                    <div class="at-kpi-value"><?= number_format($totalAdulto, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($filtro_paciente === 'pediatrico' || !$filtro_paciente): ?>
            <div class="at-kpi-card kpi-blue">
                <div class="at-kpi-icon"><i class="fas fa-child"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">TOTAL PEDIÁTRICO</div>
                    <div class="at-kpi-value"><?= number_format($totalPediatrico, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div class="at-kpi-card kpi-red">
                <div class="at-kpi-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">DEPARTAMENTOS</div>
                    <div class="at-kpi-value"><?= number_format($totalDepartamentos, 0, ',', '.') ?></div>
                </div>
            </div>

            <div class="at-kpi-card kpi-navy">
                <div class="at-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">TOTAL GENERAL</div>
                    <div class="at-kpi-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="at-panel at-stats-panel" id="statsPanel">
            <div class="at-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE INTERNACIÓN DOMICILIARIA</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="at-stats-body">
                    <!-- Tabla detalle -->
                    <div class="at-stats-table-wrap">
                        <div class="at-chart-title" style="text-align:left;">Total por departamento / ejercicio</div>
                        <?php 
                            // Condición dinámica: hay departamento seleccionado y paciente en "Todos"
                            $mostrarDesgloseTipo = (!empty($filtro_departamento) && empty($filtro_paciente));
                        ?>
                        <table class="at-stats-table">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th>Ejercicio</th>
                                    <?php if ($mostrarDesgloseTipo): ?>
                                        <th class="num">Adulto</th>
                                        <th class="num">Pediátrico</th>
                                    <?php endif; ?>
                                    <th class="num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statsFilas)): ?>
                                    <tr><td colspan="<?= $mostrarDesgloseTipo ? 5 : 3 ?>" class="at-stats-vacio">Sin identificaciones para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($statsFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['departamento']) ?></td>
                                    <td><?= esc($f['ejercicio']) ?></td>
                                    <?php if ($mostrarDesgloseTipo): ?>
                                        <td class="num"><?= number_format((int) $f['adulto'], 0, ',', '.') ?></td>
                                        <td class="num"><?= number_format((int) $f['pediatrico'], 0, ',', '.') ?></td>
                                    <?php endif; ?>
                                    <td class="num"><?= number_format((int) $f['cantidad'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($statsFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <?php if ($mostrarDesgloseTipo): ?>
                                        <td class="num"><?= number_format((int) array_sum(array_map('intval', array_column($statsFilas, 'adulto'))), 0, ',', '.') ?></td>
                                        <td class="num"><?= number_format((int) array_sum(array_map('intval', array_column($statsFilas, 'pediatrico'))), 0, ',', '.') ?></td>
                                    <?php endif; ?>
                                    <td class="num"><?= number_format((int) array_sum(array_map('intval', array_column($statsFilas, 'cantidad'))), 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Gráficos apilados: barras arriba, dona abajo -->
                    <div class="at-stats-graficos">
                        <!-- Barras comparativas -->
                        <div class="at-chart-box" id="containerBarras">
                            <div class="at-chart-title">Comparativo por departamento</div>
                            <div class="at-chart-canvas" style="height:400px;"><canvas id="chartBarras"></canvas></div>
                        </div>

                        <!-- Torta distribución -->
                        <div class="at-chart-box" id="containerTorta">
                            <div class="at-chart-title">Distribución por ejercicio / departamento</div>
                            <div class="at-chart-canvas"><canvas id="chartTorta"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /panel-body -->
</div><!-- /panel -->
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Librerías JS Externas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. REGISTRO DE PLUGINS
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

    // 2. MOSTRAR / OCULTAR FILTRO ESTADO
    var colEstado   = document.getElementById('col-estado');
    var btnToggle   = document.getElementById('btnToggle');
    var iconoToggle = document.getElementById('iconoToggle');

    var params = new URLSearchParams(window.location.search);
    var estadoVisible = !!params.get('estado');

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

    // 3. DATOS PROCESADOS DESDE PHP
    var datosBarras           = <?= json_encode($statsBarras ?? []) ?>;
    var ejerciciosRecientes   = <?= json_encode($statsEjercicios ?? []) ?>;
    var datosTorta            = <?= json_encode($statsTorta ?? []) ?>;
    var filtroEjercicio       = <?= json_encode($filtro_ejercicio ?? '') ?>;
    var filtroDepartamento    = <?= json_encode($filtro_departamento ?? '') ?>;
    var filtroPaciente        = <?= json_encode($filtro_paciente ?? '') ?>;
    var todosLosDepartamentos = <?= json_encode($departamentos ?? []) ?>;
    var todosLosEjercicios    = <?= json_encode($todosLosEjercicios ?? $ejercicios ?? []) ?>;
    var statsFilasJS          = <?= json_encode($statsFilas ?? []) ?>;

    // Paleta de colores fija
    var paleta = [
    '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
    '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
    '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
    '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
    ];

    // ── MAPA FIJO Y DINÁMICO DE COLORES POR EJERCICIO ──
    var ejerciciosOrdenados = (todosLosEjercicios || [])
        .map(function (ej) { return parseInt(ej, 10); })
        .filter(function (ej) { return !isNaN(ej); })
        .filter(function (ej, index, array) { return array.indexOf(ej) === index; })
        .sort(function (a, b) { return a - b; });

    // Colores por ejercicio: 10 tonos de azul (el más viejo primero)
    var paletaEjercicios = [
        '#4FB3E6', '#1A5FA8', '#0B2E59', '#8FD3F4', '#2F80C8',
        '#123F73', '#6EC6EA', '#1E4E8C', '#A9DDF5', '#0F5E9C'
    ];

    var ejercicioColorMap = {};
    ejerciciosOrdenados.forEach(function (ejercicio, index) {
        ejercicioColorMap[String(ejercicio)] = paletaEjercicios[index % paletaEjercicios.length];
    });

    function colorParaEjercicio(ejercicio) {
        var clave = String(parseInt(ejercicio, 10));
        return ejercicioColorMap[clave] || '#95a5a6';
    }

    // Colores por departamento: tonos de azul (misma gama que ejercicio), alternando oscuro/claro
    var coloresDepartamento = {
        'CAPITAL': '#0B2E59',
        'COCHINOCA': '#8FD3F4',
        'DR. MANUEL BELGRANO': '#1A5FA8',
        'EL CARMEN': '#C6E9FA',
        'HUMAHUACA': '#123F73',
        'LEDESMA': '#4FB3E6',
        'PALPALA': '#2F80C8',
        'PERICO': '#A9DDF5',
        'SAN PEDRO': '#1E4E8C',
        'SANTA BARBARA': '#6EC6EA',
        'SUSQUE': '#0F5E9C',
        'TILCARA': '#7FBEE8',
        'YAVI': '#07203F'
    };

    // Departamentos que no estén en el mapa fijo también reciben un azul
    var paletaDepartamentos = Object.keys(coloresDepartamento).map(function (k) { return coloresDepartamento[k]; });

    var coloresTipo = {
        'ADULTO': '#81e6d9',  
        'PEDIATRICO': '#90cdf4',   
        'JERIATRICO': '#feb2b2'    
    };

    // Mapa de colores por departamento
    var colorDepMap = {};
    (todosLosDepartamentos || []).forEach(function (dep, i) {
        colorDepMap[dep] = paletaDepartamentos[i % paletaDepartamentos.length];
    });

    function colorParaDepartamento(dep) {
        var clave = String(dep).trim().toUpperCase();
        if (coloresDepartamento[clave]) return coloresDepartamento[clave];
        return colorDepMap[dep] || '#95a5a6';
    }

    // 4. INICIALIZACIÓN DE GRÁFICOS
    var statsPanel   = document.getElementById('statsPanel');
    var statsBody    = document.getElementById('statsBody');
    var btnStats     = document.getElementById('btnToggleStats');
    var iconoStats   = document.getElementById('iconoToggleStats');
    var textoStats   = document.getElementById('textoToggleStats');
    var statsVisible = true;
    var chartsInicializados = false;

    function inicializarCharts() {
        if (chartsInicializados) return;

        var elBarras = document.getElementById('chartBarras');
        var elTorta  = document.getElementById('chartTorta');

        if (!elBarras || !elTorta) return;
        chartsInicializados = true;

        var departamentos = [];
        datosBarras.forEach(function (d) {
            if (departamentos.indexOf(d.departamento) === -1) departamentos.push(d.departamento);
        });

        var esTodosEjercicios    = (!filtroEjercicio || filtroEjercicio === '');
        var esTodosDepartamentos = (!filtroDepartamento || filtroDepartamento === '');

        // CONDICIÓN DINÁMICA: Hay departamento seleccionado Y filtro de paciente es "Todos"
        var esCasoEspecial = (!esTodosDepartamentos && (!filtroPaciente || filtroPaciente === ''));

        // ── BARRAS ──
        var datasetsBarras = [];

        if (esCasoEspecial) {
            // Calcular totales sumando la fila correspondiente
            var totalAdultos = 0;
            var totalPediatricos = 0;
            statsFilasJS.forEach(function(f) {
                totalAdultos += f.adulto || 0;
                totalPediatricos += f.pediatrico || 0;
            });

            datasetsBarras = [
                {
                    label: 'ADULTO',
                    backgroundColor: coloresTipo['ADULTO'],
                    borderColor: coloresTipo['ADULTO'],
                    borderWidth: 0,
                    maxBarThickness: 90,
                    data: [totalAdultos]
                },
                {
                    label: 'PEDIATRICO',
                    backgroundColor: coloresTipo['PEDIATRICO'],
                    borderColor: coloresTipo['PEDIATRICO'],
                    borderWidth: 0,
                    maxBarThickness: 90,
                    data: [totalPediatricos]
                }
            ];
        } else {
            // Comportamiento normal por ejercicios
            datasetsBarras = ejerciciosRecientes.map(function (ej) {
                var colorDelAno = colorParaEjercicio(ej);
                return {
                    label: 'Ejercicio ' + ej,
                    borderColor: colorDelAno,
                    backgroundColor: departamentos.map(function(dep) {
                        return esTodosEjercicios ? colorDelAno : colorParaDepartamento(dep);
                    }),
                    borderWidth: 0,
                    maxBarThickness: 90,
                    data: departamentos.map(function (dep) {
                        var fila = datosBarras.find(function (d) {
                            return d.departamento === dep && d.ejercicio == ej;
                        });
                        return fila ? parseInt(fila.cantidad, 10) : 0;
                    })
                };
            });

            if (esTodosEjercicios && !esTodosDepartamentos) {
                datasetsBarras = datasetsBarras.filter(function (ds) {
                    var sumaTotal = ds.data.reduce(function (a, b) { return a + b; }, 0);
                    return sumaTotal > 0;
                });
            }
        }

        crearGrafico(elBarras, 'Sin identificaciones por departamento para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: esCasoEspecial ? [filtroDepartamento] : departamentos,
                datasets: datasetsBarras
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            generateLabels: function(chart) {
                                var original = Chart.defaults.plugins.legend.labels.generateLabels;
                                var labels = original.call(this, chart);

                                labels.forEach(function(label, index) {
                                    var dataset = chart.data.datasets[index];
                                    // Asigna a la caja de la leyenda el color del ejercicio (borderColor)
                                    if (dataset && dataset.borderColor) {
                                        label.fillStyle = dataset.borderColor;
                                        label.strokeStyle = dataset.borderColor;
                                    }
                                });

                                return labels;
                            }
                        }
                    },
                    datalabels: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        barPercentage: 0.5,
                        categoryPercentage: 0.5
                    },
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
                }
            }
        });

        // ── TORTA / DONA ──
        var labelsTorta = [];
        var dataTorta   = [];
        var colorsTorta = [];

        if (esCasoEspecial) {
            var totalAdultos = 0;
            var totalPediatricos = 0;
            statsFilasJS.forEach(function(f) {
                totalAdultos += f.adulto || 0;
                totalPediatricos += f.pediatrico || 0;
            });

            labelsTorta = ['ADULTO', 'PEDIATRICO'];
            dataTorta   = [totalAdultos, totalPediatricos];
            colorsTorta = [coloresTipo['ADULTO'], coloresTipo['PEDIATRICO']];
        } 
        else if (esTodosEjercicios && esTodosDepartamentos) {
            labelsTorta = ejerciciosRecientes.map(function (ej) { return 'Ejercicio ' + ej; });
            dataTorta   = ejerciciosRecientes.map(function (ej) {
                var totalAno = 0;
                datosBarras.forEach(function (d) {
                    if (d.ejercicio == ej) totalAno += parseInt(d.cantidad, 10);
                });
                return totalAno;
            });
            colorsTorta = ejerciciosRecientes.map(function (ej) { return colorParaEjercicio(ej); });
        }
        else if (esTodosEjercicios && !esTodosDepartamentos) {
            labelsTorta = ejerciciosRecientes.map(function (ej) { return 'Ejercicio ' + ej; });
            dataTorta   = ejerciciosRecientes.map(function (ej) {
                var fila = datosBarras.find(function (d) { return d.ejercicio == ej; });
                return fila ? parseInt(fila.cantidad, 10) : 0;
            });
            colorsTorta = ejerciciosRecientes.map(function (ej) { return colorParaEjercicio(ej); });
        }
        else {
            labelsTorta = departamentos;
            dataTorta   = departamentos.map(function (dep) {
                var total = 0;
                datosBarras.forEach(function (d) {
                    if (d.departamento === dep) total += parseInt(d.cantidad, 10);
                });
                return total;
            });
            colorsTorta = departamentos.map(function (dep) { return colorParaDepartamento(dep); });
        }

        var seccionesConDatos = dataTorta.filter(function(value) { return Number(value) > 0; }).length;
        var totalSumTorta = dataTorta.reduce(function (a, b) { return a + b; }, 0);

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

        var donutSeparatorPlugin = {
            id: 'donutSeparator',
            afterDatasetDraw: function(chart, args) {
                if (chart.config.type !== 'doughnut' || args.index !== 0) return;
                var arcs = chart.getDatasetMeta(0).data;
                if (!arcs.length) return;
                var ctx = chart.ctx;
                ctx.save();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 1;
                ctx.lineCap = 'butt';
                arcs.forEach(function(arc) {
                    var angle = arc.endAngle;
                    ctx.beginPath();
                    ctx.moveTo(arc.x + Math.cos(angle) * arc.innerRadius, arc.y + Math.sin(angle) * arc.innerRadius);
                    ctx.lineTo(arc.x + Math.cos(angle) * arc.outerRadius, arc.y + Math.sin(angle) * arc.outerRadius);
                    ctx.stroke();
                });
                ctx.restore();
            }
        };

        crearGrafico(elTorta, 'Sin identificaciones para los filtros elegidos', {
            type: 'doughnut',
            data: {
                labels: labelsTorta,
                datasets: [{
                    data: dataTorta,
                    backgroundColor: colorsTorta,
                    borderColor: '#ffffff',
                    // Separación con borde blanco: con "spacing", Chart.js dibuja las
                    // porciones muy chicas como un aro completo
                    borderWidth: seccionesConDatos > 1 ? 3 : 0,
                    hoverOffset: seccionesConDatos > 1 ? 8 : 0,
                    borderRadius: 4
                }]
            },
            plugins: [centerTextPlugin, donutSeparatorPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: window.matchMedia('(max-width: 600px)').matches ? 'bottom' : 'right',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    },
                    datalabels: {
                            color: function (ctx) {
                                // Texto blanco sobre fondos oscuros (azules de los ejercicios), gris sobre los claros
                                var bg = ctx.dataset.backgroundColor;
                                bg = Array.isArray(bg) ? bg[ctx.dataIndex] : bg;
                                var m = /^#([0-9a-f]{6})/i.exec(bg || '');
                                if (!m) return '#5a5858';
                                var n = parseInt(m[1], 16);
                                return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 150 ? '#ffffff' : '#5a5858';
                            },
                            font: {
                                weight: 'bold',
                                size: 13
                            },
                            // 1. CONTROL DE VISIBILIDAD NATIVO:
                            // Oculta completamente el contenedor del datalabel si el valor es 0 o negativo
                            display: function(context) {
                                var val = context.dataset.data[context.dataIndex];
                                return val !== null && Number(val) > 0;
                            },
                            anchor: 'center',
                            align: 'center',

                            // 2. CÁLCULO DINÁMICO Y FORMATO SEGURO:
                            formatter: function(value, context) {
                                // Obtiene los datos del dataset actual de forma segura
                                var dataset = context.chart.data.datasets[context.datasetIndex].data;
                                
                                // Suma el total dinámicamente en tiempo real
                                var totalSum = dataset.reduce(function(acc, curr) {
                                    var num = Number(curr);
                                    return acc + (isNaN(num) ? 0 : num);
                                }, 0);

                                // Si la suma total es 0, no dibuja nada
                                if (totalSum === 0) return null;

                                var percentage = (Number(value) / totalSum) * 100;

                                // Oculta porcentajes menores o iguales al 2% para evitar que el texto
                                // se encime en sectores demasiado angostos
                                if (percentage <= 2) return null;

                                // Formato con localización local (Argentina / Latam)
                                // Redondea a entero si es exacto, o muestra máximo 1 decimal si es necesario
                                return percentage.toLocaleString('es-AR', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 1
                                }) + '%';
                            }
                        }
                }
            }
        });
    }

    // 5. COLLAPSE / EXPAND
    if (btnStats) {
        btnStats.addEventListener('click', function () {
            statsVisible = !statsVisible;
            if (statsVisible) {
                if (statsBody) statsBody.classList.remove('is-hidden');
                if (statsPanel) statsPanel.classList.remove('is-collapsed');
                if (iconoStats) iconoStats.className = 'fas fa-eye-slash';
                if (textoStats) textoStats.textContent = 'Ocultar estadísticas';
                inicializarCharts();
            } else {
                if (statsBody) statsBody.classList.add('is-hidden');
                if (statsPanel) statsPanel.classList.add('is-collapsed');
                if (iconoStats) iconoStats.className = 'fas fa-eye';
                if (textoStats) textoStats.textContent = 'Mostrar estadísticas';
            }
        });
    }

    inicializarCharts();
});
</script>
<?= $this->endSection() ?>