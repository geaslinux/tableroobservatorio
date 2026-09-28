<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GUARDIAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    .gd-wrap * { box-sizing: border-box; }

    .gd-wrap {
        --navy:       #1a2b45;
        --navy-dark:  #111e30;
        --teal:       #00b4a0;
        --teal-light: #00d4bc;
        --teal-bg:    rgba(0,180,160,0.12);
        --gray-bg:    #e8eaed;
        --white:      #ffffff;
        --text-main:  #1a2b45;
        --text-muted: #5a6a7e;
        --border:     #d0d5de;
        --danger:     #e74c3c;

        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--gray-bg);
        padding: 20px;
        min-height: 100vh;
    }

    .gd-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .gd-breadcrumb i { color: var(--teal); font-size: 13px; }
    .gd-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .gd-breadcrumb a:hover { color: var(--teal); }
    .gd-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .gd-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .gd-kpi-card {
        background: var(--white); border-radius: 12px; border: 0.5px solid var(--border);
        padding: 12px 14px 14px; min-width: 150px; text-align: center; flex: 1 1 150px;
    }
    .gd-kpi-card-label {
        display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
        padding: 4px 12px; border-radius: 14px; margin-bottom: 8px;
        background: var(--teal); color: #fff; white-space: nowrap;
    }
    .gd-kpi-card.is-navy .gd-kpi-card-label { background: var(--navy); }
    .gd-kpi-card-value {
        background: #ececec; border-radius: 8px; padding: 9px 0;
        font-size: 19px; font-weight: 700; color: var(--teal);
    }
    .gd-kpi-card.is-navy .gd-kpi-card-value { color: var(--navy); }

    .gd-panel { background: var(--white); border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
    .gd-panel-header {
        padding: 14px 20px; background: var(--navy);
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .gd-panel-title { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
    .gd-panel-title i { color: var(--teal); font-size: 17px; }
    .gd-panel-body { padding: 18px 20px; }

    .gd-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px;
    }
    .gd-tag.blue { background: rgba(52,152,219,0.13); color: #2980b9; }

    .gd-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s; white-space: nowrap;
    }
    .gd-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .gd-btn.teal  { background: var(--teal); color: #fff; }
    .gd-btn.navy  { background: var(--navy); color: #fff; }
    .gd-btn.green { background: #27ae60;     color: #fff; }
    .gd-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .gd-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
    .gd-btn.sm { padding: 5px 11px; font-size: 12px; }

    .gd-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }

    .gd-filtro-bar {
        background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px;
        padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px;
    }
    .gd-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .gd-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
    .gd-filtro-select {
        border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px;
        font-size: 13px; color: var(--text-main); background: var(--white);
        outline: none; transition: border-color 0.15s; height: 36px;
    }
    .gd-filtro-select:focus { border-color: var(--teal); }

    .gd-stats-header {
        padding: 10px 16px; font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9; border-bottom: 0.5px solid var(--border); border-radius: 12px 12px 0 0;
    }
    .gd-stats-header i { color: var(--teal); }
    .gd-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .gd-stats-panel.is-collapsed { margin-bottom: 12px; }
    .gd-stats-panel.is-collapsed .gd-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    .gd-stats-body { padding: 18px; display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .gd-stats-body.is-hidden { display: none; }
    .gd-chart-box { position: relative; min-height: 220px; }
    .gd-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 992px) { .gd-stats-body { grid-template-columns: 1fr; } }

    .gd-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .gd-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
    .gd-table thead tr { background: var(--navy); }
    .gd-table thead th {
        padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; border: none;
    }
    .gd-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .gd-table tbody tr:hover { background: #f5faff; }
    .gd-table tbody td { padding: 9px 12px; vertical-align: middle; border: none; white-space: nowrap; }
    .gd-table tbody td a.gd-icon-link { color: var(--text-muted); text-decoration: none; }
    .gd-table tbody td a.gd-icon-link:hover { color: var(--teal); }

    .gd-pager { margin-top: 14px; }
    .gd-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .gd-wrap { padding: 12px; }
        .gd-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="gd-wrap">

    <!-- BREADCRUMB -->
    <div class="gd-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Prehospitalario ›
        <strong>Guardias — Listado</strong>
    </div>

    <!-- KPI CARDS -->
    <div class="gd-kpi-cards">
        <?php foreach ($totalPorSemestre as $s): ?>
        <div class="gd-kpi-card">
            <span class="gd-kpi-card-label"><?= esc($s['semestre']) ?> SEMESTRE</span>
            <div class="gd-kpi-card-value"><?= number_format($s['total'], 0, ',', '.') ?></div>
        </div>
        <?php endforeach; ?>
        <div class="gd-kpi-card is-navy">
            <span class="gd-kpi-card-label">asdasdasd</span>
            <div class="gd-kpi-card-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
        </div>
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
                <a href="<?= base_url(route_to('base_views')); ?>" class="gd-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('guardia_create')); ?>" class="gd-btn teal">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </a>
                <a href="<?= base_url(route_to('guardia_export')); ?>?<?= http_build_query([
                    'anio'        => $filtro_anio,
                    'semestre'    => $filtro_semestre,
                    'mes'         => $filtro_mes,
                    'efector_id'  => $filtro_efector,
                    'servicio_id' => $filtro_servicio,
                ]) ?>" class="gd-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('guardia_list')); ?>" id="searchForm">
                <div class="gd-filtro-bar">

                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">Año</span>
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

                    <div class="gd-filtro-group">
                        <span class="gd-filtro-label">&nbsp;</span>
                        <button type="submit" class="gd-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- PANEL ESTADÍSTICAS -->
            <div class="gd-panel gd-stats-panel is-collapsed" id="statsPanel">
                <div class="gd-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE GUARDIAS</span>
                    <button type="button" class="gd-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye" id="iconoToggleStats"></i> <span id="textoToggleStats">Mostrar estadísticas</span>
                    </button>
                </div>
                <div class="gd-stats-body is-hidden" id="statsBody">

                    <div class="gd-chart-box">
                        <div class="gd-chart-title">Total por año</div>
                        <canvas id="chartBarras"></canvas>
                    </div>

                    <div class="gd-chart-box">
                        <div class="gd-chart-title">Distribución por semestre</div>
                        <canvas id="chartTorta"></canvas>
                    </div>

                </div>
            </div>

            <!-- TABLA PRINCIPAL -->
            <div class="gd-table-wrap">
                <table class="gd-table">
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
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td>
                                <a class="gd-icon-link" href="<?= base_url(route_to('guardia_show', $g['clave'])) ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a class="gd-icon-link" href="javascript:void(0)"
                                   onclick="eliminarGrupo('<?= esc($g['clave'], 'js') ?>', '<?= esc($g['hospital'], 'js') ?>')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="gd-pager">
                <?= $pager->links() ?>
            </div>

        </div>
    </div>

</div>

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
                    labels: totalPorSemestre.map(function (d) { return d.semestre; }),
                    datasets: [{
                        label: 'Total',
                        backgroundColor: '#00b4a0',
                        data: totalPorSemestre.map(function (d) { return parseInt(d.total, 10); })
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });

            new Chart(document.getElementById('chartTorta'), {
                type: 'pie',
                data: {
                    labels: totalPorSemestre.map(function (d) { return d.semestre; }),
                    datasets: [{
                        data: totalPorSemestre.map(function (d) { return parseInt(d.total, 10); }),
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