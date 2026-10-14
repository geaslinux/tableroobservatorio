/* =============================================================================
   Botón "Filtros" para ocultar / mostrar la barra de filtros en celular.
   Se aplica a todos los módulos: busca los contenedores *-filtro-bar
   (ml-filtro-bar, at-filtro-bar, bl-filtro-bar, filtro-bar, …).
   Se carga desde app/Views/layout/main.php. En escritorio el botón no se ve
   y los filtros siempre están visibles (ver public/css/filtros-movil.css).
   ============================================================================= */
(function () {
    var CLAVE = 'filtrosMovilOcultos';   // la preferencia se recuerda entre páginas

    function leerPreferencia() {
        try { return localStorage.getItem(CLAVE) === '1'; } catch (e) { return false; }
    }
    function guardarPreferencia(ocultos) {
        try { localStorage.setItem(CLAVE, ocultos ? '1' : '0'); } catch (e) { /* sin almacenamiento */ }
    }

    // Cantidad de filtros con un valor elegido (selects e inputs visibles que no estén vacíos)
    function contarActivos(barra) {
        var n = 0;
        barra.querySelectorAll('select, input').forEach(function (campo) {
            var tipo = (campo.type || '').toLowerCase();
            if (['hidden', 'submit', 'button', 'reset', 'checkbox', 'radio'].indexOf(tipo) !== -1) return;
            if (String(campo.value || '').trim() !== '') n++;
        });
        return n;
    }

    function iniciar() {
        var barras = Array.prototype.filter.call(
            document.querySelectorAll('.main-content [class*="filtro-bar"]'),
            function (el) {
                return Array.prototype.some.call(el.classList, function (c) {
                    return /^([a-z]+-)?filtro-bar$/.test(c);
                });
            }
        );

        var ocultos = leerPreferencia();

        barras.forEach(function (barra, i) {
            if (barra.dataset.filtrosToggle) return;
            barra.dataset.filtrosToggle = '1';
            if (!barra.id) barra.id = 'filtros-movil-' + i;

            var activos = contarActivos(barra);
            var boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'filtros-toggle';
            boton.setAttribute('aria-controls', barra.id);
            boton.innerHTML =
                '<span><i class="fas fa-sliders-h"></i> Filtros' +
                (activos ? ' <span class="filtros-toggle-badge">' + activos + '</span>' : '') +
                '</span>' +
                '<span class="filtros-toggle-accion"><span class="filtros-toggle-texto"></span> ' +
                '<i class="fas fa-chevron-down"></i></span>';

            barra.parentNode.insertBefore(boton, barra);

            function aplicar(ocultar) {
                barra.classList.toggle('filtros-ocultos', ocultar);
                boton.classList.toggle('is-cerrado', ocultar);
                boton.setAttribute('aria-expanded', ocultar ? 'false' : 'true');
                boton.querySelector('.filtros-toggle-texto').textContent = ocultar ? 'Mostrar' : 'Ocultar';
            }

            aplicar(ocultos);

            boton.addEventListener('click', function () {
                var ocultar = !barra.classList.contains('filtros-ocultos');
                aplicar(ocultar);
                guardarPreferencia(ocultar);
            });
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
})();