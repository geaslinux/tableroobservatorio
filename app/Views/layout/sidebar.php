<?php
$userName = user()->username ?? 'Usuario Demo';
$userRole = 'Usuario';
$authModel = model('Myth\Auth\Models\GroupModel');
$groups = $authModel->getGroupsForUser(user_id());
if (!empty($groups)) {
    $userRole = ucfirst($groups[0]['name']);
}

if (!function_exists('generateColorFromString')) {
    function generateColorFromString($string) {
        $hash = md5($string);
        $color = '#';
        for ($i = 0; $i < 3; $i++) {
            $component = substr($hash, $i * 2, 2);
            $decimal = hexdec($component);
            $adjustedDecimal = str_pad(dechex(max(50, min(200, $decimal))), 2, '0', STR_PAD_LEFT);
            $color .= $adjustedDecimal;
        }
        return $color;
    }
}

$userAvatarColor = generateColorFromString($userName);
?>

<style>
    .sidebar {
        width: calc(var(--sidebar-w) + var(--sidebar-tab));
        background: linear-gradient(180deg, var(--navy-2) 0%, var(--navy-3) 100%);
        position: sticky;
        top: var(--topbar-h);
        height: calc(100vh - var(--topbar-h));
        display: flex;
        flex-direction: column;
        overflow: hidden !important;
        white-space: nowrap;
        transition: width 0.22s ease;
        z-index: 90;
        align-self: start;
        padding: 0 10px 5px 10px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar a,
    .sidebar button,
    .sidebar .nav-item,
    .sidebar .nav-sub,
    .sidebar .nav-section-label {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body.sidebar-collapsed .sidebar {
        width: var(--sidebar-collapsed-w) !important;
    }

    body.sidebar-collapsed .sidebar:hover {
        width: calc(var(--sidebar-w) + var(--sidebar-tab)) !important;
        box-shadow: 15px 0 35px rgba(5, 18, 33, 0.45);
        overflow-y: auto !important;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .brand-title,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-item span,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-chevron,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-sub-group,
    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer > div:last-child {
        display: none !important;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .nav-section-label {
        font-size: 8px !important;
        letter-spacing: 0.5px;
        padding: 0;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 15px 0 10px;
        display: block !important;
        opacity: 0.6;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .brand {
        justify-content: center;
        padding: 5px 0 15px;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .brand img {
        margin: -10px 0 -20px 0;
        width: 220%;
        max-width: none;
        height: auto;
        object-fit: contain;
        margin-left: -120%;
        display: block;
        transition: all 0.28s ease;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .nav-item {
        font-size: 0;
        line-height: 0;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .nav-item {
        justify-content: center;
        padding: 13px 0;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer {
        padding: 15px 0 5px;
        text-align: center;
        align-items: center;
        justify-content: center;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-footer::before {
        content: "\f2f2";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.35);
        display: inline-block;
    }

    body.sidebar-collapsed .sidebar:hover .sidebar-footer::before {
        display: none !important;
    }

    .sidebar-toggle-wrap {
        position: absolute;
        top: 50%;
        left: calc(100% - 17px);
        transform: translate(-50%, -50%);
        width: 42px;
        height: 64px;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 105;
    }

    body.sidebar-collapsed .app-shell {
        grid-template-columns: var(--sidebar-collapsed-w) 1fr;
    }

    body.sidebar-collapsed .sidebar-toggle-wrap {
        left: calc(100% - 17px);
    }

    .topbar-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        background: transparent;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.55);
        font-size: 14px;
        transition: background 0.15s, color 0.15s;
        text-decoration: none;
    }

    .topbar-icon-btn:hover {
        background: rgba(255, 0, 0, 0.1);
        color: #fff;
    }

    #sidebar-toggle {
        width: 50%;
        height: 80%;
        background: #294062;
        border: 1px solid #fff;
        border-radius: 12px;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: none;
        transition: all .25s ease;
    }

    #sidebar-toggle i {
        font-size: 15px;
        transition: .25s;
    }

    body.sidebar-collapsed #sidebar-toggle i {
        transform: rotate(180deg);
    }

    #sidebar-toggle:hover {
        transform: translateY(-1px);
        background: rgba(255,255,255,0.16);
    }

    .sidebar-nav { flex: 1; padding: 6px 0 16px; }

    .nav-section-label {
        font-size: 9px;
        color: rgba(255,255,255,0.25);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 18px 20px 6px;
        font-weight: 700;
    }

    .nav-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px 14px 25px;
        font-size: 14px;
        font-weight: 500;
        color: rgba(255,255,255,0.78);
        cursor: pointer;
        transition: all 0.15s;
        border-left: 3px solid transparent;
        text-decoration: none;
        letter-spacing: 0.1px;
        border-radius: 14px;
    }

    .nav-item:hover {
        background: var(--navy-hover);
        color: rgba(255,255,255,0.95);
        text-decoration: none;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .nav-item {
        justify-content: center;
        padding: 13px 0;
        gap: 0;
        font-size: 0;
        line-height: 0;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .nav-item span,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-chevron,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-sub-group,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-section-label {
        display: none !important;
    }

    .nav-item.active {
        background: rgba(8, 126, 166);
        color: #dffef9;
        box-shadow: inset 0 0 0 1px rgba(8, 126, 166);
    }

    .nav-item i { font-size: 18px; width: 22px; text-align: center; flex-shrink: 0; opacity: 0.8; }

    .nav-item.active i,
    .nav-item:hover i {
        opacity: 1;
    }

    .nav-item.danger { color: rgba(255,110,110,0.6); }
    .nav-item.danger:hover { color: rgba(255,130,130,1); background: rgba(220,38,38,0.08); }

    .nav-chevron {
        position: absolute;
        right: 35px;
        font-size: 11px;
        transition: transform 0.22s;
        color: rgba(255,255,255,0.22);
    }

    .nav-item.open .nav-chevron { transform: rotate(180deg); }

    .nav-sub-group {
        overflow: hidden;
        max-height: 0;
        transition: max-height 0.28s ease;
    }

    .nav-sub-group.open { max-height: 500px; }

    .nav-sub {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 20px 10px 56px; font-size: 13px;
        color: rgba(255,255,255,0.4); text-decoration: none;
        transition: color 0.15s, background 0.15s; border-left: 3px solid transparent;
        border-radius: 14px;
    }

    .nav-sub:hover {
        color: rgba(255,255,255,0.82); background: rgba(255,255,255,0.04); text-decoration: none;
    }

    .nav-sub.active {
        color: #fff !important;
        background: rgba(8, 126, 166, 0.4) !important;
    }

    .nav-sub i { font-size: 13px; width: 15px; text-align: center; }

    .sidebar-footer {
        width: var(--sidebar-w);
        padding: 16px 20px;
        border-top: 1px solid rgba(255,255,255,0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #53d8c9, #1fa996);
        color: #07263d;
        font-weight: 800;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }

    .avatar-btn {
        width: 48px;
        height: 48px;
        border: none;
        border-radius: 50%;
        display: grid;
        place-items: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        position: relative;
        padding: 0;
        outline: none;
    }

    .avatar-btn:hover {
        filter: brightness(90%);
    }

    .avatar-initials {
        color: #fff;
        font-size: 20px;
        font-weight: bold;
        font-family: sans-serif;
        line-height: 1;
    }

    .user-name {
        font-size: 13.5px;
        font-weight: 700;
        color: #fff;
    }

    .user-role {
        font-size: 10px;
        color: rgba(255,255,255,0.32);
        text-transform: uppercase;
        letter-spacing: 0.9px;
        font-weight: 600;
        margin-top: 2px;
    }

    .profile-dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid var(--border);
        border-radius: 16px;
        min-width: 220px;
        box-shadow: var(--shadow);
        z-index: 100;
        padding: 8px;
        backdrop-filter: blur(10px);
    }

    .dropdown-menu .nav-section {
        margin: 18px 0 8px;
        font-size: 10px;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 700;
        padding: 6px 14px 4px;
        transition: all 0.2s ease;
    }

    .dropdown-menu.show { display: block; }

    .dropdown-user-info {
        padding: 8px 14px;
        display: flex;
        flex-direction: column;
    }

    .dropdown-user-info strong { font-size: 14px; color: var(--navy); }
    .dropdown-user-info span { font-size: 11px; color: var(--muted); }

    .dropdown-item {
        text-decoration: none !important;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        color: var(--text);
        font-size: 13.5px;
        font-weight: 600;
        border-radius: 10px;
        transition: background 0.15s ease, color 0.15s ease;
    }

    .dropdown-item:hover {
        background: var(--blue-soft);
        color: var(--blue);
    }

    .dropdown-item:hover,
    .dropdown-item:focus {
        text-decoration: none !important;
    }

    .dropdown-item.danger { color: #ea3b3b; }
    .dropdown-item.danger:hover { background: rgba(234, 59, 59, 0.08); }

    .dropdown-item i { width: 18px; text-align: center; font-size: 14px; }

    body.sidebar-collapsed .sidebar .nav-section-label,
    body.sidebar-collapsed .sidebar:not(:hover) .nav-section-label {
        display: block !important;
        visibility: visible !important;
        font-size: 8px !important;
        letter-spacing: 0.5px;
        padding: 0;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 15px 0 10px;
        opacity: 0.6;
        color: rgba(255,255,255,0.25);
    }

    body.sidebar-collapsed .sidebar:hover .nav-section-label {
        font-size: 9px !important;
        color: rgba(255,255,255,0.25) !important;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 18px 20px 6px !important;
        margin: 0 !important;
        text-align: left !important;
        white-space: normal;
        overflow: visible;
        text-overflow: clip;
        opacity: 1;
        display: block !important;
        visibility: visible !important;
    }

    body.sidebar-collapsed .sidebar:hover .nav-item {
        justify-content: flex-start;
        padding: 14px 20px 14px 25px;
        gap: 14px;
        font-size: 14px;
        line-height: normal;
    }

    body.sidebar-collapsed .sidebar:hover .nav-item span,
    body.sidebar-collapsed .sidebar:hover .nav-chevron,
    body.sidebar-collapsed .sidebar:hover .sidebar-footer > div:last-child {
        display: inline-block !important;
    }

    body.sidebar-collapsed .sidebar:hover .sidebar-footer {
        padding: 16px 20px;
        text-align: left;
        align-items: center;
        justify-content: flex-start;
    }

    body.sidebar-collapsed .sidebar:hover .sidebar-footer::before {
        display: none !important;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-toggle-wrap">
        <button type="button" class="topbar-icon-btn" id="sidebar-toggle" title="Ocultar menú" aria-label="Ocultar menú">
            <i class="fas fa-angles-left"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Principal</div>
        <a href="<?= base_url(route_to('inicio_views')); ?>" class="nav-item">
            <i class="fas fa-th-large"></i> Panel de inicio
        </a>

        <div class="nav-section-label">Módulos</div>

        <a href="#" class="nav-item" id="nav-sub-hospitalario" onclick="toggleSub(event,'sub-hospitalario')">
            <i class="fas fa-hospital"></i> Hospitalario
            <i class="fas fa-chevron-down nav-chevron"></i>
        </a>
        <div class="nav-sub-group" id="sub-hospitalario">
            <a href="<?= base_url(route_to('ambulatorio_list')); ?>" class="nav-sub"><i class="fas fa-walking"></i> Ambulatorio</a>
            <a href="<?= base_url(route_to('guardia_views')); ?>" class="nav-sub"><i class="fas fa-briefcase-medical"></i> Guardia</a>
            <a href="<?= base_url(route_to('quirofano_views')); ?>" class="nav-sub"><i class="fas fa-syringe"></i> Quirófano</a>
        </div>

        <a href="#" class="nav-item" id="nav-sub-prehosp" onclick="toggleSub(event,'sub-prehosp')">
            <i class="fas fa-ambulance"></i> Prehospitalario
            <i class="fas fa-chevron-down nav-chevron"></i>
        </a>
        <div class="nav-sub-group" id="sub-prehosp">
            <a href="<?= base_url(route_to('base_list')); ?>" class="nav-sub"><i class="fas fa-database"></i> Base</a>
            <a href="<?= base_url(route_to('movil_list')); ?>" class="nav-sub"><i class="fas fa-car"></i> Móviles</a>
            <a href="<?= base_url(route_to('atencion_list')); ?>" class="nav-sub"><i class="fas fa-notes-medical"></i> Atenciones realizadas</a>
            <a href="<?= base_url(route_to('identificacion_list')); ?>" class="nav-sub"><i class="fas fa-home"></i> Internación domiciliaria</a>
            <a href="<?= base_url(route_to('asistencia_list')); ?>" class="nav-sub"><i class="fas fa-hands-helping"></i> Asistencias realizadas</a>
        </div>

        <a href="#" class="nav-item" id="nav-sub-paciente" onclick="toggleSub(event,'sub-paciente')">
            <i class="fas fa-user-injured"></i> Gestión paciente
            <i class="fas fa-chevron-down nav-chevron"></i>
        </a>
        <div class="nav-sub-group" id="sub-paciente">
            <a href="<?= base_url(route_to('consulta_reclamo_list')); ?>" class="nav-sub"><i class="fas fa-comments"></i> Consultas y reclamos</a>
            <a href="<?= base_url(route_to('chat_bot_list')); ?>" class="nav-sub"><i class="fas fa-robot"></i> Chat bot turnos otorgados</a>
            <a href="<?= base_url(route_to('turno_hospitalario_list')); ?>" class="nav-sub"><i class="fas fa-calendar-alt"></i> Gestión de especialidades</a>
            <a href="<?= base_url(route_to('call_center_list')); ?>" class="nav-sub"><i class="fas fa-phone"></i> 0800 Call center</a>
        </div>

        <a href="#" class="nav-item" id="nav-sub-salud" onclick="toggleSub(event,'sub-salud')">
            <i class="fas fa-brain"></i> Salud mental
            <i class="fas fa-chevron-down nav-chevron"></i>
        </a>
        <div class="nav-sub-group" id="sub-salud">
            <a href="<?= base_url(route_to('electrodependiente_list')); ?>" class="nav-sub"><i class="fas fa-bolt"></i> Electrodependientes</a>
        </div>

        <a href="#" class="nav-item" id="nav-sub-servicios" onclick="toggleSub(event,'sub-servicios')">
            <i class="fas fa-project-diagram"></i> <span>Servicios <br> transversales</span>
            <i class="fas fa-chevron-down nav-chevron"></i>
        </a>
        <div class="nav-sub-group" id="sub-servicios">
            <a href="<?= base_url(route_to('cantidad_operativo_list')); ?>" class="nav-sub"><i class="fas fa-clipboard-list"></i> Cantidad de operativos</a>
            <a href="<?= base_url(route_to('transfusion_list')); ?>" class="nav-sub"><i class="fas fa-tint"></i> Transfusión mensual</a>
        </div>

        <div class="nav-section-label">Administración</div>
        <a href="<?= base_url(route_to('list_users')); ?>" class="nav-item"><i class="fas fa-user-cog"></i> Usuarios</a>
        <a href="<?= base_url(route_to('groups_list')); ?>" class="nav-item"><i class="fas fa-users-cog"></i> Grupos</a>
        <a href="<?= base_url(route_to('logout')); ?>" class="nav-item danger"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
</aside>

<script>
    (function() {
        const collapsed = localStorage.getItem('inicioSidebarCollapsed') === '1';
        if (collapsed) document.body.classList.add('sidebar-collapsed');
    })();

    let openSubGroupId = null;

    function toggleSub(event, subGroupId) {
        event.preventDefault();
        const subGroup = document.getElementById(subGroupId);
        const navItem = document.getElementById('nav-' + subGroupId) || event.currentTarget;
        const isOpen = subGroup.classList.contains('open');

        document.querySelectorAll('.nav-sub-group.open').forEach(g => {
            if (g.id !== subGroupId) g.classList.remove('open');
        });
        document.querySelectorAll('.nav-item.open').forEach(t => {
            if (t !== navItem) t.classList.remove('open');
        });

        if (!isOpen) {
            subGroup.classList.add('open');
            navItem.classList.add('open');
            openSubGroupId = subGroupId;
        } else {
            subGroup.classList.remove('open');
            navItem.classList.remove('open');
            openSubGroupId = null;
        }
    }

    document.getElementById('sidebar').addEventListener('mouseleave', function() {
        if (document.body.classList.contains('sidebar-collapsed')) {
            document.querySelectorAll('.nav-sub-group').forEach(group => group.classList.remove('open'));
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('open'));
            openSubGroupId = null;
        }
    });

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
            document.querySelectorAll('.nav-sub-group').forEach(group => group.classList.remove('open'));
            document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('open'));
            openSubGroupId = null;
        });
    })();

    (function() {
    const currentPath = window.location.pathname.replace(/\/+$/, '');
    if (!currentPath) return;

    const clearActiveState = () => {
        document.querySelectorAll('.sidebar .nav-item.active, .sidebar .nav-sub.active')
            .forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.sidebar .nav-sub-group.open, .sidebar .nav-item.open')
            .forEach(item => item.classList.remove('open'));
    };

    const activateGroup = (groupId) => {
        const group = document.getElementById('sub-' + groupId);
        const parent = document.getElementById('nav-sub-' + groupId);
        if (!group || !parent) return;

        clearActiveState();
        group.classList.add('open');
        parent.classList.add('open', 'active');
    };

    // Primero busca el enlace más específico del módulo actual.
    const links = Array.from(document.querySelectorAll('.sidebar a[href]:not([href="#"])'));
    let bestLink = null;
    let maxScore = 0;

    links.forEach(el => {
        const href = el.getAttribute('href');
        if (!href) return;

        try {
            const linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '');
            
            if (currentPath === linkPath) {
                bestLink = el;
                maxScore = 1000;
            } else if (currentPath.startsWith(linkPath + '/')) {
                const score = linkPath.length;
                if (score > maxScore) {
                    bestLink = el;
                    maxScore = score;
                }
            }
        } catch(e) {}
    });

    if (bestLink) {
        clearActiveState();
        activateSidebarItem(bestLink);
    } else {
        // Los paneles resumen no son enlaces secundarios, por eso se asignan explícitamente.
        const summaryGroups = [
            { suffix: '/basev', group: 'prehosp' },
            { suffix: '/pacientev', group: 'paciente' },
            { suffix: '/saludmental', group: 'salud' },
            { suffix: '/serviciov', group: 'servicios' },
            { suffix: '/hospitalariov', group: 'hospitalario' },
        ];
        const summary = summaryGroups.find(item => currentPath.endsWith(item.suffix));
        if (summary) activateGroup(summary.group);
    }

    // Función auxiliar para activar un enlace y abrir a su contenedor padre
    function activateSidebarItem(el) {
        el.classList.add('active');

        if (el.classList.contains('nav-sub')) {
            const group = el.closest('.nav-sub-group');
            if (group) {
                group.classList.add('open');
                const parentNavItem = group.previousElementSibling;
                if (parentNavItem && parentNavItem.classList.contains('nav-item')) {
                    parentNavItem.classList.add('open', 'active');
                }
            }
        }
    }
})();
</script>