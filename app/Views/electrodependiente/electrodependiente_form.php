<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVO' ?> ELECTRODEPENDIENTE <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ══════════════════════════════════════════
       MOBILE ≤ 768px
    ══════════════════════════════════════════ */
    @media (max-width: 768px) {
        .section { padding: 0.75rem 0.5rem !important; }
        .container.is-max-widescreen { padding: 0 !important; }
        .notification { padding: 0.85rem !important; }
        .columns { display: block !important; }
        .column  { padding: 0.3rem 0 !important; width: 100% !important; }
        .input, .select select, .textarea { font-size: 1rem !important; min-height: 44px; }
        .select { width: 100% !important; }
        .select select { width: 100% !important; }
        h2.subtitle { font-size: 1.1rem !important; }
        .bloque-titulo { font-size: 0.85rem; }
        .field.is-grouped { flex-wrap: wrap; gap: 8px; }
        .field.is-grouped .control { flex: 1; min-width: 140px; }
        .field.is-grouped .button { width: 100%; justify-content: center; }
        hr { margin: 0.6rem 0 !important; }
        .equipo-card { padding: 0.6rem !important; }
    }

    .bloque-titulo {
        color: #ffe08a;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        letter-spacing: 0.04em;
    }

    /* ── CONTACTO / COORDENADAS ── */
    #inp_contacto.input-error,
    #inp_coordenadas.input-error { border-color: #e53e3e !important; box-shadow: 0 0 0 2px rgba(229,62,62,0.25) !important; }
    .contacto-hint, .coordenadas-hint { font-size: 0.75rem; margin-top: 3px; display: none; color: #e53e3e; }
    .contacto-hint.visible, .coordenadas-hint.visible { display: block; }

    /* ── EQUIPAMIENTO ── */
    .equipo-card {
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 8px;
        padding: 0.85rem 1rem;
        margin-bottom: 0.75rem;
        position: relative;
    }
    .equipo-card .equipo-num {
        color: #ffe08a;
        font-weight: 700;
        font-size: 0.82rem;
        margin-bottom: 0.5rem;
        letter-spacing: 0.03em;
    }
    .btn-quitar-equipo {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
    #lista-equipos { margin-top: 0.5rem; }
    #btn-agregar-equipo {
        margin-top: 0.4rem;
    }
    .sin-equipos-msg {
        color: rgba(255,255,255,0.45);
        font-size: 0.83rem;
        font-style: italic;
        padding: 0.4rem 0;
    }
</style>

<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Electrodependiente' : 'Nuevo Electrodependiente' ?>
</h2>

<form id="form-electro"
      action="<?= isset($registro) ? base_url(route_to('electrodependiente_update')) : base_url(route_to('electrodependiente_store')) ?>"
      method="POST">
    <?= csrf_field() ?>
    <?php if (isset($registro)): ?>
        <?= $registro->getCampoOculto() ?>
    <?php endif; ?>

    <div class="container is-max-widescreen">
    <div class="notification" style="background-color: #13304d;">

        <?php if (session()->has('msg')): $msg = session('msg'); ?>
            <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
        <?php endif; ?>
        <?php if (session()->has('errors')): ?>
            <div class="notification is-danger is-light">
                <ul><?php foreach (session('errors') as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php
        $registro = $registro ?? null;
        $v = function($campo, $default = '') use ($registro) {
            return old($campo, isset($registro) ? ($registro->$campo ?? $default) : $default);
        };
        ?>

        <!-- ══════════════════════════════════════
             DATOS PERSONALES
        ══════════════════════════════════════ -->
        <p class="bloque-titulo">DATOS PERSONALES</p>

        <div class="columns is-multiline">

            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Paciente *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.paciente') ? 'is-danger' : '' ?>"
                            type="text" name="paciente" maxlength="200"
                            placeholder="Apellido y Nombre"
                            value="<?= esc($v('paciente')) ?>">
                        <span class="icon is-left"><i class="fas fa-user"></i></span>
                    </div>
                    <?php if (session('errors.paciente')): ?>
                        <p class="help is-danger"><?= session('errors.paciente') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column is-6-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">DNI</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="dni" maxlength="20"
                            placeholder="Nº de documento"
                            value="<?= esc($v('dni')) ?>">
                        <span class="icon is-left"><i class="fas fa-id-card"></i></span>
                    </div>
                </div>
            </div>

            <div class="column is-6-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">F. Nacimiento</label>
                    <div class="control has-icons-left">
                        <input class="input" type="date" name="fecha_nacimiento" id="inp_fnac"
                            value="<?= esc($v('fecha_nacimiento')) ?>">
                        <span class="icon is-left"><i class="fas fa-calendar"></i></span>
                    </div>
                </div>
            </div>

            <div class="column is-4-mobile is-1-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Edad</label>
                    <div class="control">
                        <input class="input" type="number" name="edad" id="inp_edad"
                            min="0" max="130"
                            value="<?= esc($v('edad')) ?>">
                    </div>
                </div>
            </div>

            <div class="column is-4-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">
                        Tipo
                        <span style="font-size:0.75rem; color:#ffe08a;" id="tipo-auto-hint"></span>
                    </label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="tipo" id="sel_tipo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tipos as $t): ?>
                                    <option value="<?= $t ?>" <?= $v('tipo') == $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-4-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">CUD</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="cud">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($cudOpciones as $c): ?>
                                    <option value="<?= $c ?>" <?= $v('cud') == $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══════════════════════════════════════
             CONTACTO Y DOMICILIO
        ══════════════════════════════════════ -->
        <div class="columns is-multiline">

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Contacto</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="contacto" id="inp_contacto"
                            maxlength="200" placeholder="Ej: 0388 4123456"
                            value="<?= esc($v('contacto')) ?>">
                        <span class="icon is-left"><i class="fas fa-phone"></i></span>
                    </div>
                    <p class="contacto-hint" id="contacto-hint">
                        <i class="fas fa-exclamation-circle"></i>
                        Solo se permiten números, espacios y: + - / ( )
                    </p>
                </div>
            </div>

            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Domicilio</label>
                    <div class="control has-icons-left">
                        <input class="input" type="text" name="domicilio" maxlength="255"
                            placeholder="Calle, número, barrio"
                            value="<?= esc($v('domicilio')) ?>">
                        <span class="icon is-left"><i class="fas fa-map-marker-alt"></i></span>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Coordenadas *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.coordenadas') ? 'is-danger' : '' ?>"
                            type="text" name="coordenadas" id="inp_coordenadas"
                            maxlength="100" placeholder="-24.385, -65.115"
                            value="<?= esc($v('coordenadas')) ?>">
                        <span class="icon is-left"><i class="fas fa-map-pin"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">
                        <i class="fas fa-info-circle"></i>
                        Ejemplo: <strong style="color:#ffe08a;">-24.385, -65.115</strong> (lat, lon)
                    </p>
                    <p class="coordenadas-hint" id="coordenadas-hint">
                        <i class="fas fa-exclamation-circle"></i>
                        Solo se permiten números, punto, coma, espacio y ( - )
                    </p>
                    <?php if (session('errors.coordenadas')): ?>
                        <p class="help is-danger"><?= session('errors.coordenadas') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column is-12-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Localidad</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="localidad_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($localidades as $l): ?>
                                    <option value="<?= $l['localidad_id'] ?>" <?= $v('localidad_id') == $l['localidad_id'] ? 'selected' : '' ?>>
                                        <?= esc($l['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <hr>

        <!-- ══════════════════════════════════════
             DATOS MÉDICOS
        ══════════════════════════════════════ -->
        <p class="bloque-titulo">DATOS MÉDICOS</p>

        <div class="columns is-multiline">

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Hospital de Referencia</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="efector_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($efectores as $ef): ?>
                                    <option value="<?= $ef->efector_id ?>" <?= $v('efector_id') == $ef->efector_id ? 'selected' : '' ?>>
                                        <?= esc($ef->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Obra Social / Programa</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="obra_social_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($obras_sociales as $os): ?>
                                    <option value="<?= $os['obra_social_id'] ?>" <?= $v('obra_social_id') == $os['obra_social_id'] ? 'selected' : '' ?>>
                                        <?= esc($os['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Diagnóstico</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="diagnostico_id">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($diagnosticos as $dx): ?>
                                    <option value="<?= $dx['diagnostico_id'] ?>" <?= $v('diagnostico_id') == $dx['diagnostico_id'] ? 'selected' : '' ?>>
                                        <?= esc($dx['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Factor de Riesgo</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="factor_riesgo">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($factores as $f): ?>
                                    <option value="<?= $f ?>" <?= $v('factor_riesgo') == $f ? 'selected' : '' ?>><?= $f ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ══════════════════════════════════════
             DX COMPLEMENTARIO / OBSERVACIÓN / SEGUIMIENTO
        ══════════════════════════════════════ -->
        <div class="columns is-multiline">

            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label" style="color:white;">DX Complementario</label>
                    <div class="control">
                        <textarea class="textarea" name="dx_complementario" rows="2"
                            placeholder="Diagnósticos secundarios..."><?= esc($v('dx_complementario')) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-4-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Observación</label>
                    <div class="control">
                        <textarea class="textarea" name="observacion" rows="2"
                            placeholder="Observaciones..."><?= esc($v('observacion')) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="column is-12-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Seguimiento</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="seguimiento">
                                <option value="">Seleccionar...</option>
                                <?php foreach ($seguimientos as $s): ?>
                                    <option value="<?= $s ?>" <?= $v('seguimiento') == $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($registro)): ?>
            <div class="column is-12-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="estado">
                                <option value="activo"      <?= ($registro->estado ?? 'activo') == 'activo' ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= ($registro->estado ?? '') == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="estado" value="activo">
            <?php endif; ?>

        </div>

        <hr>

        <!-- ══════════════════════════════════════
             EQUIPAMIENTO
        ══════════════════════════════════════ -->
        <p class="bloque-titulo">
            <span class="icon"><i class="fas fa-plug"></i></span>
            EQUIPAMIENTO
            <span style="font-size:0.75rem; color:rgba(255,255,255,0.5); font-weight:400; margin-left:8px;">
                (podés agregar uno o más equipos)
            </span>
        </p>

        <div id="lista-equipos">
            <?php if (!empty($equipos)): ?>
                <?php foreach ($equipos as $idx => $eq): ?>
                    <?= view('electrodependiente/_equipo_fila', [
                        'idx'        => $idx,
                        'eq'         => $eq,
                        'tiemposUso' => $tiemposUso,
                    ]) ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="sin-equipos-msg" id="sin-equipos-msg">
                    <span class="icon"><i class="fas fa-info-circle"></i></span>
                    Sin equipos cargados. Usá el botón para agregar.
                </p>
            <?php endif; ?>
        </div>

        <button type="button" class="button is-info is-small is-outlined" id="btn-agregar-equipo">
            <span class="icon"><i class="fas fa-plus-circle"></i></span>
            <span>Agregar equipo</span>
        </button>

        <hr>

        <!-- ── BOTONES ── -->
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit" id="btn-guardar">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($registro) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('electrodependiente_list')) ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>

    </div>
    </div>
</form>
</section>

<!-- ══════════════════════════════════════
     TEMPLATE OCULTO — se clona con JS
══════════════════════════════════════ -->
<template id="tpl-equipo">
    <?= view('electrodependiente/_equipo_fila', [
        'idx'        => '__IDX__',
        'eq'         => [],
        'tiemposUso' => $tiemposUso,
    ]) ?>
</template>

<script>
// ══════════════════════════════════════════════════════
//  EDAD ↔ TIPO
// ══════════════════════════════════════════════════════
var inpFnac  = document.getElementById('inp_fnac');
var inpEdad  = document.getElementById('inp_edad');
var selTipo  = document.getElementById('sel_tipo');
var tipoHint = document.getElementById('tipo-auto-hint');

function asignarTipo(edad) {
    if (isNaN(edad) || edad < 0) return;
    selTipo.value = edad >= 15 ? 'ADULTO' : 'NIÑO';
    tipoHint.textContent = '(auto)';
    clearTimeout(asignarTipo._t);
    asignarTipo._t = setTimeout(function(){ tipoHint.textContent = ''; }, 3000);
}
function calcularDesdefnac() {
    var fnac = new Date(inpFnac.value);
    if (isNaN(fnac.getTime())) return;
    var hoy = new Date(), edad = hoy.getFullYear() - fnac.getFullYear();
    var m = hoy.getMonth() - fnac.getMonth();
    if (m < 0 || (m === 0 && hoy.getDate() < fnac.getDate())) edad--;
    if (edad < 0) return;
    inpEdad.value = edad;
    asignarTipo(edad);
}
inpFnac.addEventListener('change', calcularDesdefnac);
inpEdad.addEventListener('change', function(){ asignarTipo(parseInt(this.value, 10)); });

// ══════════════════════════════════════════════════════
//  CONTACTO
// ══════════════════════════════════════════════════════
var inpContacto  = document.getElementById('inp_contacto');
var contactoHint = document.getElementById('contacto-hint');
var CONTACTO_RE  = /^[0-9 +\-\/()]*$/;

function validarContacto() {
    var v = inpContacto.value, l = v.replace(/[^0-9 +\-\/()]/g, '');
    if (l !== v) {
        inpContacto.value = l;
        inpContacto.classList.add('input-error');
        contactoHint.classList.add('visible');
        clearTimeout(validarContacto._t);
        validarContacto._t = setTimeout(function(){ inpContacto.classList.remove('input-error'); contactoHint.classList.remove('visible'); }, 2500);
    }
}
inpContacto.addEventListener('keypress', function(e){
    if (!CONTACTO_RE.test(String.fromCharCode(e.which||e.keyCode))) {
        e.preventDefault();
        inpContacto.classList.add('input-error'); contactoHint.classList.add('visible');
        clearTimeout(validarContacto._t);
        validarContacto._t = setTimeout(function(){ inpContacto.classList.remove('input-error'); contactoHint.classList.remove('visible'); }, 2000);
    }
});
inpContacto.addEventListener('input', validarContacto);

// ══════════════════════════════════════════════════════
//  COORDENADAS
// ══════════════════════════════════════════════════════
var inpCoordenadas  = document.getElementById('inp_coordenadas');
var coordenadasHint = document.getElementById('coordenadas-hint');
var COORD_RE = /^[0-9.,\s\-]*$/;

function validarCoordenadas() {
    var v = inpCoordenadas.value, l = v.replace(/[^0-9.,\s\-]/g, '');
    if (l !== v) {
        inpCoordenadas.value = l;
        inpCoordenadas.classList.add('input-error'); coordenadasHint.classList.add('visible');
        clearTimeout(validarCoordenadas._t);
        validarCoordenadas._t = setTimeout(function(){ inpCoordenadas.classList.remove('input-error'); coordenadasHint.classList.remove('visible'); }, 2500);
    }
}
inpCoordenadas.addEventListener('keypress', function(e){
    if (!COORD_RE.test(String.fromCharCode(e.which||e.keyCode))) {
        e.preventDefault();
        inpCoordenadas.classList.add('input-error'); coordenadasHint.classList.add('visible');
        clearTimeout(validarCoordenadas._t);
        validarCoordenadas._t = setTimeout(function(){ inpCoordenadas.classList.remove('input-error'); coordenadasHint.classList.remove('visible'); }, 2000);
    }
});
inpCoordenadas.addEventListener('input', validarCoordenadas);

// ── Validación al enviar ──
document.getElementById('form-electro').addEventListener('submit', function(e) {
    var vc = inpContacto.value.trim();
    if (vc !== '' && !CONTACTO_RE.test(vc)) {
        e.preventDefault(); inpContacto.classList.add('input-error'); contactoHint.classList.add('visible'); inpContacto.focus(); return;
    }
    var vco = inpCoordenadas.value.trim();
    if (vco === '') {
        e.preventDefault(); inpCoordenadas.classList.add('input-error');
        coordenadasHint.textContent = 'Las coordenadas son obligatorias.'; coordenadasHint.classList.add('visible'); inpCoordenadas.focus(); return;
    }
    if (!COORD_RE.test(vco)) {
        e.preventDefault(); inpCoordenadas.classList.add('input-error'); coordenadasHint.classList.add('visible'); inpCoordenadas.focus();
    }
});

// ══════════════════════════════════════════════════════
//  EQUIPAMIENTO DINÁMICO
// ══════════════════════════════════════════════════════
var listaEquipos = document.getElementById('lista-equipos');
var sinEquiposMsg = document.getElementById('sin-equipos-msg');
var tpl = document.getElementById('tpl-equipo');
var contadorEquipos = <?= count($equipos ?? []) ?>;

function actualizarNumeros() {
    var cards = listaEquipos.querySelectorAll('.equipo-card');
    cards.forEach(function(card, i) {
        var numEl = card.querySelector('.equipo-num');
        if (numEl) numEl.textContent = 'EQUIPO ' + (i + 1);
    });
}

function quitarEquipo(btn) {
    var card = btn.closest('.equipo-card');
    if (card) {
        card.remove();
        actualizarNumeros();
        // Mostrar mensaje si no quedan equipos
        if (listaEquipos.querySelectorAll('.equipo-card').length === 0) {
            if (!document.getElementById('sin-equipos-msg')) {
                var msg = document.createElement('p');
                msg.className = 'sin-equipos-msg';
                msg.id = 'sin-equipos-msg';
                msg.innerHTML = '<span class="icon"><i class="fas fa-info-circle"></i></span> Sin equipos cargados. Usá el botón para agregar.';
                listaEquipos.appendChild(msg);
            }
        }
    }
}

document.getElementById('btn-agregar-equipo').addEventListener('click', function() {
    // Ocultar mensaje "sin equipos"
    var msgEl = document.getElementById('sin-equipos-msg');
    if (msgEl) msgEl.remove();

    var html = tpl.innerHTML.replace(/__IDX__/g, contadorEquipos);
    contadorEquipos++;

    var div = document.createElement('div');
    div.innerHTML = html;
    var card = div.firstElementChild;
    listaEquipos.appendChild(card);
    actualizarNumeros();

    // Focus en primer campo del nuevo equipo
    var primerInput = card.querySelector('input, select');
    if (primerInput) primerInput.focus();
});

// Delegación de eventos para botones "quitar"
listaEquipos.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-quitar-equipo');
    if (btn) quitarEquipo(btn);
});
</script>

<?= $this->endSection() ?>