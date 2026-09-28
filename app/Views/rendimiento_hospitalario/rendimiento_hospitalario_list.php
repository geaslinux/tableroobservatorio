<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RENDIMIENTO HOSPITALARIO <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
.rh-wrap * { box-sizing: border-box; }
.rh-wrap {
    --navy: #1a2b45; --teal: #00b4a0; --gray-bg: #e8eaed;
    --border: #d0d5de; --text-main: #1a2b45; --text-muted: #5a6a7e;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gray-bg); padding: 20px; min-height: 100vh;
}
.rh-breadcrumb { font-size: 12px; color: var(--text-muted); margin-bottom: 16px; }
.rh-breadcrumb a { color: var(--text-muted); text-decoration: none; }
.rh-breadcrumb a:hover { color: var(--teal); }
.rh-breadcrumb strong { color: var(--text-main); font-weight: 600; }

.rh-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.rh-kpi-card {
    background: #fff; border-radius: 12px; border: 0.5px solid var(--border);
    padding: 12px 14px 14px; min-width: 150px; text-align: center; flex: 1 1 150px;
}
.rh-kpi-card-label {
    display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
    padding: 4px 12px; border-radius: 14px; margin-bottom: 8px; background: var(--teal); color: #fff;
}
.rh-kpi-card.is-navy .rh-kpi-card-label { background: var(--navy); }
.rh-kpi-card-value { background: #ececec; border-radius: 8px; padding: 9px 0; font-size: 19px; font-weight: 700; color: var(--teal); }
.rh-kpi-card.is-navy .rh-kpi-card-value { color: var(--navy); }

.rh-panel { background: #fff; border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
.rh-panel-header { padding: 14px 20px; background: var(--navy); }
.rh-panel-title { font-size: 15px; font-weight: 700; color: #fff; }
.rh-panel-title i { color: var(--teal); margin-right: 8px; }
.rh-panel-body { padding: 18px 20px; }

.rh-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.rh-btn {
    display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px;
    border: none; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
}
.rh-btn.teal  { background: var(--teal);  color: #fff; }
.rh-btn.green { background: #27ae60; color: #fff; }
.rh-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
.rh-btn:hover { filter: brightness(1.1); text-decoration: none; }

.rh-filtro-bar { background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px; padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px; }
.rh-filtro-group { display: flex; flex-direction: column; gap: 5px; }
.rh-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
.rh-filtro-select { border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px; font-size: 13px; height: 36px; background: #fff; }

.rh-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
.rh-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
.rh-table thead tr { background: var(--navy); }
.rh-table thead th { padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; }
.rh-table tbody tr { border-bottom: 1px solid #eef0f3; }
.rh-table tbody tr:hover { background: #f5faff; }
.rh-table tbody td { padding: 9px 12px; text-align: center; white-space: nowrap; }
.rh-tag { display: inline-flex; font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px; background: rgba(52,152,219,0.13); color: #2980b9; }
.rh-pager { margin-top: 14px; }
.rh-pager .pagination { justify-content: flex-end; }
</style>

<div class="rh-wrap">

    <div class="rh-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Hospitalario ›
        <strong>Rendimiento Hospitalario</strong>
    </div>

    <div class="rh-kpi-cards">
        <div class="rh-kpi-card">
            <span class="rh-kpi-card-label">TOTAL ALTAS</span>
            <div class="rh-kpi-card-value"><?= number_format($totalAltas, 0, ',', '.') ?></div>
        </div>
        <div class="rh-kpi-card">
            <span class="rh-kpi-card-label">TOTAL EGRESOS</span>
            <div class="rh-kpi-card-value"><?= number_format($totalEgresos, 0, ',', '.') ?></div>
        </div>
        <div class="rh-kpi-card">
            <span class="rh-kpi-card-label">% OCUPACIONAL PROM.</span>
            <div class="rh-kpi-card-value"><?= number_format($promedioOcupacional, 2, ',', '.') ?>%</div>
        </div>
        <div class="rh-kpi-card is-navy">
            <span class="rh-kpi-card-label">GIRO CAMA PROM.</span>
            <div class="rh-kpi-card-value"><?= number_format($promedioGiroCama, 2, ',', '.') ?></div>
        </div>
    </div>

    <div class="rh-panel">
        <div class="rh-panel-header">
            <span class="rh-panel-title"><i class="fas fa-chart-line"></i> RENDIMIENTO HOSPITALARIO POR EFECTOR</span>
        </div>

        <div class="rh-panel-body">

            <div class="rh-toolbar">
                <a href="<?= base_url(route_to('base_views')); ?>" class="rh-btn ghost"><i class="fas fa-arrow-left"></i> Volver</a>
                <a href="<?= base_url(route_to('rendimiento_hospitalario_create')); ?>" class="rh-btn teal"><i class="fas fa-plus"></i> Nuevo Registro</a>
                <a href="<?= base_url(route_to('rendimiento_hospitalario_export')); ?>?<?= http_build_query([
                    'efector_id' => $filtro_efector,
                    'ejercicio'  => $filtro_ejercicio,
                    'semestre'   => $filtro_semestre,
                    'region'     => $filtro_region,
                ]) ?>" class="rh-btn green"><i class="fas fa-file-excel"></i> Descargar Excel</a>
            </div>

            <form method="GET" action="<?= base_url(route_to('rendimiento_hospitalario_list')); ?>">
                <div class="rh-filtro-bar">
                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Efector</span>
                        <select name="efector_id" class="rh-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Ejercicio</span>
                        <select name="ejercicio" class="rh-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($ejercicios as $anio): ?>
                                <option value="<?= $anio ?>" <?= $filtro_ejercicio == $anio ? 'selected' : '' ?>><?= $anio ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Semestre</span>
                        <select name="semestre" class="rh-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($semestres as $s): ?>
                                <option value="<?= $s ?>" <?= $filtro_semestre == $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">Región</span>
                        <select name="region" class="rh-filtro-select">
                            <option value="">Todas</option>
                            <?php foreach ($regiones as $r): ?>
                                <option value="<?= $r ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="rh-filtro-group">
                        <span class="rh-filtro-label">&nbsp;</span>
                        <button type="submit" class="rh-btn teal" style="height:36px;"><i class="fas fa-filter"></i></button>
                    </div>
                </div>
            </form>

         <div class="rh-table-wrap">
                <table class="rh-table">
                    <thead>
                        <tr>
                            <th>Efector</th>
                            <th>Ejercicio</th>
                            <th>Semestre</th>
                            <th>Días Func. Serv.</th>
                            <th>Altas</th>
                            <th>Defunción</th>
                            <th>Total Egresos</th>
                            <th>Pases a Sala</th>
                            <th>Días Estada</th>
                            <th>Paciente Día</th>
                            <th>Cama Disponible</th>
                            <th>Prom. Cama Disp.</th>
                            <th>Prom. Pcte. Día</th>
                            <th>Prom. Permanencia</th>
                            <th>% Ocupacional</th>
                            <th>Estándares</th>
                            <th>Prom. Días Estada</th>
                            <th>Tasa Mortalidad</th>
                            <th>Giro Cama</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->nombre) ?></td>
                            <td><?= esc($r->ejercicio) ?></td>
                            <td><span class="rh-tag"><?= esc($r->semestre) ?></span></td>
                            <td><?= number_format($r->dias_func_servicio, 0, ',', '.') ?></td>
                            <td><?= number_format($r->altas, 0, ',', '.') ?></td>
                            <td><?= number_format($r->defuncion, 0, ',', '.') ?></td>
                            <td><strong><?= number_format($r->total_egresos, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->pases_a_sala, 0, ',', '.') ?></td>
                            <td><?= number_format($r->dias_estada, 0, ',', '.') ?></td>
                            <td><?= number_format($r->paciente_dia, 0, ',', '.') ?></td>
                            <td><?= number_format($r->cama_disponible, 0, ',', '.') ?></td>
                            <td><?= number_format($r->promedio_cama_disp, 2, ',', '.') ?></td>
                            <td><?= number_format($r->promedio_pcte_dia, 2, ',', '.') ?></td>
                            <td><?= number_format($r->promedio_permanencia, 2, ',', '.') ?></td>
                            <td><strong><?= number_format($r->porcentaje_ocupacional, 2, ',', '.') ?>%</strong></td>
                            <td><?= esc($r->estandares) ?></td>
                            <td><?= number_format($r->promedio_dias_estada, 2, ',', '.') ?></td>
                            <td><?= number_format($r->tasa_mortalidad, 2, ',', '.') ?></td>
                            <td><strong><?= number_format($r->giro_cama, 2, ',', '.') ?></strong></td>
                            <td><?= $r->getEditLink() ?></td>
                            <td><?= $r->getDeleteLink() ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="rh-pager"><?= $pager->links() ?></div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>