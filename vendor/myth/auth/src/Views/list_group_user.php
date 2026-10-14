<?= $this->extend('layout/main');?>

<?= $this->section('title')?>
    USUARIOS
<?= $this->endSection()?>

<?= $this->section('menu')?>
    <?php //echo $this->include('admin/menu');?>
<?= $this->endSection()?>

<?= $this->section('content')?>

<div class="container is-max-widescreen">
    <div class="notification is-light-green">
       
        <h2 class="subtitle">Listado de grupos</h2>
       

     <div class="field">
         <?php $uri = service('request')->getUri(); ?>
            <a class="button is-success" href="<?= base_url(route_to('group_create_user', $uri->getSegment($uri->getTotalSegments())))?>" data-route="<?= base_url(route_to('group_create_user', $uri->getSegment($uri->getTotalSegments())))?>" data-modal="create_group_user" data-titulo="ASIGNAR&nbsp;GRUPO">
                <span class="icon">
                    <i class="fas fa-users"></i> <!-- Icono de usuarios -->
                </span>
                <span>Asignar Nuevo Grupo</span>
            </a>
    </div>

        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Grupo</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($grupos as $v):?>
                    <tr>
                        <td><?= $v->group_id?></td>
                        <td><?= $v->name?></td>
                        <td><?= $v->description?></td>
                        <td>                    
                            <form style="display:inline;" action="<?= base_url(route_to('destroy_group_user'))?>" method="post">
                                <input type="hidden" name="_method" value="DELETE" />
                                <input type="hidden" name="user_id" value="<?=$v->user_id?>" />
                                <input type="hidden" name="group_id" value="<?=$v->group_id?>" />
                                <?=csrf_field()?>
                                <a onclick="this.closest('form').submit();return false;">Quitar grupo</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>    
        </table>

        <?= $pager->links();?>
        <br>

        <input type="button" class="button is-light-green" value="Volver" onclick="volver('<?= base_url(route_to('list_users', $uri->getSegment($uri->getTotalSegments()))) ?>');">
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
