<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> MANUAL - CARGA <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>
<?= $this->section('css') ?><link rel="stylesheet" href="/css/panel.css"><link rel="stylesheet" href="/css/manual.css?v=9"><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('partials/breadcrumb', ['nodo' => 'documentacion_list']) ?>

    <!-- TARJETAS KPI -->
    <?= view('partials/kpi_cards', ['tarjetas' => [
        ['label' => 'Páginas del manual', 'valor' => $kpi['paginas'], 'icono' => 'fa-book', 'color' => 'navy', 'sub' => $kpi['publicadas'] . ' publicadas'],
        ['label' => $roles[$rolSistema], 'valor' => $kpi['delRol'], 'icono' => $rolSistema === 'administrativo' ? 'fa-user-tie' : 'fa-user', 'color' => $rolSistema === 'administrativo' ? 'teal' : 'blue'],
        ['label' => 'Ambos roles', 'valor' => $kpi['ambos'], 'icono' => 'fa-users', 'color' => 'purple', 'sub' => 'También en el otro sistema'],
        ['label' => 'Pantallas sin documentar', 'valor' => $kpi['pendientes'], 'icono' => 'fa-file-circle-exclamation', 'color' => $kpi['pendientes'] ? 'amber' : 'green'],
    ]]) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title"><i class="fas fa-book-medical"></i> Manual de usuario — carga</span>
            <div class="bl-kpi-group">
                <span class="bl-tag green"><i class="fas fa-circle dot"></i> Publicada</span>
                <span class="bl-tag gray"><i class="fas fa-circle dot"></i> Borrador</span>
            </div>
        </div>

        <div class="bl-panel-body">

            <!-- TOOLBAR -->
            <div class="bl-toolbar">
                <a href="<?= base_url(route_to('documentacion_create')); ?>" class="bl-btn teal">
                    <i class="fas fa-plus"></i> Nueva página
                </a>
                <a href="<?= base_url(route_to('manual_inicio')); ?>" class="bl-btn navy">
                    <i class="fas fa-book-open"></i> Ver documentación
                </a>
                <a href="<?= base_url(route_to('documentacion_export')); ?>?<?= http_build_query([
                    'modulo' => $filtro_modulo, 'rol' => $filtro_rol, 'tipo_vista' => $filtro_tipo, 'estado' => $filtro_estado, 'search' => $search,
                ]) ?>" class="bl-btn green">
                    <i class="fas fa-file-excel"></i> Exportar índice
                </a>
            </div>

            <!-- PANTALLAS SIN DOCUMENTAR -->
            <?php if ($pendientes): ?>
            <div class="bl-panel bl-stats-panel is-collapsed" id="panelPendientes">
                <div class="bl-stats-header">
                    <span><i class="fas fa-triangle-exclamation"></i> Pantallas del sistema sin documentar (<?= count($pendientes) ?>)</span>
                    <button type="button" class="bl-btn ghost sm" id="btnPendientes">
                        <i class="fas fa-eye" id="iconoPendientes"></i> <span id="textoPendientes">Mostrar</span>
                    </button>
                </div>
                <div class="bl-section-body" id="cuerpoPendientes" style="display:none;">
                    <p class="muted" style="margin:0 0 10px;">Hacé clic en una pantalla para crear su página con el módulo y la vista ya elegidos.</p>
                    <div class="mn-pendientes">
                        <?php foreach ($pendientes as $p): ?>
                            <a href="<?= base_url(route_to('documentacion_create')) ?>?<?= http_build_query(['modulo' => $p['modulo'], 'vista' => $p['vista']]) ?>">
                                <i class="fas fa-plus"></i> <?= esc($p['vista_nombre']) ?> <small>· <?= esc($p['modulo_nombre']) ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- FILTROS -->
            <form method="GET" action="<?= base_url(route_to('documentacion_list')); ?>">
                <div class="bl-filtro-bar">
                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">Buscar</span>
                        <input class="bl-filtro-input" type="text" name="search" placeholder="Título, resumen, pantalla..." value="<?= esc($search) ?>" style="min-width:200px;">
                    </div>

                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">Módulo</span>
                        <select name="modulo" class="bl-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($modulos as $clave => $m): ?>
                                <option value="<?= esc($clave) ?>" <?= $filtro_modulo == $clave ? 'selected' : '' ?>><?= esc($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">Rol</span>
                        <select name="rol" class="bl-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($roles as $clave => $nombre): ?>
                                <option value="<?= $clave ?>" <?= $filtro_rol == $clave ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">Tipo de vista</span>
                        <select name="tipo_vista" class="bl-filtro-select">
                            <option value="">Todos</option>
                            <?php foreach ($tipos as $clave => $nombre): ?>
                                <option value="<?= $clave ?>" <?= $filtro_tipo == $clave ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">Estado</span>
                        <select name="estado" class="bl-filtro-select">
                            <option value="">Todos</option>
                            <option value="activo"      <?= $filtro_estado == 'activo' ? 'selected' : '' ?>>Publicada</option>
                            <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Borrador</option>
                        </select>
                    </div>

                    <div class="bl-filtro-group">
                        <span class="bl-filtro-label">&nbsp;</span>
                        <div class="bl-filtro-actions">
                            <button type="submit" class="bl-btn teal filter-btn" title="Filtrar"><i class="fas fa-filter"></i></button>
                            <a href="<?= base_url(route_to('documentacion_list')); ?>" class="bl-btn ghost filter-btn" title="Limpiar filtros"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </form>

            <!-- TABLA -->
            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Pantalla</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Rol</th>
                            <th class="center">Captura</th>
                            <th class="center">Validaciones</th>
                            <th class="center">Orden</th>
                            <th>Actualizado</th>
                            <th>Estado</th>
                            <th>Ver</th>
                            <th>Editar</th>
                            <th>Eliminar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $r):
                        $rolVer = $rolSistema; // la vista previa abre el manual de este sistema
                    ?>
                        <tr class="<?= $r->estado === 'desactivado' ? 'row-inactiva' : '' ?>">
                            <td><span class="bl-tag navy"><?= esc($r->modulo_nombre) ?></span></td>
                            <td><?= esc($r->vista_nombre) ?></td>
                            <td class="strong">
                                <?= esc($r->titulo) ?>
                                <?php if ($r->resumen): ?><div class="muted"><?= esc(mb_strimwidth($r->resumen, 0, 90, '…')) ?></div><?php endif; ?>
                            </td>
                            <td><?= esc($r->getTipoVistaNombre()) ?></td>
                            <td><span class="bl-tag <?= $r->rol === 'administrativo' ? 'teal' : ($r->rol === 'funcionario' ? 'blue' : 'navy') ?>"><?= esc($r->getRolNombre()) ?></span></td>
                            <td class="center">
                                <?php if ($r->imagen): ?>
                                    <img class="mn-thumb" src="<?= esc($r->getImagenUrl(), 'attr') ?>" alt="Captura" data-ampliar>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="center"><?= count($r->getValidacionesLista()) ?></td>
                            <td class="center"><?= (int) $r->orden ?></td>
                            <td class="muted"><?= $r->updated_at ? $r->updated_at->format('d/m/Y H:i') : '—' ?></td>
                            <td>
                                <?php if ($r->estado === 'activo'): ?>
                                    <span class="bl-tag green"><i class="fas fa-circle dot"></i> Publicada</span>
                                <?php else: ?>
                                    <span class="bl-tag gray"><i class="fas fa-circle dot"></i> Borrador</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($r->estado === 'activo'): ?>
                                    <a class="bl-icon-link" title="Ver en la documentación" href="<?= base_url(route_to('manual_pagina', $rolVer, $r->documentacion_id)) ?>"><i class="fas fa-eye"></i></a>
                                <?php else: ?>
                                    <span class="muted" title="Los borradores no se publican">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $r->getEditLink() ?></td>
                            <td><?= $r->getDeleteLink() ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="13" class="bl-empty"><i class="fas fa-book"></i> Todavía no hay páginas del manual con estos filtros.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bl-pager"><?= $pager->links() ?></div>

        </div>
    </div>
</div>

<!-- Visor de capturas -->
<div class="mn-lightbox" id="mnLightbox" aria-hidden="true">
    <button type="button" aria-label="Cerrar"><i class="fas fa-times"></i></button>
    <img src="" alt="Captura ampliada">
</div>

<script>
(function () {
    // Pantallas sin documentar: plegable
    var btn = document.getElementById('btnPendientes');
    if (btn) {
        btn.addEventListener('click', function () {
            var cuerpo  = document.getElementById('cuerpoPendientes');
            var visible = cuerpo.style.display === 'none';
            cuerpo.style.display = visible ? '' : 'none';
            document.getElementById('panelPendientes').classList.toggle('is-collapsed', !visible);
            document.getElementById('iconoPendientes').className = visible ? 'fas fa-eye-slash' : 'fas fa-eye';
            document.getElementById('textoPendientes').textContent = visible ? 'Ocultar' : 'Mostrar';
        });
    }

    // Ampliar miniaturas
    var visor = document.getElementById('mnLightbox');
    document.querySelectorAll('[data-ampliar]').forEach(function (img) {
        img.addEventListener('click', function () {
            visor.querySelector('img').src = img.src;
            visor.classList.add('is-open');
        });
    });
    visor.addEventListener('click', function () { visor.classList.remove('is-open'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') visor.classList.remove('is-open'); });
})();
</script>

<?= $this->endSection() ?>