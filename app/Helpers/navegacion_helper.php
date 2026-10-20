<?php

/**
 * Árbol de navegación del sistema: el mismo orden y los mismos nombres que el menú lateral
 * (app/Views/layout/sidebar.php). De acá sale breadcrumb de todas las pantallas
 * (app/Views/partials/breadcrumb.php), así queda siempre igual al menú.
 *
 * Cada nodo: 'clave' => [nombre, ruta con nombre (o null si no tiene página), clave del padre (o null)].
 * Si agregás un módulo al sidebar, agregalo también acá.
 */
if (! function_exists('navegacion_mapa')) {
    function navegacion_mapa(): array
    {
        return [
            // ── Grupos del menú (sus rutas son los paneles de cada módulo) ──
            'hospitalario'    => ['Hospitalario',            'hospitalario_views', null],
            'ambulatorio'     => ['Ambulatorio',             'ambulatorio_list',   'hospitalario'],
            'internacion'     => ['Internación',             'internacion_views',  'hospitalario'],
            'quirofano'       => ['Quirófano',               'quirofano_views',    'hospitalario'],
            'prehospitalario' => ['Prehospitalario',         'base_views',         null],
            'paciente'        => ['Gestión paciente',        'paciente_views',     null],
            'salud_mental'    => ['Salud mental',            'saludmental_views',  null],
            'servicios'       => ['Servicios transversales', 'servicio_views',     null],
            'laboratorio'     => ['Laboratorio',             'laboratorio_views',  null],
            'catalogos'       => ['Catálogos',               null,                 null],
            'administracion'  => ['Administración',          null,                 null],

            // ── Hospitalario ──
            'carta_servicio_list'               => ['Carta de servicio',            'carta_servicio_list',               'ambulatorio'],
            'rrhh_carta_servicio_list'          => ['RRHH carta de servicio',       'rrhh_carta_servicio_list',          'ambulatorio'],
            'guardia_list'                      => ['Guardia',                      'guardia_list',                      'hospitalario'],
            'capacidad_camas_list'              => ['Camas hospitalarias',          'capacidad_camas_list',              'internacion'],
            'gestion_cama_list'                 => ['Gestión de camas',             'gestion_cama_list',                 'internacion'],
            'salud_mental_camas_list'           => ['Salud mental camas',           'salud_mental_camas_list',           'internacion'],
            'rendimiento_hospitalario_list'     => ['Rendimiento hospitalario',     'rendimiento_hospitalario_list',     'internacion'],
            'rendimiento_hospitalario_uti_list' => ['Rendimiento hospitalario UTI', 'rendimiento_hospitalario_uti_list', 'internacion'],
            'rh_materno_list'                   => ['RH Materno',                   'rh_materno_list',                   'internacion'],
            'produccion_quirofano_list'         => ['Producción quirófano',         'produccion_quirofano_list',         'quirofano'],
            'quirofano_list'                    => ['Producción quirófano hosp.',   'quirofano_list',                    'quirofano'],
            'lista_espera_list'                 => ['Lista de espera quirúrgica',   'lista_espera_list',                 'quirofano'],

            // ── Prehospitalario ──
            'base_list'           => ['Base',                     'base_list',           'prehospitalario'],
            'movil_list'          => ['Móviles',                  'movil_list',          'prehospitalario'],
            'atencion_list'       => ['Atenciones realizadas',    'atencion_list',       'prehospitalario'],
            'identificacion_list' => ['Internación domiciliaria', 'identificacion_list', 'prehospitalario'],
            'asistencia_list'     => ['Asistencias realizadas',   'asistencia_list',     'prehospitalario'],

            // ── Gestión paciente ──
            'consulta_reclamo_list'          => ['Consultas y reclamos',      'consulta_reclamo_list',          'paciente'],
            'chat_bot_list'                  => ['Chat bot turnos otorgados', 'chat_bot_list',                  'paciente'],
            'turno_hospitalario_list'        => ['Gestión de especialidades', 'turno_hospitalario_list',        'paciente'],
            'call_center_list'               => ['0800 Call center',          'call_center_list',               'paciente'],
            'gestion_paciente_hospital_list' => ['Gestión paciente hospital', 'gestion_paciente_hospital_list', 'paciente'],

            // ── Salud mental ──
            'electrodependiente_list' => ['Electrodependientes', 'electrodependiente_list', 'salud_mental'],

            // ── Servicios transversales ──
            'cantidad_operativo_list' => ['Cantidad de operativos', 'cantidad_operativo_list', 'servicios'],
            'transfusion_list'        => ['Transfusión mensual',    'transfusion_list',        'servicios'],
            'transfusion_resumen'     => ['Resumen por efector',    'transfusion_resumen',     'transfusion_list'],

            // ── Laboratorio ──
            'laboratorio_list' => ['Red de laboratorios', 'laboratorio_list', 'laboratorio'],

            // ── Catálogos ──
            'efector_list'        => ['Efectores',              'efector_list',        'catalogos'],
            'operativo_list'      => ['Operativos',             'operativo_list',      'catalogos'],
            'tipo_operativo_list' => ['Tipos de operativo',     'tipo_operativo_list', 'operativo_list'],
            'categoria_list'      => ['Categorías de reclamos', 'categoria_list',      'catalogos'],
            'sub_categoria_list'  => ['Subcategorías',          'sub_categoria_list',  'categoria_list'],

            // ── Administración (Myth:Auth) ──
            'list_users'           => ['Usuarios',             'list_users',           'administracion'],
            'permisos_list'        => ['Permisos del usuario', 'permisos_list',        'list_users'],
            'group_list_user'      => ['Grupos del usuario',   'group_list_user',      'list_users'],
            'groups_list'          => ['Grupos',               'groups_list',          'administracion'],
            'groups_permisos_list' => ['Permisos del grupo',   'groups_permisos_list', 'groups_list'],
            'documentacion_list'   => ['Manual',               'documentacion_list',   'administracion'],

            // ── Documentación (consulta del manual) ──
            'manual_inicio' => ['Documentación', 'manual_inicio', null],
        ];
    }
}

if (! function_exists('navegacion_ruta_existe')) {
    /**
     * Si la ruta con nombre está definida en este sistema. Las rutas que piden parámetros
     * (ej. permisos de un usuario) lanzan una excepción sin ellos: igual existen.
     */
    function navegacion_ruta_existe(string $ruta): bool
    {
        try {
            return route_to($ruta) !== false;
        } catch (\Throwable $e) {
            return true;
        }
    }
}

if (! function_exists('navegacion_documentables')) {
    /**
     * Módulos del sistema con sus pantallas, para documentarlas en el manual.
     * Devuelve: ['clave_modulo' => ['nombre' => ..., 'vistas' => ['clave_vista' => 'Nombre', ...]], ...]
     *
     * Módulo = nodo de primer nivel del árbol. Vistas = todos los nodos con página que cuelgan
     * de él (a cualquier profundidad), incluido el panel del propio módulo si tiene.
     */
    function navegacion_documentables(): array
    {
        $mapa    = navegacion_mapa();
        $modulos = [];

        foreach ($mapa as $clave => [$nombre, $ruta, $padre]) {
            if ($padre === null) {
                $modulos[$clave] = ['nombre' => $nombre, 'vistas' => []];
            }
        }

        foreach ($mapa as $clave => [$nombre, $ruta]) {
            // Sin página propia, o pantalla que este sistema no tiene (el mapa se comparte con el otro sistema)
            if ($ruta === null || !navegacion_ruta_existe($ruta)) {
                continue;
            }
            $raiz = navegacion_camino($clave)[0]['clave'] ?? null;
            if ($raiz === null) {
                continue;
            }
            // El panel del propio módulo se distingue del resto de las pantallas
            $modulos[$raiz]['vistas'][$clave] = $clave === $raiz ? "Panel de {$nombre}" : $nombre;
        }

        // Módulos sin pantallas propias (ej. un grupo sin páginas) no se pueden documentar
        return array_filter($modulos, static fn ($m) => $m['vistas'] !== []);
    }
}

if (! function_exists('navegacion_camino')) {
    /**
     * Camino desde el nivel superior hasta el nodo: [['nombre' => ..., 'ruta' => ...], ...].
     * Un nodo desconocido devuelve [] (breadcrumb muestra solo "Inicio").
     */
    function navegacion_camino(?string $nodo): array
    {
        $mapa   = navegacion_mapa();
        $camino = [];

        while ($nodo !== null && isset($mapa[$nodo])) {
            [$nombre, $ruta, $padre] = $mapa[$nodo];
            array_unshift($camino, ['clave' => $nodo, 'nombre' => $nombre, 'ruta' => $ruta]);
            $nodo = $padre;
        }

        return $camino;
    }
}