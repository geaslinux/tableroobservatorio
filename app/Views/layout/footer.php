<link rel="stylesheet" href="/css/style.css">

<footer class="footer">
    <div class="container">
        <img class="logoapp" src="/imag/jujuy00.png" alt="">
        <p>Ministerio de Salud de Jujuy- Dir. Gral. de Observatorio y Estadistica del Ministerio de Salud</p>
        
        <?php if (isset($ultimaVista) && $ultimaVista != '2000-01-01 00:00:00'): ?>
            <p class="footer-last-update">
                <i class="fas fa-history"></i> Última actualización: <?= date('d/m/Y H:i', strtotime($ultimaVista)) ?>
            </p>
        <?php endif; ?>
    </div>
</footer>


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