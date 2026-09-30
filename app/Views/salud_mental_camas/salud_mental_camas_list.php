<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> SALUD MENTAL - CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.smc-wrap * { box-sizing: border-box; }
.smc-wrap {
    --navy: #1a2b45; --teal: #00b4a0; --gray-bg: #e8eaed;
    --border: #d0d5de; --text-main: #1a2b45; --text-muted: #5a6a7e;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gray-bg); padding: 20px; min-height: 100vh;
}
.smc-breadcrumb { font-size: 12px; color: var(--text-muted); margin-bottom: 16px; }
.smc-breadcrumb a { color: var(--text-muted); text-decoration: none; }
.smc-breadcrumb a:hover { color: var(--teal); }
.smc-breadcrumb strong { color: var(--text-main); font-weight: 600; }

.smc-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.smc-kpi-card {
    background: #fff; border-radius: 12px; border: 0.5px solid var(--border);
    padding: 12px 14px 14px; min-width: 150px; text-align: center; flex: 1 1 150px;
}
.smc-kpi-card-label {
    display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
    padding: 4px 12px; border-radius: 14px; margin-bottom: 8px; background: var(--teal); color: #fff;
}
.smc-kpi-card.is-navy .smc-kpi-card-label { background: var(--navy); }
.smc-kpi-card-value { background: #ececec; border-radius: 8px; padding: 9px 0; font-size: 19px; font-weight: 700; color: var(--teal); }
.smc-kpi-card.is-navy .smc-kpi-card-value { color: var(--navy); }

.smc-panel { background: #fff; border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
.smc-panel-header { padding: 14px 20px; background: var(--navy); }
.smc-panel-title { font-size: 15px; font-weight: 700; color: #fff; }
.smc-panel-title i { color: #fff; margin-right: 8px; }
.smc-panel-body { padding: 18px 20px; }

.smc-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.smc-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px;
    border: none; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
}
.smc-btn.teal  { background: var(--teal);  color: #fff; }
.smc-btn.green { background: #27ae60; color: #fff; }
.smc-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
.smc-btn:hover { filter: brightness(1.1); text-decoration: none; }

.smc-filtro-bar { background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px; }
.smc-filtro-group { display: flex; flex-direction: column; gap: 5px; }
.smc-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
.smc-filtro-select { border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px; font-size: 13px; height: 36px; background: #fff; }

.smc-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
.smc-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
.smc-table thead tr { background: var(--navy); }
.smc-table thead th { padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; }
.smc-table tbody tr { border-bottom: 1px solid #eef0f3; }
.smc-table tbody tr:hover { background: #f5faff; }
.smc-table tbody td { padding: 9px 12px; text-align: center; white-space: nowrap; }
.smc-tag { display: inline-flex; font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px; background: rgba(52,152,219,0.13); color: #2980b9; }
.smc-tag.purple { background: rgba(155,89,182,0.13); color: #8e44ad; }
.smc-pager { margin-top: 14px; }
.smc-pager .pagination { justify-content: flex-end; }
</style>

<div class="smc-wrap">

    <div class="smc-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Salud Mental ›
        <strong>Camas</strong>
    </div>

    <div class="smc-kpi-cards">
        <div class="smc-kpi-card">
            <span class="smc-kpi-card-label">CB ADULTOS</span>
            <div class="smc-kpi-card-value"><?= number_format($totalCbAdultos, 0, ',', '.') ?></div>
        </div>
        <div class="smc-kpi-card">
            <span class="smc-kpi-card-label">CB PEDIÁTRICOS</span>
            <div class="smc-kpi-card-value"><?= number_format($totalCbPediatricos, 0, ',', '.') ?></div>
        </div>
        <div class="smc-kpi-card is-navy">
            <span class="smc-kpi-card-label">TOTAL BÁSICAS</span>
            <div class="smc-kpi-card-value"><?= number_format($totalBasicas, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="smc-panel">
        <div class="smc-panel-header">
            <span class="smc-panel-title"><i class="fas fa-brain"></i> CAMAS DE SALUD MENTAL POR EFECTOR</span>
        </div>

        <div class="smc-panel-body">

            <div class="smc-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="smc-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
                <a href="<?= base_url(route_to('salud_mental_camas_create')); ?>" class="smc-btn teal"><i class="fas fa-plus"></i> Nuevo Registro</a>
                <a href="<?= base_url(route_to('salud_mental_camas_export')); ?>?<?= http_build_query([
                    'efector_id' => $filtro_efector,
                    'tipo'       => $filtro_tipo,
                    'modalidad'  => $filtro_modalidad,
                    'region'     => $filtro_region,
                ]) ?>" class="smc-btn green"><i class="fas fa-file-excel"></i> Descargar Excel</a>
            </div>

            <form method="GET" action="<?= base_url(route_to('salud_mental_camas_list')); ?>">
                <div class="smc-filtro-bar">
                    <div class="smc-filtro-group">
                        <span class="smc-filtro-label">Efector</span>
                        <select name="efector_id" class="smc-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="smc-filtro-group">
                        <span class="smc-filtro-label">Modalidad</span>
                        <select name="modalidad" class="smc-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($modalidades as $m): ?>
                                <option value="<?= $m ?>" <?= $filtro_modalidad == $m ? 'selected' : '' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="smc-filtro-group">
                        <span class="smc-filtro-label">Tipo</span>
                        <select name="tipo" class="smc-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="smc-filtro-group">
                        <span class="smc-filtro-label">Región</span>
                        <select name="region" class="smc-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= $r ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="smc-filtro-group">
                        <span class="smc-filtro-label">&nbsp;</span>
                        <button type="submit" class="smc-btn teal" style="height:36px;"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </form>

            <div class="smc-table-wrap">
                <table class="smc-table">
                    <thead>
                        <tr>
                            <th>Efector</th>
                            <th>Modalidad</th>
                            <th>Tipo</th>
                            <th>CB Adultos</th>
                            <th>CB Pediátricos</th>
                            <th>Total Básicas</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="8" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin camas de salud mental para los filtros elegidos</td></tr>
                        <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->nombre) ?></td>
                            <td><span class="smc-tag purple"><?= esc($r->modalidad) ?></span></td>
                            <td><span class="smc-tag"><?= esc($r->tipo) ?></span></td>
                            <td><?= number_format($r->cb_adultos, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cb_pediatricos, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_basicas, 0, ',', '.') ?></strong></td>
                            <td><?= $r->getEditLink() ?></td>
                            <td><?= $r->getDeleteLink() ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="smc-pager"><?= $pager->links() ?></div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>