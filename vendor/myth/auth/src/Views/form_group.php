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
        <h1 class="title">Grupos</h1>
        <h2 class="subtitle"><?= $titulo?></h2>
    <form action="<?=base_url(route_to($formRoute))?>" method="POST">

                <div class="field">
                    <label class="label">NOMBRE GRUPO</label>
                    <div class="control">
                        <input class="input" type="text" name="name" value="<?= old('name') ?? $group->name ?? '';?>" placeholder="Text input">                                               
                        <input type="hidden" name="id" value="<?= old('id') ?? $group->id ?? ''?>" >
                        <?php //echo isset($causa)? $causa->getCampoOculto() : '';?>
                        <?= csrf_field() ?>
                    </div>
                    <p class="help is-success">
                        <?= session('errors.name');?>
                    </p>
                </div>
                <div class="field">
                    <label class="label">DESCRIPCION</label>
                    <div class="control">
                        <input class="input" type="text" name="description" value="<?= old('description') ?? $group->description ?? '';?>" placeholder="Text input">
                    </div>
                    <p class="help is-success">
                    <?= session('errors.description');?>
                    </p>
                </div>  

        <div class="field">
            <div class="control">
                <input type="submit" class="button is-link" value="Guardar">
                <input type="button" class="button" value="Cancelar" onclick="volver('<?= base_url(route_to('groups_list')) ?>')">
            </div>
        </div>
                  
    </form>
  
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
