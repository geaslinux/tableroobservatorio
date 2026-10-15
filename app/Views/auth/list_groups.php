<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GRUPOS <?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('auth/_encabezado', ['camino' => [['Grupos', null]]]) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title"><i class="fas fa-users-cog"></i> GRUPOS</span>
        </div>

        <div class="bl-panel-body">

            <div class="bl-toolbar">
                <a href="<?= base_url(route_to('inicio_views')) ?>" class="bl-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('list_users')) ?>" class="bl-btn navy">
                    <i class="fas fa-user-cog"></i> Usuarios
                </a>
            </div>

            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Grupo</th>
                            <th>Descripción</th>
                            <th>Permisos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grupos)): ?>
                            <tr><td colspan="4" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">No hay grupos cargados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($grupos ?? [] as $v): ?>
                            <tr>
                                <td><?= esc($v->id) ?></td>
                                <td><strong><?= esc($v->name) ?></strong></td>
                                <td class="bl-obs"><?= esc($v->description) ?></td>
                                <td>
                                    <a href="<?= base_url(route_to('groups_permisos_list', $v->id)) ?>" class="bl-btn sm teal">
                                        <i class="fas fa-key"></i> Ver permisos
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($pager)): ?>
                <div class="bl-pager"><?= $pager->links(); ?></div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?= $this->endSection() ?>