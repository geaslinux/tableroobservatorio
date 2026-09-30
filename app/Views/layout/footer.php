<link rel="stylesheet" href="/css/style.css">

<style>
    /* ===== Footer responsivo (ancho y zoom) ===== */
    .footer .container {
        flex-wrap: wrap;
        max-width: 100%;
        text-align: center;
        padding: clamp(6px, 1vw, 10px) 16px;
        gap: clamp(8px, 1.2vw, 15px);
    }
    .footer .container p {
        margin: 0;
        min-width: 0;
        font-size: clamp(12px, 1vw, 14px);
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .footer .logoapp { flex-shrink: 0; }

    /* "Última actualización" dentro del flujo (en style.css estaba absolute con left:-180px y quedaba detrás del sidebar) */
    .footer .footer-last-update {
        position: static;
        left: auto;
        white-space: nowrap;
        padding-left: 12px;
        border-left: 1px solid rgba(255, 255, 255, 0.25);
    }

    /* Tablets y móviles: logo arriba y texto debajo */
    @media (max-width: 768px) {
        .footer .container { flex-direction: column; gap: 6px; }
        .footer .logoapp { max-height: 24px; margin-right: 0; }
        .footer .footer-last-update { white-space: normal; padding-left: 0; border-left: none; }
    }

    @media (max-width: 480px) {
        .footer { padding: 6px 0; }
        .footer .container { padding: 4px 10px; }
        .footer .container p { font-size: 11px; }
        .footer .logoapp { max-height: 20px; }
    }

    /* Zoom alto / ventanas bajas: footer compacto en una sola franja */
    @media (max-height: 520px) {
        .footer { padding: 4px 0; }
        .footer .container { flex-direction: row; gap: 8px; padding: 2px 10px; }
        .footer .container p { font-size: 11px; line-height: 1.25; }
        .footer .logoapp { max-height: 18px; margin-right: 0; }
    }
    @media (max-height: 260px) {
        .footer { padding: 2px 0; }
        .footer .container p { font-size: 10px; }
        .footer .logoapp { max-height: 14px; }
    }
</style>

<footer class="footer">
    <div class="container">
        <img class="logoapp" src="/imag/jujuy00.png" alt="">
        <p>Ministerio de Salud de Jujuy- Dir. Gral. de Observatorio y Estadistica - Unidad Informatica</p>
        
        <?php
            // Última carga/modificación de datos del módulo actual (ver Config\UltimaActualizacion)
            helper('actualizacion');
            $fmtFecha = static fn ($f) => $f ? date('d/m/Y H:i', strtotime($f)) : 'sin dato';

            // Paneles con pestañas: cada pestaña tiene su propia fecha
            $footerPestanas = array_map($fmtFecha, ultima_actualizacion_pestanas());

            if ($footerPestanas) {
                $tabActual   = service('request')->getGet('tab');
                $tabActual   = isset($footerPestanas[$tabActual]) ? $tabActual : array_key_first($footerPestanas);
                $footerTexto = $footerPestanas[$tabActual];
            } else {
                $footerTexto = $fmtFecha(ultima_actualizacion());
            }
        ?>
        <p class="footer-last-update">
            <i class="fas fa-history"></i> Última actualización:
            <span id="footer-last-update-fecha"><?= esc($footerTexto) ?></span>
        </p>
    </div>
</footer>

<?php if ($footerPestanas): ?>
<script>
    // Al cambiar de pestaña (sin recargar), el footer muestra la fecha de esa pestaña
    (function () {
        var fechas = <?= json_encode($footerPestanas) ?>;
        var span   = document.getElementById('footer-last-update-fecha');
        if (!span) return;

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-tab]');
            if (btn && Object.prototype.hasOwnProperty.call(fechas, btn.dataset.tab)) {
                span.textContent = fechas[btn.dataset.tab];
            }
        });
    })();
</script>
<?php endif; ?>


<!-- <script>

    document.addEventListener('DOMContentLoaded', () => {

        // Get all "navbar-burger" elements
        const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

        // Check if there are any navbar burgers
        if ($navbarBurgers.length > 0) {

            // Add a click event on each of them
            $navbarBurgers.forEach( el => {
                el.addEventListener('click', () => {

                    // Get the target from the "data-target" attribute
                    const target = el.dataset.target;
                    const $target = document.getElementById(target);

                    // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
                    el.classList.toggle('is-active');
                    $target.classList.toggle('is-active');

                });
            });
        }

    });

</script> -->