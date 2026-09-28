<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> BASE <?= $this->endSection() ?>
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
    .bf-panel-title i { color: #fff; font-size: 17px; }
    .bf-panel-body { padding: 24px; }

    /* ── ALERTA MAYÚSCULAS ── */
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
    .bf-input.readonly {
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
        <a href="<?= base_url(route_to('base_list')); ?>">Bases</a> ›
        <strong><?= isset($base) ? 'Editar base' : 'Nueva base' ?></strong>
    </div>

    <div class="bf-panel">

        <!-- HEADER -->
        <div class="bf-panel-header">
            <span class="bf-panel-title">
                <i class="fas fa-<?= isset($base) ? 'edit' : 'plus-circle' ?>"></i>
                <?= isset($base) ? 'Editar Base' : 'Nueva Base' ?>
            </span>
        </div>

        <div class="bf-panel-body">

            <!-- AVISO MAYÚSCULAS -->
            <div class="bf-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Todos los campos de texto deben ingresarse en MAYÚSCULAS obligatoriamente.</strong></span>
            </div>

            <form action="<?= base_url(route_to($formRoute)) ?>" method="POST">
                <?= isset($base) ? $base->getCampoOculto() : '' ?>
                <?= csrf_field() ?>

                <div class="bf-cols">

                    <!-- ── COL 1 ── -->
                    <div>

                        <!-- TIPO -->
                        <div class="bf-field">
                            <label class="bf-label">Tipo</label>
                            <select name="tipo" class="bf-select">
                                <option value="BASE" <?= (old('tipo') ?? $base->tipo ?? '') == 'BASE' ? 'selected' : '' ?>>BASE</option>
                                <option value="USE"  <?= (old('tipo') ?? $base->tipo ?? '') == 'USE'  ? 'selected' : '' ?>>USE</option>
                            </select>
                        </div>

                        <!-- NRO -->
                        <div class="bf-field">
                            <label class="bf-label">Nro</label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-hashtag bf-input-icon"></i>
                                <input class="bf-input"
                                    type="text"
                                    name="nro"
                                    placeholder="NRO"
                                    style="text-transform:uppercase;"
                                    oninput="this.value=this.value.replace(/[^A-Za-z\s]/g,'').toUpperCase()"
                                    value="<?= old('nro') ?? $base->nro ?? '' ?>">
                            </div>
                        </div>

                        <!-- NOMBRE -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Nombre <span class="bf-required">*</span>
                            </label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-map-marker-alt bf-input-icon"></i>
                                <input class="bf-input <?= isset($errors['nombre']) ? 'has-error' : '' ?>"
                                    type="text"
                                    name="nombre"
                                    placeholder="NOMBRE"
                                    style="text-transform:uppercase;"
                                    oninput="this.value=this.value.toUpperCase()"
                                    value="<?= old('nombre') ?? $base->nombre ?? '' ?>">
                            </div>
                            <?php if (isset($errors['nombre'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['nombre'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- COORDENADAS -->
                        <div class="bf-field">
                            <label class="bf-label">Coordenadas</label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-globe bf-input-icon"></i>
                                <input class="bf-input"
                                    type="text"
                                    name="coordenadas"
                                    placeholder="-000000000,-000000000"
                                    style="text-transform:uppercase;"
                                    oninput="this.value=this.value.toUpperCase()"
                                    value="<?= old('coordenadas') ?? $base->coordenadas ?? '' ?>">
                            </div>
                        </div>

                    </div>

                    <!-- ── COL 2 ── -->
                    <div>

                        <!-- UBICACIÓN -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Ubicación <span class="bf-required">*</span>
                            </label>
                            <div class="bf-input-wrap">
                                <i class="fas fa-city bf-input-icon"></i>
                                <input class="bf-input <?= isset($errors['ubicacion']) ? 'has-error' : '' ?>"
                                    type="text"
                                    name="ubicacion"
                                    placeholder="UBICACIÓN"
                                    style="text-transform:uppercase;"
                                    oninput="this.value=this.value.toUpperCase()"
                                    value="<?= old('ubicacion') ?? $base->ubicacion ?? '' ?>">
                            </div>
                            <?php if (isset($errors['ubicacion'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['ubicacion'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- REGIÓN -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Región <span class="bf-required">*</span>
                            </label>
                            <select name="region" class="bf-select <?= isset($errors['region']) ? 'has-error' : '' ?>">
                                <option value="">Seleccionar región...</option>
                                <?php
                                $regionActual = old('region') ?? $base->region ?? '';
                                foreach (['PUNA','QUEBRADA','CENTRO','VALLE','RAMAL I','RAMAL II'] as $r):
                                ?>
                                    <option value="<?= $r ?>" <?= $regionActual == $r ? 'selected' : '' ?>><?= $r ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['region'])): ?>
                                <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= $errors['region'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- PROVINCIA (readonly) -->
                        <div class="bf-field">
                            <label class="bf-label">
                                Provincia <span class="bf-required">*</span>
                            </label>
                            <input class="bf-input readonly"
                                type="text"
                                name="provincia"
                                value="JUJUY"
                                readonly>
                        </div>

                        <!-- ESTADO (solo en edición) -->
                        <?php if (isset($base)): ?>
                        <div class="bf-field">
                            <label class="bf-label">Estado <span class="bf-required">*</span></label>
                            <select name="estado" class="bf-select">
                                <option value="activo"      <?= ($base->estado ?? 'activo') == 'activo'      ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= ($base->estado ?? '')       == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                        <?php else: ?>
                            <input type="hidden" name="estado" value="activo">
                        <?php endif; ?>

                    </div>

                </div><!-- /bf-cols -->

                <hr class="bf-divider">

                <div class="bf-actions">
                    <button type="submit" class="bf-btn teal">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="<?= base_url(route_to('base_list')); ?>" class="bf-btn ghost">
                        <i class="fas fa-list"></i> Volver al listado
                    </a>
                </div>

            </form>
        </div><!-- /panel-body -->
    </div><!-- /panel -->

</div><!-- /base-form-wrap -->

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var camposTexto = ['nombre', 'nro', 'coordenadas', 'ubicacion'];
    var hayError = false;

    camposTexto.forEach(function(campo) {
        var input = document.querySelector('[name="' + campo + '"]');
        if (!input) return;
        input.value = input.value.toUpperCase();
        var valor = input.value.trim();
        if (valor !== valor.toUpperCase()) {
            hayError = true;
            input.classList.add('has-error');
        } else {
            input.classList.remove('has-error');
        }
    });

    if (hayError) {
        e.preventDefault();
        alert('⚠️ Todos los campos de texto deben estar en MAYÚSCULAS antes de guardar.');
    }
});
</script>

<?= $this->endSection() ?>