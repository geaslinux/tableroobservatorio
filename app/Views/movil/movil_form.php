<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> MÓVIL <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ── DESIGN TOKENS (igual que base_views) ── */
    .base-form-wrap {
        --navy:       #1a2b45;
        --navy-dark:  #111e30;
        --teal:       #00b4a0;
        --teal-light: #00d4bc;
        --teal-bg:    rgba(0,180,160,0.12);
        --gray-bg:    #e8eaed;
        --white:      #ffffff;
        --text-main:  #1a2b45;
        --text-muted: #5a6a7e;
        --border:     #d0d5de;
        --kpi-orange: #e67e22;

        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--gray-bg);
        padding: 20px;
        min-height: 100vh;
    }
    .base-form-wrap * { box-sizing: border-box; }

    /* ── BREADCRUMB ── */
    .bf-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .bf-breadcrumb i { color: var(--teal); font-size: 13px; }
    .bf-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .bf-breadcrumb a:hover { color: var(--teal); }
    .bf-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── PANEL ── */
    .bf-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        max-width: 860px;
        margin: 0 auto;
    }
    .bf-panel-header {
        padding: 16px 24px;
        background: var(--navy);
        display: flex; align-items: center; gap: 12px;
    }
    .bf-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .bf-panel-title i { color: var(--teal); font-size: 17px; }
    .bf-panel-body { padding: 24px; }

    /* ── ALERTA / AVISO ── */
    .bf-notice {
        background: rgba(230,126,34,0.1);
        border-left: 4px solid var(--kpi-orange);
        border-radius: 8px;
        padding: 11px 16px;
        font-size: 12.5px; color: var(--text-main);
        margin-bottom: 22px;
        display: flex; align-items: center; gap: 10px;
    }
    .bf-notice i { color: var(--kpi-orange); font-size: 15px; flex-shrink: 0; }

    /* ── GRID DE COLUMNAS ── */
    .bf-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 28px;
    }
    @media (max-width: 640px) {
        .bf-cols { grid-template-columns: 1fr; }
    }

    /* ── FIELD ── */
    .bf-field { margin-bottom: 18px; }
    .bf-label {
        display: block;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
        margin-bottom: 6px;
    }
    .bf-label .bf-required { color: var(--teal); margin-left: 2px; }

    /* Input / Select base */
    .bf-input,
    .bf-select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: #f8f9fb;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        height: 40px;
        font-family: inherit;
    }
    .bf-input:focus,
    .bf-select:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 3px rgba(0,180,160,0.12);
        background: var(--white);
    }
    .bf-input.has-error,
    .bf-select.has-error { border-color: #e74c3c; }
    .bf-input.readonly,
    .bf-select.readonly {
        background: #eef0f3; color: #8a96a4; cursor: not-allowed;
    }

    /* Input con ícono */
    .bf-input-wrap {
        position: relative;
    }
    .bf-input-wrap .bf-input { padding-left: 38px; }
    .bf-input-icon {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: var(--teal); font-size: 13px; pointer-events: none;
    }

    .bf-error {
        font-size: 11px; color: #e74c3c; margin-top: 4px;
        display: flex; align-items: center; gap: 4px;
    }
    .bf-error i { font-size: 10px; }

    .bf-help {
        font-size: 11px; color: var(--text-muted); margin-top: 4px;
        display: flex; align-items: center; gap: 4px;
    }
    .bf-help i { color: var(--kpi-orange); font-size: 11px; }

    /* ── DIVIDER ── */
    .bf-divider {
        border: none; border-top: 1px solid var(--border);
        margin: 20px 0;
    }

    /* ── ACTIONS ── */
    .bf-actions {
        display: flex; align-items: center; justify-content: center; gap: 12px;
        flex-wrap: wrap;
        padding-top: 6px;
    }
    .bf-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 22px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s;
        font-family: inherit;
    }
    .bf-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .bf-btn.teal  { background: var(--teal);  color: #fff; }
    .bf-btn.ghost { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .bf-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
</style>

<div class="base-form-wrap">

    <!-- BREADCRUMB -->
    <div class="bf-breadcrumb">
        <i class="fas fa-home"></i>
        <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
        Prehospitalario ›
        <a href="<?= base_url(route_to('movil_list')); ?>">Móviles</a> ›
        <strong><?= isset($movil) ? 'Editar móvil' : 'Nuevo móvil' ?></strong>
    </div>

    <div class="bf-panel">

        <!-- HEADER -->
        <div class="bf-panel-header">
            <span class="bf-panel-title">
                <i class="fas fa-<?= isset($movil) ? 'edit' : 'plus-circle' ?>"></i>
                <?= isset($movil) ? 'Editar Móvil' : 'Nuevo Móvil' ?>
            </span>
        </div>

        <div class="bf-panel-body">

            <form action="<?= base_url(route_to($formRoute)) ?>" method="POST">
                <?= isset($movil) ? $movil->getCampoOculto() : '' ?>
                <?= csrf_field() ?>

                <div class="bf-cols">

                    <!-- ── COL 1 ── -->
                    <div>

                        <!-- EJERCICIO -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Ejercicio <span class="bf-required">*</span>
                            </label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-calendar bf-input-icon"></i>
                                <input class="bf-input <?= isset($errors['ejercicio']) ? 'has-error' : '' ?>"
                                    type="number"
                                    name="ejercicio"
                                    placeholder="<?= date('Y') ?>"
                                    max="<?= date('Y') ?>"
                                    value="<?= old('ejercicio') ?? $movil->ejercicio ?? date('Y') ?>">
                            </div>
                            <!-- Aviso siempre visible -->
                            <div class="bf-help">
                                <i class="fas fa-info-circle"></i>
                                Solo se permiten ejercicios hasta el año actual (<?= date('Y') ?>).
                            </div>
                            <!-- Error del servidor si intentó cargar uno mayor -->
                            <?php if (isset($errors['ejercicio'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['ejercicio'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- TIPO -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Tipo <span class="bf-required">*</span>
                            </label>
                            <select name="tipo" class="bf-select <?= isset($errors['tipo']) ? 'has-error' : '' ?>">
                                <?php
                                $tipos   = ['MOVILES OPERATIVOS', 'MOVILES LOGISTICA', 'FUERA DE SERVICIO'];
                                $selTipo = old('tipo') ?? $movil->tipo ?? '';
                                foreach ($tipos as $t):
                                ?>
                                    <option value="<?= $t ?>" <?= $selTipo == $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['tipo'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['tipo'] ?></div>
                            <?php endif; ?>
                        </div>

                    </div>

                    <!-- ── COL 2 ── -->
                    <div>

                        <!-- CANTIDAD -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Cantidad <span class="bf-required">*</span>
                            </label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-car bf-input-icon"></i>
                                <input class="bf-input <?= isset($errors['cantidad']) ? 'has-error' : '' ?>"
                                    type="number"
                                    name="cantidad"
                                    placeholder="0"
                                    min="0"
                                    value="<?= old('cantidad') ?? $movil->cantidad ?? 0 ?>">
                            </div>
                            <?php if (isset($errors['cantidad'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['cantidad'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- ESTADO (deshabilitado, igual lógica que el original) -->
                        <div class="bf-field">
                            <label class="bf-label">Estado <span class="bf-required">*</span></label>
                            <select name="estado_display" class="bf-select readonly" disabled>
                                <option value="activo"
                                    <?= (old('estado') ?? $movil->estado ?? 'activo') == 'activo' ? 'selected' : '' ?>>
                                    Activo
                                </option>
                                <option value="desactivado"
                                    <?= (old('estado') ?? $movil->estado ?? '') == 'desactivado' ? 'selected' : '' ?>>
                                    Desactivado
                                </option>
                            </select>
                            <!-- input oculto para que el valor sí viaje en el POST -->
                            <input type="hidden" name="estado" value="<?= old('estado') ?? $movil->estado ?? 'activo' ?>">
                        </div>

                    </div>

                </div><!-- /bf-cols -->

                <hr class="bf-divider">

                <div class="bf-actions">
                    <button type="submit" class="bf-btn teal">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="<?= base_url(route_to('movil_list')); ?>" class="bf-btn ghost">
                        <i class="fas fa-list"></i> Volver al listado
                    </a>
                </div>

            </form>
        </div><!-- /panel-body -->
    </div><!-- /panel -->

</div><!-- /base-form-wrap -->

<?= $this->endSection() ?>