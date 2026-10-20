/*
 * Paleta de colores de los gráficos de barras y de dona de todo el tablero.
 * Se carga en el <head> del layout (app/Views/layout/main.php), antes que cualquier gráfico.
 *
 * - dashboardColors: colores con nombre (primario, estados, fondos).
 * - chartPalette:    colores para series y categorías, en orden.
 * - colorPaleta(i):  color i de la paleta (vuelve a empezar si i supera el largo).
 * - colorAlfa(hex, a): el mismo color con transparencia (ej. barras de años anteriores).
 */
(function (global) {
    var dashboardColors = {
        primary: '#39A9C6',
        secondary: '#243B63',

        success: '#5BB8A6',
        warning: '#F2A272',
        danger: '#D96C6C',

        teal: '#6CB4A5',
        coral: '#D98B73',

        steel: '#5D7C99',
        slate: '#708090',

        backgroundDark: '#0f172a',
        surfaceDark: '#1e293b',

        textLight: '#f8fafc',

        tooltipDark: 'rgba(11, 19, 34, 0.96)'
    };

    var chartPalette = [
        '#243B63', // azul petróleo profundo
        '#3FA7C4', // celeste institucional
        '#F2A272', // naranja suave
        '#5BB8A6', // verde aqua

        '#7D8A99', // gris azulado
        '#8E6C88', // malva apagado
        '#C97C5D', // terracota
        '#A89B6D', // oliva suave

        '#54728C', // azul humo
        '#74C0B3', // mint apagado
        '#D98B73', // salmón tostado
        '#6F8F72', // verde musgo suave

        '#4E5D78', // azul acero oscuro
        '#B38B59', // mostaza apagado
        '#8C8C8C', // gris neutro
        '#5C7A95', // azul institucional claro

        '#B06C7A', // rosa viejo
        '#4D9BB5', // azul océano
        '#8AA29E', // sage gray
        '#6B7280', // slate gray moderno

        '#A76F5A', // cobre suave
        '#617C63', // verde oliva frío
        '#927AA8', // violeta grisáceo
        '#3E5C76'  // navy desaturado
    ];

    function colorPaleta(i) {
        var n = chartPalette.length;
        return chartPalette[((i % n) + n) % n];
    }

    function colorAlfa(hex, alfa) {
        var h = String(hex).replace('#', '');
        if (h.length === 3) h = h.replace(/(.)/g, '$1$1');
        var r = parseInt(h.substring(0, 2), 16);
        var g = parseInt(h.substring(2, 4), 16);
        var b = parseInt(h.substring(4, 6), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alfa + ')';
    }

    // Color de texto legible sobre un fondo: blanco sobre oscuros, gris oscuro sobre claros.
    // Acepta #rgb, #rrggbb, #rrggbbaa (transparencia: se mezcla con el blanco del gráfico) y rgb()/rgba().
    function colorTextoSobre(fondo) {
        var texto = String(fondo || '').trim();
        var r, g, b, a = 1, m;

        if ((m = /^#([0-9a-f]{3})$/i.exec(texto))) {
            texto = '#' + m[1].replace(/(.)/g, '$1$1');
        }
        if ((m = /^#([0-9a-f]{6})([0-9a-f]{2})?$/i.exec(texto))) {
            var n = parseInt(m[1], 16);
            r = n >> 16; g = (n >> 8) & 255; b = n & 255;
            if (m[2]) a = parseInt(m[2], 16) / 255;
        } else if ((m = /^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*(?:,\s*([\d.]+)\s*)?\)$/i.exec(texto))) {
            r = +m[1]; g = +m[2]; b = +m[3];
            if (m[4] !== undefined) a = parseFloat(m[4]);
        } else {
            return '#1d2b3c';
        }

        // Con transparencia, el color que se ve es la mezcla con el fondo blanco
        r = r * a + 255 * (1 - a);
        g = g * a + 255 * (1 - a);
        b = b * a + 255 * (1 - a);

        return (0.299 * r + 0.587 * g + 0.114 * b) < 150 ? '#ffffff' : '#1d2b3c';
    }

    // Para chartjs-plugin-datalabels: color de la etiqueta según el segmento donde se dibuja.
    // Uso: datalabels: { color: colorEtiqueta, ... } en etiquetas que van DENTRO de barras o donas.
    function colorEtiqueta(ctx) {
        var bg = ctx.dataset.backgroundColor;
        if (Array.isArray(bg)) bg = bg[ctx.dataIndex];
        return typeof bg === 'string' ? colorTextoSobre(bg) : '#1d2b3c';
    }

    global.dashboardColors = dashboardColors;
    global.chartPalette    = chartPalette;
    global.colorPaleta     = colorPaleta;
    global.colorAlfa       = colorAlfa;
    global.colorTextoSobre = colorTextoSobre;
    global.colorEtiqueta   = colorEtiqueta;
})(window);