<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:       #1a2b45;
    --navy-dark:  #111e30;
    --teal:       #00a18f;
    --teal-dark:  #008577;
    --tinta:      #1a2b45;
    --gris:       #5b6b80;
    --borde:      #cfd8e3;
}

/* Fuerza fondo oscuro y centrado aunque el viewLayout meta wrappers */
body,
body > div,
body > main,
body > .container,
body > .container-fluid {
    background: var(--navy-dark) !important;
    margin: 0 !important;
    padding: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    border-radius: 0 !important;
    box-shadow: none !important;
}

body {
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* ── WRAPPER CENTRADOR ── */
.login-page {
    min-height: 100vh;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background:
        radial-gradient(circle at 15% 20%, rgba(0,161,143,0.18), transparent 45%),
        radial-gradient(circle at 85% 85%, rgba(0,161,143,0.10), transparent 40%),
        var(--navy-dark);
}

/* ── CARD: una columna en móvil, dos desde 768px ── */
.login-card {
    width: 100%;
    max-width: 420px;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0,0,0,0.45);
}

/* ── PANEL DE MARCA (el logo es blanco: va sobre fondo oscuro) ── */
.login-marca {
    background: linear-gradient(160deg, #203655 0%, var(--navy) 55%, var(--navy-dark) 100%);
    border-bottom: 4px solid var(--teal);
    padding: 32px 28px 28px;
    color: #fff;
    text-align: center;
}

.login-logo {
    display: block;
    width: 100%;
    max-width: 260px;
    height: auto;
    margin: 0 auto 24px;
}

.login-title {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 8px;
}

.login-sub {
    font-size: 14px;
    line-height: 1.5;
    color: rgba(255,255,255,0.78);
}

/* ── FORMULARIO ── */
.login-body { padding: 28px 28px 24px; }

.login-body-titulo {
    font-size: 20px;
    font-weight: 700;
    color: var(--tinta);
    margin-bottom: 4px;
}

.login-body-sub {
    font-size: 14px;
    color: var(--gris);
    margin-bottom: 22px;
}

/* Alertas Myth/Auth */
.login-body .alert {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 13.5px;
    margin-bottom: 18px;
    border: 1px solid transparent;
}
.login-body .alert-danger  { background: #fdecec; border-color: #f5c2c2; color: #a61b1b; }
.login-body .alert-success { background: #e6f6ef; border-color: #b4e3cd; color: #17643f; }

.field-group { margin-bottom: 18px; }

.field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--tinta);
    margin-bottom: 6px;
}

.field-wrap { position: relative; }

.field-icon {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--gris);
    font-size: 15px;
    pointer-events: none;
}

.field-input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    background: #fff;
    border: 1px solid var(--borde);
    border-radius: 8px;
    color: var(--tinta);
    font-size: 15px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.field-input-clave { padding-right: 42px; }

.field-input::placeholder { color: #8493a6; }

.field-input:focus {
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(0,161,143,0.18);
}

.field-input.is-invalid { border-color: #c93030; }

.field-error {
    font-size: 12.5px;
    color: #a61b1b;
    margin-top: 6px;
}

.field-eye {
    position: absolute;
    right: 6px; top: 50%;
    transform: translateY(-50%);
    width: 32px; height: 32px;
    color: var(--gris);
    font-size: 15px;
    cursor: pointer;
    background: none;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s;
}
.field-eye:hover { color: var(--teal-dark); }

.login-options {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 20px;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    color: var(--gris);
    cursor: pointer;
    margin: 0;
}

.remember-label input[type="checkbox"] {
    accent-color: var(--teal);
    cursor: pointer;
    width: 15px; height: 15px;
}

.forgot-link {
    font-size: 13px;
    color: var(--teal-dark);
    text-decoration: none;
}
.forgot-link:hover { color: var(--navy); text-decoration: underline; }

.login-btn {
    width: 100%;
    padding: 12px;
    background: var(--teal-dark);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.login-btn:hover  { background: #006f63; }
.login-btn:focus-visible { outline: 3px solid rgba(0,161,143,0.45); outline-offset: 2px; }

.login-footer-txt {
    text-align: center;
    font-size: 12px;
    color: var(--gris);
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e6ebf1;
}

@media (min-width: 768px) {
    .login-card {
        max-width: 860px;
        display: grid;
        grid-template-columns: 1fr 1fr;
    }
    .login-marca {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 48px 40px;
        border-bottom: none;
        border-right: 4px solid var(--teal);
        text-align: left;
    }
    .login-logo { max-width: 320px; margin: 0 0 40px; }
    .login-title { font-size: 28px; }
    .login-body { padding: 44px 40px 32px; }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<?php $soloEmail = $config->validFields === ['email']; ?>

<div class="login-page">
<div class="login-card">

    <div class="login-marca">
        <img class="login-logo" src="<?= base_url('imag/jujuy01.png') ?>" alt="Ministerio de Salud de Jujuy">
        <div class="login-title">Sistema de Gestión</div>
        <div class="login-sub">Gestión e indicadores de salud de la provincia de Jujuy</div>
    </div>

    <div class="login-body">
        <div class="login-body-titulo">Iniciar sesión</div>
        <div class="login-body-sub">Ingresá con tus credenciales institucionales</div>

        <?= view('Myth\Auth\Views\_message_block') ?>

        <form action="<?= url_to('login') ?>" method="post">
            <?= csrf_field() ?>

            <div class="field-group">
                <label class="field-label" for="login-user"><?= $soloEmail ? 'Correo' : 'Usuario o correo' ?></label>
                <div class="field-wrap">
                    <i class="fas fa-user field-icon" aria-hidden="true"></i>
                    <input
                        type="<?= $soloEmail ? 'email' : 'text' ?>"
                        id="login-user"
                        name="login"
                        placeholder="<?= $soloEmail ? 'correo institucional' : 'usuario o correo institucional' ?>"
                        autocomplete="username"
                        class="field-input <?= session('errors.login') ? 'is-invalid' : '' ?>"
                        value="<?= esc(old('login') ?? '', 'attr') ?>"
                        required
                        autofocus
                    >
                </div>
                <?php if (session('errors.login')): ?>
                    <div class="field-error"><?= esc(session('errors.login')) ?></div>
                <?php endif ?>
            </div>

            <div class="field-group">
                <label class="field-label" for="login-pass">Contraseña</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="login-pass"
                        name="password"
                        placeholder="Tu contraseña"
                        autocomplete="current-password"
                        class="field-input field-input-clave <?= session('errors.password') ? 'is-invalid' : '' ?>"
                        required
                    >
                    <button type="button" class="field-eye" id="toggle-pass" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye" id="eye-icon" aria-hidden="true"></i>
                    </button>
                </div>
                <?php if (session('errors.password')): ?>
                    <div class="field-error"><?= esc(session('errors.password')) ?></div>
                <?php endif ?>
            </div>

            <div class="login-options">
                <?php if ($config->allowRemembering): ?>
                    <label class="remember-label">
                        <input type="checkbox" name="remember" <?= old('remember') ? 'checked' : '' ?>>
                        Recordarme
                    </label>
                <?php else: ?>
                    <span></span>
                <?php endif ?>
                <?php if ($config->activeResetter): ?>
                    <a href="<?= url_to('forgot') ?>" class="forgot-link">¿Olvidaste tu contraseña?</a>
                <?php endif ?>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                Ingresar
            </button>
        </form>

        <div class="login-footer-txt">Acceso exclusivo para personal autorizado</div>
    </div>

</div>
</div>

<script>
    document.getElementById('toggle-pass').addEventListener('click', function () {
        const inp  = document.getElementById('login-pass');
        const icon = document.getElementById('eye-icon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'fas fa-eye-slash';
            this.setAttribute('aria-label', 'Ocultar contraseña');
        } else {
            inp.type = 'password';
            icon.className = 'fas fa-eye';
            this.setAttribute('aria-label', 'Mostrar contraseña');
        }
    });
</script>

<?= $this->endSection() ?>