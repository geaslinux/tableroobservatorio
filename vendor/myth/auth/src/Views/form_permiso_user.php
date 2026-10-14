<?= $this->extend('layout/main');?>

<?= $this->section('title')?>
    Proveedor
<?= $this->endSection()?>

<?= $this->section('menu')?>
    <?= $this->include('admin/menu');?>
<?= $this->endSection()?>

<?= $this->section('content')?>

<div class="container is-flex is-justify-content-center" style="margin-top: 2rem;">
    <form action="<?=base_url(route_to($formRoute))?>" method="POST" class="box is-light-green" style="width: 50rem;">
        <h2 class="subtitle has-text-centered has-text-weight-bold"><?= $titulo?></h2>
        <div class="columns">
            <div class="column">
                <div class="field">
                    <label class="label">PERMISOS</label>
                    <div class="control">
                        <div class="select">
                            <?= form_dropdown('permission_id', $option, old('permission_id') ?? $permiso->permission_id ?? '');?>
                        </div>                        
                        <?php $uri = service('request')->getUri();?>
                        <input type="hidden" name="user_id" value="<?= old('user_id') ?? $permiso->user_id ?? $uri->getSegment($uri->getTotalSegments())?>" >
                        <?= csrf_field() ?>
                    </div>
                    <p class="help is-danger">
                        <?= session('errors.permission_id');?>
                    </p>
                </div>
            </div>
        </div>

        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button type="submit" class="button is-success">
                    <span class="icon">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Guardar</span>
                </button>
            </div>
            <div class="control">
                <button type="button" class="button is-success" onclick="location.href='<?= base_url(route_to('permisos_list', $uri->getSegment($uri->getTotalSegments()))) ?>'">
                    <span class="icon">
                        <i class="fas fa-times"></i>
                    </span>
                    <span>Cancelar</span>
                </button>
            </div>
        </div>                  
    </form>
</div>

<style>
/* Estilo personalizado */
.is-light-green {
    background-color: #58D68D; /* Verde más claro */
    color: white;
}

.is-light-green .button {
    background-color: #58D68D;
    color: white;
}

.button.is-success {
    background-color: #58D68D;
    border-color: #58D68D;
    color: white;
}

.button.is-success:hover {
    background-color: #45b078;
    border-color: #45b078;
}

.is-light-green .help.is-danger {
    color: #e3342f;
}

.is-light-green .label {
    color: white;
}

nav {
    margin-bottom: 2rem; /* Ajusta según sea necesario */
}

</style>

<?= $this->endSection()?>
