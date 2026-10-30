/* =============================================================================
   Tablas anchas con columnas fijas (adaptado de sistemabasec).

   Solo actúa sobre tablas marcadas: <table class="bl-table" data-fijas> dentro de
   .bl-table-wrap. El resto de las tablas del sistema no se toca.

   Cuando la tabla no entra en su recuadro y hay que desplazarla de costado:

   1. Barra de navegación arriba de la tabla (todas las pantallas):
      "Viendo 13 de 21 columnas · 8 fuera de vista" + flechas ◀ ▶ para desplazar.
   2. Columnas fijas (solo 769px o más):
      - Las celdas de encabezado con data-fija="izq" / data-fija="der" quedan fijas
        a la izquierda / derecha (por ejemplo Mes y Total).
      - Si no se marca ninguna, se usa la regla de sistemabasec: columnas de acción del
        final a la derecha y la primera columna a la izquierda si identifica la fila.
   3. Sombra en el borde de las columnas fijas solo del lado donde hay columnas ocultas.

   Las columnas ocultas (display: none) no se cuentan.
   En celular no se fija ninguna columna: la tabla se desplaza completa.
   Se recalcula al cambiar el tamaño, al abrir un <details> y con window.tablasFijas.aplicar().
   Estilos: .bl-scroll-nav / .tiene-fijas en panel.css.
   ============================================================================= */
(function () {
    var ACCION = /^(editar|eliminar|acciones|accion|historial|quitar|mapa|ver|borrar)$/i;
    var IDENTIFICA = /^(hospital|efector|establecimiento|paciente|datos del paciente|nombre)$/i;
    var ESCRITORIO = window.matchMedia('(min-width: 769px)');

    function texto(celda) {
        return (celda.textContent || '').replace(/\s+/g, ' ').trim();
    }

    // Ubica cada celda en su columna real, respetando colspan y rowspan (sin contar celdas ocultas)
    function grilla(filas) {
        var ocupadas = [], celdas = [];
        filas.forEach(function (fila, f) {
            ocupadas[f] = ocupadas[f] || [];
            var col = 0;
            Array.prototype.forEach.call(fila.cells, function (celda) {
                if (getComputedStyle(celda).display === 'none') return;
                while (ocupadas[f][col]) col++;
                var ancho = celda.colSpan || 1, alto = celda.rowSpan || 1;
                for (var r = 0; r < alto; r++) {
                    ocupadas[f + r] = ocupadas[f + r] || [];
                    for (var c = 0; c < ancho; c++) ocupadas[f + r][col + c] = true;
                }
                celdas.push({ celda: celda, desde: col, hasta: col + ancho - 1, fila: f });
                col += ancho;
            });
        });
        return celdas;
    }

    function limpiar(wrap, tabla) {
        wrap.classList.remove('tiene-fijas', 'hay-mas-izq', 'hay-mas-der');
        tabla.querySelectorAll('.bl-fija').forEach(function (c) {
            c.classList.remove('bl-fija', 'bl-fija-izq', 'bl-fija-der', 'bl-fija-borde-izq', 'bl-fija-borde-der');
            c.style.left = c.style.right = '';
            if (c.dataset.fondoFija) { c.style.backgroundColor = ''; delete c.dataset.fondoFija; }
        });
    }

    // Primer fondo opaco hacia arriba (para que el encabezado fijo no sea transparente)
    function fondoOpaco(el) {
        while (el && el !== document.body) {
            var bg = getComputedStyle(el).backgroundColor;
            if (bg && bg !== 'transparent' && !/rgba\(.*,\s*0\)$/.test(bg)) return bg;
            el = el.parentElement;
        }
        return '#ffffff';
    }

    // ── Columnas fijas (solo escritorio) ──────────────────────────────────
    function fijarColumnas(wrap, tabla, head, rotulos, anchos, total) {
        var marcadas = rotulos.some(function (r) { return r && r.fija; });
        var derecha = [], izquierda = [], c;
        if (marcadas) {
            for (c = total - 1; c >= 0; c--) {
                if (rotulos[c] && rotulos[c].fija === 'der') derecha.unshift(c);
                else break;
            }
            for (c = 0; c < total; c++) {
                if (rotulos[c] && rotulos[c].fija === 'izq') izquierda.push(c);
                else break;
            }
        } else {
            for (c = total - 1; c >= 0; c--) {
                if (rotulos[c] && ACCION.test(rotulos[c].texto)) derecha.unshift(c);
                else break;
            }
            if (rotulos[0] && IDENTIFICA.test(rotulos[0].texto)) izquierda = [0];
        }
        if (!derecha.length && !izquierda.length) return { derecha: [], izquierda: [] };

        // Que lo fijo no ocupe más de la mitad del recuadro (si no, no queda lugar para desplazar)
        var anchoFijo = derecha.concat(izquierda).reduce(function (s, col) { return s + (anchos[col] || 0); }, 0);
        if (anchoFijo > wrap.clientWidth * 0.5) return { derecha: [], izquierda: [] };

        function offsetDer(col) {
            return derecha.filter(function (x) { return x > col; })
                          .reduce(function (s, x) { return s + (anchos[x] || 0); }, 0);
        }
        function offsetIzq(col) {
            return izquierda.filter(function (x) { return x < col; })
                            .reduce(function (s, x) { return s + (anchos[x] || 0); }, 0);
        }

        var todas = head.concat(
            Array.prototype.concat.apply([], Array.prototype.map.call(tabla.tBodies, function (tb) {
                return grilla(Array.prototype.slice.call(tb.rows));
            })),
            tabla.tFoot ? grilla(Array.prototype.slice.call(tabla.tFoot.rows)) : []
        );

        todas.forEach(function (h) {
            var cel = h.celda;
            var enDer = derecha.length && h.desde >= derecha[0];
            var enIzq = izquierda.length && h.hasta <= izquierda[izquierda.length - 1];
            if (!enDer && !enIzq) return;

            cel.classList.add('bl-fija', enDer ? 'bl-fija-der' : 'bl-fija-izq');
            if (enDer) {
                cel.style.right = offsetDer(h.hasta) + 'px';
                if (h.desde === derecha[0]) cel.classList.add('bl-fija-borde-der');
            } else {
                cel.style.left = offsetIzq(h.desde) + 'px';
                if (h.hasta === izquierda[izquierda.length - 1]) cel.classList.add('bl-fija-borde-izq');
            }
            // Encabezados: fondo opaco igual al que tenían (algunos grupos tienen color propio)
            if (cel.parentElement.parentElement === tabla.tHead) {
                cel.style.backgroundColor = fondoOpaco(cel);
                cel.dataset.fondoFija = '1';
            }
        });

        wrap.classList.add('tiene-fijas');
        return { derecha: derecha, izquierda: izquierda };
    }

    // ── Barra de navegación ("Viendo 13 de 21 columnas" + flechas) ────────
    function barraDe(wrap) {
        if (wrap._barraNav && wrap._barraNav.isConnected) return wrap._barraNav;

        var barra = document.createElement('div');
        barra.className = 'bl-scroll-nav';
        barra.innerHTML =
            '<span class="bl-scroll-nav-texto"><i class="fas fa-arrows-left-right"></i> <span></span></span>' +
            '<span class="bl-scroll-nav-ayuda">Shift + rueda del mouse también desplaza</span>' +
            '<span class="bl-scroll-nav-botones">' +
                '<button type="button" class="bl-scroll-nav-btn" data-dir="-1" title="Ver columnas anteriores" aria-label="Ver columnas anteriores"><i class="fas fa-chevron-left"></i></button>' +
                '<button type="button" class="bl-scroll-nav-btn" data-dir="1" title="Ver más columnas" aria-label="Ver más columnas"><i class="fas fa-chevron-right"></i></button>' +
            '</span>';
        wrap.parentNode.insertBefore(barra, wrap);
        wrap._barraNav = barra;

        barra.addEventListener('click', function (e) {
            var btn = e.target.closest('.bl-scroll-nav-btn');
            if (!btn || btn.disabled) return;
            var paso = Math.max(160, wrap.clientWidth * 0.7);
            wrap.scrollBy({ left: paso * parseInt(btn.dataset.dir, 10), behavior: 'smooth' });
        });
        return barra;
    }

    // Actualiza el texto, las flechas y las sombras según lo que se ve
    function actualizar(wrap) {
        var info = wrap._tablaAncha;
        if (!info) return;
        var barra = info.barra;
        var max = wrap.scrollWidth - wrap.clientWidth;
        var izq = wrap.scrollLeft > 2;
        var der = wrap.scrollLeft < max - 2;

        wrap.classList.toggle('hay-mas-izq', izq);
        wrap.classList.toggle('hay-mas-der', der);
        barra.querySelector('[data-dir="-1"]').disabled = !izq;
        barra.querySelector('[data-dir="1"]').disabled = !der;

        // Zona realmente visible: el recuadro menos lo que tapan las columnas fijas
        var caja = wrap.getBoundingClientRect();
        var desde = caja.left, hasta = caja.right;
        info.fijas.izquierda.forEach(function (c) { desde += info.anchos[c] || 0; });
        info.fijas.derecha.forEach(function (c) { hasta -= info.anchos[c] || 0; });

        var visibles = 0, ocultasIzq = 0, ocultasDer = 0;
        info.celdas.forEach(function (cel, col) {
            if (!cel) return;
            var r = cel.getBoundingClientRect();
            var fija = info.fijas.izquierda.indexOf(col) !== -1 || info.fijas.derecha.indexOf(col) !== -1;
            // Visible si se ve al menos la mitad de la columna; si no, se cuenta del lado en que quedó
            var visto = Math.min(r.right, hasta) - Math.max(r.left, desde);
            if (fija || visto >= r.width / 2) visibles++;
            else if (r.left + r.width / 2 < desde) ocultasIzq++;
            else ocultasDer++;
        });

        var total = info.celdas.filter(Boolean).length;
        var ocultas = function (n, lado) { return '<span class="bl-scroll-nav-ocultas">' + n + ' ' + lado + '</span>'; };
        var html;
        if (!der && izq) {
            html = '<i class="fas fa-check-circle bl-scroll-nav-fin"></i> <strong>Llegaste a la última columna (' + total + ' de ' + total + ')</strong>'
                 + (ocultasIzq ? ' · ' + ocultasIzq + ' a la izquierda' : '');
        } else {
            html = 'Viendo <strong>' + visibles + '</strong> de <strong>' + total + '</strong> columnas';
            if (ocultasIzq && ocultasDer) html += ' · ' + ocultasIzq + ' a la izquierda · ' + ocultas(ocultasDer, 'a la derecha');
            else if (ocultasDer)         html += ' · ' + ocultas(ocultasDer, 'más a la derecha');
            else if (ocultasIzq)         html += ' · ' + ocultasIzq + ' a la izquierda';
        }
        barra.querySelector('.bl-scroll-nav-texto > span').innerHTML = html;
    }

    function aplicar(wrap) {
        var tabla = wrap.querySelector('table.bl-table[data-fijas]');
        if (!tabla || !tabla.tHead) return;
        limpiar(wrap, tabla);

        // Dentro de un <details> cerrado no tiene medidas: se resuelve al abrirlo
        var desborda = wrap.clientWidth > 0 && tabla.scrollWidth > wrap.clientWidth + 1;
        if (!desborda) {
            if (wrap._barraNav) wrap._barraNav.hidden = true;
            delete wrap._tablaAncha;
            return;
        }

        var head = grilla(Array.prototype.slice.call(tabla.tHead.rows));
        if (!head.length) return;

        // Rótulo, ancho y celda de cada columna: la celda de encabezado más baja que la cubre sola
        var rotulos = [], anchos = [], celdas = [], total = 0;
        head.forEach(function (h) {
            total = Math.max(total, h.hasta + 1);
            if (h.desde === h.hasta && (rotulos[h.desde] === undefined || h.fila >= rotulos[h.desde].fila)) {
                rotulos[h.desde] = { texto: texto(h.celda), fila: h.fila, fija: h.celda.dataset.fija || '' };
                anchos[h.desde] = h.celda.getBoundingClientRect().width;
                celdas[h.desde] = h.celda;
            }
        });

        var fijas = ESCRITORIO.matches
            ? fijarColumnas(wrap, tabla, head, rotulos, anchos, total)
            : { derecha: [], izquierda: [] };

        wrap._tablaAncha = { barra: barraDe(wrap), celdas: celdas, anchos: anchos, fijas: fijas };
        wrap._tablaAncha.barra.hidden = false;
        if (!wrap.dataset.scrollNav) {
            wrap.dataset.scrollNav = '1';
            wrap.addEventListener('scroll', function () { actualizar(wrap); }, { passive: true });
        }
        actualizar(wrap);
    }

    function aplicarTodas() {
        document.querySelectorAll('.bl-table-wrap').forEach(function (wrap) {
            if (wrap.querySelector('table.bl-table[data-fijas]')) aplicar(wrap);
        });
    }

    var espera;
    function alCambiar() {
        clearTimeout(espera);
        espera = setTimeout(aplicarTodas, 120);
    }

    function iniciar() {
        aplicarTodas();
        window.addEventListener('resize', alCambiar);
        if (ESCRITORIO.addEventListener) ESCRITORIO.addEventListener('change', alCambiar);
        // El sidebar al contraerse / expandirse cambia el ancho disponible
        document.addEventListener('click', function (e) {
            if (e.target.closest && e.target.closest('#sidebar-toggle, .seccion-toggle')) setTimeout(aplicarTodas, 260);
        });
        // Al abrir un <details> la tabla recién tiene medidas
        document.addEventListener('toggle', function (e) {
            if (e.target.tagName === 'DETAILS' && e.target.open && e.target.querySelector('table.bl-table[data-fijas]')) aplicarTodas();
        }, true);
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(aplicarTodas);
    }

    window.tablasFijas = { aplicar: aplicarTodas };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar);
    else iniciar();
})();
