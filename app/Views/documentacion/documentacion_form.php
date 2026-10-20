<?php
$reg    = $registro ?? null;
$editar = $reg !== null;

// Valor de un campo: lo reenviado (old) > el registro en edición > lo precargado desde "sin documentar"
$valor = static fn (string $campo, $defecto = '') => old($campo, $reg ? ($reg->$campo ?? $defecto) : ($prefill[$campo] ?? $defecto));

// Filas de validaciones: las reenviadas tras un error, o las guardadas, o una fila vacía
$validaciones = [];
if (old('validacion_campo') !== null) {
    foreach ((array) old('validacion_campo') as $i => $campo) {
        $validaciones[] = [
            'campo'   => $campo,
            'regla'   => old('validacion_regla')[$i] ?? '',
            'mensaje' => old('validacion_mensaje')[$i] ?? '',
        ];
    }
} elseif ($reg) {
    $validaciones = $reg->getValidacionesLista();
}
if (!$validaciones) {
    $validaciones = [['campo' => '', 'regla' => '', 'mensaje' => '']];
}

$moduloActual = $valor('modulo');
$vistaActual  = $valor('vista');

// Pantallas por módulo para el select dependiente (JS)
$vistasPorModulo = array_map(static fn ($m) => $m['vistas'], $modulos);
?>
<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= $editar ? 'EDITAR' : 'NUEVA' ?> PÁGINA DEL MANUAL <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="/plugins/summernote/summernote-lite.min.css">
<link rel="stylesheet" href="/css/panel.css">
<link rel="stylesheet" href="/css/manual.css?v=9">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-form-wrap">

    <?= view('partials/breadcrumb', ['nodo' => 'documentacion_list', 'actual' => $editar ? 'Editar página' : 'Nueva página']) ?>

    <div class="bf-panel is-wide">
        <div class="bf-panel-header">
            <span class="bf-panel-title">
                <i class="fas fa-<?= $editar ? 'edit' : 'plus-circle' ?>"></i>
                <?= $editar ? 'Editar página del manual' : 'Nueva página del manual' ?>
            </span>
            <?php if ($editar): ?>
                <span class="bl-tag <?= $reg->estado === 'activo' ? 'green' : 'gray' ?>"><?= $reg->estado === 'activo' ? 'Publicada' : 'Borrador' ?></span>
            <?php endif; ?>
        </div>

        <div class="bf-panel-body">

            <div class="bf-notice is-info">
                <i class="fas fa-circle-info"></i>
                <span>Cada página documenta <strong>una pantalla</strong> del sistema. Este sistema carga el manual
                    <strong><?= esc(mb_strtolower($roles[$rolSistema])) ?></strong>; las páginas de "Ambos roles" aparecen también en el manual del otro sistema.</span>
            </div>

            <?= $this->include('partials/form_errores') ?>

            <form action="<?= base_url(route_to($editar ? 'documentacion_update' : 'documentacion_store')) ?>" method="POST" enctype="multipart/form-data" id="formManual">
                <?= csrf_field() ?>
                <?= $editar ? $reg->getCampoOculto() : '' ?>

                <!-- ── 1. UBICACIÓN ── -->
                <div class="bf-section"><i class="fas fa-sitemap"></i> Ubicación en el sistema</div>

                <div class="bf-cols cols-3">
                    <div class="bf-field">
                        <label class="bf-label">Módulo <span class="bf-required">*</span></label>
                        <select name="modulo" id="sel-modulo" class="bf-select <?= session('errors.modulo') ? 'has-error' : '' ?>">
                            <option value="">Seleccionar módulo...</option>
                            <?php foreach ($modulos as $clave => $m): ?>
                                <option value="<?= esc($clave) ?>" <?= $moduloActual === $clave ? 'selected' : '' ?>><?= esc($m['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (session('errors.modulo')): ?>
                            <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.modulo') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="bf-field">
                        <label class="bf-label">Pantalla (vista) <span class="bf-required">*</span></label>
                        <select name="vista" id="sel-vista" class="bf-select <?= session('errors.vista') ? 'has-error' : '' ?>">
                            <option value="">Primero elegí el módulo...</option>
                            <?php foreach ($modulos[$moduloActual]['vistas'] ?? [] as $clave => $nombre): ?>
                                <option value="<?= esc($clave) ?>" <?= $vistaActual === $clave ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="bf-help"><i class="fas fa-info-circle"></i> Las pantallas salen del menú del sistema.</div>
                        <?php if (session('errors.vista')): ?>
                            <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.vista') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="bf-field">
                        <label class="bf-label">Tipo de vista <span class="bf-required">*</span></label>
                        <select name="tipo_vista" class="bf-select <?= session('errors.tipo_vista') ? 'has-error' : '' ?>">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($tipos as $clave => $nombre): ?>
                                <option value="<?= $clave ?>" <?= $valor('tipo_vista') === $clave ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (session('errors.tipo_vista')): ?>
                            <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.tipo_vista') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="bf-field">
                        <label class="bf-label">Rol <span class="bf-required">*</span></label>
                        <select name="rol" class="bf-select <?= session('errors.rol') ? 'has-error' : '' ?>">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($roles as $clave => $nombre): ?>
                                <option value="<?= $clave ?>" <?= $valor('rol') === $clave ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="bf-help"><i class="fas fa-info-circle"></i> "Ambos roles" aparece en los dos manuales (este sistema y el otro).</div>
                        <?php if (session('errors.rol')): ?>
                            <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.rol') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="bf-field">
                        <label class="bf-label">Orden dentro del módulo</label>
                        <div class="bf-input-wrap">
                            <i class="fas fa-sort-numeric-down bf-input-icon"></i>
                            <input class="bf-input <?= session('errors.orden') ? 'has-error' : '' ?>" type="number" name="orden" min="0" placeholder="0"
                                   value="<?= esc($valor('orden', 0)) ?>">
                        </div>
                        <?php if (session('errors.orden')): ?>
                            <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.orden') ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="bf-field">
                        <label class="bf-label">Versión (opcional)</label>
                        <div class="bf-input-wrap">
                            <i class="fas fa-code-branch bf-input-icon"></i>
                            <input class="bf-input" type="text" name="version" maxlength="20" placeholder="Ej: 1.0"
                                   value="<?= esc($valor('version')) ?>">
                        </div>
                    </div>

                    <?php if ($editar): ?>
                        <div class="bf-field">
                            <label class="bf-label">Estado</label>
                            <select name="estado" class="bf-select">
                                <option value="activo"      <?= $reg->estado === 'activo' ? 'selected' : '' ?>>Publicada</option>
                                <option value="desactivado" <?= $reg->estado === 'desactivado' ? 'selected' : '' ?>>Borrador (no se muestra)</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ── 2. CONTENIDO ── -->
                <div class="bf-section"><i class="fas fa-file-lines"></i> Contenido</div>

                <div class="bf-field">
                    <label class="bf-label">Título <span class="bf-required">*</span></label>
                    <div class="bf-input-wrap">
                        <i class="fas fa-heading bf-input-icon"></i>
                        <input class="bf-input <?= session('errors.titulo') ? 'has-error' : '' ?>" type="text" name="titulo" maxlength="150"
                               placeholder="Ej: Cómo cargar una nueva base"
                               value="<?= esc($valor('titulo')) ?>">
                    </div>
                    <?php if (session('errors.titulo')): ?>
                        <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.titulo') ?></div>
                    <?php endif; ?>
                </div>

                <div class="bf-field">
                    <label class="bf-label">Resumen (opcional)</label>
                    <textarea class="bf-textarea <?= session('errors.resumen') ? 'has-error' : '' ?>" name="resumen" id="resumen" maxlength="300" rows="2"
                              style="min-height:64px;" placeholder="Una o dos líneas: para qué sirve esta pantalla."><?= esc($valor('resumen')) ?></textarea>
                    <div class="bf-help"><i class="fas fa-info-circle"></i> Aparece en el índice y en el buscador · <span id="contadorResumen">0</span>/300</div>
                    <?php if (session('errors.resumen')): ?>
                        <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.resumen') ?></div>
                    <?php endif; ?>
                </div>

                <div class="bf-field">
                    <label class="bf-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion"><?= esc(old('descripcion', $reg->descripcion ?? '')) ?></textarea>
                    <div class="bf-help"><i class="fas fa-info-circle"></i> Explicá paso a paso cómo se usa la pantalla. Podés usar títulos, listas, tablas y negritas. La captura va en su propio campo, abajo.</div>
                </div>

                <!-- ── 3. VALIDACIONES ── -->
                <div class="bf-section"><i class="fas fa-list-check"></i> Validaciones de la pantalla</div>

                <div class="bf-help" style="margin:-6px 0 12px;"><i class="fas fa-info-circle"></i> Una fila por regla: qué campo, qué se controla y el mensaje que ve el usuario si no se cumple.</div>

                <div class="bl-table-wrap mn-validaciones">
                    <table class="bl-table" id="tablaValidaciones">
                        <thead>
                            <tr>
                                <th style="width:24%;">Campo</th>
                                <th style="width:36%;">Regla</th>
                                <th>Mensaje que ve el usuario</th>
                                <th class="col-acc"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($validaciones as $v): ?>
                            <tr>
                                <td><input class="bf-input" type="text" name="validacion_campo[]" maxlength="100" placeholder="Ej: Ejercicio" value="<?= esc($v['campo'] ?? '') ?>"></td>
                                <td><textarea class="bf-input" name="validacion_regla[]" maxlength="300" rows="1" placeholder="Ej: Obligatorio, no mayor al año actual"><?= esc($v['regla'] ?? '') ?></textarea></td>
                                <td><textarea class="bf-input" name="validacion_mensaje[]" maxlength="300" rows="1" placeholder="Ej: El ejercicio no puede ser mayor a <?= date('Y') ?>."><?= esc($v['mensaje'] ?? '') ?></textarea></td>
                                <td class="col-acc"><button type="button" class="bl-btn danger-ghost sm btn-quitar-fila" title="Quitar fila"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin:10px 0 6px;">
                    <button type="button" class="bl-btn teal-ghost sm" id="btnAgregarValidacion"><i class="fas fa-plus"></i> Agregar validación</button>
                </div>

                <!-- ── 4. CAPTURA ── -->
                <div class="bf-section"><i class="fas fa-image"></i> Captura de pantalla</div>

                <?php if ($editar && $reg->imagen): ?>
                    <div class="mn-captura-actual">
                        <img src="<?= esc($reg->getImagenUrl(), 'attr') ?>" alt="Captura actual">
                        <div>
                            <strong>Captura actual</strong>
                            <div class="bf-help">Subí otra para reemplazarla.</div>
                            <label class="bf-check" style="margin-top:8px;">
                                <input type="checkbox" name="quitar_imagen" value="1"> Quitar la captura
                            </label>
                        </div>
                    </div>
                <?php endif; ?>

                <label class="bf-upload" for="imagen">
                    <i class="fas fa-camera"></i>
                    <strong>Seleccioná o arrastrá la captura</strong>
                    <span class="bf-help" style="justify-content:center;">PNG, JPG, WEBP o GIF · hasta 5 MB</span>
                    <input type="file" id="imagen" name="imagen" accept=".png,.jpg,.jpeg,.webp,.gif">
                </label>
                <?php if (session('errors.imagen')): ?>
                    <div class="bf-error"><i class="fas fa-exclamation-circle"></i> <?= session('errors.imagen') ?></div>
                <?php endif; ?>
                <div class="mn-preview" id="previewCaptura"><img src="" alt="Vista previa de la captura"></div>

                <hr class="bf-divider">

                <div class="bf-actions">
                    <button type="submit" class="bf-btn teal">
                        <i class="fas fa-save"></i> <?= $editar ? 'Guardar cambios' : 'Guardar' ?>
                    </button>
                    <button type="submit" name="ver" value="1" class="bf-btn navy">
                        <i class="fas fa-eye"></i> Guardar y ver en la documentación
                    </button>
                    <a href="<?= base_url(route_to('documentacion_list')); ?>" class="bf-btn ghost">
                        <i class="fas fa-list"></i> Volver al listado
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fila modelo para agregar validaciones -->
<template id="filaValidacion">
    <tr>
        <td><input class="bf-input" type="text" name="validacion_campo[]" maxlength="100" placeholder="Ej: Ejercicio"></td>
        <td><textarea class="bf-input" name="validacion_regla[]" maxlength="300" rows="1" placeholder="Ej: Obligatorio"></textarea></td>
        <td><textarea class="bf-input" name="validacion_mensaje[]" maxlength="300" rows="1" placeholder="Mensaje que ve el usuario"></textarea></td>
        <td class="col-acc"><button type="button" class="bl-btn danger-ghost sm btn-quitar-fila" title="Quitar fila"><i class="fas fa-trash"></i></button></td>
    </tr>
</template>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="/plugins/summernote/summernote-lite.min.js"></script>
<script src="/plugins/summernote/lang/summernote-es-ES.min.js"></script>
<script>
(function () {
    // ── Editor tipo Word ──
    $('#descripcion').summernote({
        lang: 'es-ES',
        height: 320,
        placeholder: 'Describí la pantalla: para qué sirve, cómo se usa paso a paso, qué hacer si aparece un error...',
        toolbar: [
            ['estilo', ['style']],
            ['fuente', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['color', ['forecolor', 'backcolor']],
            ['parrafo', ['ul', 'ol', 'paragraph']],
            ['insertar', ['table', 'link', 'hr']],
            ['ver', ['undo', 'redo', 'fullscreen']]
        ],
        styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
        callbacks: {
            // Las imágenes no van dentro del texto: van en el campo "Captura de pantalla"
            onImageUpload: function () {
                Swal.fire({ icon: 'info', title: '', text: 'Las capturas se cargan en el campo "Captura de pantalla", debajo de las validaciones.' });
            }
        }
    });

    // ── Módulo → pantallas ──
    var vistas   = <?= json_encode($vistasPorModulo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
    var selMod   = document.getElementById('sel-modulo');
    var selVista = document.getElementById('sel-vista');

    selMod.addEventListener('change', function () {
        selVista.innerHTML = '';
        var lista = vistas[this.value] || {};
        var vacia = document.createElement('option');
        vacia.value = '';
        vacia.textContent = this.value ? 'Seleccionar pantalla...' : 'Primero elegí el módulo...';
        selVista.appendChild(vacia);
        Object.keys(lista).forEach(function (clave) {
            var op = document.createElement('option');
            op.value = clave;
            op.textContent = lista[clave];
            selVista.appendChild(op);
        });
    });

    // ── Contador del resumen ──
    var resumen  = document.getElementById('resumen');
    var contador = document.getElementById('contadorResumen');
    function contar() { contador.textContent = resumen.value.length; }
    resumen.addEventListener('input', contar);
    contar();

    // ── Validaciones: agregar / quitar filas ──
    var tbody = document.querySelector('#tablaValidaciones tbody');
    document.getElementById('btnAgregarValidacion').addEventListener('click', function () {
        tbody.appendChild(document.getElementById('filaValidacion').content.cloneNode(true));
        tbody.lastElementChild.querySelector('input').focus();
    });
    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-quitar-fila');
        if (!btn) return;
        var fila = btn.closest('tr');
        if (tbody.rows.length > 1) {
            fila.remove();
        } else {
            fila.querySelectorAll('input, textarea').forEach(function (c) { c.value = ''; });
        }
    });

    // ── Vista previa de la captura ──
    var inputImg = document.getElementById('imagen');
    var preview  = document.getElementById('previewCaptura');
    inputImg.addEventListener('change', function () {
        var archivo = this.files && this.files[0];
        if (!archivo || !archivo.type.startsWith('image/')) {
            preview.style.display = 'none';
            return;
        }
        preview.querySelector('img').src = URL.createObjectURL(archivo);
        preview.style.display = 'block';
    });
})();
</script>
<?= $this->endSection() ?>
