<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Móviles · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── MOVIL_LIST SPECIFIC STYLES ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── KPIS (Adaptación de base_list) ── */
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
    .ml-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
        align-items: center;
    }
    .kpi-teal { border-left: 6px solid #81e6d9; }
    .kpi-blue { border-left: 6px solid #90cdf4; }
    .kpi-red  { border-left: 6px solid #feb2b2; }
    .kpi-navy { border-left: 6px solid #a0aec0; }

    .ml-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .ml-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .ml-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .ml-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-navy .ml-kpi-icon { background: #f7fafc; color: #4a5568; }
    
    .ml-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
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
    .ml-filtro-group { display: flex; flex-direction: column; gap: 5px; }
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
    .ml-chart-box { position: relative; width: 100%; height: 350px; }
    .ml-chart-box canvas { height: 350px !important; width: 100% !important; }
    .ml-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    
    .ml-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .ml-stats-header i { color: var(--teal); }
    .ml-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .ml-stats-panel.is-collapsed { margin-bottom: 12px; }
    .ml-stats-panel.is-collapsed .ml-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .ml-stats-body.is-hidden { display: none; }

    @media (max-width: 992px) {
        .ml-stats-body { grid-template-columns: 1fr; }
    }
</style>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('base_views')); ?>">Prehospitalario</a> ›
    <strong>Móviles — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-truck-moving"></i> MÓVILES
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('base_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('movil_export')); ?>?<?= http_build_query([
                'ejercicio' => $filtro_ejercicio,
                'tipo'      => $filtro_tipo,
                'estado'    => $filtro_estado,
            ]) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('movil_list')); ?>" id="searchForm">
            <div class="ml-filtro-bar">
                <!-- EJERCICIO -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- TIPO -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Tipo</span>
                    <select name="tipo" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= esc($t) ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ESTADO -->
                <div class="ml-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="ml-filtro-label">Estado</span>
                    <select name="estado" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>
                <!-- ACCIONES -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">&nbsp;</span>
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
        <div class="ml-kpi-cards">
            <div class="ml-kpi-card kpi-teal">
                <div class="ml-kpi-icon"><i class="fas fa-truck-moving"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">OPERATIVOS</div>
                    <div class="ml-kpi-value"><?= $totalOperativos ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-blue">
                <div class="ml-kpi-icon"><i class="fas fa-boxes"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">LOGÍSTICA</div>
                    <div class="ml-kpi-value"><?= $totalLogistica ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-red">
                <div class="ml-kpi-icon"><i class="fas fa-times-circle"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">FUERA DE SERVICIO</div>
                    <div class="ml-kpi-value"><?= $totalFueraServicio ?></div>
                </div>
            </div>
            <div class="ml-kpi-card kpi-navy">
                <div class="ml-kpi-icon"><i class="fas fa-truck"></i></div>
                <div class="ml-kpi-content">
                    <div class="ml-kpi-label">TOTAL</div>
                    <div class="ml-kpi-value"><?= $totalGeneralCantidad ?></div>
                </div>
            </div>
        </div>
                            
        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="ml-panel ml-stats-panel" id="statsPanel">
            <div class="ml-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE ATENCIONES</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div class="" id="statsBody">

        <div class="ml-stats-body">
            <!-- Tabla detalle -->
            <div class="ml-stats-table-wrap">
                <div class="ml-chart-title" style="text-align:left;">Vehículos por tipo / ejercicio</div>
                <table class="ml-stats-table">
                    <thead>
                        <tr><th>Tipo</th><th>Ejercicio</th><th>Cantidad</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsFilas as $f): ?>
                        <tr>
                            <td><?= esc($f['tipo']) ?></td>
                            <td><?= esc($f['ejercicio']) ?></td>
                            <td><?= esc($f['cantidad']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Barras comparativas -->
            <div class="ml-chart-box">
                <div class="ml-chart-title">Comparativo por tipo (últimos ejercicios)</div>
                <canvas id="chartBarras"></canvas>
            </div>

            <!-- Torta distribución -->
            <div class="ml-chart-box">
                <div class="ml-chart-title">Distribución por ejercicio</div>
                <canvas id="chartTorta"></canvas>
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

    // ── MOVIL_LIST CHART SCRIPTS ──
    (function () {
        var colEstado = document.getElementById('col-estado');
        var btn       = document.getElementById('btnToggle');
        var icono     = document.getElementById('iconoToggle');

        var params  = new URLSearchParams(window.location.search);
        var visible = !!params.get('estado');

        function aplicar() {
            var display = visible ? '' : 'none';
            colEstado.style.display = display;
            icono.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
            btn.title = visible ? 'Ocultar Estado' : 'Mostrar Estado';
        }

        aplicar();

        btn.addEventListener('click', function () {
            visible = !visible;
            aplicar();
        });
    })();
    

    var datosBarras = <?= json_encode($statsBarras) ?>;
    var ejerciciosRecientes = <?= json_encode($statsEjercicios) ?>;
    var datosTorta = <?= json_encode($statsTorta) ?>;
    var filtroEjercicio = <?= json_encode($filtro_ejercicio) ?>;
    var todosLosEjercicios = <?= json_encode($todosLosEjercicios) ?>;

    // Colores para cada tipo de móvil
    var coloresTipo = {
        'MOVILES OPERATIVOS': '#81e6d9',  // Teal pastel
        'MOVILES LOGISTICA': '#90cdf4',   // Blue pastel
        'FUERA DE SERVICIO': '#feb2b2'    // Red pastel
    };

    (function () {
        // 1. Elementos del DOM para colapsar estadísticas
        var btnToggleStats = document.getElementById('btnToggleStats');
        var statsBody      = document.getElementById('statsBody');
        var statsPanel     = document.getElementById('statsPanel');
        var iconoStats     = document.getElementById('iconoToggleStats');
        var textoStats     = document.getElementById('textoToggleStats');

        var statsVisible = true;

        // Evento Click para colapsar / expandir
        if (btnToggleStats) {
            btnToggleStats.addEventListener('click', function () {
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

        // 2. Paleta de 24 colores únicos
        var paleta = [
        '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
        '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
        '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
        '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
        ];

        // 3. Limpiar, filtrar y ordenar TODOS los ejercicios de la BD
        var ejerciciosOrdenados = (todosLosEjercicios || [])
            .map(function (ej) { return parseInt(ej, 10); })
            .filter(function (ej) { return !isNaN(ej); })
            .filter(function (ej, index, array) { return array.indexOf(ej) === index; })
            .sort(function (a, b) { return a - b; });

        // 4. Crear mapa fijo permanente (Año -> Color)
        var ejercicioColorMap = {};
        ejerciciosOrdenados.forEach(function (ejercicio, index) {
            ejercicioColorMap[String(ejercicio)] = paleta[index % paleta.length];
        });

        function colorParaEjercicio(ejercicio) {
            var clave = String(parseInt(ejercicio, 10));
            return ejercicioColorMap[clave] || '#95a5a6';
        }

        // 5. Configurar Gráfico de Barras
        var tipos = [];
        datosBarras.forEach(function (d) {
            if (tipos.indexOf(d.tipo) === -1) tipos.push(d.tipo);
        });

        var datasets = ejerciciosRecientes.map(function (ej) {
            return {
                label: String(ej),
                backgroundColor: colorParaEjercicio(ej),
                maxBarThickness: 90,
                data: tipos.map(function (t) {
                    var fila = datosBarras.find(function (d) {
                        return d.tipo === t && d.ejercicio == ej;
                    });

                    return fila ? parseInt(fila.cantidad, 10) : 0;
                })
            };
        });

        new Chart(document.getElementById('chartBarras'), {
            type: 'bar',
            data: {
                labels: tipos,
                datasets: datasets.map(function(dataset) {
                    if (filtroEjercicio) {
                        return {
                            label: dataset.label,
                            backgroundColor: tipos.map(function(tipo) {
                                return coloresTipo[tipo] || '#9b59b6';
                            }),
                            maxBarThickness: 90,
                            data: dataset.data
                        };
                    }
                    return dataset;
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: false
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            generateLabels: function(chart) {
                                return chart.data.datasets.map(function(ds, i) {
                                    return {
                                        text: ds.label,
                                        fillStyle: colorParaEjercicio(ds.label),
                                        strokeStyle: colorParaEjercicio(ds.label),
                                        hidden: !chart.isDatasetVisible(i),
                                        datasetIndex: i
                                    };
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { ticks: { autoSkip: false } }
                },
                layout: {
                    padding: { left: 10, right: 10, top: 10, bottom: 10 }
                }
            }
        });

        // 6. Configurar Gráfico de Torta
        var elTorta = document.getElementById('chartTorta');

        // Plugins para la dona
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
                var total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                ctx.fillText(total.toLocaleString(), centerX, centerY + 20);
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
        

        // 1. Obtener los valores numéricos
            var dataValues = filtroEjercicio
                ? tipos.map(function(tipo) {
                    var total = 0;
                    datosBarras.forEach(function(item) {
                        if (item.tipo === tipo && item.ejercicio == filtroEjercicio) {
                            total += parseInt(item.cantidad, 10);
                        }
                    });
                    return total;
                })
                : datosTorta.map(function(d) { return parseInt(d.total, 10); });

            // 2. Contar cuántas secciones tienen valores mayor a 0 (al igual que en asistencia)
            var seccionesConDatos = dataValues.filter(function(value) {
                return Number(value) > 0;
            }).length;

            // 3. Pasar las variables al gráfico
            new Chart(elTorta, {
                type: 'doughnut',
                data: {
                    labels: filtroEjercicio ? tipos : datosTorta.map(function(d) { return d.ejercicio; }),
                    datasets: [{
                        data: dataValues,
                        backgroundColor: filtroEjercicio
                            ? tipos.map(function(tipo) { return coloresTipo[tipo] || '#9b59b6'; })
                            : datosTorta.map(function(d) { return colorParaEjercicio(d.ejercicio); }),
                        borderColor: '#ffffff',
                        borderWidth: 0,
                        // Si hay más de 1 dato, se aplican spacing y offset; si es 1 solo dato, quedan en 0
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
                            // Obtener la suma total directamente de los datos del dataset
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
    })();
</script>
<?= $this->endSection() ?>