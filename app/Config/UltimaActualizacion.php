<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Tablas de las que sale la "Última actualización" que muestra el footer.
 *
 * La clave es el segmento de la URL después de /admin/ (ej. /admin/transfusion → 'transfusion').
 * Cada módulo lista sus tablas por grupo de conexión (app/Config/Database.php).
 * Todas las tablas deben tener created_at y updated_at (ver app/Database/Scripts/fechas_carga_modulos.sql).
 */
class UltimaActualizacion extends BaseConfig
{
    /** Minutos que se guarda el resultado en caché antes de volver a consultar la base. */
    public int $minutosCache = 5;

    /** @var array<string, array<string, list<string>>> */
    public array $modulos = [
        // ── Prehospitalario ──
        'base'               => ['default' => ['base']],
        'movil'              => ['default' => ['movil']],
        'atencion'           => ['default' => ['atencion']],
        'asistencia'         => ['default' => ['asistencia']],
        'identificacion'     => ['default' => ['identificacion']],
        'basev'              => ['default' => ['base', 'movil', 'asistencia', 'identificacion']],

        // ── Hospitalario ──
        'guardia'            => ['default' => ['guardia']],
        'guardiav'           => ['default' => ['guardia']],
        'quirofano'          => ['default' => ['produccion_quirofano_hosp']],
        'produccion-quirofano' => ['default' => ['produccion_quirofano']],
        'quirofanov'         => ['default' => ['produccion_quirofano_hosp', 'produccion_quirofano']],
        'lista-espera'       => ['default' => ['lista_espera']],
        'capacidad-camas'    => ['default' => ['capacidad_camas']],
        'salud-mental-camas' => ['default' => ['salud_mental_camas']],
        'rendimiento-hospitalario'     => ['default' => ['rendimiento_hospitalario']],
        'rendimiento-hospitalario-uti' => ['default' => ['rendimiento_hospitalario_uti']],
        'rh-materno'         => ['default' => ['rh_materno']],
        'gestion_cama'       => ['default' => ['gestion_cama']],
        'internacionv'       => ['default' => [
            'lista_espera', 'rendimiento_hospitalario', 'rendimiento_hospitalario_uti',
            'rh_materno', 'salud_mental_camas', 'capacidad_camas',
        ]],
        'ambulatorio'         => ['base2' => ['carta_servicio', 'rrhh_carta_servicio']],
        'carta_servicio'      => ['base2' => ['carta_servicio']],
        'rrhh_carta_servicio' => ['base2' => ['rrhh_carta_servicio']],
        'hospitalariov'       => [
            'default' => [
                'guardia', 'capacidad_camas', 'lista_espera', 'produccion_quirofano_hosp',
                'rendimiento_hospitalario', 'salud_mental_camas',
            ],
            'base2' => ['carta_servicio', 'rrhh_carta_servicio'],
        ],

        // ── Gestión paciente ──
        'consulta-reclamo'   => ['default' => ['consulta_reclamo']],
        'chat-bot'           => ['default' => ['chat_bot']],
        'turno-hospitalario' => ['default' => ['turno_hospitalario']],
        'call-center'        => ['default' => ['call_center']],
        'gestion_paciente_hospital' => ['default' => ['gestion_cama_hospitales']],
        'pacientev'          => ['default' => ['consulta_reclamo', 'chat_bot', 'turno_hospitalario', 'call_center']],

        // ── Salud mental ──
        'electrodependiente' => ['default' => ['electrodependiente', 'electrodependiente_equipamiento']],
        'saludmental'        => ['default' => ['electrodependiente', 'electrodependiente_equipamiento']],

        // ── Servicios transversales ──
        'cantidad-operativo' => ['default' => ['cantidad_operativo']],
        'transfusion'        => ['default' => ['transfusion']],
        'transfusion-resumen' => ['default' => ['transfusion']],
        'serviciov'          => ['default' => ['cantidad_operativo', 'transfusion']],
    ];

    /**
     * Paneles con pestañas que cambian sin recargar la página (?tab=...).
     * Cada pestaña muestra la fecha de sus propias tablas; la primera es la pestaña
     * por defecto del panel (debe coincidir con la del controlador).
     *
     * @var array<string, array<string, array<string, list<string>>>>
     */
    public array $pestanas = [
        'ambulatorio' => [
            'carta' => ['base2' => ['carta_servicio']],
            'rrhh'  => ['base2' => ['rrhh_carta_servicio']],
        ],
        'hospitalariov' => [
            'amb' => ['base2' => ['carta_servicio', 'rrhh_carta_servicio']],
            'gua' => ['default' => ['guardia']],
            'int' => ['default' => [
                'lista_espera', 'rendimiento_hospitalario', 'rendimiento_hospitalario_uti',
                'rh_materno', 'salud_mental_camas', 'capacidad_camas',
            ]],
            'qui' => ['default' => ['produccion_quirofano', 'produccion_quirofano_hosp']],
        ],
        'internacionv' => [
            'camas'     => ['default' => ['lista_espera']],
            'salud'     => ['default' => ['salud_mental_camas']],
            'rend'      => ['default' => ['rendimiento_hospitalario']],
            'uti'       => ['default' => ['rendimiento_hospitalario_uti']],
            'materno'   => ['default' => ['rh_materno']],
            'capacidad' => ['default' => ['capacidad_camas']],
        ],
        'quirofanov' => [
            'prod' => ['default' => ['produccion_quirofano']],
            'hosp' => ['default' => ['produccion_quirofano_hosp']],
        ],
        'serviciov' => [
            'operativos'  => ['default' => ['cantidad_operativo']],
            'transfusion' => ['default' => ['transfusion']],
        ],
        'guardiav' => [
            'servicio' => ['default' => ['guardia']],
            'hospital' => ['default' => ['guardia']],
        ],
        'saludmental' => [
            'perfil' => ['default' => ['electrodependiente', 'electrodependiente_equipamiento']],
            'mapa'   => ['default' => ['electrodependiente', 'electrodependiente_equipamiento']],
        ],
    ];

    /**
     * Páginas sin módulo propio (Inicio, usuarios, catálogos…): se muestra la carga
     * más reciente de todo el sistema.
     *
     * @var array<string, list<string>>
     */
    public array $general = [
        'default' => [
            'base', 'movil', 'atencion', 'asistencia', 'identificacion', 'guardia',
            'produccion_quirofano', 'produccion_quirofano_hosp', 'lista_espera',
            'capacidad_camas', 'salud_mental_camas', 'rendimiento_hospitalario',
            'rendimiento_hospitalario_uti', 'rh_materno', 'gestion_cama',
            'gestion_cama_hospitales', 'consulta_reclamo', 'chat_bot',
            'turno_hospitalario', 'call_center', 'electrodependiente',
            'electrodependiente_equipamiento', 'cantidad_operativo', 'transfusion',
        ],
        'base2' => ['carta_servicio', 'rrhh_carta_servicio'],
    ];
}