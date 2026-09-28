<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= $seccion === 'tipo' ? 'TIPOS DE OPERATIVO' : 'OPERATIVOS' ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">
        <?= $seccion === 'tipo' ? 'TIPOS DE OPERATIVO' : 'OPERATIVOS' ?>
    </h1>
    <hr/>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light">
            <?= $msg['body'] ?>
        </div>
    <?php endif; ?>

    <!-- TABS para navegar entre los dos ABM -->
    <div class="tabs is-boxed" style="margin-bottom:1rem;">
        <ul>
            <li class="<?= $seccion === 'operativo' ? 'is-active' : '' ?>">
                <a href="<?= base_url(route_to('operativo_list')) ?>" style="<?= $seccion === 'operativo' ? 'background:#fff;color:#13304d;font-weight:bold;' : 'color:white;' ?>">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Operativos</span>
                </a>
            </li>
            <li class="<?= $seccion === 'tipo' ? 'is-active' : '' ?>">
                <a href="<?= base_url(route_to('tipo_operativo_list')) ?>" style="<?= $seccion === 'tipo' ? 'background:#fff;color:#13304d;font-weight:bold;' : 'color:white;' ?>">
                    <span class="icon"><i class="fas fa-tags"></i></span>
                    <span>Tipos de Operativo</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('cantidad_operativo_list')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver</span>
            </a>
        </div>
        <div class="column is-narrow">
            <?php if ($seccion === 'tipo'): ?>
                <a class="button is-warning" href="<?= base_url(route_to('tipo_operativo_create')) ?>">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nuevo Tipo</span>
                </a>
            <?php else: ?>
                <a class="button is-warning" href="<?= base_url(route_to('operativo_create')) ?>">
                    <span class="icon"><i class="fas fa-plus"></i></span>
                    <span>Nuevo Operativo</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($seccion === 'operativo'): ?>
    <!-- FILTROS solo para operativo -->
    <form method="GET" action="<?= base_url(route_to('operativo_list')) ?>">
        <div class="columns is-vcentered">
            <div class="column is-4">
                <div class="field">
                    <label class="label" style="color:white;">Nombre</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="nombre"
                            placeholder="Buscar por nombre..."
                            value="<?= esc($filtro_nombre ?? '') ?>">
                        <span class="icon is-left"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-3">
                <div class="field">
                    <label class="label" style="color:white;">Tipo</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tipo">
                                <option value="">Todos</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?= $t->tipo_op_id ?>" <?= ($filtro_tipo ?? '') == $t->tipo_op_id ? 'selected' : '' ?>>
                                        <?= esc($t->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white;">&nbsp;</label>
                    <div class="control">
                        <button class="button is-link" type="submit">
                            <span class="icon"><i class="fas fa-filter"></i></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <!-- ── TABLA TIPOS ── -->
    <?php if ($seccion === 'tipo'): ?>
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
        <?php foreach ($tipos as $t): ?>
            <tr>
                <td><?= $t->tipo_op_id ?></td>
                <td><?= esc($t->nombre) ?></td>
                <td><?= $t->getEditLink() ?></td>
                <td><?= $t->getDeleteLink() ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($tipos)): ?>
            <tr><td colspan="4" class="has-text-centered">No hay tipos cargados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- ── TABLA OPERATIVOS ── -->
    <?php else: ?>
    <div class="table-container">
    <table class="table is-fullwidth is-hoverable">
        <thead>
            <tr>
                <th style="color:black;">#</th>
                <th style="color:black;">Nombre</th>
                <th style="color:black;">Tipo</th>
                <th style="color:black;">Editar</th>
                <th style="color:black;">Eliminar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($operativos as $op): ?>
            <tr>
                <td><?= $op->operativo_id ?></td>
                <td><?= esc($op->nombre) ?></td>
                <td><?= esc($op->tipo_nombre ?? '—') ?></td>
                <td><?= $op->getEditLink() ?></td>
                <td><?= $op->getDeleteLink() ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($operativos)): ?>
            <tr><td colspan="5" class="has-text-centered">No hay operativos cargados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

</div>
</div>
<?= $this->endSection() ?>