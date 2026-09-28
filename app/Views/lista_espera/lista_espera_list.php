<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> LISTA DE ESPERA QUIRÚRGICA <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
  .le-wrap * { box-sizing: border-box; }

:root {
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
}

.le-wrap {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--gray-bg);
    padding: 20px;
    min-height: 100vh;
}

    .le-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .le-breadcrumb i { color: var(--teal); font-size: 13px; }
    .le-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .le-breadcrumb a:hover { color: var(--teal); }
    .le-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .le-kpi-cards { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .le-kpi-card {
        background: var(--white); border-radius: 12px; border: 0.5px solid var(--border);
        padding: 12px 14px 14px; min-width: 170px; text-align: center; flex: 1 1 170px;
    }
    .le-kpi-card-label {
        display: inline-block; font-size: 10px; font-weight: 700; letter-spacing: 0.4px;
        padding: 4px 12px; border-radius: 14px; margin-bottom: 8px;
        background: var(--teal); color: #fff; white-space: nowrap;
    }
    .le-kpi-card.is-navy .le-kpi-card-label { background: var(--navy); }
    .le-kpi-card-value {
        background: #ececec; border-radius: 8px; padding: 9px 0;
        font-size: 19px; font-weight: 700; color: var(--teal);
    }
    .le-kpi-card.is-navy .le-kpi-card-value { color: var(--navy); }

    .le-panel { background: var(--white); border-radius: 12px; border: 0.5px solid var(--border); overflow: hidden; margin-bottom: 16px; }
    .le-panel-header {
        padding: 14px 20px; background: var(--navy);
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .le-panel-title { font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 10px; }
    .le-panel-title i { color: var(--teal); font-size: 17px; }
    .le-panel-body { padding: 18px 20px; }

    .le-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600; padding: 4px 11px; border-radius: 10px;
    }
    .le-tag.blue { background: rgba(52,152,219,0.13); color: #2980b9; }

    .le-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s; white-space: nowrap;
    }
    .le-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .le-btn.teal  { background: var(--teal); color: #fff; }
    .le-btn.navy  { background: var(--navy); color: #fff; }
    .le-btn.green { background: #27ae60;     color: #fff; }
    .le-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .le-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
    .le-btn.sm { padding: 5px 11px; font-size: 12px; }

    .le-toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }

    .le-filtro-bar {
        background: #f4f6f9; border: 0.5px solid var(--border); border-radius: 10px;
        padding: 14px 16px; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 16px;
    }
    .le-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .le-filtro-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); }
    .le-filtro-select {
        border: 1px solid var(--border); border-radius: 7px; padding: 7px 12px;
        font-size: 13px; color: var(--text-main); background: var(--white);
        outline: none; transition: border-color 0.15s; height: 36px;
    }
    .le-filtro-select:focus { border-color: var(--teal); }

    .le-table-wrap { overflow-x: auto; border-radius: 8px; border: 0.5px solid var(--border); }
    .le-table { width: 100%; border-collapse: collapse; font-size: 12.5px; color: var(--text-main); }
    .le-table thead tr { background: var(--navy); }
    .le-table thead th {
        padding: 10px 12px; font-size: 9px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.8px; color: rgba(255,255,255,0.7); white-space: nowrap; border: none;
    }
    .le-table tbody tr { border-bottom: 1px solid #eef0f3; transition: background 0.12s; }
    .le-table tbody tr:hover { background: #f5faff; }
    .le-table tbody td { padding: 9px 12px; vertical-align: middle; border: none; white-space: nowrap; }
    .le-table tbody td a.le-icon-link { color: var(--text-muted); text-decoration: none; }
    .le-table tbody td a.le-icon-link:hover { color: var(--teal); }
    .le-esp { max-width: 260px; white-space: normal; color: var(--text-muted); font-size: 11.5px; }

    .le-pager { margin-top: 14px; }
    .le-pager .pagination { justify-content: flex-end; }

    @media (max-width: 768px) {
        .le-wrap { padding: 12px; }
        .le-panel-header { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="le-wrap">

    <!-- BREADCRUMB -->
    <div class="le-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('base_views')); ?>">Inicio</a> ›
        Hospitalario ›
        <strong>Lista de Espera Quirúrgica — Listado</strong>
    </div>

    <!-- KPI CARDS -->
    <div class="le-kpi-cards">
        <div class="le-kpi-card">
            <span class="le-kpi-card-label">PACIENTES EN ESPERA</span>
            <div class="le-kpi-card-value"><?= number_format($totalPacientes, 0, ',', '.') ?></div>
        </div>
        <div class="le-kpi-card">
            <span class="le-kpi-card-label">COMP. ALTA</span>
            <div class="le-kpi-card-value"><?= number_format($totalAlta, 0, ',', '.') ?></div>
        </div>
        <div class="le-kpi-card">
            <span class="le-kpi-card-label">COMP. MEDIANA</span>
            <div class="le-kpi-card-value"><?= number_format($totalMediana, 0, ',', '.') ?></div>
        </div>
        <div class="le-kpi-card is-navy">
            <span class="le-kpi-card-label">COMP. BAJA</span>
            <div class="le-kpi-card-value"><?= number_format($totalBaja, 0, ',', '.') ?></div>
        </div>
    </div>

    <!-- PANEL PRINCIPAL -->
    <div class="le-panel">
        <div class="le-panel-header">
            <span class="le-panel-title">
                <i class="fas fa-procedures"></i> LISTA DE ESPERA QUIRÚRGICA
            </span>
        </div>

        <div class="le-panel-body">

            <!-- TOOLBAR -->
           <div class="le-toolbar">
    <a href="<?= base_url(route_to('base_views')); ?>" class="le-btn ghost">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    <a href="<?= base_url(route_to('lista_espera_create')); ?>" class="le-btn teal">
        <i class="fas fa-plus"></i> Nuevo Registro
    </a>
    <button type="button" class="le-btn navy" onclick="abrirModalEspecialidad()">
        <i class="fas fa-stethoscope"></i> Nueva Especialidad
    </button>
    <a href="<?= base_url(route_to('lista_espera_export')); ?>?<?= http_build_query([
        'ejercicio'  => $filtro_ejercicio,
        'efector_id' => $filtro_efector,
    ]) ?>" class="le-btn green">
        <i class="fas fa-file-excel"></i> Descargar Excel
    </a>
</div>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('lista_espera_list')); ?>" id="searchForm">
                <div class="le-filtro-bar">

                    <div class="le-filtro-group">
                        <span class="le-filtro-label">Ejercicio</span>
                        <select name="ejercicio" class="le-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($ejercicios as $e): ?>
                                <option value="<?= $e ?>" <?= $filtro_ejercicio == $e ? 'selected' : '' ?>><?= $e ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="le-filtro-group">
                        <span class="le-filtro-label">Efector</span>
                        <select name="efector_id" class="le-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($efectores as $e): ?>
                                <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="le-filtro-group">
                        <span class="le-filtro-label">&nbsp;</span>
                        <button type="submit" class="le-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>

                </div>
            </form>

            <!-- TABLA PRINCIPAL -->
            <div class="le-table-wrap">
                <table class="le-table">
                    <thead>
                        <tr>
                            <th>Ejercicio</th>
                            <th>Efector</th>
                            <th>Especialidad</th>
                            <th>Cant. Pacientes</th>
                            <th>Comp. Alta</th>
                            <th>Comp. Mediana</th>
                            <th>Comp. Baja</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $r): ?>
                        <tr>
                            <td><?= esc($r->ejercicio) ?></td>
                            <td><?= esc($r->efector_nombre) ?></td>
                            <td class="le-esp"><?= esc($r->especialidad_nombre ?? '—') ?></td>
                            <td><strong><?= number_format($r->cantidad_pacientes ?? 0, 0, ',', '.') ?></strong></td>
                            <td><?= number_format($r->comp_quirurgica_alta ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->comp_quirurgica_mediana ?? 0, 0, ',', '.') ?></td>
                            <td><?= number_format($r->comp_quirurgica_baja ?? 0, 0, ',', '.') ?></td>
                            <td>
                                <a class="le-icon-link" href="<?= base_url(route_to('lista_espera_show', $r->lista_espera_id)) ?>" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                            <td>
                                <a class="le-icon-link" href="javascript:void(0)"
                                   onclick="eliminarRegistro('<?= $r->lista_espera_id ?>', '<?= esc($r->efector_nombre, 'js') ?>')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div class="le-pager">
                <?= $pager->links() ?>
            </div>

        </div>
    </div>

</div>

<script>
    function eliminarRegistro(id, efector) {
        if (!confirm('¿Eliminar el registro de lista de espera de "' + efector + '"?')) return;

        fetch('<?= base_url(route_to('lista_espera_destroy')) ?>?id=' + encodeURIComponent(id), {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                '<?= esc(config('Config\Security')->headerName ?? 'X-CSRF-TOKEN', 'js') ?>': '<?= csrf_hash() ?>'
            }
        }).then(() => window.location.reload());
    }
</script>
<style>
    .le-modal-overlay {
        display: none; position: fixed; inset: 0; background: rgba(17,30,48,0.55);
        z-index: 999; align-items: center; justify-content: center;
    }
    .le-modal-overlay.activo { display: flex; }
    .le-modal-box {
        background: #fff; border-radius: 10px; width: 100%; max-width: 380px;
        padding: 20px 22px; box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    }
    .le-modal-title {
        font-size: 15px; font-weight: 700; color: #1a2b45;
        margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
    }
    .le-modal-title i { color: #00b4a0; }
    .le-modal-box label {
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        color: #5a6a7e; display: block; margin-bottom: 6px;
    }
    .le-modal-box input {
        width: 100%; border: 1px solid #d0d5de; border-radius: 7px;
        padding: 9px 12px; font-size: 13px; outline: none; margin-bottom: 16px;
    }
    .le-modal-box input:focus { border-color: #00b4a0; }
    .le-modal-acciones { display: flex; justify-content: flex-end; gap: 8px; }
</style>

<div class="le-modal-overlay" id="modalEspecialidad">
    <div class="le-modal-box">
        <div class="le-modal-title">
            <i class="fas fa-stethoscope"></i> Nueva Especialidad
        </div>
        <form action="<?= base_url(route_to('especialidad_store')) ?>" method="POST">
            <?= csrf_field() ?>
            <label>Nombre *</label>
            <input type="text" name="nombre" placeholder="Ej: CIRUGIA GENERAL" required autofocus>
            <div class="le-modal-acciones">
                <button type="button" class="le-btn ghost" onclick="cerrarModalEspecialidad()">Cancelar</button>
                <button type="submit" class="le-btn teal">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalEspecialidad() {
        document.getElementById('modalEspecialidad').classList.add('activo');
    }
    function cerrarModalEspecialidad() {
        document.getElementById('modalEspecialidad').classList.remove('activo');
    }
    // Cerrar al clickear afuera del box
    document.getElementById('modalEspecialidad').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalEspecialidad();
    });
</script>
<?= $this->endSection() ?>