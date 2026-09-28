<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> PRODUCCIÓN QUIRÓFANO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    .pq-wrap * { box-sizing: border-box; }

    .pq-wrap {
        --navy:       #1a2b45;
        --navy-dark:  #111e30;
        --teal:       #00b4a0;
        --teal-light: #00d4bc;
        --gray-bg:    #e8eaed;
        --white:      #ffffff;
        --text-main:  #1a2b45;
        --text-muted: #5a6a7e;
        --border:     #d0d5de;

        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--gray-bg);
        padding: 20px;
        min-height: 100vh;
    }

    .pq-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .pq-breadcrumb i { color: var(--teal); font-size: 13px; }
    .pq-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .pq-breadcrumb a:hover { color: var(--teal); }
    .pq-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .pq-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .pq-kpi-card {
        background: var(--white); border-radius: 12px; border: 0.5px solid var(--border);
        padding: 12px 14px 14px; min-width: 150px; text-align: center; flex: 1 1 150px;
    }
    .pq-kpi-card-label {
        display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
        padding: 4px 12px; border-radius: 14px; margin-bottom: 8px;
        background: var(--teal); color: #fff; white-space: nowrap;
    }
    .pq-kpi-card.is-navy .pq-kpi-card-label { background: var(--navy); }
    .pq-kpi-card-value {
        background: #ececec; border-radius: 8px; padding: 9px 0;
        font-size: 19px; font-weight: 700; color: var(--teal);
    }
    .pq-kpi-card.is-navy .pq-kpi-card-value { color: var(--navy); }

    .pq-panel { background: var(--white); border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
    .pq-panel-header {
        padding: 14px 20px; background: var(--navy);
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .pq-panel-title { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
    .pq-panel-title i { color: var(--teal); font-size: 17px; }
    .pq-panel-body { padding: 18px 20px; }

    .pq-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px;
        background: rgba(52,152,219,0.13); color: #2980b9;
    }

    .pq-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s; white-space: nowrap;
    }
    .pq-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .pq-btn.teal  { background: var(--teal); color: #fff; }
    .pq-btn.green { background: #27ae60;     color: #fff; }
    .pq-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .pq-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }

    .pq-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }

    .pq-filtro-bar {
        background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px;
        padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px;
    }
    .pq-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .pq-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
    .pq-filtro-select {
        border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px;
        font-size: 13px; color: var(--text-main); background: var(--white);
        outline: none; transition: border-color 0.15s; height: 36px;
    }
    .pq-filtro-select:focus { border-color: var(--teal); }

    .pq-stats-header {
        padding: 10px 16px; font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9; border-bottom: 0.5px solid var(--border); border-radius: 12px 12px 0 0;
    }
    .pq-stats-header i { color: var(--teal); }
    .pq-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .pq-stats-panel.is-collapsed { margin-bottom: 12px; }
    .pq-stats-panel.is-collapsed .pq-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    .pq-stats-body { padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .pq-stats-body.is-hidden { display: none; }
    .pq-chart-box { position: relative; min-height: 220px; }
    .pq-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 992px) { .pq-stats-body { grid-template-columns: 1fr; } }

    .pq-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .pq-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
    .pq-table thead tr { background: var(--navy); }
    .pq-table thead th {
        padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; border: none;
    }
    .pq-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .pq-table tbody tr:hover { background: #f5faff; }
    .pq-table tbody td { padding: 9px 12px; vertical-align: middle; border: none; white-space: nowrap; }
    .pq-table tbody td a.pq-icon-link { color: var(--text-muted); text-decoration: none; }
    .pq-table tbody td a.pq-icon-link:hover { color: var(--teal); }

    .pq-pager { margin-top: 14px; }
    .pq-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .pq-wrap { padding: 12px; }
        .pq-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="pq-wrap">

    <!-- BREADCRUMB -->
    <div class="pq-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Prehospitalario ›
        <strong>Producción Quirúrgica — Listado</strong>
    </div>

    <!-- KPI CARDS -->
    <div class="pq-kpi-cards">
        <?php foreach ($totalPorEjercicio as $t): ?>
        <div class="pq-kpi-card">
            <span class="pq-kpi-card-label">EJERCICIO <?= esc($t['ejercicio']) ?></span>
            <div class="pq-kpi-card-value"><?= number_format($t['total'], 0, ',', '.') ?></div>
        </div>
        <?php endforeach; ?>
        <div class="pq-kpi-card is-navy">
            <span class="pq-kpi-card-label">TOTAL GENERAL</span>
            <div class="pq-kpi-card-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="pq-panel">
        <div class="pq-panel-header">
            <span class="pq-panel-title">
                <i class="fas fa-procedures"></i> PRODUCCIÓN QUIRÚRGICA POR EFECTOR
            </span>
        </div>

        <div class="pq-panel-body">

            <!-- TOOLBAR -->
            <div class="pq-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="pq-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('produccion_quirofano_create')); ?>" class="pq-btn teal">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </a>
                <a href="<?= base_url(route_to('produccion_quirofano_export')); ?>?<?= http_build_query([
                    'ejercicio'  => $filtro_ejercicio,
                    'efector_id' => $filtro_efector,
                    'region'     => $filtro_region,
                ]) ?>" class="pq-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('produccion_quirofano_list')); ?>" id="searchForm">
                <div class="pq-filtro-bar">

                    <div class="pq-filtro-group">
                        <span class="pq-filtro-label">Ejercicio</span>
                        <select name="ejercicio" class="pq-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($ejercicios as $e): ?>
                                <option value="<?= $e ?>" <?= $filtro_ejercicio == $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pq-filtro-group">
                        <span class="pq-filtro-label">Efector</span>
                        <select name="efector_id" class="pq-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pq-filtro-group">
                        <span class="pq-filtro-label">Región</span>
                        <select name="region" class="pq-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pq-filtro-group">
                        <span class="pq-filtro-label">&nbsp;</span>
                        <button type="submit" class="pq-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- PANEL ESTADÍSTICAS -->
            <div class="pq-panel pq-stats-panel is-collapsed" id="statsPanel">
                <div class="pq-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE PRODUCCIÓN</span>
                    <button type="button" class="pq-btn ghost" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye" id="iconoToggleStats"></i> <span id="textoToggleStats">Mostrar estadísticas</span>
                    </button>
                </div>
                <div class="pq-stats-body is-hidden" id="statsBody">

                    <div class="pq-chart-box">
                        <div class="pq-chart-title">Total por ejercicio</div>
                        <canvas id="chartBarras"></canvas>
                    </div>

                    <div class="pq-chart-box">
                        <div class="pq-chart-title">Distribución por ejercicio</div>
                        <canvas id="chartTorta"></canvas>
                    </div>

                </div>
            </div>

            <!-- TABLA PRINCIPAL -->
            <div class="pq-table-wrap">
                <table class="pq-table">
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Nivel de Complejidad</th>
                            <th>Departamento</th>
                            <th>Ubicación</th>
                            <th>Región</th>
                            <th>Ejercicio</th>
                            <th>Producción</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->efector_nombre) ?></td>
                            <td><?= esc($r->nivel_complejidad) ?></td>
                            <td><?= esc($r->departamento) ?></td>
                            <td><?= esc($r->ubicacion) ?></td>
                            <td><span class="pq-tag"><?= esc($r->region) ?></span></td>
                            <td><?= esc($r->ejercicio) ?></td>
                            <td><strong><?= number_format($r->produccion, 0, ',', '.') ?></strong></td>
                            <td>
                                <a class="pq-icon-link" href="<?= base_url(route_to('produccion_quirofano_edit', $r->produccion_quirofano_id)) ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a class="pq-icon-link" href="javascript:void(0)"
                                   onclick="eliminarRegistro('<?= esc($r->produccion_quirofano_id, 'js') ?>', '<?= esc($r->efector_nombre, 'js') ?>')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="pq-pager">
                <?= $pager->links() ?>
            </div>

        </div>
    </div>

</div>

<script>
    function eliminarRegistro(id, hospital) {
        if (!confirm('¿Eliminar el registro de producción quirúrgica de "' + hospital + '"?')) return;

        fetch('<?= base_url(route_to('produccion_quirofano_destroy')) ?>?id=' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= esc(config('Config\Security')->headerName ?? 'X-CSRF-TOKEN', 'js') ?>': '<?= csrf_hash() ?>'
            }
        }).then(() => window.location.reload());
    }
</script>

<script>
    var totalPorEjercicio = <?= json_encode($totalPorEjercicio) ?>;

    (function () {
        var statsPanel = document.getElementById('statsPanel');
        var statsBody  = document.getElementById('statsBody');
        var btnStats   = document.getElementById('btnToggleStats');
        var iconoStats = document.getElementById('iconoToggleStats');
        var textoStats = document.getElementById('textoToggleStats');
        var statsVisible = false;
        var chartsInicializados = false;

        function inicializarCharts() {
            if (chartsInicializados) return;
            chartsInicializados = true;

            new Chart(document.getElementById('chartBarras'), {
                type: 'bar',
                data: {
                    labels: totalPorEjercicio.map(function (d) { return d.ejercicio; }),
                    datasets: [{
                        label: 'Total',
                        backgroundColor: '#00b4a0',
                        data: totalPorEjercicio.map(function (d) { return parseInt(d.total, 10); })
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            new Chart(document.getElementById('chartTorta'), {
                type: 'pie',
                data: {
                    labels: totalPorEjercicio.map(function (d) { return d.ejercicio; }),
                    datasets: [{
                        data: totalPorEjercicio.map(function (d) { return parseInt(d.total, 10); }),
                        backgroundColor: ['#1a2b45', '#00b4a0', '#3498db', '#e67e22']
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
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