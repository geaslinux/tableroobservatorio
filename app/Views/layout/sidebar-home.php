<style>
    /* ── SIDEBAR STYLES ── */
    .sidebar {
        height: 100vh;
        padding: 18px 16px 16px;
        background: linear-gradient(180deg, var(--navy-2) 0%, var(--navy-3) 100%);
        color: #fff; 
        display: flex; 
        flex-direction: column;
        box-shadow: 12px 0 30px rgba(5, 18, 33, 0.22);
        overflow: hidden !important; 
        white-space: nowrap; 
        width: 100%;
        transition: padding 0.28s ease;
        position: relative;
    }

    .sidebar-toggle-wrap {
        position: absolute; 
        top: 50%; 
        right: -15px;
        transform: translateY(-50%); 
        width: 32px; 
        height: 50px;
        display: flex; 
        justify-content: center; 
        align-items: center; 
        z-index: 105;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .brand-title,
    body.sidebar-collapsed .sidebar:not(:hover) .brand-subtitle,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-item span,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-chevron,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-sub-group,
    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer .user-info { display: none !important; }
    
    body.sidebar-collapsed .sidebar:not(:hover) .nav-section { font-size: 8px !important; letter-spacing: 0.5px; padding: 0; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 15px 0 10px; display: block !important; opacity: 0.6; }
    body.sidebar-collapsed .sidebar:not(:hover) .brand { justify-content: center; padding: 5px 0 15px; }
    body.sidebar-collapsed .sidebar:not(:hover) .brand img { margin: 0; width: 42px; }
    body.sidebar-collapsed .sidebar:not(:hover) .nav-item { justify-content: center; padding: 13px 0; }

    #sidebar-toggle { width:100%; height:100%; background: #294062; border:1px solid #fff; border-radius:12px; color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .25s ease; }
    #sidebar-toggle i { font-size: 14px; transition: .25s; }
    body.sidebar-collapsed #sidebar-toggle i { transform: rotate(180deg); }
    #sidebar-toggle:hover { background: rgba(255, 255, 255, 0.15); transform: scale(1.05); }

    .brand { display: flex; align-items: center; justify-content: center; gap: 12px; padding: 10px 8px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); margin-bottom: 5px; }
    .brand img { width:90px; height: auto; object-fit: contain; filter: brightness(1.2); margin-top: -20px; margin-bottom: -10px; }
    .brand-title { font-size: 12.5px; line-height: 1.05; font-weight: 700; letter-spacing: 0.2px; }

    .nav { flex: 1; overflow-y: auto; padding-right: 4px; }
    .nav-section { margin: 18px 0 8px; font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: rgba(255, 255, 255, 0.34); font-weight: 700; padding: 0 10px; }
    .nav-item { display: flex; align-items: center; gap: 12px; border-radius: 14px; padding: 13px 14px; font-size: 14px; color: rgba(255, 255, 255, 0.78); margin-bottom: 6px; transition: all .18s ease; }
    .nav-item:hover { background: rgba(255, 255, 255, 0.08); color: #fff; transform: translateX(2px); }
    .nav-item.active { background: rgba(8, 126, 166); color: #dffef9; box-shadow: inset 0 0 0 1px rgba(8, 126, 166); }
    .nav-item i { font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; opacity: 0.8; }

    .sidebar-update {
        margin-top: auto;
        padding: 15px 10px 5px;
        border-top: 1px solid rgba(255,255,255,0.08);
        font-size: 11px;
        color: rgba(255,255,255,0.4);
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-toggle-wrap">
        <button type="button" id="sidebar-toggle" title="Ocultar menú" aria-label="Ocultar menú">
            <i class="fas fa-angles-left"></i>
        </button>
    </div>
    <a href="<?= base_url(route_to('home')); ?>" class="brand">
        <img src="/imag/jujuy00.png" alt="Logo Jujuy">
        <div class="brand-text">
            <div class="brand-title">Ministerio de Salud</div>
        </div>
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
    <div class="sidebar-update">
        <i class="fas fa-rotate"></i>
        <div>
            <div class="update-label">Última actualización</div>
            <div class="update-value">13 jul 2026, 18:30</div> 
        </div>
    </div>
</aside>

<script>
    (function() {
        const button = document.getElementById('sidebar-toggle');
        if (!button) return;
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
        });
    })();
</script>