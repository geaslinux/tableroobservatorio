<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Turnos Hospitalarios · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── TURNOS_HOSPITALARIOS SPECIFIC STYLES ── */
    .th-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .th-breadcrumb i { color: var(--teal); font-size: 13px; }
    .th-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .th-breadcrumb a:hover { color: var(--teal); }
    .th-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .th-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .th-kpi-card {
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
    .th-kpi-content {
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
    .kpi-orange { border-left: 6px solid #ed8936; }
    .kpi-purple { border-left: 6px solid #9f7aea; }
    .kpi-navy { border-left: 6px solid var(--navy); }

    .th-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .th-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .th-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .th-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-orange .th-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .th-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .th-kpi-icon { background: #eef3f8; color: var(--navy); }

    .th-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .th-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .th-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .th-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .th-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .th-panel-title i { color: var(--teal); font-size: 17px; }
    .th-panel-body { padding: 18px 20px; }

    .th-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .th-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .th-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .th-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .th-filtro-select {
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
    .th-filtro-select:focus { border-color: var(--teal); }

    .th-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .th-stats-table-wrap {
        grid-area: table;
        max-height: 1000px;
        overflow-y: auto;
    }
    .th-stats-table { width: 90%; border-collapse: collapse; font-size: 12.5px; }
    .th-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .th-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .th-stats-table tbody tr:hover { background: #f8f9fb; }
    .th-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .th-chart-box.is-large { height: 500px; }
    .th-chart-box canvas { height: 100% !important; width: 100% !important; }
    .th-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .th-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .th-stats-header i { color: var(--teal); }
    .th-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .th-stats-panel.is-collapsed { margin-bottom: 12px; }
    .th-stats-panel.is-collapsed .th-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }

    .th-stats-body.is-hidden { display: none; }

    .th-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .th-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .th-kpi-badge.teal { background: #e6fffa; color: #319795; }

    @media (min-width: 993px) {
        .th-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie"
                "table meses";
            column-gap: 35px;
            row-gap: 55px;
        }

        .th-stats-table-wrap {
            grid-area: table;
        }

        #containerBarras {
            grid-area: bar;
            margin-bottom: 15px;
        }

        #containerTorta {
            grid-area: pie;
            margin-top: 15px;
            padding-top: 25px;
        }

        #containerMeses {
            grid-area: meses;
            margin-top: 15px;
            padding-top: 25px;
        }
    }

    @media (max-width: 992px) {
        .th-stats-body {
            grid-template-columns: 1fr;
            row-gap: 45px;
        }
    }
</style>

<!-- BREADCRUMB -->
<div class="th-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Turnos Hospitalarios — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="th-panel">
    <div class="th-panel-header">
        <span class="th-panel-title">
            <i class="fas fa-notes-medical"></i> TURNOS HOSPITALARIOS
        </span>
    </div>

    <div class="th-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="th-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('turno_hospitalario_list')) ?>" id="searchForm">
            <div class="th-filtro-bar">

                <!-- Filtros  -->
                <div class="th-filtro-group">
                    <span class="th-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="th-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= $ej ?>" <?= ($filtro_ejercicio ?? '') == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="th-filtro-group">
                    <span class="th-filtro-label">Mes</span>
                    <select name="mes" class="th-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= $m ?>" <?= ($filtro_mes ?? '') == $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ACCIONES FILTRO -->
                <div class="th-filtro-group">
                    <span class="th-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                    <button type="submit" class="bl-btn teal" style="height:36px;">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
        </div>
    </form>

        <!-- ── TARJETAS KPI ── -->
        <div class="th-kpi-cards">
            <div class="th-kpi-card kpi-teal">
                <div class="th-kpi-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="th-kpi-content">
                    <div class="th-kpi-label">ATENDIDOS</div>
                    <div class="th-kpi-value"><?= number_format($totAtendidos ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="th-kpi-card kpi-orange">
                <div class="th-kpi-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="th-kpi-content">
                    <div class="th-kpi-label">AUSENTES</div>
                    <div class="th-kpi-value"><?= number_format($totAusentes ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="th-kpi-card kpi-red">
                <div class="th-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="th-kpi-content">
                    <div class="th-kpi-label">CANCELADOS</div>
                    <div class="th-kpi-value"><?= number_format($totCancelados ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="th-kpi-card kpi-purple">
                <div class="th-kpi-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="th-kpi-content">
                    <div class="th-kpi-label">SIN CODIFICAR</div>
                    <div class="th-kpi-value"><?= number_format($totSinCodificar ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
            <div class="th-kpi-card kpi-navy">
                <div class="th-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="th-kpi-content">
                    <div class="th-kpi-label">TOTAL</div>
                    <div class="th-kpi-value"><?= number_format($totalGeneral ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS ── -->
        <div class="th-panel th-stats-panel" id="statsPanel">
            <div class="th-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="th-stats-body">
                <!-- Tabla detalle -->
                <div class="th-stats-table-wrap">
                    <div class="th-chart-title" style="text-align:left;">Región / Ejercicio</div>
                    <table class="th-stats-table">
                        <thead>
                            <tr>
                                <th>Región</th>
                                <th>Ejercicio</th>
                                <th>Cantidad</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statsFilas as $f): ?>
                            <tr>
                                <td><?= esc($f['region'] ?? '—') ?></td>
                                <td><?= esc($f['ejercicio']) ?></td>
                                <td><?= number_format($f['cantidad'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Barras comparativas -->
                <div class="th-chart-box">
                    <div class="th-chart-title">Comparativo por región (últimos ejercicios)</div>
                    <canvas id="chartBarras"></canvas>
                </div>

                <!-- Torta distribución -->
                <div class="th-chart-box">
                    <div class="th-chart-title">Distribución por ejercicio</div>
                    <canvas id="chartTorta"></canvas>
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
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // ── PLUGINS DE CHART.JS ──
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

            var totalCentro = chart.options.plugins.centerTotal || 0;
            ctx.fillText(totalCentro.toLocaleString(), centerX, centerY + 20);
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

    // ── DATOS DESDE PHP ──
    var datosBarras = <?= json_encode($statsBarras) ?>;
    var ejerciciosRecientes = <?= json_encode($statsEjercicios) ?>;
    var datosTorta = <?= json_encode($statsTorta) ?>;
    var datosMeses = <?= json_encode($statsMeses) ?>;
    var todasLasRegiones = <?= json_encode($regiones ?? []) ?>;
    var filtroEjercicio = <?= json_encode($filtro_ejercicio ?? '') ?>;
    var filtroMes = <?= json_encode($filtro_mes ?? '') ?>;
    var todosLosEjercicios = <?= json_encode($ejercicios ?? []) ?>;
    var totalGeneral = <?= (int) ($totalGeneral ?? 0) ?>;

    (function () {
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
        
        // ── MAPA FIJO Y DINÁMICO DE COLORES POR EJERCICIO ──
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

        // Extraer Regiones dinámicamente si no vienen definidas
        var regiones = [];
        datosBarras.forEach(function (d) {
            var reg = d.region || '—';
            if (regiones.indexOf(reg) === -1) regiones.push(reg);
        });

        // Generar colores por región
        var regionColorMap = {};
        regiones.forEach(function (r, idx) {
            regionColorMap[r] = paleta[idx % paleta.length];
        });

        function colorParaRegion(region) {
            return regionColorMap[region] || '#95a5a6';
        }

        // ── CONFIGURACIÓN BARRAS (Región vs Ejercicios) ──
        var etiquetasBarras = filtroEjercicio ? <?php echo json_encode($meses); ?> : regiones;
        var datasets = filtroEjercicio ? [{
            label: 'Total',
            backgroundColor: <?php echo json_encode($meses); ?>.map(function(m) { return colorParaMes(m); }),
            maxBarThickness: 90,
            data: <?php echo json_encode($meses); ?>.map(function(m) {
                var fila = datosBarras.find(function(d) { return d.mes === m; });
                return fila ? parseInt(fila.total, 10) : 0;
            })
        }] : ejerciciosRecientes.map(function (ej) {
            return {
                label: String(ej),
                backgroundColor: colorParaEjercicio(ej),
                maxBarThickness: 90,
                data: regiones.map(function (r) {
                    var fila = datosBarras.find(function (d) { 
                        return (d.region || '—') === r && d.ejercicio == ej; 
                    });
                    return fila ? parseInt(fila.cantidad, 10) : 0;
                })
            };
        });

        // Gráfico de Barras
        new Chart(document.getElementById('chartBarras'), {
            type: 'bar',
            data: { labels: etiquetasBarras, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: { display: false },
                    legend: { display: !filtroEjercicio, position: 'top' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        // ── CONFIGURACIÓN DONA (Distribución por Ejercicio o Mes) ──
        var etiquetasDona = filtroEjercicio ? <?php echo json_encode($meses); ?> : datosTorta.map(function (d) { return d.ejercicio; });

        var valoresDona = filtroEjercicio
            ? <?php echo json_encode($meses); ?>.map(function(m) {
                var fila = datosBarras.find(function(d) { return d.mes === m; });
                return fila ? parseInt(fila.total, 10) : 0;
            })
            : datosTorta.map(function (d) { return parseInt(d.total, 10); });

        var coloresDona = filtroEjercicio
            ? <?php echo json_encode($meses); ?>.map(colorParaMes)
            : datosTorta.map(function (d) { return colorParaEjercicio(d.ejercicio); });

        var seccionesConDatos = valoresDona.filter(function(v) { return Number(v) > 0; }).length;

        new Chart(document.getElementById('chartTorta'), {
            type: 'doughnut',
            data: {
                labels: etiquetasDona,
                datasets: [{
                    data: valoresDona,
                    backgroundColor: coloresDona,
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
                        }
                    },
                    centerTotal: totalGeneral
                }
            }
        });

        // Toggle Estadísticas
        var statsPanel = document.getElementById('statsPanel');
        var statsBody  = document.getElementById('statsBody');
        var btnStats   = document.getElementById('btnToggleStats');
        var iconoStats = document.getElementById('iconoToggleStats');
        var textoStats = document.getElementById('textoToggleStats');
        var statsVisible = true;

        if (btnStats) {
            btnStats.addEventListener('click', function () {
                statsVisible = !statsVisible;
                if (statsVisible) {
                    statsBody.classList.remove('is-hidden');
                    statsPanel.classList.remove('is-collapsed');
                    iconoStats.className = 'fas fa-eye-slash';
                    textoStats.textContent = 'Ocultar estadísticas';
                } else {
                    statsBody.classList.add('is-hidden');
                    statsPanel.classList.add('is-collapsed');
                    iconoStats.className = 'fas fa-eye';
                    textoStats.textContent = 'Mostrar estadísticas';
                }
            });
        }
    })();
});
</script>
<?= $this->endSection() ?>
