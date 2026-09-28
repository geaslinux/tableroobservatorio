<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= $seccion === 'categoria' ? 'CATEGORÍAS' : 'SUB CATEGORÍAS' ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">
        <?= $seccion === 'categoria' ? 'CATEGORÍAS' : 'SUB CATEGORÍAS' ?>
    </h1>
    <hr/>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tabs is-boxed" style="margin-bottom:1rem;">
        <ul>
            <li class="<?= $seccion === 'categoria' ? 'is-active' : '' ?>">
                <a href="<?= base_url(route_to('categoria_list')) ?>"
                   style="<?= $seccion === 'categoria' ? 'background:#fff;color:#13304d;font-weight:bold;' : 'color:white;' ?>">
                    <span class="icon"><i class="fas fa-tag"></i></span>
                    <span>Categorías</span>
                </a>
            </li>
            <li class="<?= $seccion === 'sub_categoria' ? 'is-active' : '' ?>">
                <a href="<?= base_url(route_to('sub_categoria_list')) ?>"
                   style="<?= $seccion === 'sub_categoria' ? 'background:#fff;color:#13304d;font-weight:bold;' : 'color:white;' ?>">
                    <span class="icon"><i class="fas fa-tags"></i></span>
                    <span>Sub Categorías</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('consulta_reclamo_list')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver a Consultas/Reclamos</span>
            </a>
        </div>
        <div class="column is-narrow">
            <?php if ($seccion === 'categoria'): ?>
                <a class="button is-warning" href="<?= base_url(route_to('categoria_create')) ?>">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nueva Categoría</span>
                </a>
            <?php else: ?>
                <a class="button is-warning" href="<?= base_url(route_to('sub_categoria_create')) ?>">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nueva Sub Categoría</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-container">
    <table class="table is-fullwidth is-hoverable">
        <thead>
            <tr>
                <th style="color:black;">#</th>
                <th style="color:black;">Nombre</th>
                <th style="color:black;">Editar</th>
                <th style="color:black;">Eliminar</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($seccion === 'categoria'): ?>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= $c->categoria_id ?></td>
                    <td><?= esc($c->nombre) ?></td>
                    <td><?= $c->getEditLink() ?></td>
                    <td><?= $c->getDeleteLink() ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categorias)): ?>
                <tr><td colspan="4" class="has-text-centered">No hay categorías cargadas.</td></tr>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($sub_categorias as $s): ?>
                <tr>
                    <td><?= $s->sub_categoria_id ?></td>
                    <td><?= esc($s->nombre) ?></td>
                    <td><?= $s->getEditLink() ?></td>
                    <td><?= $s->getDeleteLink() ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sub_categorias)): ?>
                <tr><td colspan="4" class="has-text-centered">No hay sub categorías cargadas.</td></tr>
            <?php endif; ?>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
</div>
<?= $this->endSection() ?>