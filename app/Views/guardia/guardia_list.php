<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GUARDIAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ── ATENCION_LIST SPECIFIC STYLES ── */
    .gd-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .gd-breadcrumb i { color: var(--teal); font-size: 13px; }
    .gd-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .gd-breadcrumb a:hover { color: var(--teal); }
    .gd-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .gd-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .gd-kpi-card {
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
    .gd-kpi-content {
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
    
    .gd-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .gd-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .gd-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .gd-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .gd-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .gd-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .gd-kpi-icon { background: #eef3f8; color: var(--navy); }
    
    .gd-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .gd-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }
    
    .gd-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .gd-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .gd-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .gd-panel-title i { color: #fff; font-size: 17px; }
    .gd-panel-body { padding: 18px 20px; }
    .gd-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .gd-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .gd-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .gd-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .gd-filtro-select {
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
    .gd-filtro-select:focus { border-color: var(--teal); }

    .gd-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .gd-stats-table-wrap { overflow-x: auto; grid-area: table; }
    .gd-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .gd-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left;
    }
    .gd-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .gd-stats-table tbody tr:hover { background: #f8f9fb; }
    .gd-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .gd-chart-box.is-large { height: 500px; }
    .gd-chart-box canvas { height: 100% !important; width: 100% !important; }
    .gd-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    .gd-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .gd-stats-header i { color: var(--teal); }
    .gd-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .gd-stats-panel.is-collapsed { margin-bottom: 12px; }
    .gd-stats-panel.is-collapsed .gd-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .gd-stats-body.is-hidden { display: none; }
    

    @media (min-width: 993px) {
        .gd-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie";
        }
        .gd-stats-table-wrap { grid-area: table; }
        #containerBarras { grid-area: bar; }
        #containerTorta { grid-area: pie; }
    }

    @media (max-width: 992px) {
        .gd-stats-body { grid-template-columns: 1fr; }
    }
</style>

    <!-- BREADCRUMB -->
    <div class="gd-breadcrumb">
        <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
        <strong>Guardias — Listado</strong>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="gd-panel">
        <div class="gd-panel-header">
            <span class="gd-panel-title">
                <i class="fas fa-user-shield"></i> GUARDIAS POR ESPECIALIDAD
            </span>
        </div>

        <div class="gd-panel-body">

            <!-- TOOLBAR -->
            <div class="gd-toolbar">
                <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="bl-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('guardia_export')); ?>?<?= http_build_query([
                    'anio'        => $filtro_anio,
                    'semestre'    => $filtro_semestre,
                    'mes'         => $filtro_mes,
                    'efector_id'  => $filtro_efector,
                    'servicio_id' => $filtro_servicio,
                ]) ?>" class="bl-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('guardia_list')); ?>" id="searchForm">
                <div class="gd-filtro-bar">
                    <!-- EJERCICIO -->
                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Ejercicio</span>
                        <select name="anio" class="gd-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($anios as $a): ?>
                                <option value="<?= $a ?>" <?= $filtro_anio == $a ? 'selected' : '' ?>><?= $a ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Semestre</span>
                        <select name="semestre" class="gd-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($semestres as $s): ?>
                                <option value="<?= $s ?>" <?= $filtro_semestre == $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Mes</span>
                        <select name="mes" class="gd-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($meses as $m): ?>
                                <option value="<?= $m ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Efector</span>
                        <select name="efector_id" class="gd-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Servicio</span>
                        <select name="servicio_id" class="gd-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($servicios as $s): ?>
                                <option value="<?= $s->servicio_id ?>" <?= $filtro_servicio == $s->servicio_id ? 'selected' : '' ?>><?= esc($s->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- ACCIONES -->
                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">&nbsp;</span>
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                        </div>
                    </div>
                </div>
            </form>     

                <!-- KPI CARDS -->
                <div class="gd-kpi-cards">
                    <?php foreach ($totalPorSemestre as $s): ?>
                    <div class="gd-kpi-card kpi-teal">
                        <div class="gd-kpi-icon"><i class="fas fa-map-marked-alt"></i></div>
                        <div class="gd-kpi-content">
                            <div class="gd-kpi-label"><?= esc($s['semestre']) ?> SEMESTRE</div>
                            <div class="gd-kpi-value"><?= number_format($s['total'], 0, ',', '.') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="gd-kpi-card kpi-navy">
                        <div class="gd-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                        <div class="gd-kpi-content">
                            <div class="gd-kpi-label">TOTAL</div>
                            <div class="gd-kpi-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
                        </div>
                    </div>
                </div>

            <!-- PANEL ESTADÍSTICAS -->
            <div class="gd-panel gd-stats-panel" id="statsPanel">
                <div class="gd-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE GUARDIAS</span>
                    <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                    </button>
                </div>
                <div id="statsBody">
                    <div class="gd-stats-body">
                        <!-- TABLA PRINCIPAL -->
                        <div class="gd-stats-table-wrap">
                            <div class="gd-chart-title" style="text-align:left;">Resumen de guardias</div>
                            <table class="gd-stats-table">
                                <thead>
                                    <tr>
                                        <th>Hospital</th>
                                        <th>Año</th>
                                        <th>Semestre</th>
                                        <th>Mes</th>
                                        <?php foreach ($servicios as $s): ?>
                                            <th><?= esc(mb_strtoupper($s->nombre)) ?></th>
                                        <?php endforeach; ?>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($grupos)): ?>
                                        <tr><td colspan="<?= count($servicios) + 5 ?>" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin atenciones de guardia para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                <?php foreach ($grupos as $g): ?>
                                    <tr>
                                        <td><?= esc($g['hospital']) ?></td>
                                        <td><?= esc($g['anio']) ?></td>
                                        <td><span class="gd-tag blue"><?= esc($g['semestre']) ?></span></td>
                                        <td><?= esc($g['mes'] ?? '—') ?></td>
                                        <?php foreach ($servicios as $s): ?>
                                            <td><?= number_format($g['servicios'][$s->servicio_id] ?? 0, 0, ',', '.') ?></td>
                                        <?php endforeach; ?>
                                        <td><strong><?= number_format($g['total'], 0, ',', '.') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="gd-chart-box" id="containerBarras">
                            <div class="gd-chart-title">Total por año</div>
                            <canvas id="chartBarras"></canvas>
                        </div>

                        <div class="gd-chart-box" id="containerTorta">
                            <div class="gd-chart-title">Distribución por semestre</div>
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
    function eliminarGrupo(clave, hospital) {
        if (!confirm('¿Eliminar todos los registros de guardia de "' + hospital + '" para este período?')) return;

        fetch('<?= base_url(route_to('guardia_destroy')) ?>?id=' + encodeURIComponent(clave), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= esc(config('Config\Security')->headerName ?? 'X-CSRF-TOKEN', 'js') ?>': '<?= csrf_hash() ?>'
            }
        }).then(() => window.location.reload());
    }
</script>

<script>
    var totalPorSemestre = <?= json_encode($totalPorSemestre) ?>;

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

    (function () {
        var statsPanel = document.getElementById('statsPanel');
        var statsBody  = document.getElementById('statsBody');
        var btnStats   = document.getElementById('btnToggleStats');
        var iconoStats = document.getElementById('iconoToggleStats');
        var textoStats = document.getElementById('textoToggleStats');
        var statsVisible = false;
        var chartsInicializados = false;

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

        function inicializarCharts() {
            if (chartsInicializados) return;
            chartsInicializados = true;

            crearGrafico(document.getElementById('chartBarras'), 'Sin atenciones de guardia para los filtros elegidos', {
                type: 'bar',
                data: {
                    labels: totalPorSemestre.map(function (d) { return d.semestre; }),
                    datasets: [{
                        label: 'Total',
                        backgroundColor: '#00b4a0',
                        data: totalPorSemestre.map(function (d) { return parseInt(d.total, 10); })
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            crearGrafico(document.getElementById('chartTorta'), 'Sin atenciones de guardia por semestre para los filtros elegidos', {
                type: 'doughnut',
                plugins: [centerTextPlugin],
                data: {
                    labels: totalPorSemestre.map(function (d) { return d.semestre; }),
                    datasets: [{
                        data: totalPorSemestre.map(function (d) { return parseInt(d.total, 10); }),
                        backgroundColor: ['#1a2b45', '#00b4a0', '#3498db', '#e67e22']
                    }]
                },
                options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom' } } }
            });
        }

        btnStats.addEventListener('click', function () {
            statsVisible = !statsVisible;
            if (statsVisible) {
                statsBody.classList.remove('is-hidden');
                statsPanel.classList.remove('is-collapsed');
                iconoStats.className = 'fas fa-eye-slash';
                textoStats.textContent = 'Ocultar estadísticas';
                inicializarCharts();
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