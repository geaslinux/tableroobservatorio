<?= $this->extend($config->viewLayout) ?>
<?= $this->section('main') ?>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --navy:       #1a2b45;
    --navy-dark:  #111e30;
    --teal:       #00b4a0;
    --teal-light: #00d4bc;
    --white:      #ffffff;
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
    background: var(--navy-dark);
    padding: 24px 16px;
}

/* ── CARD ── */
.login-card {
    width: 100%;
    max-width: 400px;
    background: var(--navy);
    border-radius: 16px;
    border: 0.5px solid rgba(0,180,160,0.25);
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0,0,0,0.50);
}

.login-teal-bar {
    height: 3px;
    background: var(--teal);
    width: 100%;
}

/* ── HEADER ── */
.login-header {
    background: var(--navy-dark);
    padding: 28px 32px 22px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}

.login-logo-wrap {
    width: 68px; height: 68px;
    border-radius: 50%;
    background: rgba(0,180,160,0.10);
    border: 2px solid rgba(0,180,160,0.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
    overflow: hidden;
}

.login-logo-wrap img {
    width: 50px; height: 50px;
    object-fit: contain;
}

.login-org {
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.30);
    margin-bottom: 5px;
}

.login-title {
    font-size: 15.5px;
    font-weight: 700;
    color: var(--white);
    margin-bottom: 3px;
}

.login-sub {
    font-size: 11px;
    color: rgba(255,255,255,0.28);
}

/* ── BODY ── */
.login-body {
    padding: 28px 32px 32px;
}

/* Alertas Myth/Auth */
.login-body .alert {
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12.5px;
    margin-bottom: 18px;
    display: flex;
    align-items: flex-start;
    gap: 9px;
    border: none;
}
.login-body .alert-danger {
    background: rgba(220,38,38,0.12);
    border: 1px solid rgba(220,38,38,0.28);
    color: #ff7675;
}
.login-body .alert-success {
    background: rgba(39,174,96,0.12);
    border: 1px solid rgba(39,174,96,0.28);
    color: #55efc4;
}

/* ── CAMPOS ── */
.field-group { margin-bottom: 20px; }

.field-label {
    display: block;
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.34);
    margin-bottom: 7px;
}

.field-wrap { position: relative; }

.field-icon {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.24);
    font-size: 15px;
    pointer-events: none;
}

.field-input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.10);
    border-radius: 8px;
    color: var(--white);
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.15s, background 0.15s;
}

.field-input::placeholder { color: rgba(255,255,255,0.20); }

.field-input:focus {
    border-color: var(--teal);
    background: rgba(0,180,160,0.08);
}

.field-input.is-invalid {
    border-color: rgba(220,38,38,0.60);
    background: rgba(220,38,38,0.06);
}

.invalid-feedback {
    font-size: 11.5px;
    color: #ff7675;
    margin-top: 6px;
    padding-left: 2px;
}

.field-eye {
    position: absolute;
    right: 13px; top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.26);
    font-size: 15px;
    cursor: pointer;
    transition: color 0.15s;
    background: none;
    border: none;
    padding: 0;
    display: flex;
    align-items: center;
}
.field-eye:hover { color: var(--teal); }

/* ── OPCIONES ── */
.login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 22px;
}

.remember-label {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    color: rgba(255,255,255,0.34);
    cursor: pointer;
}

.remember-label input[type="checkbox"] {
    accent-color: var(--teal);
    cursor: pointer;
    width: 14px; height: 14px;
}

.forgot-link {
    font-size: 12px;
    color: var(--teal);
    text-decoration: none;
    transition: color 0.15s;
}
.forgot-link:hover { color: var(--teal-light); }

/* ── BOTÓN ── */
.login-btn {
    width: 100%;
    padding: 12px;
    background: var(--teal);
    color: var(--white);
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    letter-spacing: 0.2px;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.login-btn:hover  { background: var(--teal-light); }
.login-btn:active { background: #009e8e; }

/* ── FOOTER ── */
.login-divider {
    height: 1px;
    background: rgba(255,255,255,0.07);
    margin: 22px 0 16px;
}

.login-footer-txt {
    text-align: center;
    font-size: 10px;
    color: rgba(255,255,255,0.16);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<!-- WRAPPER que garantiza centrado sin importar qué meta el viewLayout -->
<div class="login-page">
<div class="login-card">
    <div class="login-teal-bar"></div>

    <!-- HEADER -->
    <div class="login-header">
        <div class="login-logo-wrap">
            <img src="/imag/jujuy01.png" alt="Logo Ministerio de Salud Jujuy">
        </div>
        <div class="login-org">Ministerio de Salud · Jujuy</div>
        <div class="login-title">Sistema de Gestion</div>
        <div class="login-sub">Ingresá con tus credenciales institucionales</div>
    </div>

    <!-- BODY -->
    <div class="login-body">

        <?= view('Myth\Auth\Views\_message_block') ?>

        <form action="<?= route_to('login') ?>" method="post">
            <?= csrf_field() ?>

            <!-- Campo usuario / email -->
            <?php if ($config->validFields === ['email']): ?>
                <div class="field-group">
                    <label class="field-label" for="login-user"><?= lang('Auth.email') ?></label>
                    <div class="field-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input
                            type="email"
                            id="login-user"
                            name="login"
                            placeholder="nombre@salud.jujuy.gob.ar"
                            autocomplete="username"
                            class="field-input <?= session('errors.login') ? 'is-invalid' : '' ?>"
                            value="<?= old('login') ?>"
                        >
                    </div>
                    <?php if (session('errors.login')): ?>
                        <div class="invalid-feedback"><?= session('errors.login') ?></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="field-group">
                    <label class="field-label" for="login-user"><?= lang('Auth.emailOrUsername') ?></label>
                    <div class="field-wrap">
                        <i class="fas fa-user field-icon"></i>
                        <input
                            type="text"
                            id="login-user"
                            name="login"
                            placeholder="usuario o correo institucional"
                            autocomplete="username"
                            class="field-input <?= session('errors.login') ? 'is-invalid' : '' ?>"
                            value="<?= old('login') ?>"
                        >
                    </div>
                    <?php if (session('errors.login')): ?>
                        <div class="invalid-feedback"><?= session('errors.login') ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Contraseña -->
            <div class="field-group">
                <label class="field-label" for="login-pass"><?= lang('Auth.password') ?></label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input
                        type="password"
                        id="login-pass"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        class="field-input <?= session('errors.password') ? 'is-invalid' : '' ?>"
                        style="padding-right:40px"
                    >
                    <button type="button" class="field-eye" id="toggle-pass" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
                <?php if (session('errors.password')): ?>
                    <div class="invalid-feedback"><?= session('errors.password') ?></div>
                <?php endif; ?>
            </div>

            <!-- Recordarme + Olvidé -->
            <div class="login-options">
                <?php if ($config->allowRemembering): ?>
                    <label class="remember-label">
                        <input type="checkbox" name="remember" <?= old('remember') ? 'checked' : '' ?>>
                        <?= lang('Auth.rememberMe') ?>
                    </label>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
                <a href="<?= url_to('forgot') ?>" class="forgot-link"><?= lang('Auth.forgotPassword') ?></a>
            </div>

            <!-- Botón submit -->
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                <?= lang('Auth.loginAction') ?>
            </button>

        </form>

        <div class="login-divider"></div>
        <div class="login-footer-txt">Acceso exclusivo · Personal autorizado</div>
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