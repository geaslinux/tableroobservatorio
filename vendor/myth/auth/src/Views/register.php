<?= $this->extend('layout/main');?>

<?= $this->section('title')?>
    USUARIOS
<?= $this->endSection()?>

<?= $this->section('menu')?>
    <?= $this->include('admin/menu');?>
<?= $this->endSection()?>

<?= $this->section('main') ?>

<div class="container is-flex is-justify-content-center" style="margin-top: 2rem;">
    <form action="<?= route_to('register') ?>" method="post" class="box is-light-green" style="width: 50rem;">
        <?= isset($user) ? $user->getCampoOculto() : '';?>
        <?= csrf_field() ?>

        <h2 class="subtitle has-text-centered has-text-weight-bold">Registro de Usuario</h2>

        <div class="field">
            <label class="label"><?=lang('Auth.email')?></label>
            <div class="control">
                <input type="email" class="input" name="email" placeholder="<?=lang('Auth.email')?>" value="<?= old('email') ?>">
            </div>
            <small id="emailHelp" class="form-text text-muted"><?=lang('Auth.weNeverShare')?></small>
            <p class="help is-danger">
                <?= session('errors.email');?>
            </p>
        </div>                        

        <div class="field">
            <label class="label"><?=lang('Auth.username')?></label>
            <div class="control">
                <input type="text" class="input" name="username" placeholder="<?=lang('Auth.username')?>" value="<?= old('username') ?>">
            </div>                            
            <p class="help is-danger">
                <?= session('errors.username');?>
            </p>
        </div>                        

        <div class="field">
            <label class="label"><?=lang('Auth.password')?></label>
            <div class="control">
                <input type="password" name="password" class="input" placeholder="<?=lang('Auth.password')?>" autocomplete="off">
            </div>                            
            <p class="help is-danger">
                <?= session('errors.password');?>
            </p>
        </div>                       
        
        <div class="field">
            <label class="label"><?=lang('Auth.repeatPassword')?></label>
            <div class="control">
                <input type="password" name="pass_confirm" class="input" placeholder="<?=lang('Auth.repeatPassword')?>" autocomplete="off">
            </div>                            
            <p class="help is-danger">
                <?= session('errors.pass_confirm');?>
            </p>
        </div>                                                

        <br>

        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button type="submit" class="button is-success">
                    <span class="icon">
                        <i class="fas fa-save"></i>
                    </span>
                    <span><?=lang('Auth.register')?></span>
                </button>
            </div>
            <div class="control">
                <button type="button" class="button is-success" onclick="location.href='<?= base_url(route_to('list_users')); ?>'">
                    <span class="icon">
                        <i class="fas fa-list"></i>
                    </span>
                    <span>Volver</span>
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

<?= $this->endSection() ?>
