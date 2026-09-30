/* Scripts del Home (antes inline en app/Views/admin/home.php) */
    (function() {
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const fecha = new Date();
        const dia = `${fecha.getDate()} ${meses[fecha.getMonth()]} ${fecha.getFullYear()}`;
        // Período: solo la fecha actual (según el reloj del usuario)
        const element = document.getElementById('topbar-date');
        if (element) element.textContent = dia;

        const lastUpdate = document.getElementById('last-update-label');
        if (lastUpdate) {
            const horas = String(fecha.getHours()).padStart(2, '0');
            const minutos = String(fecha.getMinutes()).padStart(2, '0');
            lastUpdate.innerHTML = `<i class="fas fa-clock"></i> Última sincronización: ${dia} - ${horas}:${minutos} hs`;
        }
    })();

    function toggleGroup(event, id) {
        event.preventDefault();
        const group = document.getElementById(id);
        const trigger = event.currentTarget;
        const isOpen = group.classList.contains('open');

        document.querySelectorAll('.nav-sub-group.open').forEach(item => {
            if (item.id !== id) item.classList.remove('open');
        });
        document.querySelectorAll('.nav-item.open').forEach(item => {
            if (item !== trigger) item.classList.remove('open');
        });

        group.classList.toggle('open', !isOpen);
        trigger.classList.toggle('open', !isOpen);
    }

    (function() {
        const path = window.location.pathname;
        document.querySelectorAll('.nav-sub, .nav-item').forEach(link => {
            const href = link.getAttribute('href');
            if (!href || href === '#') return;
            const cleanHref = href.split('/').pop();
            if (cleanHref && path.includes(cleanHref)) {
                link.classList.add('active');
            }
        });
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        // Alternar el menú al hacer clic en el botón
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });

        // Cerrar el menú si se hace clic fuera de él
        document.addEventListener('click', function(e) {
            if (!profileMenu.contains(e.target) && e.target !== profileBtn) {
                profileMenu.classList.remove('show');
            }
        });
    });

    // Comportamiento del botón de colapsar barra lateral
    (function() {
        const button = document.getElementById('sidebar-toggle');
        if (!button) return;

        // Recuperar el estado guardado previamente
        const savedCollapsed = localStorage.getItem('inicioSidebarCollapsed');
        if (savedCollapsed === '1') {
            document.body.classList.add('sidebar-collapsed');
        }

        const syncButton = () => {
            const collapsed = document.body.classList.contains('sidebar-collapsed');
            button.title = collapsed ? 'Mostrar menú' : 'Ocultar menú';
            button.setAttribute('aria-label', button.title);
            button.innerHTML = `<i class="fas fa-${collapsed ? 'bars' : 'angles-left'}"></i>`;
        };

        syncButton();

        button.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('inicioSidebarCollapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
            syncButton();
            
            // Cerrar subgrupos abiertos para evitar visualizaciones extrañas en modo colapsado
            document.querySelectorAll('.nav-sub-group').forEach(group => group.classList.remove('open'));
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('open'));
        });
    })();

    // Móvil / tablet: el botón ☰ abre el sidebar; se cierra con el fondo oscuro, Escape o al elegir una opción
    (function() {
        const btn = document.getElementById('mobile-menu-btn');
        const backdrop = document.getElementById('sidebar-backdrop');
        const sidebar = document.getElementById('sidebar');
        if (!btn || !backdrop || !sidebar) return;

        const mq = window.matchMedia('(max-width: 992px)');
        const setOpen = (open) => {
            document.body.classList.toggle('sidebar-mobile-open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
            btn.innerHTML = `<i class="fas fa-${open ? 'xmark' : 'bars'}"></i>`;
        };

        btn.addEventListener('click', () => setOpen(!document.body.classList.contains('sidebar-mobile-open')));
        backdrop.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
        sidebar.addEventListener('click', (e) => {
            if (mq.matches && e.target.closest('a[href]:not([href="#"])')) setOpen(false);
        });

        // Al volver a pantalla grande, el panel móvil se cierra
        const sync = () => { if (!mq.matches) setOpen(false); };
        if (mq.addEventListener) mq.addEventListener('change', sync); else mq.addListener(sync);
    })();