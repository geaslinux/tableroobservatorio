<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RRHH CARTA DE SERVICIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .rh-wrap * { box-sizing: border-box; }

    .rh-wrap {
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

    .rh-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .rh-breadcrumb i { color: var(--teal); font-size: 13px; }
    .rh-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .rh-breadcrumb a:hover { color: var(--teal); }
    .rh-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .rh-kpi-cards {
        display: flex; gap: 14px; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .rh-kpi-card {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        padding: 14px 18px 16px;
        min-width: 150px;
        text-align: center;
        flex: 1 1 150px;
    }
    .rh-kpi-card-label {
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
    .rh-kpi-card.is-navy .rh-kpi-card-label { background: var(--navy); }
    .rh-kpi-card.is-danger .rh-kpi-card-label { background: var(--danger); }
    .rh-kpi-card-value {
        background: #ececec;
        border-radius: 8px;
        padding: 10px 0;
        font-size: 22px; font-weight: 700;
        color: var(--teal);
    }
    .rh-kpi-card.is-navy .rh-kpi-card-value { color: var(--navy); }
    .rh-kpi-card.is-danger .rh-kpi-card-value { color: var(--danger); }

    .rh-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .rh-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .rh-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .rh-panel-title i { color: #fff; font-size: 17px; }
    .rh-panel-body { padding: 18px 20px; }

    .rh-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s;
        white-space: nowrap;
    }
    .rh-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .rh-btn.teal    { background: var(--teal);    color: #fff; }
    .rh-btn.navy    { background: var(--navy);    color: #fff; }
    .rh-btn.green   { background: #27ae60;        color: #fff; }
    .rh-btn.ghost   { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .rh-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }

    .rh-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .rh-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .rh-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .rh-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .rh-filtro-select {
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
    .rh-filtro-select:focus { border-color: var(--teal); }

    .rh-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .rh-table {
        width: 100%; border-collapse: collapse;
        font-size: 13px; color: var(--text-main);
    }
    .rh-table thead tr { background: var(--navy); }
    .rh-table thead th {
        padding: 11px 14px;
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: rgba(255,255,255,0.7);
        white-space: nowrap; border: none;
    }
    .rh-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .rh-table tbody tr:hover { background: #f5faff; }
    .rh-table tbody td { padding: 10px 14px; vertical-align: middle; border: none; white-space: nowrap; }

    .rh-pager { margin-top: 14px; }
    .rh-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .rh-wrap { padding: 12px; }
        .rh-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="rh-wrap">

    <!-- BREADCRUMB -->
    <div class="rh-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        <strong>RRHH Carta de Servicio — Listado</strong>
    </div>

    <!-- ── TARJETAS KPI ── -->
    <div class="rh-kpi-cards">
        <div class="rh-kpi-card">
            <span class="rh-kpi-card-label">REGISTROS</span>
            <div class="rh-kpi-card-value"><?= number_format($totalRegistros, 0, ',', '.') ?></div>
        </div>
        <div class="rh-kpi-card is-navy">
            <span class="rh-kpi-card-label">HOSPITALES</span>
            <div class="rh-kpi-card-value"><?= number_format($totalHospitales, 0, ',', '.') ?></div>
        </div>
        <div class="rh-kpi-card is-danger">
            <span class="rh-kpi-card-label">PROFESIONALES</span>
            <div class="rh-kpi-card-value"><?= number_format($totalProfesionales, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="rh-panel">
        <div class="rh-panel-header">
            <span class="rh-panel-title">
                <i class="fas fa-user-md"></i> RRHH CARTA DE SERVICIO
            </span>
        </div>

        <div class="rh-panel-body">

            <!-- ── TOOLBAR ── -->
            <div class="rh-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="rh-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('rrhh_carta_servicio_import')); ?>" class="rh-btn navy">
                    <i class="fas fa-upload"></i> Importar Excel
                </a>
                <a href="<?= base_url(route_to('rrhh_carta_servicio_export')); ?>?<?= http_build_query([
                    'hospital'          => $filtro_hospital,
                    'region'            => $filtro_region,
                    'nivel_complejidad' => $filtro_nivel,
                    'especialidad'      => $filtro_especialidad,
                ]) ?>" class="rh-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- ── FILTROS ── -->
            <form method="GET" action="<?= base_url(route_to('rrhh_carta_servicio_list')); ?>" id="searchForm">
                <div class="rh-filtro-bar">

                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Hospital</span>
                        <select name="hospital" class="rh-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($hospitales as $h): ?>
                                <option value="<?= esc($h) ?>" <?= $filtro_hospital == $h ? 'selected' : '' ?>><?= esc($h) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Región</span>
                        <select name="region" class="rh-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Nivel Complejidad</span>
                        <select name="nivel_complejidad" class="rh-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($niveles as $n): ?>
                                <option value="<?= esc($n) ?>" <?= $filtro_nivel == $n ? 'selected' : '' ?>><?= esc($n) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Especialidad</span>
                        <select name="especialidad" class="rh-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($especialidades as $e): ?>
                                <option value="<?= esc($e) ?>" <?= $filtro_especialidad == $e ? 'selected' : '' ?>><?= esc($e) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">&nbsp;</span>
                        <button type="submit" class="rh-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- ── TABLA PRINCIPAL ── -->
            <div class="rh-table-wrap">
                <table class="rh-table">
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Nivel</th>
                            <th>Región</th>
                            <th>DNI</th>
                            <th>Nombre y Apellido</th>
                            <th>Profesión</th>
                            <th>Especialidad</th>
                            <th>Revista</th>
                            <th>Consultorio</th>
                            <th>Guardia a Cargo</th>
                            <th>Telemedicina</th>
                            <th>Prosane</th>
                            <th>Carnet Sanitario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="13" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin profesionales de RRHH para los filtros elegidos</td></tr>
                        <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->hospital) ?></td>
                            <td><?= esc($r->nivel_complejidad) ?></td>
                            <td><?= esc($r->region) ?></td>
                            <td><?= esc($r->dni) ?></td>
                            <td><?= esc($r->nombre_apellido) ?></td>
                            <td><?= esc($r->profesion) ?></td>
                            <td><?= esc($r->especialidad) ?></td>
                            <td><?= esc($r->revista) ?></td>
                            <td><?= esc($r->consultorio) ?></td>
                            <td><?= esc($r->guardia_cargo) ?></td>
                            <td><?= esc($r->telemedicina) ?></td>
                            <td><?= esc($r->prosane) ?></td>
                            <td><?= esc($r->carnet_sanitario) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="rh-pager">
                <?= $pager->links() ?>
            </div>

        </div><!-- /panel-body -->
    </div><!-- /panel -->

</div><!-- /rh-wrap -->

<?= $this->endSection() ?>