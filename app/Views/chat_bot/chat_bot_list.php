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
        max-width: 350px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 90px;
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
    .kpi-teal { border-left: 6px solid #38b2ac; }
    .kpi-blue { border-left: 6px solid #4299e1; }
    .kpi-red  { border-left: 6px solid #f56565; }
    .kpi-navy { border-left: 6px solid var(--navy); }

    .cb-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cb-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cb-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cb-kpi-icon { background: #fff5f5; color: #e53e3e; }
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
    .cb-panel-title i { color: var(--teal); font-size: 17px; }
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

    @media (min-width: 993px) {
        .cb-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie";
        }
        .cb-stats-table-wrap { grid-area: table; }
        #containerBarras { grid-area: bar; }
        #containerTorta { grid-area: pie; }
    }

    @media (max-width: 992px) {
        .cb-stats-body { grid-template-columns: 1fr; }
    }
</style>

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
            <i class="fas fa-robot"></i> CHAT BOT - TURNOS OTORGADOS
        </span>
    </div>

    <div class="cb-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="cb-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
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
                            <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Mes</span>
                    <select name="mes" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= $m ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Hospital</span>
                    <select name="efector" class="cb-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $ef): ?>
                            <option value="<?= $ef->efector_id ?>" <?= $filtro_efector == $ef->efector_id ? 'selected' : '' ?>>
                                <?= esc($ef->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cb-filtro-group">
                    <span class="cb-filtro-label">Región</span>
                    <select name="region" class="cb-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $reg): ?>
                            <option value="<?= $reg ?>" <?= $filtro_region == $reg ? 'selected' : '' ?>><?= $reg ?></option>
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
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
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
            <?php foreach ($totalesPorRegion as $region => $total): ?>
            <div class="cb-kpi-card kpi-teal">
                <div class="cb-kpi-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label"><?= esc($region) ?></div>
                    <div class="cb-kpi-value"><?= number_format($total, 0, ',', '.') ?></div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="cb-kpi-card kpi-navy">
                <div class="cb-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="cb-kpi-content">
                    <div class="cb-kpi-label">TOTAL</div>
                    <div class="cb-kpi-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="cb-panel cb-stats-panel" id="statsPanel">
            <div class="cb-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE ATENCIONES</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="cb-stats-body">
                    <!-- Tabla detalle -->
                    <div class="cb-stats-table-wrap">
                        <div class="cb-chart-title" style="text-align:left;">Turnos por región / ejercicio</div>
                        <table class="cb-stats-table">
                            <thead>
                                <tr>
                                    <th>Región</th>
                                    <th>Ejercicio</th>
                                    <th>Turnos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statsFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['region']) ?></td>
                                    <td><?= esc($f['ejercicio']) ?></td>
                                    <td><?= number_format($f['cantidad'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Barras comparativas -->
                    <div class="cb-chart-box" id="containerBarras">
                        <div class="cb-chart-title">Comparativo por región (últimos ejercicios)</div>
                        <canvas id="chartBarras"></canvas>
                    </div>

                    <!-- Torta distribución -->
                    <div class="cb-chart-box" id="containerTorta">
                        <div class="cb-chart-title">Distribución por ejercicio</div>
                        <canvas id="chartTorta"></canvas>
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

    // 1. REGISTRO DE PLUGINS
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
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
    var filtroRegion          = <?= json_encode($filtro_region ?? '') ?>;
    var todasLasRegiones      = <?= json_encode($regiones ?? []) ?>;
    var todosLosEjercicios    = <?= json_encode($ejercicios ?? []) ?>;

    // Paleta de colores fija
    var paleta = [
        '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
        '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
        '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
        '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
    ];

    // ── MAPAS DE COLORES ──
    var ejerciciosOrdenados = (todosLosEjercicios || [])
        .map(function (ej) { return parseInt(ej, 10); })
        .filter(function (ej) { return !isNaN(ej); })
        .filter(function (ej, index, array) { return array.indexOf(ej) === index; })
        .sort(function (a, b) { return a - b; });

    var ejercicioColorMap = {};
    ejerciciosOrdenados.forEach(function (ejercicio, index) {
        ejercicioColorMap[String(ejercicio)] = paleta[index % paleta.length];
    });

    function colorParaEjercicio(ejercicio) {
        var clave = String(parseInt(ejercicio, 10));
        return ejercicioColorMap[clave] || '#95a5a6';
    }

    var colorRegMap = {};
    (todasLasRegiones || []).forEach(function (reg, i) {
        colorRegMap[reg] = paleta[i % paleta.length];
    });

    function colorParaRegion(reg) {
        return colorRegMap[reg] || '#95a5a6';
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

        var regiones = [];
        datosBarras.forEach(function (d) {
            if (regiones.indexOf(d.region) === -1) regiones.push(d.region);
        });

        var esTodosEjercicios = (!filtroEjercicio || filtroEjercicio === '');
        var esTodasLasRegiones = (!filtroRegion || filtroRegion === '');

        // ── BARRAS ──
        var datasetsBarras = ejerciciosRecientes.map(function (ej) {
            var colorDelAno = colorParaEjercicio(ej);
            return {
                label: 'Ejercicio ' + ej,
                borderColor: colorDelAno,
                backgroundColor: regiones.map(function(reg) {
                    return esTodosEjercicios ? colorDelAno : colorParaRegion(reg);
                }),
                borderWidth: 0,
                maxBarThickness: 90,
                data: regiones.map(function (reg) {
                    var fila = datosBarras.find(function (d) {
                        return d.region === reg && d.ejercicio == ej;
                    });
                    return fila ? parseInt(fila.cantidad, 10) : 0;
                })
            };
        });

        if (esTodosEjercicios && !esTodasLasRegiones) {
            datasetsBarras = datasetsBarras.filter(function (ds) {
                var sumaTotal = ds.data.reduce(function (a, b) { return a + b; }, 0);
                return sumaTotal > 0;
            });
        }

        new Chart(elBarras, {
            type: 'bar',
            data: {
                labels: regiones,
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
                    x: { grid: { display: false }, barPercentage: 0.5, categoryPercentage: 0.5 },
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
                }
            }
        });

        // ── TORTA / DONA ──
        var labelsTorta = [];
        var dataTorta   = [];
        var colorsTorta = [];

        if (esTodosEjercicios && esTodasLasRegiones) {
            labelsTorta = datosTorta.map(function (d) { return 'Ejercicio ' + d.ejercicio; });
            dataTorta   = datosTorta.map(function (d) { return parseInt(d.total, 10); });
            colorsTorta = datosTorta.map(function (d) { return colorParaEjercicio(d.ejercicio); });
        }
        else if (esTodosEjercicios && !esTodasLasRegiones) {
            labelsTorta = ejerciciosRecientes.map(function (ej) { return 'Ejercicio ' + ej; });
            dataTorta   = ejerciciosRecientes.map(function (ej) {
                var fila = datosBarras.find(function (d) { return d.ejercicio == ej; });
                return fila ? parseInt(fila.cantidad, 10) : 0;
            });
            colorsTorta = ejerciciosRecientes.map(function (ej) { return colorParaEjercicio(ej); });
        }
        else {
            labelsTorta = regiones;
            dataTorta   = regiones.map(function (reg) {
                var total = 0;
                datosBarras.forEach(function (d) {
                    if (d.region === reg) total += parseInt(d.cantidad, 10);
                });
                return total;
            });
            colorsTorta = regiones.map(function (reg) { return colorParaRegion(reg); });
        }

        var seccionesConDatos = dataTorta.filter(function(value) { return Number(value) > 0; }).length;
        var totalSumTorta = dataTorta.reduce(function (a, b) { return a + b; }, 0);

        var centerTextPlugin = {
            id: 'centerText',
            afterDraw: function(chart) {
                if (chart.config.type !== 'doughnut') return;
                var ctx = chart.ctx;
                ctx.save();
                ctx.font = "bold 20px sans-serif";
                ctx.fillStyle = "#555555";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                ctx.fillText("TOTAL", centerX, centerY - 15);
                ctx.font = "bold 22px sans-serif";
                ctx.fillStyle = "#222222";
                ctx.fillText(totalSumTorta.toLocaleString(), centerX, centerY + 20);
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

        new Chart(elTorta, {
            type: 'doughnut',
            data: {
                labels: labelsTorta,
                datasets: [{
                    data: dataTorta,
                    backgroundColor: colorsTorta,
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    spacing: seccionesConDatos > 1 ? 8 : 0,
                    offset: seccionesConDatos > 1 ? 12 : 0,
                    borderRadius: 5
                }]
            },
            plugins: [centerTextPlugin, donutSeparatorPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 15, padding: 10 } },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 15 },
                        formatter: function(value, ctx) {
                            var dataset = ctx.chart.data.datasets[0].data;
                            var totalSum = dataset.reduce(function(a, b) { return a + b; }, 0);
                            if (totalSum === 0) return '0%';
                            var percentage = ((value / totalSum) * 100).toFixed(0);
                            return percentage > 3 ? percentage + '%' : '';
                        },
                        anchor: 'center',
                        align: 'center'
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