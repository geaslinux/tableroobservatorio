<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> USUARIOS <?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('auth/_encabezado', ['camino' => [['Usuarios', null]]]) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title">
                <i class="fas fa-user-cog"></i> USUARIOS
            </span>
        </div>

        <div class="bl-panel-body">

            <div class="bl-toolbar">
                <a href="<?= base_url(route_to('inicio_views')) ?>" class="bl-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <a href="<?= base_url(route_to('groups_list')) ?>" class="bl-btn navy">
                    <i class="fas fa-users-cog"></i> Grupos
                </a>
            </div>

            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>E-mail</th>
                            <th>Nombre de usuario</th>
                            <th>Permisos</th>
                            <th>Grupos</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="6" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">No hay usuarios cargados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($users ?? [] as $v): ?>
                            <tr>
                                <td><?= esc($v->id) ?></td>
                                <td><?= esc($v->email) ?></td>
                                <td><strong><?= esc($v->username) ?></strong></td>
                                <td>
                                    <a href="<?= base_url(route_to('permisos_list', $v->id)) ?>" class="bl-btn sm teal">
                                        <i class="fas fa-key"></i> Ver permisos
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= base_url(route_to('group_list_user', $v->id)) ?>" class="bl-btn sm navy">
                                        <i class="fas fa-users"></i> Ver grupos
                                    </a>
                                </td>
                                <td>
                                    <?php if (method_exists($v, 'isBanned') && $v->isBanned()): ?>
                                        <span class="bl-tag red"><i class="fas fa-circle dot"></i> Desactivado</span>
                                    <?php else: ?>
                                        <span class="bl-tag green"><i class="fas fa-circle dot"></i> Activo</span>
                                    <?php endif; ?>
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