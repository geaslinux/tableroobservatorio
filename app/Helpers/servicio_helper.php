<?php

/**
 * Número de servicio eléctrico (EJESA). helper('servicio');
 *
 * Misma normalización que sistemabasec (app/Helpers/servicio_helper.php): las dos
 * aplicaciones escriben en la misma tabla `electrodependiente`, así que tienen que
 * guardar el número igual. Si sistemabasec cambia el formato, cambiarlo también acá.
 *
 * El número de servicio y el titular son datos del PACIENTE (tabla electrodependiente),
 * no de cada equipo.
 */

if (! function_exists('normalizar_nro_servicio')) {
    function normalizar_nro_servicio(?string $nro): ?string
    {
        if ($nro === null) {
            return null;
        }
        $nro = trim($nro);

        return $nro === '' ? null : $nro; // siempre texto: conserva ceros iniciales
    }
}

if (! function_exists('riesgo_operativo')) {
    /**
     * Riesgo para mostrar. NULL / vacío / desconocido => 'PENDIENTE'. Nunca 'BAJO' por defecto.
     */
    function riesgo_operativo(?string $factorRiesgo): string
    {
        $r = strtoupper(trim((string) $factorRiesgo));

        return in_array($r, ['ALTO', 'MEDIANO', 'BAJO'], true) ? $r : 'PENDIENTE';
    }
}

if (! function_exists('enmascarar_nro_servicio')) {
    /**
     * Para paneles y auditoría: solo los 3 últimos caracteres (•••••123).
     */
    function enmascarar_nro_servicio(?string $nro): string
    {
        $nro = trim((string) $nro);
        if ($nro === '') {
            return '—';
        }

        return '•••••' . mb_substr($nro, -3);
    }
}
