<aside class="sidebar" id="sidebar">
    <div class="sidebar-toggle-wrap">
        <button type="button" class="topbar-icon-btn" id="sidebar-toggle" title="Ocultar menú" aria-label="Ocultar menú">
            <i class="fas fa-angles-left"></i>
        </button>
    </div>

    <a href="<?= base_url(route_to('home')); ?>" class="brand">
        <img src="/imag/jujuy01.png" alt="Logo Jujuy">
    </a>

    <nav class="nav">
        <div class="nav-section">Principal</div>
        <a href="<?= base_url(route_to('home')); ?>" class="nav-item active">
            <i class="fas fa-house"></i>
            <span>Inicio</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-file-lines"></i>
            <span>Digesto</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-book"></i>
            <span>Manuales</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-chart-column"></i>
            <span>Indicadores</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-chart-pie"></i>
            <span>Estadísticas</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fas fa-database"></i>
            <span>Carga de datos</span>
        </a>
    </nav>

    <!-- Carga de datos más reciente de todo el sistema (Home::index → ultima_actualizacion()) -->
    <div class="sidebar-update" title="Última actualización: <?= esc($ultimaActTexto) ?>">
        <span class="update-icon"><i class="fas fa-rotate"></i></span>
        <div class="update-text">
            <div class="update-label">Última actualización</div>
            <div class="update-value"><?= esc($ultimaActTexto) ?></div>
        </div>
    </div>
</aside>