<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> CANTIDAD OPERATIVOS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ── BREADCRUMB ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }
</style>

<div class="ml-breadcrumb">
    <i class="fas fa-home"></i>
    <a href="<?= base_url(route_to('home')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('servicio_views')); ?>">Servicios Transversales</a> ›
    <strong>Cantidad Operativos</strong>
</div>

<div class="notification" style="background-color: #13304d; padding-bottom: 0.75rem;">
    <h1 class="title" style="color:white; margin-bottom:0.5rem;">CANTIDAD DE OPERATIVOS</h1>
    <hr style="background-color:#2a5298; margin:0.5rem 0;"/>

    <?php if ($nuevas > 0 || $modificadas > 0): ?>
    <div class="notification" style="background-color:#fff3cd; border-left:5px solid #f0a500; color:#333; margin-bottom:1rem;">
        <div class="columns is-vcentered is-mobile">
            <div class="column">
                <strong>📋 Actividad desde tu última visita
                    (<?= $ultimaVista == '2000-01-01 00:00:00' ? 'primera vez' : date('d/m/Y H:i', strtotime($ultimaVista)) ?>)
                </strong>
                <br><br>
                <?php if ($nuevas > 0): ?>
                    <span class="tag is-success is-medium" style="margin-right:8px; font-size:0.95rem;">
                        🟢 <?= $nuevas ?> nuevo<?= $nuevas > 1 ? 's' : '' ?> cargado<?= $nuevas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
                <?php if ($modificadas > 0): ?>
                    <span class="tag is-warning is-medium" style="font-size:0.95rem;">
                        🟡 <?= $modificadas ?> modificado<?= $modificadas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="column is-narrow">
                <a href="<?= base_url(route_to('cantidad_operativo_visto')) ?>" class="button is-dark is-small">
                    <span class="icon"><i class="fas fa-check"></i></span>
                    <span>VISTO BUENO</span>
                </a>
            </div>
        </div>
        <div style="margin-top:0.5rem; font-size:0.82em; display:flex; gap:12px;">
            <span style="background:#d4edda; border-left:4px solid #28a745; padding:2px 10px; border-radius:3px;">
                🟢 Fila verde = nuevo cargado
            </span>
            <span style="background:#fff8cc; border-left:4px solid #f0c040; padding:2px 10px; border-radius:3px;">
                🟡 Fila amarilla = modificado
            </span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light">
            <?= $msg['body'] ?>
        </div>
    <?php endif; ?>

    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:0.75rem;">
        <a class="button is-info is-small" href="<?= base_url(route_to('servicio_views')) ?>">
            <span class="icon"><i class="fas fa-arrow-left"></i></span>
            <span>Volver</span>
        </a>
        <a class="button is-warning is-small" href="<?= base_url(route_to('cantidad_operativo_create')) ?>">
            <span class="icon"><i class="fas fa-plus"></i></span>
            <span>Nuevo Registro</span>
        </a>
        <a class="button is-light is-small" href="<?= base_url(route_to('operativo_list')) ?>">
            <span class="icon"><i class="fas fa-cogs"></i></span>
            <span>ABM Operativos</span>
        </a>
        <a class="button is-success is-small" href="<?= base_url(route_to('cantidad_operativo_export')) ?>?<?= http_build_query(['ejercicio' => $filtro_ejercicio, 'operativo' => $filtro_operativo, 'estado' => $filtro_estado]) ?>">
            <span class="icon"><i class="fas fa-file-excel"></i></span>
            <span>Descargar Excel</span>
        </a>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="<?= base_url(route_to('cantidad_operativo_list')) ?>" id="searchForm">
        <div class="columns is-multiline is-vcentered is-mobile" style="margin-bottom:0;">
            <div class="column is-6-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Ejercicio</label>
                    <div class="control">
                        <div class="select is-fullwidth is-small">
                            <select name="ejercicio">
                                <option value="">Todos</option>
                                <?php foreach ($ejercicios as $ej): ?>
                                    <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-6-mobile is-4-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Operativo</label>
                    <div class="control">
                        <div class="select is-fullwidth is-small">
                            <select name="operativo">
                                <option value="">Todos</option>
                                <?php foreach ($operativos as $op): ?>
                                    <option value="<?= $op->operativo_id ?>" <?= $filtro_operativo == $op->operativo_id ? 'selected' : '' ?>>
                                        <?= esc($op->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-6-mobile is-3-tablet" id="col-estado" style="display:none;">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth is-small">
                            <select name="estado">
                                <option value="">Todos</option>
                                <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">&nbsp;</label>
                    <div class="control" style="display:flex; gap:5px;">
                        <button class="button is-link is-small" type="submit">
                            <span class="icon"><i class="fas fa-filter"></i></span>
                        </button>
                        <button class="button is-light is-small" id="btnToggle" type="button" title="Mostrar/ocultar Estado">
                            <span class="icon"><i class="fas fa-sliders-h" id="iconoToggle"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- TABLA -->
    <div class="table-container">
    <table class="table is-fullwidth is-hoverable is-bordered">
        <thead>
            <tr style="background-color:#c8d8f0;">
                <th>Ejercicio</th>
                <th>Operativo</th>
                <th>Tipo</th>
                <th>Vía Pública</th>
                <th>Vía Hospitalaria</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Editar</th>
                <th>Eliminar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr><td colspan="9" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">Sin cantidades de operativos para los filtros elegidos</td></tr>
            <?php endif; ?>
        <?php foreach ($registros as $r):
            $esNuevo      = $r->created_at > $ultimaVista;
            $esModificado = ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
        ?>
            <tr <?php
                if ($r->estado == 'desactivado')  echo 'style="opacity:0.45; background:#ccc;"';
                elseif ($esNuevo)                 echo 'style="background-color:#d4edda; border-left:4px solid #28a745;"';
                elseif ($esModificado)            echo 'style="background-color:#fff8cc; border-left:4px solid #f0c040;"';
            ?>>
                <td><?= $r->ejercicio ?></td>
                <td><?= esc($r->operativo_nombre) ?></td>
                <td><?= esc($r->tipo_nombre ?? '—') ?></td>
                <td><?= number_format($r->via_publica,      0, ',', '.') ?></td>
                <td><?= number_format($r->via_hospitalaria, 0, ',', '.') ?></td>
                <td><strong><?= number_format($r->total, 0, ',', '.') ?></strong></td>
                <td>
                    <?php if ($r->estado == 'activo'): ?>
                        <span class="tag is-success is-small">Activo</span>
                    <?php else: ?>
                        <span class="tag is-danger is-small">Desactivado</span>
                    <?php endif; ?>
                </td>
                <td><?= $r->getEditLink() ?></td>
                <td><?= $r->getDeleteLink() ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?= $pager->links() ?>
    </div>
</div>

<script>
(function () {
    var colEstado = document.getElementById('col-estado');
    var btn       = document.getElementById('btnToggle');
    var icono     = document.getElementById('iconoToggle');

    var params  = new URLSearchParams(window.location.search);
    var visible = !!params.get('estado');

    function aplicar() {
        colEstado.style.display = visible ? '' : 'none';
        icono.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
        btn.title = visible ? 'Ocultar Estado' : 'Mostrar Estado';
    }

    aplicar();

    btn.addEventListener('click', function () {
        visible = !visible;
        aplicar();
    });
})();
</script>

<?= $this->endSection() ?>