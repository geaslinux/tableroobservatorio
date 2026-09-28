<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> PRODUCCIÓN QUIRÓFANO HOSPITALARIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .gq-wrap * { box-sizing: border-box; }

    .gq-wrap {
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

    .gq-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .gq-breadcrumb i { color: var(--teal); font-size: 13px; }
    .gq-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .gq-breadcrumb a:hover { color: var(--teal); }
    .gq-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .gq-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .gq-kpi-card {
        background: var(--white); border-radius: 12px; border: 0.5px solid var(--border);
        padding: 12px 14px 14px; min-width: 170px; text-align: center; flex: 1 1 170px;
    }
    .gq-kpi-card-label {
        display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
        padding: 4px 12px; border-radius: 14px; margin-bottom: 8px;
        background: var(--teal); color: #fff; white-space: nowrap;
    }
    .gq-kpi-card.is-navy .gq-kpi-card-label { background: var(--navy); }
    .gq-kpi-card-value {
        background: #ececec; border-radius: 8px; padding: 9px 0;
        font-size: 19px; font-weight: 700; color: var(--teal);
    }
    .gq-kpi-card.is-navy .gq-kpi-card-value { color: var(--navy); }

    .gq-panel { background: var(--white); border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
    .gq-panel-header {
        padding: 14px 20px; background: var(--navy);
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .gq-panel-title { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
    .gq-panel-title i { color: #fff; font-size: 17px; }
    .gq-panel-body { padding: 18px 20px; }

    .gq-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px;
    }
    .gq-tag.blue { background: rgba(52,152,219,0.13); color: #2980b9; }

    .gq-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s; white-space: nowrap;
    }
    .gq-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .gq-btn.teal  { background: var(--teal); color: #fff; }
    .gq-btn.navy  { background: var(--navy); color: #fff; }
    .gq-btn.green { background: #27ae60;     color: #fff; }
    .gq-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .gq-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
    .gq-btn.sm { padding: 5px 11px; font-size: 12px; }

    .gq-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }

    .gq-filtro-bar {
        background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px;
        padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px;
    }
    .gq-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .gq-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
    .gq-filtro-select {
        border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px;
        font-size: 13px; color: var(--text-main); background: var(--white);
        outline: none; transition: border-color 0.15s; height: 36px;
    }
    .gq-filtro-select:focus { border-color: var(--teal); }

    .gq-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .gq-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
    .gq-table thead tr { background: var(--navy); }
    .gq-table thead th {
        padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; border: none;
    }
    .gq-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .gq-table tbody tr:hover { background: #f5faff; }
    .gq-table tbody td { padding: 9px 12px; vertical-align: middle; border: none; white-space: nowrap; }
    .gq-table tbody td a.gq-icon-link { color: var(--text-muted); text-decoration: none; }
    .gq-table tbody td a.gq-icon-link:hover { color: var(--teal); }
    .gq-obs { max-width: 260px; white-space: normal; color: var(--text-muted); font-size: 11.5px; }

    .gq-pager { margin-top: 14px; }
    .gq-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .gq-wrap { padding: 12px; }
        .gq-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="gq-wrap">

    <!-- BREADCRUMB -->
    <div class="gq-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Hospitalario ›
        <strong>Producción Quirófano — Listado</strong>
    </div>

    <!-- KPI CARDS -->
    <div class="gq-kpi-cards">
        <div class="gq-kpi-card">
            <span class="gq-kpi-card-label">CIRUGÍAS URGENCIA</span>
            <div class="gq-kpi-card-value"><?= number_format($totalUrgencia, 0, ',', '.') ?></div>
        </div>
        <div class="gq-kpi-card">
            <span class="gq-kpi-card-label">CIRUGÍAS PROGRAMADAS</span>
            <div class="gq-kpi-card-value"><?= number_format($totalProgramadas, 0, ',', '.') ?></div>
        </div>
        <div class="gq-kpi-card is-navy">
            <span class="gq-kpi-card-label">TOTAL GENERAL</span>
            <div class="gq-kpi-card-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="gq-panel">
        <div class="gq-panel-header">
            <span class="gq-panel-title">
                <i class="fas fa-procedures"></i> PRODUCCIÓN QUIRÓFANO HOSPITALARIO
            </span>
        </div>

        <div class="gq-panel-body">

            <!-- TOOLBAR -->
            <div class="gq-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="gq-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('quirofano_create')); ?>" class="gq-btn teal">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </a>
                <a href="<?= base_url(route_to('quirofano_export')); ?>?<?= http_build_query([
                    'ejercicio'  => $filtro_ejercicio,
                    'efector_id' => $filtro_efector,
                ]) ?>" class="gq-btn green">
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            </div>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('quirofano_list')); ?>" id="searchForm">
                <div class="gq-filtro-bar">

                    <div class="gq-filtro-group">
                        <span class="gq-filtro-label">Ejercicio</span>
                        <select name="ejercicio" class="gq-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($ejercicios as $e): ?>
                                <option value="<?= $e ?>" <?= $filtro_ejercicio == $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gq-filtro-group">
                        <span class="gq-filtro-label">Efector</span>
                        <select name="efector_id" class="gq-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="gq-filtro-group">
                        <span class="gq-filtro-label">&nbsp;</span>
                        <button type="submit" class="gq-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- TABLA PRINCIPAL -->
            <div class="gq-table-wrap">
                <table class="gq-table">
                    <thead>
                        <tr>
                            <th>Ejercicio</th>
                            <th>Efector</th>
                            <th>Quir. Disp.</th>
                            <th>Quir. Urg.</th>
                            <th>Quir. Prog.</th>
                            <th>Cir. Urgencia</th>
                            <th>Cir. Prog. Alta</th>
                            <th>Cir. Prog. Mediana</th>
                            <th>Cir. Prog. Baja</th>
                            <th>Cir. Prog. Desc.</th>
                            <th>Total Prog.</th>
                            <th>Sub Total</th>
                            <th>% Provincia</th>
                            <th>Observación</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr><td colspan="16" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin producción de quirófano para los filtros elegidos</td></tr>
                        <?php endif; ?>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->ejercicio) ?></td>
                            <td><?= esc($r->efector_nombre) ?></td>
                            <td><?= number_format($r->quirofanos_disponibles ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->quirofanos_urgencias ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->quirofanos_programadas ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cirugias_urgencia ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cirugias_prog_alta ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cirugias_prog_mediana ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cirugias_prog_baja ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cirugias_prog_desconocido ?? 0, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_cirugias_programadas ?? 0, 0, ',', '.') ?></strong></td>
                            <td><strong><?= number_format($r->sub_total ?? 0, 0, ',', '.') ?></strong></td>
                            <td><span class="gq-tag blue"><?= $r->porcentaje_provincia !== null ? number_format($r->porcentaje_provincia * 100, 2, ',', '.') . '%' : '—' ?></span></td>
                            <td class="gq-obs"><?= esc($r->observacion ?? '—') ?></td>
                            <td>
                                <a class="gq-icon-link" href="<?= base_url(route_to('quirofano_show', $r->produccion_id)) ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a class="gq-icon-link" href="javascript:void(0)"
                                   onclick="eliminarRegistro('<?= $r->produccion_id ?>', '<?= esc($r->efector_nombre, 'js') ?>')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="gq-pager">
                <?= $pager->links() ?>
            </div>

        </div>
    </div>

</div>

<script>
    function eliminarRegistro(id, hospital) {
        if (!confirm('¿Eliminar el registro de producción quirúrgica de "' + hospital + '"?')) return;

        fetch('<?= base_url(route_to('quirofano_destroy')) ?>?id=' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= esc(config('Config\Security')->headerName ?? 'X-CSRF-TOKEN', 'js') ?>': '<?= csrf_hash() ?>'
            }
        }).then(() => window.location.reload());
    }
</script>

<?= $this->endSection() ?>