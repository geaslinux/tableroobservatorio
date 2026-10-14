<?= $this->extend('layout/main');?>

<?= $this->section('title')?>
    USUARIOS
<?= $this->endSection()?>

<?= $this->section('menu')?>
    <?php //echo $this->include('admin/menu');?>
<?= $this->endSection()?>

<?= $this->section('content')?>


<div class="container is-max-widescreen">
    <div class="notification is-success">
        <section class="section">
            <h2 class="subtitle has-text-centered has-text-weight-bold">Creación de Permisos a Usuario</h2>
        
        <div class="field">
            <?php $uri = service('request')->getUri();?>
            <a class="button is-light-green" href="<?= base_url(route_to('group_permiso_create', $uri->getSegment($uri->getTotalSegments())))?>" data-route="<?= base_url(route_to('permiso_group_create', $uri->getSegment($uri->getTotalSegments())))?>" data-modal="create_permiso_group" data-titulo="ASIGNAR&nbsp;PERMISO&nbsp;AL&nbsp;GRUPO">Asignar Permiso al Grupo</a>
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
                <?php foreach($permisos as $v):?>
                    <tr>
                        <td><?= $v->permission_id?></td>
                        <td><?= $v->name?></td>
                        <td>                    
                            <form style="display:inline;" action="<?= base_url(route_to('destroy_group_permiso'))?>" method="post">
                                <input type="hidden" name="_method" value="DELETE" />
                                <input type="hidden" name="group_id" value="<?=$v->group_id?>" />
                                <input type="hidden" name="permission_id" value="<?=$v->permission_id?>" />
                                <?=csrf_field()?>
                                <a onclick="this.closest('form').submit();return false;">Quitar permiso</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>    
        </table>

        <?= $pager->links();?>

        <input type="button" class="button is-light-green" value="Volver" onclick="volver('<?= base_url(route_to('groups_list')) ?>');">
    </div>
</div>
<style>/* En tu archivo CSS principal (styles.css) */
.is-light-green {
    background-color: #58D68D; /* Un verde más claro */
    color: white;
}

.is-light-green .button {
    background-color: #58D68D;
    color: white;
}
</style>
<?= $this->endSection()?>
