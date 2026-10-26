(function () {
    'use strict';
    const form = document.getElementById('consulta-form');
    if (!form) return;
    const efector = form.elements.efector;
    const formulario = form.elements.formulario;
    const especialidad = form.elements.especialidad;
    const catalogo = JSON.parse(document.getElementById('consulta-formularios').textContent);
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
    form.addEventListener('submit', event => {
        if (form.elements.desde.value && form.elements.hasta.value && form.elements.desde.value > form.elements.hasta.value) {
            event.preventDefault();
            form.elements.hasta.setCustomValidity('La fecha hasta debe ser posterior o igual a la fecha desde.');
            form.elements.hasta.reportValidity();
            return;
        }
        document.getElementById('consulta-carga').hidden = false;
    });
    ['desde', 'hasta'].forEach(key => form.elements[key].addEventListener('input', () => form.elements.hasta.setCustomValidity('')));
    const definiciones = JSON.parse(document.getElementById('consulta-graficos').textContent);
    if (!window.Chart) {
        document.querySelectorAll('.consulta-chart').forEach(contenedor => {
            const mensaje = document.createElement('p');
            mensaje.textContent = 'No se pudo cargar el gráfico. Consulte los valores en la tabla.';
            contenedor.replaceChildren(mensaje);
        });
        return;
    }
    const formato = new Intl.NumberFormat('es-AR');
    definiciones.forEach(def => {
        const canvas = document.getElementById(def.id);
        if (!canvas || !def.filas.length) return;
        const barras = def.tipo === 'bar';
        const nombres = def.filas.map(f => String(f.nombre));
        new Chart(canvas, {
            type: def.tipo,
            data: {
                labels: nombres,
                datasets: [{
                    label: 'Atenciones',
                    data: def.filas.map(f => Number(f.cantidad)),
                    backgroundColor: barras ? def.filas.map((_, i) => window.colorPaleta ? window.colorPaleta(i) : '#243B63') : '#39A9C6',
                    borderColor: '#243B63',
                    borderWidth: barras ? 0 : 2,
                    tension: 0,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: barras ? 'y' : 'x',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        title: items => nombres[items[0].dataIndex],
                        label: item => formato.format(def.filas[item.dataIndex].cantidad) + ' atenciones'
                    } }
                },
                scales: {
                    [barras ? 'x' : 'y']: { beginAtZero: true, ticks: { precision: 0 } },
                    [barras ? 'y' : 'x']: { ticks: { callback: function (valor) {
                        const texto = this.getLabelForValue(valor);
                        return texto.length > 36 ? texto.slice(0, 33) + '…' : texto;
                    } } }
                }
            }
        });
    });
}());
