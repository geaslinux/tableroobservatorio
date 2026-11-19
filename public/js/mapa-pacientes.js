// Mapa de pacientes compartido (mismo diseño que la "Distribución territorial" de Salud mental):
// lista de tarjetas + mapa Leaflet con regiones sanitarias, pines por riesgo, leyenda de riesgo que
// filtra y un único buscador por nombre o DNI.
// Estilos: public/css/mapa-pacientes.css. Lo usa el detalle del corte de EJESA.
//
// Uso:
//   MapaPacientes.iniciar({
//       mapa: 'idDelDivDelMapa', lista: 'idLista', buscador: 'idInput', limpiar: 'idBotonX',
//       info: 'idInfo', leyenda: 'idLeyendaRiesgo',
//       personas: [{ id, paciente, dni, edad, tipo, localidad, efector, diagnostico, riesgo, lat, lng, historial }],
//       colores: { ALTO: '#..', MEDIANO: '#..', BAJO: '#..', PENDIENTE: '#..' },
//       zona: geojson opcional (se dibuja en rojo y encuadra el mapa),
//       departamentos: 'url del geojson de departamentos'
//   });
(function (global) {
    'use strict';

    // Regiones sanitarias sobre los departamentos de Jujuy (mismos colores que Bases y Salud mental)
    var REGION_COLORES = { 'CENTRO': '#90CAF9', 'VALLE': '#A5D6A7', 'RAMAL I': '#F48FB1', 'RAMAL II': '#CE93D8', 'QUEBRADA': '#FFAB91', 'PUNA': '#FFF59D' };
    var REGION_BORDES  = { 'CENTRO': '#4c6bb8', 'VALLE': '#4c8f74', 'RAMAL I': '#b36693', 'RAMAL II': '#976cc2', 'QUEBRADA': '#d88e6b', 'PUNA': '#e2b464' };
    var DEPTO_REGION = {
        'Dr. Manuel Belgrano': 'CENTRO',
        'Palpala': 'VALLE', 'El Carmen': 'VALLE', 'San Antonio': 'VALLE',
        'Ledesma': 'RAMAL II', 'Valle Grande': 'RAMAL II',
        'San Pedro': 'RAMAL I', 'Santa Barbara': 'RAMAL I',
        'Humahuaca': 'QUEBRADA', 'Tilcara': 'QUEBRADA', 'Tumbaya': 'QUEBRADA', 'Susques': 'QUEBRADA',
        'Yavi': 'PUNA', 'Cochinoca': 'PUNA', 'Rinconada': 'PUNA', 'Santa Catalina': 'PUNA'
    };

    function escapar(t) {
        return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    // Minúsculas y sin acentos: "Pérez" encuentra "PEREZ"
    function normalizar(t) {
        return String(t || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();
    }
    function soloDigitos(t) { return String(t || '').replace(/\D/g, ''); }

    // Buscador único: si se escriben solo números (con puntos o espacios) busca por DNI; si no, por nombre
    // (todas las palabras, en cualquier orden: "maria perez" = "PEREZ MARIA").
    function criterio(texto) {
        var t = String(texto || '').trim();
        if (!t) return { tipo: '', valor: '' };
        if (/^[\d.\s-]+$/.test(t) && soloDigitos(t)) return { tipo: 'dni', valor: soloDigitos(t) };
        return { tipo: 'nombre', valor: normalizar(t) };
    }
    function coincide(p, c) {
        if (!c.tipo) return true;
        if (c.tipo === 'dni') return soloDigitos(p.dni).indexOf(c.valor) !== -1;
        var n = normalizar(p.paciente);
        return c.valor.split(/\s+/).every(function (w) { return n.indexOf(w) !== -1; });
    }
    // Resalta lo buscado en el nombre o en el DNI
    function resaltarNombre(nombre, c) {
        var seguro = escapar(nombre);
        if (c.tipo !== 'nombre' || !nombre) return seguro;
        var n = normalizar(nombre);
        if (n.length !== String(nombre).length) return seguro;
        var marcas = new Array(n.length).fill(false);
        c.valor.split(/\s+/).forEach(function (w) {
            var desde = 0, pos;
            while (w && (pos = n.indexOf(w, desde)) !== -1) {
                for (var k = pos; k < pos + w.length; k++) marcas[k] = true;
                desde = pos + w.length;
            }
        });
        return marcar(String(nombre), function (i) { return marcas[i]; });
    }
    function resaltarDni(dni, c) {
        var texto = String(dni || '');
        if (c.tipo !== 'dni' || !texto) return escapar(texto);
        var pos = soloDigitos(texto).indexOf(c.valor);
        if (pos === -1) return escapar(texto);
        var d = -1, dentro = [];
        for (var i = 0; i < texto.length; i++) {
            if (/\d/.test(texto[i])) d++;
            dentro[i] = /\d/.test(texto[i]) && d >= pos && d < pos + c.valor.length;
        }
        return marcar(texto, function (i) { return dentro[i]; });
    }
    function marcar(texto, enMarca) {
        var html = '', abierto = false;
        for (var i = 0; i < texto.length; i++) {
            var m = enMarca(i);
            if (m && !abierto) { html += '<mark>'; abierto = true; }
            if (!m && abierto) { html += '</mark>'; abierto = false; }
            html += escapar(texto[i]);
        }
        return html + (abierto ? '</mark>' : '');
    }

    // Pin en forma de gota; el color indica el riesgo
    function icono(color) {
        return L.divIcon({
            className: '',
            html: '<div style="width:24px; height:24px; background:' + color + '; border-radius:50% 50% 50% 0; border:2px solid #fff; transform:rotate(-45deg); box-shadow:0 0 4px rgba(0,0,0,0.4);"></div>',
            iconSize: [24, 24], iconAnchor: [12, 24], popupAnchor: [0, -22]
        });
    }

    function cargarDepartamentos(mapa, url) {
        if (!url) return;
        var seleccionado = null;
        var normal = { fillOpacity: 0.5, weight: 2 }, elegido = { fillOpacity: 0.25, weight: 4 };
        fetch(url).then(function (r) { return r.json(); }).then(function (geojson) {
            var capa = L.geoJSON(geojson, {
                style: function (f) {
                    var region = DEPTO_REGION[f.properties.nam] || 'CENTRO';
                    return { color: REGION_BORDES[region], weight: 2, opacity: 1, fillColor: REGION_COLORES[region], fillOpacity: 0.5 };
                },
                onEachFeature: function (f, layer) {
                    layer.bindTooltip('<b>' + escapar(f.properties.nam || f.properties.fna || 'Departamento') + '</b>', { sticky: true });
                    layer.on({
                        mouseover: function (e) { if (e.target !== seleccionado) e.target.setStyle({ fillOpacity: 0.2, weight: 1 }); },
                        mouseout: function (e) { if (e.target !== seleccionado) e.target.setStyle(normal); },
                        click: function (e) {
                            if (seleccionado && seleccionado !== e.target) seleccionado.setStyle(normal);
                            seleccionado = e.target;
                            seleccionado.setStyle(elegido);
                            mapa.flyToBounds(e.target.getBounds(), { padding: [30, 30], duration: 0.6 });
                        }
                    });
                }
            }).addTo(mapa);
            capa.bringToBack();   // la zona del corte y los pines quedan por encima
        }).catch(function (e) { console.error('Error al cargar la capa de departamentos:', e); });
    }

    // Datos de la tarjeta y del popup: nombre, DNI, edad, localidad, hospital, diagnóstico, riesgo y "Ver historial"
    function ficha(p, colores, c) {
        var color = colores[p.riesgo] || colores.PENDIENTE || '#708090';
        return '<table class="mp-datos">' +
            '<tr><td>DNI:</td><td class="mp-dni">' + (p.dni ? resaltarDni(p.dni, c) : '—') + '</td></tr>' +
            '<tr><td>Edad:</td><td>' + escapar(p.edad || '—') + (p.tipo ? ' (' + escapar(p.tipo) + ')' : '') + '</td></tr>' +
            '<tr><td>Localidad:</td><td>' + escapar(p.localidad || '—') + '</td></tr>' +
            '<tr><td>Hospital:</td><td>' + escapar(p.efector || '—') + '</td></tr>' +
            '<tr><td>Diagnóstico:</td><td>' + escapar(p.diagnostico || '—') + '</td></tr>' +
            '<tr><td>Riesgo:</td><td><strong style="color:' + color + '">' + escapar(p.riesgo) + '</strong></td></tr>' +
            '</table>';
    }
    function botonHistorial(p) {
        return p.historial
            ? '<a class="mp-btn-historial" href="' + escapar(p.historial) + '" title="Ver el historial del paciente (solo lectura)"><i class="fas fa-history"></i> Ver historial</a>'
            : '';
    }

    function iniciar(o) {
        var colores = o.colores, personas = o.personas || [];
        var $ = function (id) { return document.getElementById(id); };
        var lista = $(o.lista), input = $(o.buscador), limpiar = $(o.limpiar), info = $(o.info), leyenda = $(o.leyenda);
        var conGeo = personas.filter(function (p) { return p.lat != null; }).length;
        var riesgoActivo = null, texto = '';

        // Mapa
        var mapa = L.map(o.mapa, { zoomControl: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 18 }).addTo(mapa);
        cargarDepartamentos(mapa, o.departamentos);
        var zona = o.zona ? L.geoJSON(o.zona, { style: { color: '#c53030', weight: 3, fillColor: '#c53030', fillOpacity: .15 } }).addTo(mapa) : null;
        if (zona) zona.bindTooltip('<b>Zona del corte</b>', { sticky: true });

        var grupo = L.featureGroup().addTo(mapa);
        var marcadores = personas.map(function (p) {
            if (p.lat == null) return null;
            return L.marker([p.lat, p.lng], { icon: icono(colores[p.riesgo] || colores.PENDIENTE) })
                .bindPopup('<strong>' + escapar(p.paciente) + '</strong>' + ficha(p, colores, {}) + botonHistorial(p), { maxWidth: 300, minWidth: 240 })
                .addTo(grupo);
        });

        if (zona) mapa.fitBounds(zona.getBounds().extend(conGeo ? grupo.getBounds() : zona.getBounds()), { padding: [30, 30] });
        else if (conGeo) mapa.fitBounds(grupo.getBounds(), { padding: [30, 30], maxZoom: 14 });
        else mapa.setView([-23.3128, -65.3097], 7);   // centro de Jujuy

        // Lista de tarjetas (los que no tienen coordenadas van al final, con borde punteado)
        var orden = personas.map(function (p, i) { return i; }).sort(function (a, b) {
            return (personas[a].lat == null) - (personas[b].lat == null);
        });
        lista.innerHTML = '';
        var items = [];
        orden.forEach(function (i) {
            var p = personas[i];
            var el = document.createElement('div');
            el.className = 'mp-item' + (p.lat == null ? ' is-sin-geo' : '');
            el.title = p.lat == null ? 'Sin coordenadas: no figura en el mapa' : 'Ver en el mapa';
            el.dataset.indice = i;
            lista.appendChild(el);
            items[i] = el;
            el.addEventListener('click', function (e) {
                if (e.target.closest('a')) return;
                var m = marcadores[i];
                if (!m) return;
                lista.querySelectorAll('.mp-item.is-active').forEach(function (a) { a.classList.remove('is-active'); });
                el.classList.add('is-active');
                mapa.setView(m.getLatLng(), Math.max(mapa.getZoom(), 14));
                m.openPopup();
            });
        });
        var vacio = document.createElement('div');
        vacio.className = 'mp-vacia';
        lista.appendChild(vacio);

        function pintar(i, c) {
            var p = personas[i];
            items[i].innerHTML = '<div class="mp-nombre">' + resaltarNombre(p.paciente, c) + '</div>' + ficha(p, colores, c) +
                (p.lat == null ? '<span class="mp-tag"><i class="fas fa-location-crosshairs"></i> Sin coordenadas</span>' : '') +
                (p.historial ? '<div class="mp-acciones">' + botonHistorial(p) + '</div>' : '');
        }

        function aplicar() {
            var c = criterio(texto), visibles = 0, enMapa = [];
            personas.forEach(function (p, i) {
                var ver = (!riesgoActivo || p.riesgo === riesgoActivo) && coincide(p, c);
                items[i].hidden = !ver;
                if (!ver) items[i].classList.remove('is-active');
                pintar(i, ver ? c : {});
                if (ver) visibles++;
                var m = marcadores[i];
                if (m) {
                    if (ver && !grupo.hasLayer(m)) grupo.addLayer(m);
                    if (!ver && grupo.hasLayer(m)) grupo.removeLayer(m);
                    if (ver) enMapa.push(m);
                }
            });
            var hayFiltro = !!riesgoActivo || !!c.tipo;
            vacio.hidden = visibles > 0;
            vacio.textContent = !personas.length ? 'El protocolo se activó sin personas en la zona del corte.'
                : c.tipo ? 'Ninguna persona coincide con ' + (c.tipo === 'dni' ? 'el DNI' : 'el nombre') + ' "' + texto.trim() + '"' + (riesgoActivo ? ' con riesgo ' + riesgoActivo : '')
                : 'Sin personas con riesgo ' + riesgoActivo;
            info.textContent = hayFiltro && visibles ? visibles + ' de ' + personas.length + ' personas' : '';

            // Con búsqueda: si queda una sola persona se centra y abre su ficha; si son pocas, se encuadran
            if (c.tipo && enMapa.length === 1) {
                mapa.setView(enMapa[0].getLatLng(), Math.max(mapa.getZoom(), 14));
                enMapa[0].openPopup();
            } else if (c.tipo && enMapa.length > 1 && enMapa.length <= 30) {
                mapa.closePopup();
                mapa.fitBounds(L.featureGroup(enMapa).getBounds(), { padding: [30, 30], maxZoom: 14 });
            } else {
                mapa.closePopup();
            }
        }

        // Leyenda de riesgo: filtra lista y mapa; segundo clic sobre el mismo nivel lo quita
        if (leyenda) leyenda.querySelectorAll('[data-riesgo]').forEach(function (b) {
            b.addEventListener('click', function () {
                riesgoActivo = riesgoActivo === b.dataset.riesgo ? null : b.dataset.riesgo;
                leyenda.classList.toggle('has-filter', !!riesgoActivo);
                leyenda.querySelectorAll('[data-riesgo]').forEach(function (x) {
                    var activo = x.dataset.riesgo === riesgoActivo;
                    x.classList.toggle('is-active', activo);
                    x.setAttribute('aria-pressed', activo ? 'true' : 'false');
                });
                aplicar();
            });
        });

        // Buscador: filtra mientras se escribe · Enter abre la primera persona · Esc limpia
        var espera = null;
        input.addEventListener('input', function () {
            limpiar.hidden = input.value === '';
            clearTimeout(espera);
            espera = setTimeout(function () { texto = input.value; aplicar(); }, 150);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var primero = items.filter(function (it) { return it && !it.hidden && !it.classList.contains('is-sin-geo'); })[0];
                if (primero) primero.click();
            }
            if (e.key === 'Escape') limpiar.click();
        });
        limpiar.addEventListener('click', function () {
            input.value = ''; limpiar.hidden = true; texto = ''; aplicar(); input.focus();
        });

        aplicar();
        return mapa;
    }

    global.MapaPacientes = {
        iniciar: iniciar,
        criterio: criterio,
        coincide: coincide,
        regiones: REGION_COLORES
    };
})(window);