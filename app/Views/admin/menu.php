<div class="navbar-start">

    <!-- INICIO -->
    <a class="navbar-item" href="<?= base_url(route_to('inicio_views')); ?>">
        <span class="menu-label">Inicio</span>
    </a>

    <!-- HOSPITALARIO -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Hospitalario</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="#">
                <span class="menu-label">Próximamente</span>
            </a>
        </div>
    </div>

    <!-- PREHOSPITALARIO -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Prehospitalario</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="<?= base_url(route_to('base_views')); ?>">
                <span class="menu-label">Resumen / Estadísticas</span>
            </a>
            <hr class="navbar-divider">
            <a class="navbar-item" href="<?= base_url(route_to('base_list')); ?>">
                <span class="menu-label">Base</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('movil_list')); ?>">
                <span class="menu-label">Móviles</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('atencion_list')); ?>">
                <span class="menu-label">Atenciones realizadas</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('identificacion_list')); ?>">
                <span class="menu-label">Internación domiciliaria</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('asistencia_list')); ?>">
                <span class="menu-label">Asistencias realizadas</span>
            </a>
        </div>
    </div>

    <!-- GESTIÓN PACIENTE -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Gestión Paciente</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="<?= base_url(route_to('paciente_views')); ?>">
                <span class="menu-label">Resumen</span>
            </a>
            <hr class="navbar-divider">
            <a class="navbar-item" href="<?= base_url(route_to('consulta_reclamo_list')); ?>">
                <span class="menu-label">Consultas y reclamos</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('chat_bot_list')); ?>">
                <span class="menu-label">Chat bot turnos otorgados</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('turno_hospitalario_list')); ?>">
                <span class="menu-label">Gestión de especialidades</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('call_center_list')); ?>">
                <span class="menu-label">0800 Call center</span>
            </a>
        </div>
    </div>

    <!-- SALUD MENTAL -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Salud Mental</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="<?= base_url(route_to('saludmental_views')); ?>">
                <span class="menu-label">Resumen</span>
            </a>
            <hr class="navbar-divider">
            <a class="navbar-item" href="<?= base_url(route_to('electrodependiente_list')); ?>">
                <span class="menu-label">Electrodependientes</span>
            </a>
        </div>
    </div>

    <!-- SERVICIOS TRANSVERSALES -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Servicios Transversales</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="<?= base_url(route_to('servicio_views')); ?>">
                <span class="menu-label">Resumen</span>
            </a>
            <hr class="navbar-divider">
            <a class="navbar-item" href="<?= base_url(route_to('cantidad_operativo_list')); ?>">
                <span class="menu-label">Cantidad de operativos</span>
            </a>
            <a class="navbar-item" href="<?= base_url(route_to('transfusion_list')); ?>">
                <span class="menu-label">Transfusión mensual</span>
            </a>
        </div>
    </div>

</div>

<div class="navbar-end">

    <!-- ADMINISTRACIÓN -->
    <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" style="color: inherit;">
            <span class="menu-label">Administración</span>
        </a>
        <div class="navbar-dropdown">
            <a class="navbar-item" href="<?= base_url(route_to('list_users')); ?>">
                <span class="menu-label">Admin Usuarios</span>
            </a>
            <hr class="navbar-divider">
            <a class="navbar-item" href="<?= base_url(route_to('groups_list')); ?>">
                <span class="menu-label">Admin Grupos</span>
            </a>
        </div>
    </div>

    <a class="navbar-item" href="<?= base_url(route_to('logout')); ?>">
        <span class="menu-label">Salir</span>
    </a>

</div>