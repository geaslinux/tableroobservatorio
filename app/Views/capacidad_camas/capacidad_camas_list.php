<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> CAPACIDAD DE CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .cc-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cc-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cc-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cc-breadcrumb a:hover { color: var(--teal); }
    .cc-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .cc-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .cc-kpi-card {
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
    .cc-kpi-content {
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

    .cc-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cc-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cc-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cc-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-navy .cc-kpi-icon { background: #eef3f8; color: var(--navy); }

    .cc-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .cc-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }
    .cc-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cc-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cc-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cc-panel-title i { color: #fff; font-size: 17px; }
    .cc-panel-body { padding: 18px 20px; }
    .cc-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .cc-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cc-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cc-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cc-filtro-select {
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
    .cc-filtro-select:focus { border-color: var(--teal); }

    .cc-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .cc-stats-table-wrap { overflow-x: auto; grid-area: table; }
    .cc-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .cc-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left;
    }
    .cc-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .cc-stats-table tbody tr:hover { background: #f8f9fb; }
    .cc-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .cc-chart-box canvas { height: 100% !important; width: 100% !important; }
    .cc-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .cc-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .cc-stats-header i { color: var(--teal); }
    .cc-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .cc-stats-panel.is-collapsed { margin-bottom: 12px; }
    .cc-stats-panel.is-collapsed .cc-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .cc-stats-body.is-hidden { display: none; }

    @media (min-width: 993px) {
        .cc-stats-body {
            grid-template-columns: 1fr 2fr;
            grid-template-areas:
                "table bar"
                "table pie";
        }
        .cc-stats-table-wrap { grid-area: table; }
        #containerBarras { grid-area: bar; }
        #containerTorta { grid-area: pie; }
    }

    @media (max-width: 992px) {
        .cc-stats-body { grid-template-columns: 1fr; }
    }
</style>

<div class="cc-wrap">

    <!-- BREADCRUMB -->
    <div class="cc-breadcrumb">
        <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
        <a href="<?= base_url(route_to('internacion_views')); ?>">Internación</a> ›
        <strong>Capacidad de Camas</strong>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="cc-panel">
        <div class="cc-panel-header">
            <span class="cc-panel-title"><i class="fas fa-bed"></i> CAPACIDAD DE CAMAS POR EFECTOR</span>
        </div>

        <div class="cc-panel-body">

            <!-- ── TOOLBAR ── -->
            <div class="cc-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="cc-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
                <a href="<?= base_url(route_to('capacidad_camas_create')); ?>" class="cc-btn teal"><i class="fas fa-plus"></i> Nuevo Registro</a>
                <a href="<?= base_url(route_to('capacidad_camas_export')); ?>?<?= http_build_query([
                    'efector_id' => $filtro_efector,
                    'tipo'       => $filtro_tipo,
                    'region'     => $filtro_region,
                ]) ?>" class="cc-btn green"><i class="fas fa-file-excel"></i> Descargar Excel</a>
            </div>

            <!-- ── FILTROS ── -->
            <form method="GET" action="<?= base_url(route_to('capacidad_camas_list')); ?>">
                <div class="cc-filtro-bar">
                    <!-- EJERCICIO -->
                    <div class="cc-filtro-group">
                        <span class="cc-filtro-label">Efector</span>
                        <select name="efector_id" class="cc-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cc-filtro-group">
                        <span class="cc-filtro-label">Tipo</span>
                        <select name="tipo" class="cc-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="cc-filtro-group">
                        <span class="cc-filtro-label">Región</span>
                        <select name="region" class="cc-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= $r ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ACCIONES FILTRO -->
                    <div class="cc-filtro-group">
                        <span class="cc-filtro-label">&nbsp;</span>
                        <button type="submit" class="cc-btn teal" style="height:36px;"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </form>

            <!-- ── TARJETAS KPI ── -->
            <div class="cc-kpi-cards">
                <div class="cc-kpi-card">
                    <span class="cc-kpi-card-label">TOTAL UTI</span>
                    <div class="cc-kpi-card-value"><?= number_format($totalUti, 0, ',', '.') ?></div>
                </div>
                <div class="cc-kpi-card">
                    <span class="cc-kpi-card-label">TOTAL UTIN</span>
                    <div class="cc-kpi-card-value"><?= number_format($totalUtin, 0, ',', '.') ?></div>
                </div>
                <div class="cc-kpi-card">
                    <span class="cc-kpi-card-label">TOTAL BÁSICAS</span>
                    <div class="cc-kpi-card-value"><?= number_format($totalBasicas, 0, ',', '.') ?></div>
                </div>
                <div class="cc-kpi-card is-navy">
                    <span class="cc-kpi-card-label">CAMAS DISPONIBLES</span>
                    <div class="cc-kpi-card-value"><?= number_format($totalCamasDisponibles, 0, ',', '.') ?></div>
                </div>
            </div>

        <!-- Tabla detalle -->
         <div class="cc-table-wrap">
                <table class="cc-table">
                    <thead>
                        <tr>
                            <th>Efector</th>
                            <th>Tipo</th>
                            <th>UTI Adulto</th>
                            <th>UTI Coronario</th>
                            <th>UTI Pediátrico</th>
                            <th>UTI Neonatal</th>
                            <th>Total UTI</th>
                            <th>Total UTI Públicos</th>
                            <th>UTIN Adulto</th>
                            <th>UTIN Pediátrico</th>
                            <th>UTIN Neonatal</th>
                            <th>Total UTIN</th>
                            <th>Total UTIN Públicos</th>
                            <th>CB Adultos</th>
                            <th>CB Pediátricos</th>
                            <th>CB Neonatales</th>
                            <th>Total Básicas</th>
                            <th>Total Básicas Públicos</th>
                            <th>Camas Disponibles</th>
                            <th>Camas Disp. Públicas</th>
                            <th>Camas Disp. Púb. Salud Mental</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="23" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin capacidad de camas para los filtros elegidos</td></tr>
                        <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->nombre) ?></td>
                            <td><span class="cc-tag"><?= esc($r->tipo) ?></span></td>
                            <td><?= number_format($r->uti_adulto, 0, ',', '.') ?></td>
                            <td><?= number_format($r->uti_coronario, 0, ',', '.') ?></td>
                            <td><?= number_format($r->uti_pediatrico, 0, ',', '.') ?></td>
                            <td><?= number_format($r->uti_neonatal, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_uti, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->total_uti_publicos, 0, ',', '.') ?></td>
                            <td><?= number_format($r->utin_adulto, 0, ',', '.') ?></td>
                            <td><?= number_format($r->utin_pediatrico, 0, ',', '.') ?></td>
                            <td><?= number_format($r->utin_neonatal, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_utin, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->total_utin_publicos, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cb_adultos, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cb_pediatricos, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cb_neonatales, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_basicas, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->total_basicas_publicos, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->camas_disponibles, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->camas_disponibles_publicas, 0, ',', '.') ?></td>
                            <td><?= number_format($r->camas_disponibles_publicas_salud_mental, 0, ',', '.') ?></td>
                            <td><?= $r->getEditLink() ?></td>
                            <td><?= $r->getDeleteLink() ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cc-pager"><?= $pager->links() ?></div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>