<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> CAPACIDAD DE CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.cc-wrap * { box-sizing: border-box; }
.cc-wrap {
    --navy: #1a2b45; --teal: #00b4a0; --gray-bg: #e8eaed;
    --border: #d0d5de; --text-main: #1a2b45; --text-muted: #5a6a7e;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gray-bg); padding: 20px; min-height: 100vh;
}
.cc-breadcrumb { font-size: 12px; color: var(--text-muted); margin-bottom: 16px; }
.cc-breadcrumb a { color: var(--text-muted); text-decoration: none; }
.cc-breadcrumb a:hover { color: var(--teal); }
.cc-breadcrumb strong { color: var(--text-main); font-weight: 600; }

.cc-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.cc-kpi-card {
    background: #fff; border-radius: 12px; border: 0.5px solid var(--border);
    padding: 12px 14px 14px; min-width: 150px; text-align: center; flex: 1 1 150px;
}
.cc-kpi-card-label {
    display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
    padding: 4px 12px; border-radius: 14px; margin-bottom: 8px; background: var(--teal); color: #fff;
}
.cc-kpi-card.is-navy .cc-kpi-card-label { background: var(--navy); }
.cc-kpi-card-value { background: #ececec; border-radius: 8px; padding: 9px 0; font-size: 19px; font-weight: 700; color: var(--teal); }
.cc-kpi-card.is-navy .cc-kpi-card-value { color: var(--navy); }

.cc-panel { background: #fff; border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
.cc-panel-header { padding: 14px 20px; background: var(--navy); }
.cc-panel-title { font-size: 15px; font-weight: 700; color: #fff; }
.cc-panel-title i { color: var(--teal); margin-right: 8px; }
.cc-panel-body { padding: 18px 20px; }

.cc-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.cc-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px;
    border: none; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
}
.cc-btn.teal  { background: var(--teal);  color: #fff; }
.cc-btn.green { background: #27ae60; color: #fff; }
.cc-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
.cc-btn:hover { filter: brightness(1.1); text-decoration: none; }

.cc-filtro-bar { background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px; }
.cc-filtro-group { display: flex; flex-direction: column; gap: 5px; }
.cc-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
.cc-filtro-select { border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px; font-size: 13px; height: 36px; background: #fff; }

.cc-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
.cc-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
.cc-table thead tr { background: var(--navy); }
.cc-table thead th { padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; }
.cc-table tbody tr { border-bottom: 1px solid #eef0f3; }
.cc-table tbody tr:hover { background: #f5faff; }
.cc-table tbody td { padding: 9px 12px; text-align: center; white-space: nowrap; }
.cc-tag { display: inline-flex; font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px; background: rgba(52,152,219,0.13); color: #2980b9; }
.cc-pager { margin-top: 14px; }
.cc-pager .pagination { justify-content: flex-end; }
</style>

<div class="cc-wrap">

    <div class="cc-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Hospitalario ›
        <strong>Capacidad de Camas</strong>
    </div>

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

    <div class="cc-panel">
        <div class="cc-panel-header">
            <span class="cc-panel-title"><i class="fas fa-bed"></i> CAPACIDAD DE CAMAS POR EFECTOR</span>
        </div>

        <div class="cc-panel-body">

            <div class="cc-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="cc-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
                <a href="<?= base_url(route_to('capacidad_camas_create')); ?>" class="cc-btn teal"><i class="fas fa-plus"></i> Nuevo Registro</a>
                <a href="<?= base_url(route_to('capacidad_camas_export')); ?>?<?= http_build_query([
                    'efector_id' => $filtro_efector,
                    'tipo'       => $filtro_tipo,
                    'region'     => $filtro_region,
                ]) ?>" class="cc-btn green"><i class="fas fa-file-excel"></i> Descargar Excel</a>
            </div>

            <form method="GET" action="<?= base_url(route_to('capacidad_camas_list')); ?>">
                <div class="cc-filtro-bar">
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
                    <div class="cc-filtro-group">
                        <span class="cc-filtro-label">&nbsp;</span>
                        <button type="submit" class="cc-btn teal" style="height:36px;"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </form>

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