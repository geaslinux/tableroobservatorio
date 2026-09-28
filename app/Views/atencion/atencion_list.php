<?= $this->extend('layout/main'); ?>//777
<?= $this->section('title') ?> Atenciones · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── ATENCION_LIST SPECIFIC STYLES ── */
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
        max-width: 350px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 90px;
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
    .kpi-teal { border-left: 6px solid #38b2ac; }
    .kpi-blue { border-left: 6px solid #4299e1; }
    .kpi-red  { border-left: 6px solid #f56565; }
    .kpi-amber { border-left: 6px solid #ed8936; }
    .kpi-purple { border-left: 6px solid #9f7aea; }
    .kpi-navy { border-left: 6px solid var(--navy); }
    
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
    .at-panel-title i { color: var(--teal); font-size: 17px; }
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
    .at-chart-box.is-large { height: 500px; }
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
    

    @media (min-width: 993px) {
        .at-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie";
        }
        .at-stats-table-wrap { grid-area: table; }
        #containerBarras { grid-area: bar; }
        #containerTorta { grid-area: pie; }
    }

    @media (max-width: 992px) {
        .at-stats-body { grid-template-columns: 1fr; }
    }
</style>

<!-- BREADCRUMB -->
<div class="at-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('base_views')); ?>">Prehospitalario</a> ›
    <strong>Atenciones — Listado</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="at-panel">
    <div class="at-panel-header">
        <span class="at-panel-title">
            <i class="fas fa-notes-medical"></i> ATENCIONES
        </span>
    </div>

    <div class="at-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="at-toolbar">
            <a href="<?= base_url(route_to('base_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('atencion_export')); ?>?<?= http_build_query([
                'ejercicio' => $filtro_ejercicio,
                'mes'       => $filtro_mes,
                'estado'    => $filtro_estado,
            ]) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('atencion_list')); ?>" id="searchForm">
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

                <!-- MES -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Mes</span>
                    <select name="mes" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= $m ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
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
                <div class="at-kpi-card kpi-teal">
                    <div class="at-kpi-icon"><i class="fas fa-notes-medical"></i></div>
                    <div class="at-kpi-content">
                        <div class="at-kpi-label">ATENCIONES BASE/USE</div>
                        <div class="at-kpi-value"><?= number_format($totalAtencionesBase, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="at-kpi-card kpi-blue">
                    <div class="at-kpi-icon"><i class="fas fa-users"></i></div>
                    <div class="at-kpi-content">
                        <div class="at-kpi-label">ASISTIDOS COBERTURAS</div>
                        <div class="at-kpi-value"><?= number_format($totalAsistidosCoberturas, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="at-kpi-card kpi-red">
                    <div class="at-kpi-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="at-kpi-content">
                        <div class="at-kpi-label">COBERTURAS EN EVENTOS</div>
                        <div class="at-kpi-value"><?= number_format($totalCantidadCoberturas, 0, ',', '.') ?></div>
                    </div>
                </div>
                <div class="at-kpi-card kpi-navy">
                    <div class="at-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="at-kpi-content">
                        <div class="at-kpi-label">TOTAL</div>
                        <div class="at-kpi-value"><?= number_format($totalGeneralCantidad, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>

        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="at-panel at-stats-panel" id="statsPanel">
            <div class="at-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE ATENCIONES</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div class="" id="statsBody">

            <div class="at-stats-body">
                <!-- Tabla detalle -->
                <div class="at-stats-table-wrap">
                    <div class="at-chart-title" style="text-align:left;">Total por mes / ejercicio</div>
                    <table class="at-stats-table">
                        <thead>
                            <tr><th>Mes</th><th>Ejercicio</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsFilas as $f): ?>
                            <tr>
                                <td><?= esc($f['mes']) ?></td>
                                <td><?= esc($f['ejercicio']) ?></td>
                                <td><?= number_format($f['cantidad'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla detalle -->
                <div class="at-chart-box is-large" id="containerBarras">
                    <div class="at-chart-title">Comparativo mensual (últimos ejercicios)</div>
                    <canvas id="chartBarras"></canvas>
                </div>

                <!-- Torta distribución por ejercicio -->
                <div class="at-chart-box" id="containerTorta">
                    <div class="at-chart-title">Distribución por ejercicio</div>
                    <canvas id="chartTorta"></canvas>
                </div>
            </div>
        </div><!-- /panel-body -->
    </div><!-- /panel -->
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Librerías Externas -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. REGISTRO DE PLUGINS
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // 2. LÓGICA DE MOSTRAR / OCULTAR FILTRO ESTADO
    var colEstado = document.getElementById('col-estado');
    var btnToggle = document.getElementById('btnToggle');
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

    // 3. DATOS DESDE PHP CONTROLLER
    var datosBarras         = <?= json_encode($statsBarras ?? []) ?>;
    var ejerciciosRecientes = <?= json_encode($statsEjercicios ?? []) ?>;
    var datosTorta          = <?= json_encode($statsTorta ?? []) ?>;
    var todosLosEjercicios  = <?= json_encode($todosLosEjercicios ?? []) ?>;
    var filtroEjercicio     = <?= json_encode($filtro_ejercicio ?? null) ?>;
    var filtroMes           = <?= json_encode($filtro_mes ?? '') ?>;
    
    // 4. LÓGICA DE DIBUJADO DE GRÁFICOS
    var statsPanel   = document.getElementById('statsPanel');
    var statsBody    = document.getElementById('statsBody');
    var btnStats     = document.getElementById('btnToggleStats');
    var iconoStats   = document.getElementById('iconoToggleStats');
    var textoStats   = document.getElementById('textoToggleStats');
    var statsVisible = true;
    var chartsInicializados = false;

    // 2. Paleta de 24 colores únicos
    var paleta = [
        '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
        '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
        '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
        '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
    ];

    var colorMesMap = {
    'ENERO':      '#ce93d8',
    'FEBRERO':    '#7fd8be',
    'MARZO':      '#85c1e9',
    'ABRIL':      '#f8c471',
    'MAYO':       '#d2b4de',
    'JUNIO':      '#f1948a',
    'JULIO':      '#82e0aa',
    'AGOSTO':     '#f9e79f',
    'SEPTIEMBRE': '#ffb7b2',
    'OCTUBRE':    '#76d7c4',
    'NOVIEMBRE':  '#edbb99',
    'DICIEMBRE':  '#7dcea0'
    };

function colorParaMes(mes) {
        return colorMesMap[String(mes).toUpperCase()] || '#95a5a6';
    }

    // 3. Limpiar, filtrar y ordenar TODOS los ejercicios de la BD
    var ejerciciosOrdenados = (todosLosEjercicios || [])
        .map(function (ej) { return parseInt(ej, 10); })
        .filter(function (ej) { return !isNaN(ej); })
        .filter(function (ej, index, array) { return array.indexOf(ej) === index; })
        .sort(function (a, b) { return a - b; });

    // 4. Crear mapa fijo permanente (Año -> Color dinámico)
    var ejercicioColorMap = {};
    ejerciciosOrdenados.forEach(function (ejercicio, index) {
        ejercicioColorMap[String(ejercicio)] = paleta[index % paleta.length];
    });

    function colorParaEjercicio(ejercicio) {
        var clave = String(parseInt(ejercicio, 10));
        return ejercicioColorMap[clave] || '#95a5a6';
    }

    function colorPara(ejercicio) {
        return colorParaEjercicio(ejercicio);
    }

    function inicializarCharts() {
        if (chartsInicializados) return;

        var elBarras = document.getElementById('chartBarras');
        var elTorta  = document.getElementById('chartTorta');

        if (!elBarras || !elTorta) return;
        chartsInicializados = true;

        var meses = [];
        datosBarras.forEach(function (d) {
            if (meses.indexOf(d.mes) === -1) meses.push(d.mes);
        });

        // ── Gráfico de Barras ──
        var datasets = ejerciciosRecientes.map(function (ej) {
            return {
                label: String(ej),
                backgroundColor: colorParaEjercicio(ej),
                borderWidth: 0,
                maxBarThickness: 90,
                data: meses.map(function (m) {
                    var fila = datosBarras.find(function (d) { return d.mes === m && d.ejercicio == ej; });
                    return fila ? parseInt(fila.cantidad, 10) : 0;
                })
            };
        });

        // SI NO HAY EJERCICIO FILTRADO (Ejercicio: Todos) Y HAY UN MES FILTRADO:
        // Filtramos los datasets eliminando aquellos ejercicios cuyas cantidades sumen 0.
        if (!filtroEjercicio && filtroMes) {
            datasets = datasets.filter(function (dataset) {
                var sumaTotal = dataset.data.reduce(function (a, b) { return a + b; }, 0);
                return sumaTotal > 0;
            });
        }

        new Chart(elBarras, {
        type: 'bar',
        data: { 
            labels: meses, 
            datasets: datasets.map(function(dataset) {
                // Obtener el color constante asignado al año
                var colorDelAno = colorParaEjercicio(dataset.label);

                // 1. Filtro Ejercicio + Mes
                if (filtroEjercicio && filtroMes) {
                    return {
                        label: dataset.label,
                        borderColor: colorDelAno,
                        backgroundColor: colorMesMap[String(filtroMes).toUpperCase()] || '#95a5a6',
                        maxBarThickness: 90,
                        data: dataset.data
                    };
                }

                // 2. Solo Ejercicio seleccionado (Mes: Todos)
                if (filtroEjercicio) {
                    return {
                        label: dataset.label,
                        borderColor: colorDelAno,
                        backgroundColor: meses.map(function(m) { return colorParaMes(m); }),
                        maxBarThickness: 90,
                        data: dataset.data
                    };
                }

                // 3. Ejercicio: Todos y Mes: Todos (o solo Mes seleccionado)
                return {
                    label: dataset.label,
                    borderColor: colorDelAno,
                    backgroundColor: colorDelAno,
                    maxBarThickness: 90,
                    data: dataset.data
                };
            })
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        generateLabels: function(chart) {
                            return chart.data.datasets.map(function(ds, i) {
                                return {
                                    text: ds.label,
                                    fillStyle: ds.borderColor || ds.backgroundColor,
                                    strokeStyle: ds.borderColor || ds.backgroundColor,
                                    hidden: !chart.isDatasetVisible(i),
                                    datasetIndex: i
                                };
                            });
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
            }
        }
    });

       // ── Gráfico de Torta / Anillo ──
        var labels = filtroEjercicio ? meses : datosTorta.map(function(d) { return d.ejercicio; });
        var data   = filtroEjercicio
            ? meses.map(function(m) {
                var total = 0;
                datosBarras.forEach(function(d) { if (d.mes === m) total += parseInt(d.cantidad, 10); });
                return total;
            })
            : datosTorta.map(function (d) { return parseInt(d.total, 10); });

        // 1. Contar cuántas secciones contienen un valor mayor a 0
        var seccionesConDatos = data.filter(function(value) {
            return Number(value) > 0;
        }).length;

        var colors;

        if (filtroEjercicio && filtroMes) {

            // Ejercicio + Mes seleccionado:
            // todo el gráfico utiliza el color del mes.
            colors = meses.map(function() {
                return colorParaMes(filtroMes);
            });

        } else if (filtroEjercicio) {

            // Solo ejercicio:
            // conservar comportamiento actual.
            colors = meses.map(function(_, i) {
                return paleta[i % paleta.length];
            });

        } else {

            // Sin ejercicio:
            // usar colores por ejercicio.
            colors = datosTorta.map(function(d) {
                return colorParaEjercicio(d.ejercicio);
            });
        }
        var totalSum = data.reduce(function(a, b) { return a + b; }, 0);

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
                ctx.fillText(totalSum.toLocaleString(), centerX, centerY + 20);
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
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    // Evalúa dinámicamente si hay más de una sección con datos
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
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 15, padding: 10 }
                    },
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

    // 5. TOGGLE PANEL ESTADÍSTICAS
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

    // Inicialización al cargar la página
    inicializarCharts();
});
</script>
<?= $this->endSection() ?>