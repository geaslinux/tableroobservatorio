<?= $this->extend('layout/main'); ?> //777
<?= $this->section('title') ?> Consultas y Reclamos · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── CONSULTA_RECLAMO_LIST SPECIFIC STYLES (MATCHING ATENCION_LIST) ── */
    .cr-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cr-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cr-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cr-breadcrumb a:hover { color: var(--teal); }
    .cr-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .cr-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .cr-kpi-card {
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

    .kpi-teal { border-left: 6px solid #38b2ac; }
    .kpi-blue { border-left: 6px solid #4299e1; }
    .kpi-red  { border-left: 6px solid #f56565; }
    .kpi-orange { border-left: 6px solid #ed8936; }
    .kpi-purple { border-left: 6px solid #9f7aea; }
    .kpi-navy { border-left: 6px solid var(--navy); }

    .cr-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cr-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cr-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cr-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-orange .cr-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .cr-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .cr-kpi-icon { background: #eef3f8; color: var(--navy); }

    .cr-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .cr-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .cr-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cr-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cr-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cr-panel-title i { color: var(--teal); font-size: 17px; }
    .cr-panel-body { padding: 18px 20px; }

    .cr-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .cr-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cr-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cr-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cr-filtro-select {
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
    .cr-filtro-select:focus { border-color: var(--teal); }

    .cr-stats-body {
        padding: 25px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 30px;
    }
    .cr-stats-table-wrap {
        grid-area: table;
        max-height: 1000px;
        overflow-y: auto;
    }
    .cr-stats-table { width: 90%; border-collapse: collapse; font-size: 12.5px; }
    .cr-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .cr-stats-table tbody td {
        padding: 7px 5px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .cr-stats-table tbody tr:hover { background: #f8f9fb; }
    .cr-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .cr-chart-box.is-large { height: 500px; }
    .cr-chart-box canvas { height: 100% !important; width: 100% !important; }
    .cr-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .cr-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .cr-stats-header i { color: var(--teal); }
    .cr-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .cr-stats-panel.is-collapsed { margin-bottom: 12px; }
    .cr-stats-panel.is-collapsed .cr-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }

    .cr-stats-body.is-hidden { display: none; }

    .cr-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .cr-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .cr-kpi-badge.teal { background: #e6fffa; color: #319795; }

    @media (min-width: 993px) {
        .cr-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie";
            column-gap: 35px;
            row-gap: 55px;
        }

        .cr-stats-table-wrap {
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
    }

    @media (max-width: 992px) {
        .cr-stats-body {
            grid-template-columns: 1fr;
            row-gap: 45px;
        }
    }
</style>

<!-- BREADCRUMB -->
<div class="cr-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Consultas y Reclamos — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="cr-panel">
    <div class="cr-panel-header">
        <span class="cr-panel-title">
            <i class="fas fa-headset"></i> CONSULTAS Y RECLAMOS
        </span>
    </div>

    <div class="cr-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="cr-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('consulta_reclamo_list')) ?>" id="searchForm">
            <div class="cr-filtro-bar">

                <!-- EJERCICIO -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- MES -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Mes</span>
                    <select name="mes" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= $m ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- TIPO -- toggle -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Tipo</span>
                    <select name="tipo_llamado" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- CATEGORÍA -- toggle -->
                <div class="cr-filtro-group campo-toggle" id="col-categoria" style="display:none;">
                    <span class="cr-filtro-label">Categoría</span>
                    <select name="categoria" class="cr-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat->categoria_id ?>" <?= $filtro_categoria == $cat->categoria_id ? 'selected' : '' ?>>
                                <?= esc($cat->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ESTADO — toggle -->
                <div class="cr-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="cr-filtro-label">Estado</span>
                    <select name="estado" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px;">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button type="button" class="bl-btn ghost" id="btnToggle" style="height:36px;" title="Mostrar/ocultar Estado">
                            <i class="fas fa-sliders-h" id="iconoToggle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS KPI ── -->
        <div class="cr-kpi-cards">
            <div class="cr-kpi-card kpi-red">
                <div class="cr-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="cr-kpi-content">
                    <div class="cr-kpi-label">RECLAMO</div>
                    <div class="cr-kpi-value"><?= $totalesPorTipo['RECLAMO'] ?? 0 ?></div>
                </div>
            </div>
            <div class="cr-kpi-card kpi-blue">
                <div class="cr-kpi-icon"><i class="fas fa-info-circle"></i></div>
                <div class="cr-kpi-content">
                    <div class="cr-kpi-label">CONSULTA</div>
                    <div class="cr-kpi-value"><?= $totalesPorTipo['CONSULTA'] ?? 0 ?></div>
                </div>
            </div>
            <div class="cr-kpi-card kpi-orange">
                <div class="cr-kpi-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="cr-kpi-content">
                    <div class="cr-kpi-label">SUGERENCIA</div>
                    <div class="cr-kpi-value"><?= $totalesPorTipo['SUGERENCIA'] ?? 0 ?></div>
                </div>
            </div>
            <div class="cr-kpi-card kpi-teal">
                <div class="cr-kpi-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="cr-kpi-content">
                    <div class="cr-kpi-label">FELICITACIÓN</div>
                    <div class="cr-kpi-value"><?= $totalesPorTipo['FELICITACION'] ?? 0 ?></div>
                </div>
            </div>
                <div class="cr-kpi-card kpi-navy">
                    <div class="cr-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                    <div class="cr-kpi-content">
                        <div class="cr-kpi-label">TOTAL</div>
                        <div class="cr-kpi-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
                    </div>
                </div>
            </div>

        <!-- ── PANEL ESTADÍSTICAS ── -->
        <div class="cr-panel cr-stats-panel" id="statsPanel">
            <div class="cr-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="cr-stats-body">
                    <!-- Tabla detalle -->
                    <div class="cr-stats-table-wrap">
                        <div class="cr-chart-title" style="text-align:left;">Atenciones por tipo / ejercicio</div>
                        <table class="cr-stats-table">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Ejercicio</th>
                                    <?php if ($mostrarMesEnTabla): ?><th>Mes</th><?php endif; ?>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statsFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['tipo']) ?></td>
                                    <td><?= esc($f['ejercicio']) ?></td>
                                    <?php if ($mostrarMesEnTabla): ?><td><?= esc($f['mes']) ?></td><?php endif; ?>
                                    <td><?= number_format($f['cantidad'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Barras comparativas -->
                    <div class="cr-chart-box">
                        <div class="cr-chart-title">Comparativo por tipo (últimos ejercicios)</div>
                        <canvas id="chartBarras"></canvas>
                    </div>

                    <!-- Torta distribución -->
                    <div class="cr-chart-box">
                        <div class="cr-chart-title">Distribución por ejercicio</div>
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

if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // ── ATENCION_LIST SCRIPTS ──
    (function () {
        // Toggle filtro Estado
        var colEstado    = document.getElementById('col-estado');
        var colCategoria = document.getElementById('col-categoria');
        var btn          = document.getElementById('btnToggle');
        var icono        = document.getElementById('iconoToggle');
        var params       = new URLSearchParams(window.location.search);
        var visible      = !!params.get('estado') || !!params.get('categoria');

        function aplicar() {
            colEstado.style.display    = visible ? '' : 'none';
            colCategoria.style.display = visible ? '' : 'none';
            icono.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
            btn.title = visible ? 'Ocultar Filtros' : 'Mostrar Filtros';
        }

        aplicar();

        btn.addEventListener('click', function () {
            visible = !visible;
            aplicar();
        });
    })();

    /*
     * ========================================================
     * TEXTO DEL CENTRO
     * ========================================================
     */
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

    // ── CHARTS ──
    var datosBarras = <?= json_encode($statsBarras) ?>;
    var ejerciciosRecientes = <?= json_encode($statsEjercicios) ?>;
    var datosTorta = <?= json_encode($statsTorta) ?>;
    var todosLosTipos = <?= json_encode($tipos ?? []) ?>;
    var tipoColorMapServidor = <?= json_encode($tipoColorMap ?? []) ?>;
    var filtroEjercicio = <?= json_encode($filtro_ejercicio ?? '') ?>;
    var filtroMes = <?= json_encode($filtro_mes ?? '') ?>;
    var filtroTipo = <?= json_encode($filtro_tipo ?? '') ?>;
    var todosLosEjercicios  = <?= json_encode($ejercicios ?? []) ?>;

    (function () {
        var coloresEjercicio = {};
        var paleta = ['#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
            '#82e0aa', '#f9e79f', '#bdc3c7', '#76d7c4', '#edbb99', '#7dcea0',
            '#bb8fce', '#7fb3d5', '#f8c471', '#85929e', '#a3e4d7', '#e6b0aa',
            '#a6acaf', '#b8859f', '#ef9a9a', '#708090', '#90caf9', '#a5d6a7'];

        var colorMesMap = {
            'ENERO':      '#ce93d8',
            'FEBRERO':    '#7fd8be',
            'MARZO':      '#85c1e9',
            'ABRIL':      '#f8c471',
            'MAYO':       '#d2b4de',
            'JUNIO':      '#f1948a',
            'JULIO':      '#82e0aa',
            'AGOSTO':     '#f9e79f',
            'SEPTIEMBRE': '#bdc3c7',
            'OCTUBRE':    '#76d7c4',
            'NOVIEMBRE':  '#edbb99',
            'DICIEMBRE':  '#7dcea0'
        };

        var coloresTipo = {
            'RECLAMO':      '#feb2b2', 
            'CONSULTA':     '#90cdf4', 
            'SUGERENCIA':   '#fbd38d',
            'FELICITACION': '#81e6d9'  
        };

        function colorParaMes(mes) {
            return colorMesMap[String(mes).toUpperCase()] || '#95a5a6';
        }

        var listaEjerciciosOrdenada = todosLosEjercicios.slice().sort(function(a, b) {
            return parseInt(a, 10) - parseInt(b, 10);
        });

        var ejercicioColorMap = {};
        listaEjerciciosOrdenada.forEach(function (ejercicio, index) {
            ejercicioColorMap[String(ejercicio)] = paleta[index % paleta.length];
        });

        function colorParaEjercicio(ejercicio) {
            return ejercicioColorMap[String(ejercicio)] || '#95a5a6';
        }

        var tipos = [];
        datosBarras.forEach(function (d) {
            if (tipos.indexOf(d.tipo) === -1) tipos.push(d.tipo);
        });

        var tipoColorMap = {};
        (todosLosTipos || []).forEach(function (tipo, idx) {
            if (tipo) {
                var clave = String(tipo).toUpperCase();
                tipoColorMap[clave] = paleta[idx % paleta.length];
            }
        });

        function colorParaTipo(tipo) {
            var clave = String(tipo).trim().toUpperCase();
            if (coloresTipo[clave]) return coloresTipo[clave];
            if (tipoColorMapServidor && tipoColorMapServidor[clave]) return tipoColorMapServidor[clave];
            return tipoColorMap[clave] || '#95a5a6';
        }

        var mostrarDesgloseMensual = filtroEjercicio && filtroTipo && !filtroMes;
        var datasets;
        var etiquetasBarras;

        if (mostrarDesgloseMensual) {
            etiquetasBarras = Object.keys(colorMesMap);
            datasets = [{
                label: filtroTipo + ' (' + filtroEjercicio + ')',
                backgroundColor: etiquetasBarras.map(colorParaMes),
                maxBarThickness: 90, // <--- Control de tamaño cuando hay pocos o 1 dato
                data: etiquetasBarras.map(function (mes) {
                    return datosBarras.reduce(function (total, fila) {
                        if (String(fila.mes).toUpperCase() === mes) {
                            return total + (parseInt(fila.cantidad, 10) || 0);
                        }
                        return total;
                    }, 0);
                })
            }];
        } else {
            etiquetasBarras = tipos;
            datasets = ejerciciosRecientes.map(function (ej, idx) {
                return {
                    label: String(ej),
                    backgroundColor: filtroEjercicio
                        ? tipos.map(function (tipo) { return colorParaTipo(tipo); })
                        : colorParaEjercicio(ej),
                    maxBarThickness: 90, // <--- Control de tamaño cuando hay pocos o 1 dato
                    data: tipos.map(function (t) {
                        var fila = datosBarras.find(function (d) { return d.tipo === t && d.ejercicio == ej; });
                        return fila ? parseInt(fila.cantidad, 10) : 0;
                    })
                };
            });
        }

        // GRÁFICO DE BARRAS
        new Chart(document.getElementById('chartBarras'), {
            type: 'bar',
            data: { labels: etiquetasBarras, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                maxBarThickness: 90, // <--- Regla general para el canvas de barras
                plugins: {
                    datalabels: { display: false },
                    legend: {
                        position: 'top',
                        labels: {
                            generateLabels: function(chart) {
                                return chart.data.datasets.map(function(dataset, index) {
                                    var colorEjercicio = mostrarDesgloseMensual
                                        ? colorParaTipo(filtroTipo)
                                        : colorParaEjercicio(dataset.label);

                                    return {
                                        text: dataset.label,
                                        fillStyle: colorEjercicio,
                                        strokeStyle: colorEjercicio,
                                        lineWidth: 1,
                                        hidden: !chart.isDatasetVisible(index),
                                        datasetIndex: index
                                    };
                                });
                            }
                        }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });

        // DATOS Y GRÁFICO DE DONA
        var totalCentro = <?= $totalGeneral ?>;

        var etiquetasDona = mostrarDesgloseMensual
            ? Object.keys(colorMesMap)
            : filtroEjercicio
            ? tipos
            : datosTorta.map(function (d) { return d.ejercicio; });

        var valoresDona = mostrarDesgloseMensual
            ? etiquetasDona.map(function (mes) {
                return datosBarras.reduce(function (total, fila) {
                    if (String(fila.mes).toUpperCase() === mes) {
                        return total + (parseInt(fila.cantidad, 10) || 0);
                    }
                    return total;
                }, 0);
            })
            : filtroEjercicio
            ? tipos.map(function (tipo) {
                return datosBarras.reduce(function (total, fila) {
                    if (fila.tipo === tipo && String(fila.ejercicio) === String(filtroEjercicio)) {
                        return total + (parseInt(fila.cantidad, 10) || 0);
                    }
                    return total;
                }, 0);
            })
            : datosTorta.map(function (d) { return parseInt(d.total, 10); });

        var coloresDona = mostrarDesgloseMensual
            ? etiquetasDona.map(colorParaMes)
            : filtroEjercicio
            ? tipos.map(function (tipo) { return colorParaTipo(tipo); })
            : datosTorta.map(function (d) {
                return colorParaEjercicio(d.ejercicio);
            });

        // Contar cuántos segmentos de la dona son mayores a 0
        var seccionesConDatos = valoresDona.filter(function(value) {
            return Number(value) > 0;
        }).length;

        new Chart(document.getElementById('chartTorta'), {
            type: 'doughnut',
            data: {
                labels: etiquetasDona,
                datasets: [{
                    data: valoresDona,
                    backgroundColor: coloresDona,
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
                    },
                    centerTotal: totalCentro
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
    })();
</script>
<?= $this->endSection() ?>