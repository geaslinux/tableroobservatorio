<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> PERMISOS DEL USUARIO <?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('auth/_encabezado', ['camino' => [
        ['Usuarios', base_url(route_to('list_users'))],
        ['Permisos del usuario', null],
    ]]) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title"><i class="fas fa-key"></i> PERMISOS DEL USUARIO</span>
        </div>

        <div class="bl-panel-body">

            <div class="bl-toolbar">
                <a href="<?= base_url(route_to('list_users')) ?>" class="bl-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <div class="bl-table-wrap">
                <table class="bl-table">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Permiso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($permisos)): ?>
                            <tr><td colspan="2" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">El usuario no tiene permisos propios asignados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($permisos ?? [] as $v): ?>
                            <tr>
                                <td><?= esc($v->permission_id) ?></td>
                                <td><strong><?= esc($v->name) ?></strong></td>
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