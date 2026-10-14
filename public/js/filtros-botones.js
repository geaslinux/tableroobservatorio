/* =============================================================================
   Botones "Filtrar" y "Limpiar" iguales en todas las barras de filtros.

   Recorre cada contenedor *-filtro-bar (ml-, at-, bl-, rh-, filtro-bar, …) y:
   1. Al botón que envía el filtro le agrega el texto "Filtrar" si solo tenía ícono.
   2. Al botón de limpiar le agrega el texto "Limpiar" si solo tenía ícono.
   3. Si la barra no tiene botón de limpiar, lo crea: vuelve al módulo sin filtros
      (conserva la pestaña abierta, ?tab=…, en los paneles que la usan).

   Se carga desde app/Views/layout/main.php. Estilos en public/css/filtros-movil.css.
   ============================================================================= */
(function () {
    function esBarraDeFiltros(el) {
        return Array.prototype.some.call(el.classList, function (c) {
            return /^([a-z]+-)?filtro-bar$/.test(c);
        });
    }

    function tieneTexto(el) {
        return (el.textContent || '').replace(/\s+/g, '').length > 0;
    }

    function agregarTexto(el, texto) {
        var span = document.createElement('span');
        span.className = 'filtro-btn-texto';
        span.textContent = texto;
        el.appendChild(document.createTextNode(' '));
        el.appendChild(span);
    }

    // Botón que envía el filtro: el que tiene ícono de embudo o title "Filtrar"; si no, el último submit
    function buscarBotonFiltrar(ambito) {
        var submits = Array.prototype.slice.call(
            ambito.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])')
        );
        var preferido = submits.filter(function (b) {
            return b.querySelector('.fa-filter') || /filtrar/i.test(b.title || '') || /filtrar/i.test(b.textContent || '');
        });
        return preferido[0] || submits[submits.length - 1] || null;
    }

    function buscarBotonLimpiar(ambito) {
        return Array.prototype.find.call(ambito.querySelectorAll('a, button'), function (el) {
            return el.id === 'btnLimpiar'
                || /limpiar/i.test(el.title || '')
                || /limpiar/i.test(el.textContent || '');
        }) || null;
    }

    // Dirección del módulo sin filtros (conserva ?tab= si la página la usa)
    function urlSinFiltros(form) {
        var base = (form && form.getAttribute('action')) || window.location.pathname;
        try {
            var url = new URL(base, window.location.href);
            var tab = new URLSearchParams(window.location.search).get('tab');
            url.search = tab ? '?tab=' + encodeURIComponent(tab) : '';
            return url.pathname + url.search;
        } catch (e) {
            return window.location.pathname;
        }
    }

    function iniciar() {
        document.querySelectorAll('.main-content [class*="filtro-bar"]').forEach(function (barra) {
            if (!esBarraDeFiltros(barra) || barra.dataset.filtroBotones) return;
            barra.dataset.filtroBotones = '1';

            var form = barra.closest('form') || barra.querySelector('form');
            var ambito = form || barra;

            // 1. Filtrar
            var filtrar = buscarBotonFiltrar(ambito);
            if (!filtrar) return;   // barra sin envío (solo informativa): no se toca
            if (filtrar.tagName === 'INPUT') {
                if (!filtrar.value) filtrar.value = 'Filtrar';
            } else if (!tieneTexto(filtrar)) {
                if (!filtrar.querySelector('i')) filtrar.insertAdjacentHTML('afterbegin', '<i class="fas fa-filter"></i>');
                agregarTexto(filtrar, 'Filtrar');
            }
            filtrar.classList.add('filtro-btn-filtrar');

            // 2. Limpiar (existente)
            var limpiar = buscarBotonLimpiar(ambito);
            if (limpiar) {
                if (!tieneTexto(limpiar)) agregarTexto(limpiar, 'Limpiar');
                limpiar.classList.add('filtro-btn-limpiar');
                return;
            }

            // 3. Limpiar (nuevo): mismo alto que el botón Filtrar, justo a su lado
            limpiar = document.createElement('a');
            limpiar.href = urlSinFiltros(form);
            limpiar.className = 'filtro-limpiar-nuevo filtro-btn-limpiar';
            limpiar.title = 'Limpiar filtros';
            limpiar.innerHTML = '<i class="fas fa-undo"></i> <span class="filtro-btn-texto">Limpiar</span>';
            var alto = filtrar.offsetHeight;
            if (alto) limpiar.style.height = alto + 'px';
            filtrar.insertAdjacentElement('afterend', limpiar);
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
})();