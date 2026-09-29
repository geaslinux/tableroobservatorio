<?= $this->extend('layout/main');?>

<?= $this->section('title')?>
    Proveedor
<?= $this->endSection()?>

<?= $this->section('menu')?>
    <?= $this->include('admin/menu');?>
<?= $this->endSection()?>

<?= $this->section('content')?>

<div class="container is-max-widescreen">
    <div class="notification is-light-green">
        <h1 class="title">Permisos</h1>
        <h2 class="subtitle"><?= $titulo?></h2>
        
        <form action="<?=base_url(route_to($formRoute))?>" method="POST">
            <div class="columns">
                <div class="column">
                    <div class="field">
                        <label class="label">PERMISOS</label>
                        <div class="control">
                            <div class="select">
                                <?= form_dropdown('permission_id', $option, old('permission_id') ?? $permiso->permission_id ?? '');?>
                            </div>                        
                            <?php $uri = service('request')->getUri();?>
                            <input type="hidden" name="group_id" value="<?= old('group_id') ?? $permiso->group_id ?? $uri->getSegment($uri->getTotalSegments())?>" >
                            <?= csrf_field() ?>
                        </div>
                        <p class="help is-success">
                            <?= session('errors.permission_id');?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <input type="submit" class="button" value="Guardar">
                    <input type="button" class="button" value="Cancelar" onclick="location.href = '<?= base_url(route_to('group_permisos_list', $uri->getSegment($uri->getTotalSegments()))) ?>';">
                </div>
            </div>                  
        </form>
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
