

<?= $this->extend('layout/main'); ?>

<?= $this->section('title') ?>
    Usuarios
<?= $this->endSection() ?>

<?= $this->section('menu') ?>
    <?php //echo $this->include('admin/menu'); ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>/* En tu archivo CSS principal (por ejemplo, styles.css) */
/* En tu archivo CSS principal (styles.css) */
.is-light-green {
    background-color: #58D68D; /* Un verde más claro */
    color: white;
}

.is-light-green .button {
    background-color: #58D68D;
    color: white;
}

</style>
<div class="container is-max-widescreen">
    <div class="notification is-light-green">
        <h2 class="subtitle">Listado de Permisos</h2>
        
    
        
        <div class="field">
            <?php $uri = service('request')->getUri(); ?>
            <a class="button is-success" href="<?= base_url(route_to('permiso_create', $uri->getSegment($uri->getTotalSegments()))) ?>" data-route="<?= base_url(route_to('permiso_create', $uri->getSegment($uri->getTotalSegments()))) ?>" data-modal="create_permiso" data-titulo="ASIGNAR&nbsp;PERMISO">
                <span class="icon">
                    <i class="fas fa-user"></i> <!-- Icono de usuario -->
                </span>
                <span>Asignar Nuevo Permiso</span>
            </a>
        </div>

        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Permiso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permisos as $v): ?>
                    <tr>
                        <td><?= $v->permission_id ?></td>
                        <td><?= $v->name ?></td>
                        <td>
                            <form style="display:inline;" action="<?= base_url(route_to('destroy_permiso')) ?>" method="post">
                                <input type="hidden" name="_method" value="DELETE" />
                                <input type="hidden" name="user_id" value="<?= $v->user_id ?>" />
                                <input type="hidden" name="permission_id" value="<?= $v->permission_id ?>" />
                                <?= csrf_field() ?>
                                <button class="button is-danger" onclick="this.closest('form').submit(); return false;">Quitar permiso</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <?= $pager->links(); ?>

        
        <input type="button" class="button is-light-green" value="Volver" onclick="location.href = '<?= base_url(route_to('list_users')) ?>';">
    </div>
</div>
    </div>
</div>

<?= $this->endSection() ?>
  

