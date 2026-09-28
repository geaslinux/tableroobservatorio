<?php
// Archivo: app/Helpers/estado_helper.php

if (!function_exists('getEstadoClass')) {
    function getEstadoClass($estado) {
        switch ($estado) {
            case 'Pendiente':
                return 'warning';
            case 'En Proceso':
                return 'info';
            case 'Completado':
                return 'success';
            default:
                return 'secondary';
        }
    }
}

if (!function_exists('getPrioridadClass')) {
    function getPrioridadClass($prioridad) {
        switch ($prioridad) {
            case 'Alta':
                return 'danger';
            case 'Media':
                return 'warning';
            case 'Baja':
                return 'success';
            default:
                return 'secondary';
        }
    }
}