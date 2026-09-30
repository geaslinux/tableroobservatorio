<?php

use Config\UltimaActualizacion;

if (! function_exists('modulo_actual')) {
    /**
     * Segmento de la URL que identifica el módulo: /admin/transfusion/editar/3 → 'transfusion'.
     * Se ignoran los sufijos de acciones como "-visto" o "-template".
     */
    function modulo_actual(): ?string
    {
        $segmentos = service('request')->getUri()->getSegments();
        $i         = array_search('admin', $segmentos, true);

        if ($i === false || ! isset($segmentos[$i + 1])) {
            return null;
        }

        return preg_replace('/-(visto|template)$/', '', strtolower($segmentos[$i + 1]));
    }
}

if (! function_exists('ultima_actualizacion_tablas')) {
    /**
     * Fecha más reciente de carga/modificación entre las tablas dadas ([grupo => [tablas]]).
     *
     * Se guarda en caché unos minutos para no repetir la consulta en cada página.
     * "Sin dato" no se guarda: así, en cuanto se carga el primer registro, se ve enseguida.
     */
    function ultima_actualizacion_tablas(array $tablas): ?string
    {
        $config = config(UltimaActualizacion::class);
        $clave  = 'ultima_act_' . md5(json_encode($tablas));

        $fecha = cache($clave);
        if (! empty($fecha)) {
            return $fecha;
        }

        $fechas = [];
        foreach ($tablas as $grupo => $lista) {
            try {
                $db    = \Config\Database::connect($grupo);
                $union = implode(' UNION ALL ', array_map(
                    static fn ($t) => 'SELECT MAX(COALESCE(`updated_at`, `created_at`)) AS f FROM `' . $t . '`',
                    $lista
                ));
                $fila = $db->query("SELECT MAX(f) AS f FROM ({$union}) AS x")->getRowArray();
                if (! empty($fila['f'])) {
                    $fechas[] = $fila['f'];
                }
            } catch (\Throwable $e) {
                log_message('error', 'ultima_actualizacion(' . $grupo . '): ' . $e->getMessage());
            }
        }

        $fecha = $fechas ? max($fechas) : null;

        if ($fecha !== null) {
            cache()->save($clave, $fecha, $config->minutosCache * 60);
        }

        return $fecha;
    }
}

if (! function_exists('ultima_actualizacion')) {
    /**
     * Fecha de la última carga de datos del módulo (o de todo el sistema si el módulo
     * no está mapeado en Config\UltimaActualizacion).
     */
    function ultima_actualizacion(?string $modulo = null): ?string
    {
        $config = config(UltimaActualizacion::class);
        $modulo ??= modulo_actual();

        return ultima_actualizacion_tablas($config->modulos[$modulo] ?? $config->general);
    }
}

if (! function_exists('ultima_actualizacion_pestanas')) {
    /**
     * Para paneles con pestañas: [pestaña => fecha|null]. Vacío si el módulo no tiene pestañas.
     */
    function ultima_actualizacion_pestanas(?string $modulo = null): array
    {
        $config = config(UltimaActualizacion::class);
        $modulo ??= modulo_actual();

        $fechas = [];
        foreach ($config->pestanas[$modulo] ?? [] as $pestana => $tablas) {
            $fechas[$pestana] = ultima_actualizacion_tablas($tablas);
        }

        return $fechas;
    }
}
