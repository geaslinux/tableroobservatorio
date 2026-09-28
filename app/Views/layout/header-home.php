<?php
    $alertasActivas = $alertasActivas ?? 0;
    $userName = $userName ?? 'Usuario';
    $userAvatarColor = $userAvatarColor ?? '#1fa996';
    $userRole = $userRole ?? 'Usuario';
?>
<style>
    .topbar {
        min-height: var(--topbar-h);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 20px;
        background: transparent;
    }

    .topbar-title .eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.1px;
        text-transform: uppercase;
        color: var(--muted);
    }

    .topbar-title h1 {
        font-size: clamp(20px, 2vw, 28px);
        line-height: 1.1;
        color: var(--navy);
        font-weight: 800;
    }

    .topbar-tools {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .period-box {
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid var(--border);
        padding: 8px 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .icon-btn {
        width: 42px;
        height: 42px;
        border: 0;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.85);
        display: grid;
        place-items: center;
        color: var(--navy);
        position: relative;
        cursor: pointer;
    }

    .avatar-btn {
        width: 42px; 
        height: 42px;
        border: none;
        border-radius: 50%; 
        display: grid;
        place-items: center; 
        cursor: pointer;
        color: #fff;
        font-weight: bold;
    }

    .badge {
        position: absolute;
        top: 0;
        right: 0;
        min-width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ea3b3b;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        display: grid;
        place-items: center;
    }

    .profile-dropdown { position: relative; }
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 8px);
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        min-width: 200px;
        box-shadow: var(--shadow);
        z-index: 100;
        padding: 8px;
    }
    .dropdown-menu.show { display: block; }
</style>

<header class="topbar">
    <div class="topbar-title">
        <span class="eyebrow">Dirección General del</span>
        <h1>OBSERVATORIO Y ESTADÍSTICAS DEL M.S.</h1>
    </div>
    <div class="topbar-tools">
        <div class="period-box">
            <i class="fas fa-calendar-days"></i>
            <div>
                <div style="font-size: 9px; font-weight: 700; color: var(--muted);">PERÍODO</div>
                <div style="font-size: 12px; font-weight: 700;" id="topbar-date">Cargando...</div>
            </div>
        </div>
        <button type="button" class="icon-btn" aria-label="Notificaciones">
            <i class="fas fa-bell"></i>
            <span class="badge"><?= $alertasActivas > 9 ? '9+' : $alertasActivas; ?></span>
        </button>
        <div class="profile-dropdown">
            <button type="button" class="avatar-btn" id="profileBtn" style="background-color: <?= esc($userAvatarColor); ?>;">
                <span><?= esc(strtoupper(substr($userName, 0, 2))); ?></span>
            </button>
            <div class="dropdown-menu" id="profileMenu">
                <div style="padding: 6px 12px;">
                    <div style="font-weight:700; font-size:13px;"><?= esc($userName); ?></div>
                    <div style="font-size:11px; color:var(--muted);"><?= esc($userRole); ?></div>
                </div>
                <hr style="margin: 6px 0; border:0; border-top:1px solid var(--border);">
                <a href="<?= base_url(route_to('list_users')); ?>" class="dropdown-item"><i class="fas fa-user-cog"></i> Usuarios</a>
                <a href="<?= base_url(route_to('groups_list')); ?>" class="dropdown-item"><i class="fas fa-users-cog"></i> Grupos</a>
                <a href="<?= base_url(route_to('logout')); ?>" class="dropdown-item danger"><i class="fas fa-right-from-bracket"></i> Cerrar sesión</a>
            </div>
        </div>
    </div>
</header>

<script>
    (function() {
        const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
        const fecha = new Date();
        const dia = `${fecha.getDate()} ${meses[fecha.getMonth()]} ${fecha.getFullYear()}`;
        const element = document.getElementById('topbar-date');
        if (element) element.textContent = `${dia} - ${dia}`;
    })();

    document.addEventListener('DOMContentLoaded', function() {
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('show');
            });
            document.addEventListener('click', () => profileMenu.classList.remove('show'));
        }
    });
</script>