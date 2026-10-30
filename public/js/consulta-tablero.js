(function () {
    'use strict';
    const form = document.getElementById('consulta-form');
    if (!form) return;
    const efector = form.elements.efector;
    const formulario = form.elements.formulario;
    const especialidad = form.elements.especialidad;
    const catalogo = JSON.parse(document.getElementById('consulta-formularios').textContent);

    // Ocultar / mostrar filtros: queda visible la fila de filtros aplicados y se recuerda la elección
    const toggleFiltros = document.getElementById('consulta-filtros-toggle');
    if (toggleFiltros) {
        const panelFiltros = document.getElementById('consulta-filtros');
        function mostrarFiltros(ver, guardar) {
            panelFiltros.classList.toggle('ct-filtros-ocultos', !ver);
            toggleFiltros.setAttribute('aria-expanded', String(ver));
            toggleFiltros.querySelector('.ct-filtros-toggle-texto').textContent = ver ? 'Ocultar filtros' : 'Mostrar filtros';
            toggleFiltros.querySelector('i').className = ver ? 'fas fa-eye-slash' : 'fas fa-eye';
            if (guardar) {
                try { localStorage.setItem('consultaFiltrosOcultos', ver ? '0' : '1'); } catch (e) { /* sin almacenamiento */ }
            }
        }
        let ocultos = false;
        try { ocultos = localStorage.getItem('consultaFiltrosOcultos') === '1'; } catch (e) { /* sin almacenamiento */ }
        if (ocultos && !toggleFiltros.hasAttribute('data-forzar-abierto')) mostrarFiltros(false, false);
        toggleFiltros.addEventListener('click', () => {
            mostrarFiltros(panelFiltros.classList.contains('ct-filtros-ocultos'), true);
        });
    }

    // Ocultar / mostrar el cuerpo de un panel: <button data-ocultar-panel="gráfico"> en su encabezado.
    // Se recuerda por panel (id de la sección).
    document.querySelectorAll('[data-ocultar-panel]').forEach(boton => {
        const panel = boton.closest('.bl-panel');
        const nombre = boton.dataset.ocultarPanel;
        const clave = 'consultaPanelOculto:' + panel.id;
        function mostrarPanel(ver, guardar) {
            panel.classList.toggle('ct-panel-oculto', !ver);
            boton.setAttribute('aria-expanded', String(ver));
            boton.querySelector('span').textContent = (ver ? 'Ocultar ' : 'Mostrar ') + nombre;
            boton.querySelector('i').className = ver ? 'fas fa-eye-slash' : 'fas fa-eye';
            if (guardar) {
                try { localStorage.setItem(clave, ver ? '0' : '1'); } catch (e) { /* sin almacenamiento */ }
            }
            // Al volver a mostrarse, la tabla con columnas fijas recalcula sus medidas
            if (ver && window.tablasFijas) window.tablasFijas.aplicar();
        }
        let oculto = false;
        try { oculto = localStorage.getItem(clave) === '1'; } catch (e) { /* sin almacenamiento */ }
        if (oculto) mostrarPanel(false, false);
        boton.addEventListener('click', () => mostrarPanel(panel.classList.contains('ct-panel-oculto'), true));
    });
    function llenar(select, filas, clave, nombre) {
        select.replaceChildren(new Option('Todos los permitidos', ''));
        const vistos = new Set();
        filas.forEach(fila => {
            const id = String(fila[clave]);
            if (!vistos.has(id)) select.add(new Option(fila[nombre], id));
            vistos.add(id);
        });
    }
    function limpiarHistoricos() {
        ['medico', 'hospital', 'servicio'].forEach(key => {
            llenar(form.elements[key], [], 'id', 'nombre');
        });
        // form.submit() no dispara el evento submit: las fechas escritas se pasan acá
        fechas.forEach(f => {
            const iso = f.value.trim() === '' ? '' : aIso(f.value);
            if (iso !== null) form.elements[f.dataset.fecha].value = iso;
        });
        document.getElementById('consulta-carga').hidden = false;
        form.submit();
    }
    efector.addEventListener('change', () => {
        const filas = catalogo.filter(f => !efector.value || String(f.efector_id) === efector.value);
        llenar(formulario, filas, 'formulario_id', 'titulo');
        llenar(especialidad, filas, 'especialidad_id', 'especialidad');
        limpiarHistoricos();
    });
    formulario.addEventListener('change', () => {
        const elegido = catalogo.find(f => String(f.formulario_id) === formulario.value);
        if (elegido) especialidad.value = elegido.especialidad_id;
        limpiarHistoricos();
    });
    especialidad.addEventListener('change', () => {
        formulario.value = '';
        limpiarHistoricos();
    });
    // Fechas: se escriben o eligen como dd/mm/aaaa y viajan como aaaa-mm-dd en el campo oculto
    const fechas = ['desde', 'hasta'].map(key => document.getElementById('consulta-' + key));
    const aIso = texto => {
        const m = /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/.exec(texto.trim());
        if (!m) return null;
        const d = new Date(+m[3], m[2] - 1, +m[1]);
        return d.getDate() === +m[1] && d.getMonth() === m[2] - 1 ? m[3] + '-' + m[2].padStart(2, '0') + '-' + m[1].padStart(2, '0') : null;
    };
    const limpiarAvisos = () => fechas.forEach(f => f.setCustomValidity(''));
    fechas.forEach(f => f.addEventListener('input', limpiarAvisos));
    if (window.jQuery && jQuery.datepicker) {
        const desdeIso = iso => iso ? jQuery.datepicker.parseDate('yy-mm-dd', iso) : null;
        const minimo = desdeIso(fechas[0].dataset.min);
        const maximo = desdeIso(fechas[0].dataset.max);
        const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        jQuery(fechas).datepicker({
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            changeMonth: true,
            changeYear: true,
            // Solo los años con atenciones, hasta hoy
            yearRange: (minimo ? minimo.getFullYear() : 'c-5') + ':' + (maximo ? maximo.getFullYear() : 'c'),
            minDate: minimo,
            maxDate: maximo,
            showOtherMonths: true,
            selectOtherMonths: true,
            showButtonPanel: true,
            currentText: 'Hoy',
            closeText: 'Cerrar',
            prevText: 'Anterior',
            nextText: 'Siguiente',
            monthNames: meses,
            monthNamesShort: meses.map(m => m.slice(0, 3)),
            dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
            dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            beforeShow: (_, inst) => { inst.dpDiv.addClass('ct-calendario'); },
            onSelect: limpiarAvisos
        });
    }
    form.addEventListener('submit', event => {
        for (const f of fechas) {
            const iso = f.value.trim() === '' ? '' : aIso(f.value);
            if (iso === null) {
                event.preventDefault();
                f.setCustomValidity('Fecha inválida: usá el formato dd/mm/aaaa.');
                f.reportValidity();
                return;
            }
            form.elements[f.dataset.fecha].value = iso;
        }
        if (form.elements.desde.value && form.elements.hasta.value && form.elements.desde.value > form.elements.hasta.value) {
            event.preventDefault();
            fechas[1].setCustomValidity('La fecha hasta debe ser posterior o igual a la fecha desde.');
            fechas[1].reportValidity();
            return;
        }
        document.getElementById('consulta-carga').hidden = false;
    });

    // Tablas ordenables (class="ct-ordenable"): tocar un encabezado ordena por esa columna.
    // th data-tipo="num" ordena como número (la primera vez de mayor a menor); data-orden="no" no ordena.
    const valorCelda = (fila, i, numerica) => {
        const texto = fila.cells[i] ? fila.cells[i].textContent.trim() : '';
        if (!numerica) return texto;
        // Formato es-AR: 1.234,5 → 1234.5; "No aplica" → sin dato
        const n = parseFloat(texto.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, ''));
        return Number.isNaN(n) ? null : n;
    };
    const compararTexto = new Intl.Collator('es', { numeric: true, sensitivity: 'base' }).compare;
    document.querySelectorAll('table.ct-ordenable').forEach(tabla => {
        const cuerpo = tabla.tBodies[0];
        const encabezados = Array.from(tabla.tHead.rows[0].cells);
        const actualizarIconos = () => encabezados.forEach(th => {
            const icono = th.querySelector('.ct-orden-btn i');
            if (icono) icono.className = 'fas ' + ({ ascending: 'fa-sort-up', descending: 'fa-sort-down' }[th.getAttribute('aria-sort')] || 'fa-sort');
        });
        encabezados.forEach((th, i) => {
            if (th.dataset.orden === 'no') return;
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'ct-orden-btn';
            boton.append(...th.childNodes);
            const icono = document.createElement('i');
            icono.setAttribute('aria-hidden', 'true');
            boton.append(icono);
            th.append(boton);
            boton.addEventListener('click', () => {
                const filas = Array.from(cuerpo.rows);
                // Sin datos: la única fila es el aviso con colspan
                if (filas.length < 2 || filas.some(f => f.cells.length !== encabezados.length)) return;
                const numerica = th.dataset.tipo === 'num';
                const actual = th.getAttribute('aria-sort');
                const ascendente = actual ? actual !== 'ascending' : !numerica;
                filas.sort((a, b) => {
                    const x = valorCelda(a, i, numerica);
                    const y = valorCelda(b, i, numerica);
                    // Las celdas sin dato van siempre al final
                    if (x === null || y === null) return (x === null) - (y === null);
                    const r = numerica ? x - y : compararTexto(x, y);
                    return ascendente ? r : -r;
                });
                cuerpo.append(...filas);
                encabezados.forEach(otro => { if (otro !== th) otro.removeAttribute('aria-sort'); });
                th.setAttribute('aria-sort', ascendente ? 'ascending' : 'descending');
                actualizarIconos();
                tabla.dispatchEvent(new Event('ct-ordenada'));
            });
        });
        actualizarIconos();
    });

    // Tablas paginadas (class="ct-paginada"): 10 filas por página (data-por-pagina cambia la cantidad).
    // Pagina las filas en el orden actual del DOM, así sigue funcionando después de ordenar.
    const formatoFilas = new Intl.NumberFormat('es-AR');
    document.querySelectorAll('table.ct-paginada').forEach(tabla => {
        const porPagina = Number(tabla.dataset.porPagina) || 10;
        const cuerpo = tabla.tBodies[0];
        const filas = () => Array.from(cuerpo.rows).filter(f => !f.querySelector('.bl-empty'));
        if (filas().length <= porPagina) return;
        const pie = document.createElement('div');
        pie.className = 'ct-pie-tabla ct-pie-paginada';
        const nota = document.createElement('span');
        nota.className = 'ct-toolbar-nota';
        const nav = document.createElement('nav');
        nav.className = 'bl-pager';
        nav.setAttribute('aria-label', 'Paginación de la tabla');
        const lista = document.createElement('ul');
        nav.append(lista);
        pie.append(nota, nav);
        (tabla.closest('.bl-table-wrap') || tabla).after(pie);
        let pagina = 1;
        const boton = (contenido, destino, estado) => {
            const li = document.createElement('li');
            if (estado) li.className = estado;
            if (estado === 'disabled' || estado === 'active' || destino === null) {
                const span = document.createElement('span');
                span.innerHTML = contenido;
                if (estado === 'active') span.setAttribute('aria-current', 'page');
                li.append(span);
            } else {
                const b = document.createElement('button');
                b.type = 'button';
                b.innerHTML = contenido;
                b.addEventListener('click', () => mostrar(destino));
                li.append(b);
            }
            return li;
        };
        const mostrar = nueva => {
            const todas = filas();
            const paginas = Math.ceil(todas.length / porPagina);
            pagina = Math.min(Math.max(1, nueva), paginas);
            todas.forEach((f, i) => { f.hidden = Math.floor(i / porPagina) + 1 !== pagina; });
            nota.textContent = 'Mostrando ' + formatoFilas.format((pagina - 1) * porPagina + 1) + '–' + formatoFilas.format(Math.min(pagina * porPagina, todas.length)) + ' de ' + formatoFilas.format(todas.length);
            // Primera, última y las vecinas de la actual; el resto se resume con "…"
            const numeros = [...new Set([1, pagina - 1, pagina, pagina + 1, paginas])].filter(n => n >= 1 && n <= paginas).sort((a, b) => a - b);
            lista.replaceChildren(boton('<i class="fas fa-chevron-left" aria-hidden="true"></i>&nbsp;Anterior', pagina - 1, pagina === 1 ? 'disabled' : ''));
            numeros.forEach((n, i) => {
                if (i && n - numeros[i - 1] > 1) lista.append(boton('…', null, 'ct-pager-salto'));
                lista.append(boton(String(n), n, n === pagina ? 'active' : ''));
            });
            lista.append(boton('Siguiente&nbsp;<i class="fas fa-chevron-right" aria-hidden="true"></i>', pagina + 1, pagina === paginas ? 'disabled' : ''));
        };
        tabla.addEventListener('ct-ordenada', () => mostrar(1));
        mostrar(1);
    });

    const definiciones =JSON.parse(document.getElementById('consulta-graficos').textContent);
    if (!window.Chart) {
        document.querySelectorAll('.consulta-chart').forEach(contenedor => {
            const mensaje = document.createElement('p');
            mensaje.textContent = 'No se pudo cargar el gráfico. Consulte los valores en la tabla.';
            contenedor.replaceChildren(mensaje);
        });
        return;
    }
    const formato = new Intl.NumberFormat('es-AR');

    // Atenciones por médico en el tiempo: una línea por médico, agrupable por día, semana o mes
    const tiempo = JSON.parse(document.getElementById('consulta-medicos-tiempo-datos').textContent);
    const lienzoTiempo = document.getElementById('consulta-medicos-tiempo');
    if (lienzoTiempo && tiempo && tiempo.dias.length) {
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const fecha = iso => { const [a, m, d] = iso.split('-').map(Number); return new Date(a, m - 1, d); };
        const iso = f => f.getFullYear() + '-' + String(f.getMonth() + 1).padStart(2, '0') + '-' + String(f.getDate()).padStart(2, '0');
        let variosAnios = tiempo.dias[0].slice(0, 4) !== tiempo.dias[tiempo.dias.length - 1].slice(0, 4);
        const diaCorto = k => { const f = fecha(k); return f.getDate() + ' ' + meses[f.getMonth()] + (variosAnios ? ' ' + String(f.getFullYear()).slice(2) : ''); };
        const modos = {
            // clave: a qué grupo pertenece cada día · etiqueta: texto del eje · titulo: texto del tooltip
            dia:    { unidad: 'día',    minimo: 14, corrido: (k, n) => { const f = fecha(k); f.setDate(f.getDate() + n); return iso(f); }, clave: k => k, etiqueta: diaCorto, titulo: k => fecha(k).toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) },
            semana: { unidad: 'semana', minimo: 8, corrido: (k, n) => { const f = fecha(k); f.setDate(f.getDate() + 7 * n); return iso(f); }, clave: k => { const f = fecha(k); f.setDate(f.getDate() - (f.getDay() + 6) % 7); return iso(f); }, etiqueta: diaCorto, titulo: k => 'Semana del ' + fecha(k).toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' }) },
            mes:    { unidad: 'mes',    minimo: 6, corrido: (k, n) => { const f = new Date(Number(k.slice(0, 4)), Number(k.slice(5, 7)) - 1 + n, 1); return iso(f).slice(0, 7); }, clave: k => k.slice(0, 7), etiqueta: k => meses[Number(k.slice(5, 7)) - 1] + ' ' + k.slice(0, 4), titulo: k => meses[Number(k.slice(5, 7)) - 1] + ' ' + k.slice(0, 4) }
        };
        let claves = [];
        let modoActual = null;
        // Por día con períodos largos se ve una ventana de días (0: se ve todo el período)
        let ventana = 0;
        let inicio = 0;
        const diasVentana = () => lienzoTiempo.clientWidth < 600 ? 31 : 62;
        // Un color único por médico. La paleta del sistema tiene varios grises azulados casi iguales,
        // así que acá se generan: los tonos se reparten parejos en todo el círculo (OKLCH, que respeta
        // cómo percibe el ojo) y la claridad alterna en tres niveles, para que dos tonos vecinos
        // también se distingan por lo oscuro/claro. Los médicos con más atenciones (los primeros)
        // quedan lo más separados posible entre sí.
        const oklchHex = (l, c, h) => {
            const aRgb = croma => {
                const a = croma * Math.cos(h * Math.PI / 180), b = croma * Math.sin(h * Math.PI / 180);
                const l_ = (l + 0.3963377774 * a + 0.2158037573 * b) ** 3;
                const m_ = (l - 0.1055613458 * a - 0.0638541728 * b) ** 3;
                const s_ = (l - 0.0894841775 * a - 1.2914855480 * b) ** 3;
                return [
                    4.0767416621 * l_ - 3.3077115913 * m_ + 0.2309699292 * s_,
                    -1.2684380046 * l_ + 2.6097574011 * m_ - 0.3413193965 * s_,
                    -0.0041960863 * l_ - 0.7034186147 * m_ + 1.7076147010 * s_
                ];
            };
            // Si el color no entra en pantalla (sRGB) se baja la saturación hasta que entre
            let rgb = aRgb(c);
            for (let croma = c; rgb.some(v => v < 0 || v > 1) && croma > 0; croma -= 0.005) rgb = aRgb(croma);
            return '#' + rgb.map(v => {
                v = Math.min(1, Math.max(0, v));
                v = v <= 0.0031308 ? 12.92 * v : 1.055 * v ** (1 / 2.4) - 0.055;
                return Math.round(v * 255).toString(16).padStart(2, '0');
            }).join('');
        };
        const totalSeries = Math.max(1, tiempo.series.length);
        const claridades = [0.55, 0.72, 0.45];
        // Salto entre médicos consecutivos: cerca de 0,38 vueltas y sin divisor común con el total,
        // así cada médico cae en un lugar distinto del círculo y los primeros quedan bien separados
        const mcd = (a, b) => b ? mcd(b, a % b) : a;
        let salto = Math.max(1, Math.round(totalSeries * 0.382));
        while (totalSeries > 1 && mcd(salto, totalSeries) !== 1) salto++;
        const colores = Array.from({ length: totalSeries }, (_, i) => {
            const lugar = (i * salto) % totalSeries;
            return oklchHex(claridades[lugar % claridades.length], 0.13, 250 + lugar * 360 / totalSeries);
        });
        const color = i => colores[i % colores.length];
        const atenuado = (c, alfa = 0.12) => window.colorAlfa ? window.colorAlfa(c, alfa) : c;
        // Médico resaltado al pasar el mouse por la leyenda (null: ninguno). Los estilos son funciones
        // que Chart.js evalúa en cada dibujo: así se atenúan líneas y puntos (los puntos no se recalculan
        // si solo se cambian las propiedades del dataset).
        let resaltado = null;
        // Médicos elegidos en la leyenda (vacío: todos). Los no elegidos siguen dibujados pero atenuados.
        const elegidos = new Set();
        // Médicos en el gráfico: los primeros de la serie; el resto se suma desde el desplegable
        const visiblesIniciales = Math.min(tiempo.series.length, tiempo.visibles || tiempo.series.length);
        const incluidos = new Set(Array.from({ length: visiblesIniciales }, (_, i) => i));
        const activo = i => elegidos.size === 0 || elegidos.has(i);
        // No elegidos: atenuados pero legibles (18 %); al resaltar con el mouse los demás bajan más (12 %)
        const tono = ctx => {
            const i = ctx.datasetIndex;
            if (resaltado !== null) return i === resaltado ? color(i) : atenuado(color(i));
            return activo(i) ? color(i) : atenuado(color(i), 0.18);
        };
        const esResaltado = ctx => resaltado !== null && ctx.datasetIndex === resaltado;
        // Tamaño de los puntos según el espacio entre fechas: con muchos períodos (p. ej. años por semana)
        // se achican y los ceros no llevan punto, para que la línea se lea y la base no se llene de puntos
        const radioPunto = ctx => {
            const base = esResaltado(ctx) ? 4 : (activo(ctx.datasetIndex) ? 3 : 2);
            const area = ctx.chart.chartArea;
            // Con la ventana de días activa, la separación es la de los días que se ven, no la de todo el período
            const enPantalla = ventana || claves.length;
            const separacion = area && enPantalla > 1 ? (area.right - area.left) / (enPantalla - 1) : 40;
            if (separacion >= 18) return base;
            // Por día solo se ven puntos con pocos días en pantalla (si no, la línea alcanza)
            if (modoActual === 'dia') return 0;
            if (ctx.raw === 0) return 0;
            const radio = base * Math.max(0.4, separacion / 18);
            return radio < 1 ? 1 : radio;
        };
        const resaltar = indice => {
            if (resaltado === indice) return;
            resaltado = indice;
            grafico.update('none');
            pintarLeyenda();
        };
        // Leyenda propia (HTML): los primeros médicos siempre y la flecha del final despliega el resto.
        // Cada médico se ve pleno o atenuado igual que su línea.
        const leyenda = document.getElementById('consulta-tiempo-leyenda');
        let desplegada = false;
        const pintarLeyenda = () => {
            if (!leyenda) return;
            leyenda.querySelectorAll('[data-medico]').forEach(boton => {
                const i = Number(boton.dataset.medico);
                const pleno = resaltado !== null ? i === resaltado : activo(i);
                boton.hidden = !incluidos.has(i);
                boton.classList.toggle('is-atenuado', !(activo(i) || i === resaltado));
                boton.firstElementChild.style.background = pleno ? color(i) : atenuado(color(i), 0.25);
                boton.setAttribute('aria-pressed', String(elegidos.has(i)));
            });
        };
        const grafico = new Chart(lienzoTiempo, {
            type: 'line',
            data: {
                labels: [],
                datasets: tiempo.series.map((s, i) => ({
                    label: s.nombre,
                    hidden: !incluidos.has(i),
                    data: [],
                    borderColor: tono,
                    backgroundColor: tono,
                    pointBackgroundColor: tono,
                    pointBorderColor: tono,
                    borderWidth: ctx => esResaltado(ctx) ? 4 : (activo(ctx.datasetIndex) ? 2.5 : 1.5),
                    pointRadius: radioPunto,
                    pointHoverRadius: 5,
                    tension: 0,
                    // Uniones redondeadas: con la unión en punta (miter) las V cerradas (9 → 0 → 6)
                    // se estiran por debajo del 0 y por encima de los picos
                    borderJoinStyle: 'round',
                    borderCapStyle: 'round',
                    fill: false
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                // Al pasar de escritorio a celular (o al revés) cambia la cantidad de días de la ventana
                onResize: () => { if (modoActual === 'dia' && ventana && diasVentana() !== ventana) aplicar('dia'); },
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    // La leyenda es HTML (#consulta-tiempo-leyenda) para poder desplegar el resto de los médicos
                    legend: { display: false },
                    tooltip: {
                        // En el tooltip solo los médicos elegidos (o el resaltado con el mouse)
                        filter: item => item.parsed.y > 0 && (resaltado === null ? activo(item.datasetIndex) : item.datasetIndex === resaltado),
                        itemSort: (a, b) => b.parsed.y - a.parsed.y,
                        callbacks: {
                            title: items => items.length ? modos[modoActual].titulo(claves[items[0].dataIndex]) : '',
                            label: item => ' ' + item.dataset.label + ': ' + formato.format(item.parsed.y)
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },
                    // Margen arriba: con pocos datos la línea no queda pegada al borde del gráfico
                    y: { beginAtZero: true, grace: '15%', ticks: { precision: 0 } }
                }
            }
        });
        // Selección desde la leyenda: el primer clic deja solo ese médico; los siguientes suman los
        // ocultos o quitan los ya elegidos. Sin ninguno elegido se vuelven a ver todos.
        const botonTodos = document.getElementById('consulta-tiempo-todos');
        const mostrarElegidos = () => {
            resaltado = null;
            grafico.update();
            pintarLeyenda();
            if (botonTodos) botonTodos.hidden = elegidos.size === 0;
        };
        const elegir = i => {
            if (elegidos.has(i)) elegidos.delete(i);
            else elegidos.add(i);
            // Si quedaron elegidos todos, es lo mismo que no filtrar
            if (elegidos.size === incluidos.size) elegidos.clear();
            mostrarElegidos();
        };
        if (botonTodos) botonTodos.addEventListener('click', () => { elegidos.clear(); mostrarElegidos(); });
        if (leyenda) {
            tiempo.series.forEach((s, i) => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'ct-leyenda-item';
                boton.dataset.medico = i;
                const muestra = document.createElement('span');
                muestra.className = 'ct-leyenda-color';
                muestra.setAttribute('aria-hidden', 'true');
                boton.append(muestra, document.createTextNode(s.nombre));
                boton.title = s.nombre + ' · ' + formato.format(s.datos.reduce((a, v) => a + v, 0)) + ' atenciones';
                boton.addEventListener('click', () => elegir(i));
                boton.addEventListener('mouseenter', () => resaltar(i));
                boton.addEventListener('mouseleave', () => resaltar(null));
                boton.addEventListener('focus', () => resaltar(i));
                boton.addEventListener('blur', () => resaltar(null));
                leyenda.append(boton);
            });
            // Flecha al final: muestra u oculta los médicos que no entran entre los primeros
            const restantes = tiempo.series.length - visiblesIniciales;
            if (restantes > 0) {
                const mas = document.createElement('button');
                mas.type = 'button';
                mas.className = 'ct-leyenda-mas';
                mas.setAttribute('aria-expanded', 'false');
                const etiquetaMas = () => {
                    mas.innerHTML = desplegada
                        ? '<i class="fas fa-chevron-up" aria-hidden="true"></i> Ver menos'
                        : '<i class="fas fa-chevron-down" aria-hidden="true"></i> ' + restantes + ' médicos más';
                    mas.setAttribute('aria-expanded', String(desplegada));
                };
                mas.addEventListener('click', () => {
                    desplegada = !desplegada;
                    // Al desplegar, si hay médicos elegidos, los nuevos entran atenuados como el resto de los no elegidos.
                    // Al plegar se quitan del gráfico y de la selección.
                    for (let i = visiblesIniciales; i < tiempo.series.length; i++) {
                        if (desplegada) incluidos.add(i);
                        else { incluidos.delete(i); elegidos.delete(i); }
                        grafico.setDatasetVisibility(i, desplegada);
                    }
                    etiquetaMas();
                    mostrarElegidos();
                    // La tabla por mes y médico muestra u oculta las mismas columnas; si queda ancha, se desplaza
                    // con Mes y Total fijos (tablas-fijas.js)
                    const tablaMes = document.getElementById('consulta-tabla-mes');
                    if (tablaMes) tablaMes.classList.toggle('ct-con-extras', desplegada);
                    if (window.tablasFijas) window.tablasFijas.aplicar();
                    // Rótulo del encabezado: "Todos los médicos" desplegado, "Los 10 médicos con más atenciones" plegado
                    const rotulo = document.getElementById('consulta-tiempo-rotulo');
                    if (rotulo) rotulo.textContent = desplegada ? rotulo.dataset.desplegado : rotulo.dataset.plegado;
                });
                etiquetaMas();
                leyenda.append(mas);
            }
            pintarLeyenda();
        }
        // Navegación de la ventana de días: flechas, barra para desplazarse y el rango que se está viendo.
        // El eje Y se ajusta a los días visibles (Chart.js solo toma los datos dentro de min y max del eje X).
        const nav = document.createElement('div');
        nav.className = 'ct-tiempo-nav';
        nav.hidden = true;
        nav.innerHTML = '<button type="button" class="bl-btn ghost sm" data-paso="-1" title="Días anteriores" aria-label="Días anteriores"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>'
            + '<input type="range" min="0" max="0" step="1" value="0" aria-label="Mover los días visibles del gráfico">'
            + '<button type="button" class="bl-btn ghost sm" data-paso="1" title="Días siguientes" aria-label="Días siguientes"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>'
            + '<span class="ct-tiempo-rango" aria-live="polite"></span>';
        lienzoTiempo.parentElement.after(nav);
        const barra = nav.querySelector('input');
        const anterior = nav.querySelector('[data-paso="-1"]');
        const siguiente = nav.querySelector('[data-paso="1"]');
        const rango = nav.querySelector('.ct-tiempo-rango');
        const ubicarVentana = desde => {
            inicio = Math.max(0, Math.min(claves.length - ventana, desde));
            grafico.options.scales.x.min = inicio;
            grafico.options.scales.x.max = inicio + ventana - 1;
            barra.value = inicio;
            anterior.disabled = inicio === 0;
            siguiente.disabled = inicio >= claves.length - ventana;
            rango.textContent = 'Del ' + diaCorto(claves[inicio]) + ' al ' + diaCorto(claves[inicio + ventana - 1]) + ' · ' + ventana + ' de ' + claves.length + ' días';
        };
        const moverVentana = desde => { ubicarVentana(desde); grafico.update('none'); };
        barra.addEventListener('input', () => moverVentana(Number(barra.value)));
        // Cada flecha avanza media ventana: queda a la vista parte de lo anterior como referencia
        [anterior, siguiente].forEach(b => b.addEventListener('click', () => moverVentana(inicio + Number(b.dataset.paso) * Math.round(ventana / 2))));
        const botones = Array.from(document.querySelectorAll('[data-agrupar]'));
        const aplicar = modo => {
            const m = modos[modo];
            const indice = new Map();
            claves = [];
            tiempo.dias.forEach(d => { const k = m.clave(d); if (!indice.has(k)) { indice.set(k, claves.length); claves.push(k); } });
            // Con muy pocos períodos (p. ej. un médico con 2 atenciones en el mismo mes) el gráfico estiraba esos
            // puntos a todo el ancho. Se completa con períodos sin atenciones antes y después hasta el mínimo del modo.
            let antes = 0;
            if (claves.length < m.minimo) {
                const faltan = m.minimo - claves.length;
                antes = Math.floor(faltan / 2);
                const primero = claves[0];
                const ultimo = claves[claves.length - 1];
                claves = Array.from({ length: antes }, (_, n) => m.corrido(primero, n - antes))
                    .concat(claves, Array.from({ length: faltan - antes }, (_, n) => m.corrido(ultimo, n + 1)));
            }
            variosAnios = claves[0].slice(0, 4) !== claves[claves.length - 1].slice(0, 4);
            tiempo.series.forEach((s, i) => {
                const datos = new Array(claves.length).fill(0);
                s.datos.forEach((v, j) => { datos[antes + indice.get(m.clave(tiempo.dias[j]))] += v; });
                grafico.data.datasets[i].data = datos;
            });
            grafico.data.labels = claves.map(m.etiqueta);
            modoActual = modo;
            // Por día, si el período no entra, se muestran los últimos días y el resto se recorre con la barra
            ventana = modo === 'dia' && claves.length > diasVentana() ? diasVentana() : 0;
            nav.hidden = !ventana;
            if (ventana) {
                barra.max = claves.length - ventana;
                ubicarVentana(claves.length - ventana);
            } else {
                delete grafico.options.scales.x.min;
                delete grafico.options.scales.x.max;
            }
            grafico.update();
            botones.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.agrupar === modo)));
            document.getElementById('consulta-tiempo-unidad').textContent = m.unidad;
        };
        botones.forEach(b => b.addEventListener('click', () => aplicar(b.dataset.agrupar)));
        // Por día como en el pedido; si el período supera el año, arranca por semana para que se lea
        aplicar(tiempo.dias.length > 400 ? 'semana' : 'dia');
    }

    definiciones.forEach(def => {
        const canvas = document.getElementById(def.id);
        if (!canvas || !def.filas.length) return;
        const barras = def.tipo === 'bar';
        const nombres = def.filas.map(f => String(f.nombre));
        if (def.tipo === 'doughnut') {
            const total = def.filas.reduce((s, f) => s + Number(f.cantidad), 0);
            // Total en el centro; se dibuja antes del tooltip para que este quede encima
            const totalCentro = {
                id: 'totalCentro',
                afterDatasetsDraw: chart => {
                    const arco = chart.getDatasetMeta(0).data[0];
                    if (!arco) return;
                    const visible = chart.data.datasets[0].data.reduce((s, v, i) => s + (chart.getDataVisibility(i) ? Number(v) || 0 : 0), 0);
                    const tamNumero = Math.round(Math.max(12, Math.min(24, arco.innerRadius * 0.34)));
                    const tamTitulo = Math.round(Math.max(10, Math.min(16, arco.innerRadius * 0.22)));
                    const ctx = chart.ctx;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = 'bold ' + tamTitulo + 'px sans-serif';
                    ctx.fillStyle = '#555555';
                    ctx.fillText('TOTAL', arco.x, arco.y - tamNumero * 0.55);
                    ctx.font = 'bold ' + tamNumero + 'px sans-serif';
                    ctx.fillStyle = '#222222';
                    ctx.fillText(formato.format(visible), arco.x, arco.y + tamTitulo * 0.65);
                    ctx.restore();
                }
            };
            new Chart(canvas, {
                type: 'doughnut',
                plugins: typeof ChartDataLabels !== 'undefined' ? [ChartDataLabels, totalCentro] : [totalCentro],
                data: {
                    labels: nombres,
                    datasets: [{
                        data: def.filas.map(f => Number(f.cantidad)),
                        backgroundColor: def.filas.map((_, i) => window.colorPaleta ? window.colorPaleta(i) : '#243B63'),
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '58%',
                    plugins: {
                        legend: { position: canvas.parentElement.clientWidth < 560 ? 'bottom' : 'right', labels: {
                            usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 12, boxHeight: 12, padding: 12, color: '#1a2b45',
                            // La leyenda lleva la cantidad y el porcentaje; los nombres largos se acortan
                            generateLabels: chart => Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart).map(l => {
                                const f = def.filas[l.index];
                                const corto = nombres[l.index].replace(/^Servicio( de)? /i, '').replace(/ - (Consultorio Externo )?Hospital /i, ' · ');
                                const texto = corto.length > 46 ? corto.slice(0, 43) + '…' : corto;
                                return { ...l, text: texto + ' · ' + formato.format(f.cantidad) + ' (' + (total ? Math.round(f.cantidad * 1000 / total) / 10 : 0) + ' %)' };
                            })
                        } },
                        tooltip: { callbacks: {
                            title: items => nombres[items[0].dataIndex],
                            label: item => formato.format(def.filas[item.dataIndex].cantidad) + ' atenciones (' + (total ? Math.round(def.filas[item.dataIndex].cantidad * 1000 / total) / 10 : 0) + ' %)'
                        } },
                        // Porcentaje dentro de cada porción; en las muy chicas no entra y queda en la leyenda
                        datalabels: {
                            color: window.colorEtiqueta || '#1d2b3c',
                            font: { weight: 'bold', size: 12 },
                            formatter: (valor, ctx) => {
                                const suma = ctx.chart.data.datasets[0].data.reduce((s, v, i) => s + (ctx.chart.getDataVisibility(i) ? v : 0), 0);
                                const pct = suma ? Math.round(valor * 100 / suma) : 0;
                                return pct > 4 ? pct + '%' : '';
                            }
                        }
                    }
                }
            });
            return;
        }
        // Rótulos legibles:
        // - def.nombres (personas): siempre con mayúscula inicial ("Caro maría laura" → "Caro María Laura")
        // - el resto, solo si viene todo en mayúsculas ("HOSPITAL DR. JORGE URO" → "Hospital Dr. Jorge Uro"),
        //   respetando siglas (UPA, CAPS…) y palabras sin vocales
        // - el código del sistema de origen al final ("… (21)") no va en el eje, sí en el tooltip
        const PARTICULAS = /^(de|del|la|las|los|y|e|da|di|van|von)$/;
        const SIGLAS = /^(UPA|CAPS|APS|UTI|UCI|UTIN|UCO|HPS|CIC|SAME|ORL|ACV|EPOC|HIV|VIH|ART|PAMI|ISJ|SN)$/;
        const nombrePropio = texto => String(texto).split(/\s+/).filter(Boolean).map((p, i) => {
            const min = p.toLocaleLowerCase('es');
            if (i > 0 && PARTICULAS.test(min)) return min;
            // Siglas conocidas y palabras de 3 o más letras sin vocales (p. ej. "HPS") quedan como vienen
            if (SIGLAS.test(p) || (p.length >= 3 && !/[AEIOUÁÉÍÓÚaeiouáéíóú.]/.test(p))) return p;
            return min.charAt(0).toLocaleUpperCase('es') + min.slice(1);
        }).join(' ');
        const CODIGO = /\s*\((\d+)\)\s*$/;
        const legible = texto => {
            const limpio = String(texto).trim();
            return def.nombres || !/[a-záéíóúñ]/.test(limpio) ? nombrePropio(limpio) : limpio;
        };
        const rotular = f => legible(String(f.nombre).replace(CODIGO, ''));
        const completo = f => {
            const codigo = String(f.nombre).match(CODIGO);
            return rotular(f) + (codigo ? ' (código ' + codigo[1] + ')' : '');
        };
        // Los nombres se ven siempre completos: se reparten en los renglones que hagan falta (cortando entre
        // palabras) y el gráfico de barras crece en alto para que entren todos
        const enRenglones = (texto, ancho) => {
            const renglones = [];
            texto.split(' ').forEach(palabra => {
                const ultimo = renglones.length - 1;
                if (ultimo >= 0 && (renglones[ultimo] + ' ' + palabra).length <= ancho) renglones[ultimo] += ' ' + palabra;
                else renglones.push(palabra);
            });
            return renglones;
        };
        // Letras por renglón según el ancho del gráfico: angosto (celular) ~18, ancho ~32
        const letrasPorRenglon = chart => chart.width < 560 ? 18 : 32;
        // Alto de las barras: cada fila mide lo que sus renglones (unos 15 px cada uno) más aire, y el eje
        const ajustarAlto = chart => {
            if (!barras) return;
            const letras = letrasPorRenglon(chart);
            const alto = chart.data.labels.reduce((s, t) => s + Math.max(28, enRenglones(String(t), letras).length * 15 + 12), 0) + 50;
            const contenedor = chart.canvas.parentElement;
            if (Math.abs(contenedor.clientHeight - alto) > 2) contenedor.style.height = alto + 'px';
        };
        // def.iniciales: se dibujan esas primeras filas y un botón despliega el resto (p. ej. médicos)
        let filas = def.iniciales ? def.filas.slice(0, def.iniciales) : def.filas;
        let etiquetas = filas.map(rotular);
        const grafico = new Chart(canvas, {
            type: def.tipo,
            data: {
                labels: etiquetas,
                datasets: [{
                    label: 'Atenciones',
                    data: filas.map(f => Number(f.cantidad)),
                    backgroundColor: barras ? def.filas.map((_, i) => window.colorPaleta ? window.colorPaleta(i) : '#243B63') : 'rgba(36,59,99,0.08)',
                    borderColor: '#243B63',
                    borderWidth: barras ? 0 : 2,
                    borderRadius: barras ? 4 : 0,
                    pointBackgroundColor: '#243B63',
                    pointRadius: barras ? 0 : 3,
                    pointHoverRadius: barras ? 0 : 5,
                    tension: 0.25,
                    fill: !barras
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: barras ? 'y' : 'x',
                // Al cambiar el ancho (celular ↔ escritorio) cambian los renglones de los nombres y con ellos el alto
                onResize: chart => ajustarAlto(chart),
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        title: items => completo(filas[items[0].dataIndex]),
                        label: item => formato.format(filas[item.dataIndex].cantidad) + ' atenciones'
                    } }
                },
                scales: {
                    [barras ? 'x' : 'y']: { beginAtZero: true, ticks: { precision: 0 } },
                    [barras ? 'y' : 'x']: { ticks: { autoSkip: !barras, callback: function (valor) {
                        const texto = this.getLabelForValue(valor);
                        if (barras) return enRenglones(texto, letrasPorRenglon(this.chart));
                        return texto.length > 36 ? texto.slice(0, 33) + '…' : texto;
                    } } }
                }
            }
        });
        ajustarAlto(grafico);
        const restantes = def.iniciales ? def.filas.length - def.iniciales : 0;
        if (restantes > 0) {
            const contenedor = canvas.parentElement;
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'ct-leyenda-mas ct-ver-mas-barras';
            let desplegado = false;
            const etiquetaBoton = () => {
                boton.innerHTML = desplegado
                    ? '<i class="fas fa-chevron-up" aria-hidden="true"></i> Ver solo los primeros ' + def.iniciales
                    : '<i class="fas fa-chevron-down" aria-hidden="true"></i> Ver ' + restantes + ' más';
                boton.setAttribute('aria-expanded', String(desplegado));
            };
            boton.addEventListener('click', () => {
                desplegado = !desplegado;
                filas = desplegado ? def.filas : def.filas.slice(0, def.iniciales);
                etiquetas = filas.map(rotular);
                grafico.data.labels = etiquetas;
                grafico.data.datasets[0].data = filas.map(f => Number(f.cantidad));
                // Más filas, más alto: cada barra con el lugar que necesita su nombre
                ajustarAlto(grafico);
                grafico.update();
                etiquetaBoton();
                // Rótulo del encabezado del panel: "Todos los médicos" desplegado, "Los 10 médicos…" plegado
                const rotulo = canvas.closest('.bl-panel')?.querySelector('.bl-panel-meta[data-plegado]');
                if (rotulo) rotulo.textContent = desplegado ? rotulo.dataset.desplegado : rotulo.dataset.plegado;
            });
            etiquetaBoton();
            contenedor.after(boton);
        }
    });
}());