<header class="topbar">
    <button type="button" class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Abrir menú" aria-controls="sidebar" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>

    <div class="topbar-title">
        <span class="eyebrow">Dirección General del</span>
        <h1>OBSERVATORIO Y ESTADISTICAS DEL M.S.</h1>
    </div>

    <div class="topbar-tools">
        <div class="period-box">
            <i class="fas fa-calendar-days"></i>
            <div>
                <div class="period-label">Período</div>
                <div class="period-value" id="topbar-date"><?= esc($fechaHoy) ?></div>
            </div>
        </div>

        <button type="button" class="icon-btn" aria-label="Notificaciones">
            <i class="fas fa-bell"></i>
            <?php if ($alertasActivas > 0): ?>
                <span class="badge"><?= $alertasActivas > 9 ? '9+' : $alertasActivas ?></span>
            <?php endif; ?>
        </button>

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
