<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> ABM EFECTORES <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">ABM EFECTORES</h1>
    <hr/>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('transfusion_list')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver a Transfusiones</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-warning" href="<?= base_url(route_to('efector_create')) ?>">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>Nuevo Efector</span>
            </a>
        </div>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="<?= base_url(route_to('efector_list')) ?>">
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
            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Región</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="region">
                                <option value="">Todas</option>
                                <?php foreach ($regiones as $reg): ?>
                                    <option value="<?= $reg ?>" <?= ($filtro_region ?? '') == $reg ? 'selected' : '' ?>><?= $reg ?></option>
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

    <div class="table-container">
    <table class="table is-fullwidth is-hoverable">
        <thead>
            <tr>
                <th style="color:black;">#</th>
                <th style="color:black;">Nombre</th>
                <th style="color:black;">Nivel Complejidad</th>
                <th style="color:black;">Departamento</th>
                <th style="color:black;">Ubicación</th>
                <th style="color:black;">Región</th>
                <th style="color:black;">Editar</th>
                <th style="color:black;">Eliminar</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($efectores as $ef): ?>
            <tr>
                <td><?= $ef->efector_id ?></td>
                <td><?= esc($ef->nombre) ?></td>
                <td><?= esc($ef->nivel_complejidad ?? '—') ?></td>
                <td><?= esc($ef->departamento ?? '—') ?></td>
                <td><?= esc($ef->ubicacion ?? '—') ?></td>
                <td><?= esc($ef->region ?? '—') ?></td>
                <td><?= $ef->getEditLink() ?></td>
                <td><?= $ef->getDeleteLink() ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($efectores)): ?>
            <tr><td colspan="8" class="has-text-centered">No hay efectores cargados.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
</div>
<?= $this->endSection() ?>