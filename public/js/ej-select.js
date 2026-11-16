// Desplegables con estilo para los filtros de todos los módulos.
// Se aplica a los <select> de filtros: clases que terminan en "filtro-select" (ml-, rh-, at-, bl-…)
// y los de .ej-filtro (EJESA y electrodependientes). Un <select> con data-nativo queda como está.
// El <select> original queda oculto debajo y sigue siendo el que se envía y el que escucha cada página
// ('change'), así que los filtros funcionan igual. Si la página cambia las opciones, el valor o lo
// deshabilita por código, el desplegable se actualiza solo.
// La lista se dibuja sobre la página (position: fixed) para que ningún panel la recorte.
// Teclado: Enter/Espacio/flechas abren y recorren, Escape cierra, letras saltan a la opción.
// Estilos: public/css/ej-select.css (cargado en el layout).
(function () {
    'use strict';
    var SELECTOR = 'select[class*="filtro-select"], .ej-filtro select';
    var abierto = null;   // { caja, lista, boton, ubicar }
    var todos = [];       // funciones de sincronización de cada desplegable

    function cerrar(volverFoco) {
        if (!abierto) return;
        var a = abierto; abierto = null;
        a.lista.hidden = true;
        a.boton.setAttribute('aria-expanded', 'false');
        a.caja.classList.remove('abierto');
        if (volverFoco) a.boton.focus();
    }
    document.addEventListener('click', function (e) {
        if (abierto && !abierto.caja.contains(e.target) && !abierto.lista.contains(e.target)) cerrar(false);
    });
    // Al desplazar la página la lista acompaña al botón; si el botón sale de la vista, se cierra
    window.addEventListener('scroll', function (e) {
        if (!abierto || abierto.lista.contains(e.target)) return;
        abierto.ubicar();
    }, true);
    window.addEventListener('resize', function () { if (abierto) abierto.ubicar(); });

    function mejorar(sel, n) {
        if (sel.dataset.ejSelect || sel.multiple || sel.size > 1 || sel.hasAttribute('data-nativo')) return;
        sel.dataset.ejSelect = '1';

        // La caja lleva las mismas clases que el select: así toma el ancho, el flex y los márgenes que cada
        // módulo define (también los de celular). Bordes, fondo y relleno de esas clases se anulan en el CSS.
        var alto = sel.offsetHeight;
        var ancho = sel.offsetWidth;
        var padre = sel.parentElement;

        var caja = document.createElement('div');
        caja.className = ('ej-select ' + sel.className).trim();
        // Como el select nativo: al menos el ancho que ocupaba (nunca más que su contenedor)
        if (ancho > 0) caja.style.minWidth = ancho + 'px';

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'ej-select-boton';
        boton.id = (sel.id || 'ej-sel-' + n) + '-boton';
        boton.setAttribute('aria-haspopup', 'listbox');
        boton.setAttribute('aria-expanded', 'false');
        if (alto >= 28 && alto <= 60) boton.style.height = alto + 'px';
        if (sel.title) boton.title = sel.title;
        var texto = document.createElement('span');
        texto.className = 'ej-select-texto';
        var flecha = document.createElement('i');
        flecha.className = 'fas fa-chevron-down';
        flecha.setAttribute('aria-hidden', 'true');
        boton.append(texto, flecha);

        var lista = document.createElement('ul');
        lista.className = 'ej-select-lista';
        lista.setAttribute('role', 'listbox');
        lista.hidden = true;
        document.body.appendChild(lista);

        var etiqueta = sel.id ? document.querySelector('label[for="' + sel.id + '"]') : null;
        if (etiqueta) {
            lista.setAttribute('aria-label', etiqueta.textContent.trim());
            etiqueta.setAttribute('for', boton.id);
        } else if (sel.getAttribute('aria-label')) {
            boton.setAttribute('aria-label', sel.getAttribute('aria-label'));
            lista.setAttribute('aria-label', sel.getAttribute('aria-label'));
        }

        sel.classList.add('ej-select-original');
        sel.setAttribute('tabindex', '-1');
        sel.setAttribute('aria-hidden', 'true');
        sel.parentNode.insertBefore(caja, sel);
        caja.append(boton, sel);
        // Bulma: <div class="select"> dibuja su propia flecha y fija el alto
        if (padre && padre.classList.contains('select')) padre.classList.add('con-ej-select');

        var items = [];
        var actual = -1;

        // Arma la lista con las opciones actuales (incluye grupos y opciones deshabilitadas)
        function armar() {
            lista.replaceChildren();
            items = [];
            var grupoPrevio = null;
            Array.prototype.forEach.call(sel.options, function (op, i) {
                if (op.hidden) return;
                var grupo = op.parentElement && op.parentElement.tagName === 'OPTGROUP' ? op.parentElement : null;
                if (grupo && grupo !== grupoPrevio) {
                    var cab = document.createElement('li');
                    cab.className = 'ej-select-grupo';
                    cab.setAttribute('role', 'presentation');
                    cab.textContent = grupo.label;
                    lista.append(cab);
                }
                grupoPrevio = grupo;
                var li = document.createElement('li');
                li.setAttribute('role', 'option');
                li.dataset.indice = i;
                li.className = 'ej-select-op' + (op.value === '' && i === 0 ? ' todos' : '');
                if (op.disabled || (grupo && grupo.disabled)) { li.classList.add('deshabilitada'); li.setAttribute('aria-disabled', 'true'); }
                var nombre = document.createElement('span');
                nombre.textContent = op.textContent;
                var tilde = document.createElement('i');
                tilde.className = 'fas fa-check ej-select-tilde';
                tilde.setAttribute('aria-hidden', 'true');
                li.append(nombre, tilde);
                li.addEventListener('click', function () { if (!li.classList.contains('deshabilitada')) elegir(i); });
                li.addEventListener('mousemove', function () { marcar(items.indexOf(li)); });
                lista.append(li);
                items.push(li);
            });
            items.forEach(function (li) { li.setAttribute('aria-selected', String(+li.dataset.indice === sel.selectedIndex)); });
        }

        var ultimo = { i: null, t: null, d: null };
        function pintar() {
            var op = sel.options[sel.selectedIndex];
            var t = op ? op.textContent : '';
            if (ultimo.i === sel.selectedIndex && ultimo.t === t && ultimo.d === sel.disabled) return;
            ultimo = { i: sel.selectedIndex, t: t, d: sel.disabled };
            texto.textContent = t;
            caja.classList.toggle('vacio', !op || op.value === '');
            boton.disabled = sel.disabled;
            caja.classList.toggle('deshabilitado', sel.disabled);
            items.forEach(function (li) { li.setAttribute('aria-selected', String(+li.dataset.indice === sel.selectedIndex)); });
        }
        function marcar(k) {
            if (k < 0 || k >= items.length) return;
            if (actual >= 0 && items[actual]) items[actual].classList.remove('foco');
            actual = k;
            items[k].classList.add('foco');
            var top = items[k].offsetTop, h = items[k].offsetHeight;
            if (top < lista.scrollTop) lista.scrollTop = top;
            else if (top + h > lista.scrollTop + lista.clientHeight) lista.scrollTop = top + h - lista.clientHeight;
        }
        function mover(paso) {
            var k = actual;
            for (var c = 0; c < items.length; c++) {
                k += paso;
                if (k < 0 || k >= items.length) return;
                if (!items[k].classList.contains('deshabilitada')) { marcar(k); return; }
            }
        }
        // Lista fija junto al botón: abajo si entra, si no arriba
        function ubicar() {
            var r = boton.getBoundingClientRect();
            if (r.bottom < 0 || r.top > window.innerHeight) { cerrar(false); return; }
            var vh = window.innerHeight, vw = document.documentElement.clientWidth;
            lista.style.minWidth = r.width + 'px';
            var altoLista = Math.min(lista.scrollHeight, 300);
            var abajo = vh - r.bottom, arriba = r.top;
            var haciaArriba = abajo < altoLista + 12 && arriba > abajo;
            lista.style.maxHeight = Math.max(120, Math.min(300, (haciaArriba ? arriba : abajo) - 16)) + 'px';
            lista.style.top = haciaArriba ? '' : (r.bottom + 6) + 'px';
            lista.style.bottom = haciaArriba ? (vh - r.top + 6) + 'px' : '';
            lista.classList.toggle('arriba', haciaArriba);
            var izq = Math.min(r.left, vw - lista.offsetWidth - 8);
            lista.style.left = Math.max(8, izq) + 'px';
        }
        function abrir() {
            if (sel.disabled) return;
            if (abierto && abierto.caja !== caja) cerrar(false);
            armar();
            abierto = { caja: caja, lista: lista, boton: boton, ubicar: ubicar };
            lista.hidden = false;
            caja.classList.add('abierto');
            boton.setAttribute('aria-expanded', 'true');
            ubicar();
            var k = items.findIndex(function (li) { return +li.dataset.indice === sel.selectedIndex; });
            actual = -1;
            marcar(Math.max(0, k));
        }
        function elegir(i) {
            var cambio = sel.selectedIndex !== i;
            sel.selectedIndex = i;
            pintar();
            cerrar(true);
            if (cambio) {
                sel.dispatchEvent(new Event('input', { bubbles: true }));
                sel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        boton.addEventListener('click', function () { if (abierto && abierto.caja === caja) cerrar(false); else abrir(); });
        boton.addEventListener('keydown', function (e) {
            var esta = abierto && abierto.caja === caja;
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!esta) { abrir(); return; }
                mover(e.key === 'ArrowDown' ? 1 : -1);
            } else if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (esta && items[actual] && !items[actual].classList.contains('deshabilitada')) elegir(+items[actual].dataset.indice);
                else if (!esta) abrir();
            } else if (e.key === 'Escape' || e.key === 'Tab') {
                if (esta) { if (e.key === 'Escape') e.preventDefault(); cerrar(e.key === 'Escape'); }
            } else if (esta && e.key === 'Home') { e.preventDefault(); actual = -1; mover(1); }
            else if (esta && e.key === 'End') { e.preventDefault(); actual = items.length; mover(-1); }
            else if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                if (!esta) abrir();
                var l = e.key.toLowerCase();
                for (var c = 1; c <= items.length; c++) {
                    var k = (Math.max(actual, 0) + c) % items.length;
                    if (!items[k].classList.contains('deshabilitada') && items[k].textContent.trim().toLowerCase().indexOf(l) === 0) { marcar(k); break; }
                }
            }
        });

        // Cambios hechos por la página: eventos, jQuery (.val().trigger()), opciones nuevas o deshabilitado
        sel.addEventListener('change', pintar);
        sel.addEventListener('input', pintar);
        if (window.jQuery) window.jQuery(sel).on('change', pintar);
        if (window.MutationObserver) {
            new MutationObserver(function () {
                ultimo = { i: null, t: null, d: null };
                pintar();
                if (abierto && abierto.caja === caja) { armar(); ubicar(); }
            }).observe(sel, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'selected', 'label'] });
        }
        if (sel.form) sel.form.addEventListener('reset', function () { setTimeout(pintar, 0); });
        todos.push(pintar);
        armar();
        pintar();
    }

    function mejorarTodo(raiz) {
        Array.prototype.forEach.call((raiz || document).querySelectorAll(SELECTOR), function (s, n) { mejorar(s, todos.length + n); });
    }
    // Valores puestos por código sin evento (sel.value = …): se revisan cada medio segundo
    setInterval(function () { todos.forEach(function (f) { f(); }); }, 500);

    window.ejSelect = { mejorar: mejorarTodo };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { mejorarTodo(); });
    else mejorarTodo();
})();
