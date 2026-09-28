<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GESTIÓN PACIENTE HOSPITAL <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-fluid">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">GESTIÓN PACIENTE HOSPITAL</h1>
    <hr/>

    <!-- Nuevas / Modificadas -->
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
                <a href="<?= base_url(route_to('gestion_paciente_hospital_visto')) ?>" class="button is-dark">
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

    <!-- Flash -->
    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <!-- Botones superiores -->
    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-success"
               href="<?= base_url(route_to('gestion_paciente_hospital_export')) ?>?<?= http_build_query(array_filter([
                    'efector'      => $filtro_efector,
                    'zona'         => $filtro_zona,
                    'cuidados'     => $filtro_cuidados,
                    'nivel_riesgo' => $filtro_nivel_riesgo,
                    'estado'       => $filtro_estado,
               ])) ?>">
                <span class="icon"><i class="fas fa-file-excel"></i></span>
                <span>Exportar Excel</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-warning" href="<?= base_url(route_to('gestion_paciente_hospital_create')) ?>">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nuevo Registro</span>
            </a>
        </div>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="<?= base_url(route_to('gestion_paciente_hospital_list')) ?>">
        <div class="columns is-multiline is-vcentered">

            <div class="column is-3">
                <div class="field">
                    <label class="label" style="color:white;">Hospital</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="efector">
                                <option value="">-- Todos los efectores --</option>
                                <?php foreach ($efectores as $e): ?>
                                    <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>>
                                        <?= esc($e->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Zona</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="zona">
                                <option value="">-- Zona --</option>
                                <?php foreach ($zonas as $z): ?>
                                    <option value="<?= $z ?>" <?= $filtro_zona == $z ? 'selected' : '' ?>><?= $z ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Cuidados</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="cuidados">
                                <option value="">-- Cuidados --</option>
                                <?php foreach ($cuidados as $c): ?>
                                    <option value="<?= $c ?>" <?= $filtro_cuidados == $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-3">
                <div class="field">
                    <label class="label" style="color:white;">Nivel de Riesgo</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="nivel_riesgo">
                                <option value="">-- Nivel de Riesgo --</option>
                                <?php foreach ($nivelesRiesgo as $n): ?>
                                    <option value="<?= $n ?>" <?= $filtro_nivel_riesgo == $n ? 'selected' : '' ?>><?= $n ?></option>
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
                                <option value="">-- Estado --</option>
                                <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
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
                        <a class="button is-light" href="<?= base_url(route_to('gestion_paciente_hospital_list')) ?>">
                            <span class="icon"><i class="fas fa-times"></i></span>
                        </a>
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
                    <th style="color:black;">Nivel Complejidad</th>
                    <th style="color:black;">Departamento</th>
                    <th style="color:black;">Zona</th>
                    <th style="color:black;">Cuidados</th>
                    <th style="color:black;">Nivel de Riesgo</th>
                    <th style="color:black;">Proceso</th>
                    <th style="color:black;">Tipo de Cama</th>
                    <th style="color:black;">Tiempo Estancia</th>
                    <th style="color:black;">Estado</th>
                    <th style="color:black;">Editar</th>
                    <th style="color:black;">Eliminar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="12" class="has-text-centered">Sin registros</td></tr>
                <?php else: ?>
                    <?php foreach ($registros as $r):
                        $esNuevo      = $r->created_at > $ultimaVista;
                        $esModificado = $r->updated_at > $ultimaVista && $r->updated_at != $r->created_at;
                    ?>
                        <tr <?php
                            if ($r->estado == 'desactivado')  echo 'style="opacity:0.45; background:#ccc;"';
                            elseif ($esNuevo)                 echo 'style="background-color:#d4edda; border-left:4px solid #28a745;"';
                            elseif ($esModificado)            echo 'style="background-color:#fff8cc; border-left:4px solid #f0c040;"';
                        ?>>
                            <td><?= esc($r->efector_nombre) ?></td>
                            <td class="has-text-centered"><?= esc($r->efector_nivel ?? '—') ?></td>
                            <td><?= esc($r->departamento_nombre) ?></td>
                            <td class="has-text-centered"><?= esc($r->zona) ?></td>
                            <td class="has-text-centered"><?= esc($r->cuidados) ?></td>
                            <td><?= esc($r->nivel_riesgo) ?></td>
                            <td><?= esc($r->proceso) ?></td>
                            <td><?= esc($r->tipo_cama) ?></td>
                            <td class="has-text-centered"><?= esc($r->tiempo_estancia) ?></td>
                            <td class="has-text-centered">
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