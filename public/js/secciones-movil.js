/* =============================================================================
   Botones "Ocultar tabla" / "Ocultar gráfico de barras" / "Ocultar gráfico de dona"
   para celular, en todos los módulos.

   - Tablas: contenedores *-table-wrap, *-stats-table-wrap y .table-container
   - Gráficos: cada <canvas> de Chart.js (dentro de *-chart-canvas o *-chart-box)

   El tipo de gráfico se lee de la instancia de Chart.js. Como algunos gráficos se
   crean después (al abrir una pestaña o mostrar estadísticas), la etiqueta se
   completa cuando el gráfico ya existe.
   Se carga desde app/Views/layout/main.php; estilos en public/css/filtros-movil.css.
   ============================================================================= */
(function () {
    var NOMBRES = {
        bar: 'gráfico de barras',
        doughnut: 'gráfico de dona',
        pie: 'gráfico de torta',
        line: 'gráfico de líneas',
        polarArea: 'gráfico polar'
    };

    function crearBoton(objetivo, etiqueta, icono) {
        if (!objetivo.id) objetivo.id = 'seccion-movil-' + Math.random().toString(36).slice(2, 9);

        var boton = document.createElement('button');
        boton.type = 'button';
        boton.className = 'seccion-toggle';
        boton.setAttribute('aria-controls', objetivo.id);
        boton.setAttribute('aria-expanded', 'true');
        boton.innerHTML = '<i class="fas ' + icono + '"></i> <span class="seccion-toggle-texto"></span>';
        boton.dataset.etiqueta = etiqueta;

        function actualizarTexto() {
            var oculto = objetivo.classList.contains('seccion-oculta');
            boton.querySelector('.seccion-toggle-texto').textContent =
                (oculto ? 'Mostrar ' : 'Ocultar ') + boton.dataset.etiqueta;
            boton.querySelector('i').className = 'fas ' + (oculto ? 'fa-eye' : 'fa-eye-slash');
            boton.classList.toggle('is-cerrado', oculto);
            boton.setAttribute('aria-expanded', oculto ? 'false' : 'true');
        }
        boton.actualizarTexto = actualizarTexto;

        boton.addEventListener('click', function () {
            objetivo.classList.toggle('seccion-oculta');
            actualizarTexto();
        });

        objetivo.parentNode.insertBefore(boton, objetivo);
        actualizarTexto();
        return boton;
    }

    // ── Tablas ──
    function iniciarTablas() {
        document.querySelectorAll('.main-content [class*="table-wrap"], .main-content .table-container').forEach(function (el) {
            if (el.dataset.seccionToggle) return;
            var esContenedor = Array.prototype.some.call(el.classList, function (c) {
                return /^([a-z]+-)?(stats-)?table-wrap$/.test(c) || c === 'table-container';
            });
            if (!esContenedor || !el.querySelector('table')) return;
            // Si está dentro de otro contenedor de tabla ya procesado, no se duplica el botón
            if (el.parentElement && el.parentElement.closest('[data-seccion-toggle="tabla"]')) return;

            el.dataset.seccionToggle = 'tabla';
            crearBoton(el, 'tabla', 'fa-eye-slash');
        });
    }

    // ── Gráficos ──
    function tipoDeGrafico(canvas) {
        try {
            var chart = window.Chart && Chart.getChart ? Chart.getChart(canvas) : null;
            return chart ? chart.config.type : null;
        } catch (e) {
            return null;
        }
    }

    function iniciarGraficos() {
        document.querySelectorAll('.main-content canvas').forEach(function (canvas) {
            var objetivo = canvas.closest('[class*="chart-canvas"]') || canvas;
            if (objetivo.dataset.seccionToggle) return;
            objetivo.dataset.seccionToggle = 'grafico';

            var boton = crearBoton(objetivo, 'gráfico', 'fa-eye-slash');
            boton.dataset.canvasId = canvas.id || '';
            boton._canvas = canvas;
        });
        actualizarTiposDeGrafico();
    }

    // Completa "de barras" / "de dona" cuando Chart.js ya creó el gráfico
    function actualizarTiposDeGrafico() {
        document.querySelectorAll('.seccion-toggle').forEach(function (boton) {
            if (!boton._canvas || boton.dataset.tipoListo) return;
            var tipo = tipoDeGrafico(boton._canvas);
            if (!tipo) return;
            boton.dataset.etiqueta = NOMBRES[tipo] || 'gráfico';
            boton.dataset.tipoListo = '1';
            boton.actualizarTexto();
        });
    }

    function iniciar() {
        iniciarTablas();
        iniciarGraficos();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();

    // Los gráficos se crean después de cargar la página o al abrir pestañas / estadísticas
    window.addEventListener('load', function () {
        iniciar();
        setTimeout(actualizarTiposDeGrafico, 300);
    });
    document.addEventListener('click', function () {
        setTimeout(actualizarTiposDeGrafico, 400);
    });
})();