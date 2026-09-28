<?php
    // Logic for header variables
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
   .topbar {
        --navy:       #0e2a4d;
        --navy-2:     #132f57;
        --navy-3:     #081c34;
        --blue:       #2b7de9;
        --text:       #1d2b3c;
        --shadow:      0 18px 40px rgba(10, 28, 48, 0.08);
        --muted:      #66778a;
        --blue-soft:  rgba(43, 125, 233, 0.10);
        --navy-dark:  #111e30;
        --navy-hover: rgba(255,255,255,0.07);
        --teal:       #3489ff;
        --teal-light: #00d4bc;
        --teal-bg:    rgba(0,180,160,0.12);
        --gray-bg:    #e8eaed;
        --white:      #ffffff;
        --text-main:  #1a2b45;
        --text-muted: #5a6a7e;
        --border:     #d0d5de;
        --sidebar-w:  240px;
        --sidebar-collapsed-w: 100px;
        --sidebar-tab: 42px;
        --topbar-h:   70px;
   
        position: fixed; top: 0; left: 0; right: 0;
        height: var(--topbar-h);
        background: var(--navy-dark);
        display: flex; align-items: center;
        z-index: 10000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
    }
    .topbar-brand {
        width: calc(var(--sidebar-w) + var(--sidebar-tab));
        display: flex; align-items: center; gap: 12px;
        padding: 0 20px; flex-shrink: 0;
        border-right: 1px solid rgba(255,255,255,0.08);
        height: 100%;
        transition: width 0.22s ease;
        text-decoration: none;
    }
    .topbar-brand img { height: auto; width: auto; margin-top: -20px; margin-bottom: -20px; }
    .topbar-center { flex: 1; padding: 0 20px; display: flex; align-items: center; position: relative; }
    .topbar-secretary-label { font-size: 30px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; position: absolute; left: 50%; transform: translateX(-50%); }
    .topbar-title  { font-size: 15px; font-weight: 600; }
   
    .topbar-title #topbar-parent-name { 
        color: rgba(255, 255, 255, 0.85); 
    }

    .topbar-title #topbar-view-name { 
        color: var(--teal); 
    }
    .topbar-right  { display: flex; align-items: center; gap: 6px; padding: 0 16px; }
    .topbar-date {
        font-size: 15px; color: rgba(255,255,255,0.45);
        background: rgba(255,255,255,0.06);
        padding: 4px 10px; border-radius: 4px; margin-right: 4px;
    }
    .topbar-icon-btn {
        width: 32px; height: 32px; border-radius: 6px;
        background: transparent; border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        color: rgba(255, 255, 255, 0.55); font-size: 20px;
        transition: background 0.15s, color 0.15s; text-decoration: none;
    }
    .topbar-icon-btn:hover { background: rgba(255, 0, 0, 0.1); color: #fff; }

    .profile-dropdown { position: relative; display: inline-block; }
    
    .avatar-btn {
        width: 48px; height: 48px; border: none; border-radius: 50%; 
        display: grid; place-items: center; cursor: pointer;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        position: relative; padding: 0; outline: none; 
    }
    .avatar-btn:hover { filter: brightness(90%); }
    .avatar-initials { color: #fff; font-size: 20px; font-weight: bold; font-family: sans-serif; line-height: 1; }
    
    .dropdown-menu {
        display: none; position: absolute; right: 0 !important;      /* Alinea el borde derecho al botón */
        left: auto !important; top: calc(100% + 8px);
        background: rgba(255, 255, 255, 0.98); border: 1px solid var(--border);
        border-radius: 16px; min-width: 220px; box-shadow: var(--shadow);
        z-index: 9999; padding: 8px; backdrop-filter: blur(10px);
    }
    .dropdown-menu.show { display: block; }
    .dropdown-user-info { padding: 8px 14px; display: flex; flex-direction: column; }
    .dropdown-user-info strong { font-size: 14px; color: var(--navy); }
    .dropdown-user-info span { font-size: 11px; color: var(--muted); }
    .nav-section { margin: 18px 0 8px; font-size: 10px; letter-spacing: 1.6px; text-transform: uppercase; color: var(--muted); font-weight: 700; padding: 6px 14px 4px; }
    .dropdown-item {
        text-decoration: none !important; display: flex; align-items: center; gap: 12px;
        padding: 10px 14px; color: var(--text); font-size: 13.5px; font-weight: 600;
        border-radius: 10px; transition: background 0.15s ease, color 0.15s ease;
    }
    .dropdown-item:hover { background: var(--blue-soft); color: var(--blue); }
    .dropdown-item.danger { color: #ea3b3b; }
    .dropdown-item.danger:hover { background: rgba(234, 59, 59, 0.08); }
    .dropdown-item i { width: 18px; text-align: center; font-size: 14px; }
</style>

<!-- TOPBAR -->
<header class="topbar">
    <a href="<?= base_url(route_to('home')); ?>" class="topbar-brand">
        <img src="/imag/jujuy01.png" alt="Logo Jujuy">
    </a>
    <div class="topbar-center">
    <div class="topbar-secretary-label">SECRETARIA DE SALUD</div>
    <!-- Agrupa ambos span dentro de la clase .topbar-title -->
    <div class="topbar-title" id="topbar-dynamic-title">
        <span id="topbar-parent-name"></span>
        <span id="topbar-view-name"></span>
    </div>
</div>
    <div class="topbar-right">
        <div class="topbar-date" id="topbar-date"></div>
        <a href="#" class="topbar-icon-btn" title="Notificaciones"><i class="fas fa-bell"></i></a>
        <div class="profile-dropdown">
        <button type="button" class="avatar-btn" id="profileBtn" aria-label="Abrir menú de perfil" style="background-color: <?= esc($userAvatarColor); ?>;">
            <span class="avatar-initials"><?= esc(strtoupper(substr($userName, 0, 2))); ?></span>
        </button>
                    
        <div class="dropdown-menu" id="profileMenu">
            <div class="dropdown-user-info">
                <strong><?= esc($userName); ?></strong>
                <span><?= esc($userRole); ?></span>
            </div>
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 6px 0;">
                        
            <!-- Tus tres opciones requeridas -->
            <div class="nav-section" style="color: var(--muted); padding: 6px 14px 4px;">Administración</div>
            <a href="<?= base_url(route_to('list_users')); ?>" class="dropdown-item">
                <i class="fas fa-user-cog"></i>
                <span>Usuarios</span>
            </a>
            <a href="<?= base_url(route_to('groups_list')); ?>" class="dropdown-item">
                <i class="fas fa-users-cog"></i>
                <span>Grupos</span>
            </a>
            <a href="<?= base_url(route_to('logout')); ?>" class="dropdown-item danger">
                <i class="fas fa-right-from-bracket"></i>
                <span>Cerrar sesión</span>
            </a>
        </div>
    </div>
    </div>
     
</header>

<script>
    // Initialize date for header if topbar-date exists
    (function() {
        const dateEl = document.getElementById('topbar-date');
        if (dateEl) {
            const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
            const d = new Date();
            dateEl.textContent = d.getDate() + ' ' + meses[d.getMonth()] + ' ' + d.getFullYear();
        }
    })();

    // Profile menu toggle
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

   // Dynamic Title Logic
    window.addEventListener('load', () => {
        const parentSpan = document.getElementById('topbar-parent-name');
        const viewSpan = document.getElementById('topbar-view-name');

        if (!parentSpan || !viewSpan) return;

        // 1. Caso especial: Vista de Inicio
        if (window.location.pathname === '/' || window.location.pathname.includes('inicio_views')) {
            parentSpan.textContent = '';
            viewSpan.textContent = 'INICIO'; // Al ir en viewSpan, se pinta de color --teal
            return;
        }

        // 2. Buscar si hay un sub-ítem activo (ej: Base, Móviles)
        const activeSub = document.querySelector('.sidebar .nav-sub.active');

        if (activeSub) {
            let subText = activeSub.innerText.replace(/[^\w\sáéíóúüñÁÉÍÓÚÜÑ]/g, '').trim();

            const subGroup = activeSub.closest('.nav-sub-group');
            let parentText = '';

            if (subGroup) {
                const parentNavItem = subGroup.previousElementSibling;
                if (parentNavItem) {
                    parentText = parentNavItem.innerText.replace(/[^\w\sáéíóúüñÁÉÍÓÚÜÑ]/g, '').trim();
                }
            }

            // Padre en blanco/gris (parentSpan) y submenú en azul teal (viewSpan)
            parentSpan.textContent = parentText ? `${parentText.toUpperCase()} - ` : '';
            viewSpan.textContent = subText.toUpperCase();

        } else {
            // 3. Ítems principales sin submenú activo (ej: PREHOSPITALARIO, USUARIOS)
            const activeMain = document.querySelector('.sidebar .nav-item.active');
            if (activeMain) {
                let mainText = activeMain.innerText
                    .replace(/^Panel de\s*/i, '')
                    .replace(/[^\w\sáéíóúüñÁÉÍÓÚÜÑ]/g, '')
                    .trim();

                // Dejamos el padre vacío y mandamos el título principal a viewSpan para que sea TEAL
                parentSpan.textContent = '';
                viewSpan.textContent = mainText.toUpperCase();
            }
        }
    });
</script>
