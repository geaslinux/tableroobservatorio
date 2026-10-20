<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> GRUPOS DEL USUARIO <?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('auth/_encabezado', ['camino' => [
        ['Usuarios', base_url(route_to('list_users'))],
        ['Grupos del usuario', null],
    ]]) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title"><i class="fas fa-users"></i> GRUPOS DEL USUARIO</span>
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
                            <th>Grupo</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($grupos)): ?>
                            <tr><td colspan="3" style="text-align:center; color:#718096; font-style:italic; padding:14px 10px;">El usuario no tiene grupos asignados.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($grupos ?? [] as $v): ?>
                            <tr>
                                <td><?= esc($v->group_id) ?></td>
                                <td><strong><?= esc($v->name) ?></strong></td>
                                <td class="bl-obs"><?= esc($v->description) ?></td>
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