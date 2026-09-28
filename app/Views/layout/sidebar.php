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
        padding: 11px 0;
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

    /* A la altura de la etiqueta "Principal" */
    .sidebar-toggle-wrap {
        position: absolute;
        top: 6px;
        left: calc(100% - 17px);
        transform: translateX(-50%);
        width: 42px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 105;
    }

    body.sidebar-collapsed .app-shell {
        grid-template-columns: var(--sidebar-collapsed-w) 1fr;
    }

    /* Colapsado: el botón solo aparece al pasar el mouse (a la altura de "Principal", a la derecha).
       Sin mouse encima se oculta para no tapar la etiqueta. */
    body.sidebar-collapsed .sidebar-toggle-wrap {
        left: calc(100% - 17px);
        transition: opacity 0.2s ease;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-toggle-wrap {
        opacity: 0;
        pointer-events: none;
    }

    /* Si se llega con el teclado (Tab), se muestra igual */
    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-toggle-wrap:has(:focus-visible) {
        opacity: 1;
        pointer-events: auto;
    }

    /* Colapsado: sin barra de scroll visible para que los íconos queden centrados (sigue deslizando con la rueda) */
    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-nav {
        scrollbar-width: none;
    }

    body.sidebar-collapsed .sidebar:not(:hover) .sidebar-nav::-webkit-scrollbar {
        width: 0;
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
        width: 28px;
        height: 28px;
        padding: 0;
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0.05) 100%);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 9px;
        color: rgba(255, 255, 255, 0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.10);
        backdrop-filter: blur(6px);
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
    }

    #sidebar-toggle i {
        font-size: 12px;
        transition: transform 0.25s ease;
    }

    body.sidebar-collapsed #sidebar-toggle i {
        transform: rotate(180deg);
    }

    #sidebar-toggle:hover {
        background: linear-gradient(145deg, #0a93c2 0%, #087ea6 100%);
        border-color: rgba(83, 216, 201, 0.6);
        color: #fff;
        box-shadow: 0 4px 14px rgba(8, 126, 166, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.18);
    }

    #sidebar-toggle:active {
        transform: scale(0.92);
    }

    #sidebar-toggle:focus-visible {
        outline: 2px solid rgba(83, 216, 201, 0.85);
        outline-offset: 2px;
    }

    /* La lista tiene su propio scroll para que siempre se pueda ver todo (zoom, pantallas bajas) */
    .sidebar-nav {
        flex: 1;
        min-height: 0;
        padding: 6px 0 16px;
        overflow-y: auto;
        overflow-x: hidden;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    }

    .sidebar-nav::-webkit-scrollbar { width: 6px; }
    .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
    .sidebar-nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 6px;
    }
    .sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.35); }

    .nav-section-label {
        font-size: 9px;
        color: rgba(255,255,255,0.25);
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 14px 16px 5px;
        font-weight: 700;
    }

    .nav-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 14px 10px 18px;
        font-size: 13px;
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
        padding: 11px 0;
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

    .nav-item i { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; opacity: 0.8; }

    .nav-item.active i,
    .nav-item:hover i {
        opacity: 1;
    }

    .nav-item.danger { color: rgba(255,110,110,0.6); }
    .nav-item.danger:hover { color: rgba(255,130,130,1); background: rgba(220,38,38,0.08); }

    .nav-chevron {
        position: absolute;
        right: 10px;
        font-size: 10px;
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
        display: flex; align-items: center; gap: 9px;
        padding: 8px 14px 8px 44px; font-size: 12.5px;
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

    .nav-sub i { font-size: 12px; width: 14px; text-align: center; }

    .nav-sub-toggle { position: relative; cursor: pointer; }
    .nav-sub-toggle .nav-chevron { right: 20px; }
    .nav-sub-group .nav-sub-group .nav-sub {
        padding-left: 46px;
        font-size: 11.5px;
    }

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
        padding: 14px 16px 5px !important;
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
        padding: 10px 14px 10px 18px;
        gap: 11px;
        font-size: 13px;
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

    /* ===== Responsive ===== */
    .sidebar-mobile-btn,
    .sidebar-backdrop {
        display: none;
    }

    /* Pantallas bajas: menos espacio vertical entre ítems */
    @media (max-height: 700px) {
        .nav-item { padding-top: 8px; padding-bottom: 8px; }
        .nav-sub { padding-top: 6px; padding-bottom: 6px; }
        .nav-section-label { padding-top: 10px; }
    }

    /* Móviles y tablets chicas: el sidebar pasa a ser un panel deslizable */
    @media (max-width: 768px) {
        .sidebar,
        body .sidebar:hover {
            position: fixed;
            top: var(--topbar-h);
            left: 0;
            bottom: 0;
            /* align-self: start (del escritorio) hace que un elemento fixed crezca con su contenido y no se pueda deslizar */
            align-self: stretch;
            height: calc(100vh - var(--topbar-h));
            height: calc(100dvh - var(--topbar-h));
            width: min(240px, 80vw) !important;
            padding: 0 6px 6px;
            transform: translateX(-100%);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            z-index: 1000;
            box-shadow: none;
        }

        body.sidebar-mobile-open .sidebar {
            transform: translateX(0);
            box-shadow: 15px 0 35px rgba(5, 18, 33, 0.45);
        }

        .sidebar-toggle-wrap { display: none; }

        /* Menú compacto en el panel móvil (también se activa con zoom alto) */
        .sidebar .nav-section-label { font-size: 8.5px; padding: 10px 12px 4px; }
        .sidebar .nav-item { font-size: 12px; gap: 9px; padding: 7px 10px 7px 12px; border-radius: 10px; }
        .sidebar .nav-item i { font-size: 14px; width: 18px; }
        .sidebar .nav-chevron { right: 10px; font-size: 9px; }
        .sidebar .nav-sub { font-size: 11.5px; gap: 7px; padding: 6px 10px 6px 34px; border-radius: 10px; }
        .sidebar .nav-sub i { font-size: 11px; width: 13px; }
        .sidebar .nav-sub-toggle .nav-chevron { right: 12px; }
        .sidebar .nav-sub-group .nav-sub-group .nav-sub { padding-left: 40px; font-size: 11px; }

        .sidebar-backdrop {
            display: block;
            position: fixed;
            inset: var(--topbar-h) 0 0 0;
            background: rgba(5, 18, 33, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        body.sidebar-mobile-open .sidebar-backdrop {
            opacity: 1;
            visibility: visible;
        }

        body.sidebar-mobile-open { overflow: hidden; }

        .sidebar-mobile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            left: 12px;
            bottom: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.6);
            background: #294062;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
            z-index: 1001;
        }
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
            <a href="<?= base_url(route_to('internacion_views')); ?>" class="nav-sub"><i class="fas fa-layer-group"></i> Internación</a>
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
            <i class="fas fa-project-diagram"></i> <span>Servicios transversales</span>
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

<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
<button type="button" class="sidebar-mobile-btn" id="sidebar-mobile-btn" title="Abrir menú" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
    <i class="fas fa-bars"></i>
</button>

<script>
    (function() {
        const collapsed = localStorage.getItem('inicioSidebarCollapsed') === '1';
        if (collapsed) document.body.classList.add('sidebar-collapsed');
    })();

    let openSubGroupId = null;
    let restoreSidebarActive = null;

    function toggleSub(event, subGroupId) {
        event.preventDefault();
        const subGroup = document.getElementById(subGroupId);
        const navItem = document.getElementById('nav-' + subGroupId) || event.currentTarget;
        const isOpen = subGroup.classList.contains('open');
        const parentGroup = subGroup.parentElement.closest('.nav-sub-group');
        const siblingGroups = parentGroup
            ? parentGroup.querySelectorAll(':scope > .nav-sub-group.open')
            : document.querySelectorAll('.sidebar-nav > .nav-sub-group.open');
        const siblingItems = parentGroup
            ? parentGroup.querySelectorAll(':scope > .nav-sub-toggle.open')
            : document.querySelectorAll('.sidebar-nav > .nav-item.open');

        siblingGroups.forEach(group => {
            if (group !== subGroup) {
                group.classList.remove('open');
                group.querySelectorAll('.nav-sub-group.open, .nav-sub-toggle.open')
                    .forEach(item => item.classList.remove('open'));
            }
        });
        siblingItems.forEach(item => {
            if (item !== navItem) item.classList.remove('open');
        });

        if (!isOpen) {
            subGroup.classList.add('open');
            navItem.classList.add('open');
            openSubGroupId = subGroupId;
            if (typeof restoreSidebarActive === 'function') restoreSidebarActive(subGroupId);
        } else {
            subGroup.classList.remove('open');
            // Cerrar el menú no cambia el módulo activo; solo oculta sus enlaces.
            navItem.classList.remove('open');
            openSubGroupId = null;
        }
    }

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
            if (!document.body.classList.contains('sidebar-collapsed') && typeof restoreSidebarActive === 'function') {
                restoreSidebarActive();
            }
        });
    })();

    // Sidebar en móviles: panel deslizable con botón flotante
    (function() {
        const mobileBtn = document.getElementById('sidebar-mobile-btn');
        const backdrop = document.getElementById('sidebar-backdrop');
        const sidebar = document.getElementById('sidebar');
        if (!mobileBtn || !backdrop || !sidebar) return;

        const mq = window.matchMedia('(max-width: 768px)');

        const setOpen = (open) => {
            document.body.classList.toggle('sidebar-mobile-open', open);
            mobileBtn.innerHTML = `<i class="fas fa-${open ? 'xmark' : 'bars'}"></i>`;
            mobileBtn.title = open ? 'Cerrar menú' : 'Abrir menú';
            mobileBtn.setAttribute('aria-label', mobileBtn.title);
            mobileBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        // En móvil el menú siempre se muestra completo; al volver a escritorio se respeta la preferencia guardada.
        const syncMode = () => {
            if (mq.matches) {
                document.body.classList.remove('sidebar-collapsed');
            } else {
                setOpen(false);
                document.body.classList.toggle('sidebar-collapsed', localStorage.getItem('inicioSidebarCollapsed') === '1');
            }
        };

        mobileBtn.addEventListener('click', () => setOpen(!document.body.classList.contains('sidebar-mobile-open')));
        backdrop.addEventListener('click', () => setOpen(false));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') setOpen(false);
        });
        sidebar.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]:not([href="#"])');
            if (link && mq.matches) setOpen(false);
        });

        if (mq.addEventListener) mq.addEventListener('change', syncMode);
        else mq.addListener(syncMode);
        syncMode();
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

    const activateGroup = (groupId, nestedGroupId = null) => {
        const group = document.getElementById('sub-' + groupId);
        const parent = document.getElementById('nav-sub-' + groupId);
        if (!group || !parent) return;

        clearActiveState();
        group.classList.add('open');
        parent.classList.add('open', 'active');

        if (nestedGroupId) {
            const nestedGroup = document.getElementById('sub-' + nestedGroupId);
            const nestedParent = document.getElementById('nav-sub-' + nestedGroupId);
            if (nestedGroup && nestedParent) {
                nestedGroup.classList.add('open');
                nestedParent.classList.add('open', 'active');
            }
        }
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

    const summaryGroups = [
        { suffix: '/basev', group: 'prehosp' },
        { suffix: '/pacientev', group: 'paciente' },
        { suffix: '/saludmental', group: 'salud' },
        { suffix: '/serviciov', group: 'servicios' },
        { suffix: '/hospitalariov', group: 'hospitalario' },
    ];
    const summary = summaryGroups.find(item => currentPath.endsWith(item.suffix));

    restoreSidebarActive = () => {
        document.querySelectorAll('.sidebar .nav-item.active, .sidebar .nav-sub.active')
            .forEach(item => item.classList.remove('active'));

        if (bestLink) {
            activateSidebarItem(bestLink);
        } else if (summary) {
            const parent = document.getElementById('nav-sub-' + summary.group);
            const parentGroup = document.getElementById('sub-' + summary.group);
            if (parent) parent.classList.add('open', 'active');
            if (parentGroup) parentGroup.classList.add('open');

            if (summary.nested) {
                const nested = document.getElementById('nav-sub-' + summary.nested);
                const nestedGroup = document.getElementById('sub-' + summary.nested);
                if (nested && nestedGroup) {
                    nested.classList.add('open', 'active');
                    nestedGroup.classList.add('open');
                }
            }
        }
    };

    clearActiveState();
    restoreSidebarActive();

    // Función auxiliar para activar un enlace y abrir a su contenedor padre
    function activateSidebarItem(el) {
        el.classList.add('active');

        if (el.classList.contains('nav-sub')) {
            let group = el.closest('.nav-sub-group');
            while (group) {
                group.classList.add('open');
                const parentNavItem = group.previousElementSibling;
                if (parentNavItem && (parentNavItem.classList.contains('nav-item') || parentNavItem.classList.contains('nav-sub'))) {
                    parentNavItem.classList.add('open', 'active');
                }
                group = group.parentElement.closest('.nav-sub-group');
            }
        }
    }
})();
</script>