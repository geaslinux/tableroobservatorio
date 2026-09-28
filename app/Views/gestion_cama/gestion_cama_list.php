<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GESTIÓN DE CAMAS <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-fluid">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">GESTIÓN DE CAMAS</h1>
    <hr/>

    <?php if ($nuevas > 0 || $modificadas > 0): ?>
    <div class="notification" style="background-color:#fff3cd; border-left:5px solid #f0a500; color:#333; margin-bottom:1rem;">
        <div class="columns is-vcentered">
            <div class="column">
                <strong>📋 Actividad desde tu última visita
                    (<?= $ultimaVista == '2000-01-01 00:00:00' ? 'primera vez' : date('d/m/Y H:i', strtotime($ultimaVista)) ?>)
                </strong>
                <br><br>
                <?php if ($nuevas > 0): ?>
                    <span class="tag is-success is-medium" style="margin-right:8px;">
                        🟢 <?= $nuevas ?> nuevo<?= $nuevas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
                <?php if ($modificadas > 0): ?>
                    <span class="tag is-warning is-medium">
                        🟡 <?= $modificadas ?> modificado<?= $modificadas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="column is-narrow">
                <a href="<?= base_url(route_to('gestion_cama_visto')) ?>" class="button is-dark">
                    <span class="icon"><i class="fas fa-check"></i></span>
                    <span>VISTO BUENO</span>
                </a>
            </div>
        </div>
        <div style="margin-top:0.5rem; font-size:0.82em; display:flex; gap:12px;">
            <span style="background:#d4edda; border-left:4px solid #28a745; padding:2px 10px; border-radius:3px;">🟢 verde = nuevo</span>
            <span style="background:#fff8cc; border-left:4px solid #f0c040; padding:2px 10px; border-radius:3px;">🟡 amarillo = modificado</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('paciente_views')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-warning" href="<?= base_url(route_to('gestion_cama_create')) ?>">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nuevo Registro</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-light" href="<?= base_url(route_to('efector_list')) ?>">
                <span class="icon"><i class="fas fa-hospital"></i></span>
                <span>ABM Efectores</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-success" href="<?= base_url(route_to('gestion_cama_export')) ?>?<?= http_build_query(['efector'=>$filtro_efector,'region'=>$filtro_region,'tipo_gestion'=>$filtro_tipo_gestion,'estado'=>$filtro_estado]) ?>">
                <span class="icon"><i class="fas fa-file-excel"></i></span>
                <span>Descargar Excel</span>
            </a>
        </div>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="<?= base_url(route_to('gestion_cama_list')) ?>">
        <div class="columns is-multiline is-vcentered">

            <div class="column is-3">
                <div class="field">
                    <label class="label" style="color:white;">Hospital</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="efector">
                                <option value="">Todos</option>
                                <?php foreach ($efectores as $ef): ?>
                                    <option value="<?= $ef->efector_id ?>" <?= $filtro_efector == $ef->efector_id ? 'selected':'' ?>>
                                        <?= esc($ef->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Región</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="region">
                                <option value="">Todas</option>
                                <?php foreach ($regiones as $reg): ?>
                                    <option value="<?= $reg ?>" <?= $filtro_region == $reg ? 'selected':'' ?>><?= $reg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Tipo Gestión</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tipo_gestion">
                                <option value="">Todos</option>
                                <?php foreach ($tiposGestion as $tg): ?>
                                    <option value="<?= $tg ?>" <?= $filtro_tipo_gestion == $tg ? 'selected':'' ?>><?= $tg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2" id="col-estado" style="display:none;">
                <div class="field">
                    <label class="label" style="color:white;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="estado">
                                <option value="">Todos</option>
                                <option value="activo"      <?= $filtro_estado=='activo'      ? 'selected':'' ?>>Activo</option>
                                <option value="desactivado" <?= $filtro_estado=='desactivado' ? 'selected':'' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white;">&nbsp;</label>
                    <div class="control" style="display:flex; gap:6px;">
                        <button class="button is-link" type="submit">
                            <span class="icon"><i class="fas fa-filter"></i></span>
                        </button>
                        <button class="button is-light" id="btnToggle" type="button">
                            <span class="icon"><i class="fas fa-sliders-h" id="iconoToggle"></i></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-container">
    <table class="table is-fullwidth is-hoverable" style="font-size:0.88rem;">
        <thead>
            <tr>
                <th style="color:black;">Hospital</th>
                <th style="color:black;">Nivel</th>
                <th style="color:black;">Tipo Establecimiento</th>
                <th style="color:black;">Tipo Gestión</th>
                <th style="color:black;">Región</th>
                <th style="color:black;">C. Básicos Adultos</th>
                <th style="color:black;">C. Básicos Pediátricos</th>
                <th style="color:black;">Total C. Básicos</th>
                <th style="color:black;">Observación</th>
                <th style="color:black;">Estado</th>
                <th style="color:black;">Editar</th>
                <th style="color:black;">Eliminar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registros as $r):
            $esNuevo      = $r->created_at > $ultimaVista;
            $esModificado = ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
        ?>
            <tr <?php
                if ($r->estado == 'desactivado')  echo 'style="opacity:0.45; background:#ccc;"';
                elseif ($esNuevo)                 echo 'style="background-color:#d4edda; border-left:4px solid #28a745;"';
                elseif ($esModificado)            echo 'style="background-color:#fff8cc; border-left:4px solid #f0c040;"';
            ?>>
                <td><?= esc($r->efector_nombre) ?></td>
                <td><?= esc($r->efector_nivel ?? '—') ?></td>
                <td><?= esc($r->tipo_establecimiento ?? '—') ?></td>
                <td>
                    <span class="tag <?= $r->tipo_gestion == 'PUBLICO' ? 'is-info' : 'is-warning' ?>">
                        <?= esc($r->tipo_gestion ?? '—') ?>
                    </span>
                </td>
                <td>
                    <span class="tag <?php
                        switch($r->region) {
                            case 'CENTRO':   echo 'is-info';    break;
                            case 'VALLE':    echo 'is-success'; break;
                            case 'RAMAL I':
                            case 'RAMAL II': echo 'is-warning'; break;
                            case 'QUEBRADA': echo 'is-danger';  break;
                            case 'PUNA':     echo 'is-dark';    break;
                            default:         echo 'is-light';
                        }
                    ?>">
                        <?= esc($r->region) ?>
                    </span>
                </td>
                <td><strong><?= number_format((int)$r->cuidados_basicos_adultos, 0, ',', '.') ?></strong></td>
                <td><strong><?= number_format((int)$r->cuidados_basicos_pediatricos, 0, ',', '.') ?></strong></td>
                <td>
                    <span class="tag is-primary is-medium">
                        <strong><?= number_format((int)$r->total_cuidados_basicos, 0, ',', '.') ?></strong>
                    </span>
                </td>
                <td><?= esc($r->observacion ?? '—') ?></td>
                <td>
                    <?php if ($r->estado == 'activo'): ?>
                        <span class="tag is-success">Activo</span>
                    <?php else: ?>
                        <span class="tag is-danger">Desactivado</span>
                    <?php endif; ?>
                </td>
                <td><?= $r->getEditLink() ?></td>
                <td><?= $r->getDeleteLink() ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($registros)): ?>
            <tr><td colspan="12" class="has-text-centered">Sin registros de gestión de camas para los filtros elegidos</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <?= $pager->links() ?>
    </div>

</div>
</div>

<script>
(function () {
    var colEstado = document.getElementById('col-estado');
    var btn       = document.getElementById('btnToggle');
    var icono     = document.getElementById('iconoToggle');
    var params    = new URLSearchParams(window.location.search);
    var visible   = !!params.get('estado');
    function aplicar() {
        colEstado.style.display = visible ? '' : 'none';
        icono.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
        btn.title = visible ? 'Ocultar Estado' : 'Mostrar Estado';
    }
    aplicar();
    btn.addEventListener('click', function () { visible = !visible; aplicar(); });
})();
</script>

<?= $this->endSection() ?>