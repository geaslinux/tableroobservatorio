<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> CARTA DE SERVICIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .cs-wrap * { box-sizing: border-box; }

    .cs-wrap {
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

    .cs-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cs-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cs-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cs-breadcrumb a:hover { color: var(--teal); }
    .cs-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .cs-kpi-cards {
        display: flex; gap: 14px; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cs-kpi-card {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        padding: 14px 18px 16px;
        min-width: 150px;
        text-align: center;
        flex: 1 1 150px;
    }
    .cs-kpi-card-label {
        display: inline-block;
        font-size: 11px; font-weight: 700;
        letter-spacing: 0.5px;
        padding: 4px 16px;
        border-radius: 14px;
        margin-bottom: 10px;
        background: var(--teal);
        color: #fff;
        white-space: nowrap;
    }
    .cs-kpi-card.is-navy .cs-kpi-card-label { background: var(--navy); }
    .cs-kpi-card.is-danger .cs-kpi-card-label { background: var(--danger); }
    .cs-kpi-card-value {
        background: #ececec;
        border-radius: 8px;
        padding: 10px 0;
        font-size: 22px; font-weight: 700;
        color: var(--teal);
    }
    .cs-kpi-card.is-navy .cs-kpi-card-value { color: var(--navy); }
    .cs-kpi-card.is-danger .cs-kpi-card-value { color: var(--danger); }

    .cs-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cs-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cs-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cs-panel-title i { color: #fff; font-size: 17px; }
    .cs-panel-body { padding: 18px 20px; }

    .cs-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s;
        white-space: nowrap;
    }
    .cs-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .cs-btn.teal    { background: var(--teal);    color: #fff; }
    .cs-btn.navy    { background: var(--navy);    color: #fff; }
    .cs-btn.green   { background: #27ae60;        color: #fff; }
    .cs-btn.ghost   { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .cs-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }

    .cs-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .cs-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cs-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cs-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cs-filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: var(--white);
        outline: none;
        transition: border-color 0.15s;
        height: 36px;
        min-width: 160px;
    }
    .cs-filtro-select:focus { border-color: var(--teal); }

    .cs-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .cs-table {
        width: 100%; border-collapse: collapse;
        font-size: 13px; color: var(--text-main);
    }
    .cs-table thead tr { background: var(--navy); }
    .cs-table thead th {
        padding: 11px 14px;
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: rgba(255,255,255,0.7);
        white-space: nowrap; border: none;
    }
    .cs-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .cs-table tbody tr:hover { background: #f5faff; }
    .cs-table tbody td { padding: 10px 14px; vertical-align: middle; border: none; white-space: nowrap; }

    .cs-pager { margin-top: 14px; }
    .cs-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .cs-wrap { padding: 12px; }
        .cs-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="cs-wrap">

    <!-- BREADCRUMB -->
    <div class="cs-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        <strong>Carta de Servicio — Listado</strong>
    </div>

    <!-- ── TARJETAS KPI ── -->
    <div class="cs-kpi-cards">
        <div class="cs-kpi-card">
            <span class="cs-kpi-card-label">REGISTROS</span>
            <div class="cs-kpi-card-value"><?= number_format($totalRegistros, 0, ',', '.') ?></div>
        </div>
        <div class="cs-kpi-card is-navy">
            <span class="cs-kpi-card-label">HOSPITALES</span>
            <div class="cs-kpi-card-value"><?= number_format($totalHospitales, 0, ',', '.') ?></div>
        </div>
        <div class="cs-kpi-card is-danger">
            <span class="cs-kpi-card-label">TOTAL TURNOS</span>
            <div class="cs-kpi-card-value"><?= number_format($totalTurnos, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="cs-panel">
        <div class="cs-panel-header">
            <span class="cs-panel-title">
                <i class="fas fa-id-card"></i> CARTA DE SERVICIO
            </span>
        </div>

        <div class="cs-panel-body">

            <!-- ── TOOLBAR ── -->
            <div class="cs-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="cs-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('carta_servicio_import')); ?>" class="cs-btn navy">
                    <i class="fas fa-upload"></i> Importar Excel
                </a>
                <a href="<?= base_url(route_to('carta_servicio_export')); ?>?<?= http_build_query([
                    'hospital'          => $filtro_hospital,
                    'region'            => $filtro_region,
                    'nivel_complejidad' => $filtro_nivel,
                    'especialidad'      => $filtro_especialidad,
                ]) ?>" class="cs-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- ── FILTROS ── -->
            <form method="GET" action="<?= base_url(route_to('carta_servicio_list')); ?>" id="searchForm">
                <div class="cs-filtro-bar">

                    <div class="cs-filtro-group">
                        <span class="cs-filtro-label">Hospital</span>
                        <select name="hospital" class="cs-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($hospitales as $h): ?>
                                <option value="<?= esc($h) ?>" <?= $filtro_hospital == $h ? 'selected' : '' ?>><?= esc($h) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cs-filtro-group">
                        <span class="cs-filtro-label">Región</span>
                        <select name="region" class="cs-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cs-filtro-group">
                        <span class="cs-filtro-label">Nivel Complejidad</span>
                        <select name="nivel_complejidad" class="cs-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($niveles as $n): ?>
                                <option value="<?= esc($n) ?>" <?= $filtro_nivel == $n ? 'selected' : '' ?>><?= esc($n) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cs-filtro-group">
                        <span class="cs-filtro-label">Especialidad</span>
                        <select name="especialidad" class="cs-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($especialidades as $e): ?>
                                <option value="<?= esc($e) ?>" <?= $filtro_especialidad == $e ? 'selected' : '' ?>><?= esc($e) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="cs-filtro-group">
                        <span class="cs-filtro-label">&nbsp;</span>
                        <button type="submit" class="cs-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- ── TABLA PRINCIPAL ── -->
            <div class="cs-table-wrap">
                <table class="cs-table">
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Nivel</th>
                            <th>Región</th>
                            <th>Tipo</th>
                            <th>Profesión</th>
                            <th>Especialidad</th>
                            <th>Tipo Profesional</th>
                            <th>Profesional</th>
                            <th>Día</th>
                            <th>Horario</th>
                            <th>Turno</th>
                            <th>Cant. Turnos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="12" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin agendas de carta de servicio para los filtros elegidos</td></tr>
                        <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->hospital) ?></td>
                            <td><?= esc($r->nivel_complejidad) ?></td>
                            <td><?= esc($r->region) ?></td>
                            <td><?= esc($r->tipo) ?></td>
                            <td><?= esc($r->profesion) ?></td>
                            <td><?= esc($r->especialidad) ?></td>
                            <td><?= esc($r->tipo_profesional) ?></td>
                            <td><?= esc($r->profesional) ?></td>
                            <td><?= esc($r->dia_atencion) ?></td>
                            <td><?= esc($r->horario_atencion) ?></td>
                            <td><?= esc($r->turno) ?></td>
                            <td><strong><?= number_format($r->cant_turnos, 0, ',', '.') ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="cs-pager">
                <?= $pager->links() ?>
            </div>

        </div><!-- /panel-body -->
    </div><!-- /panel -->

</div><!-- /cs-wrap -->

<?= $this->endSection() ?>