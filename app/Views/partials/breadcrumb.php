<?php
/**
 * Breadcrumb con el mismo camino que el menú lateral.
 * El árbol de pantallas está en app/Helpers/navegacion_helper.php.
 *
 * Uso:
 *   <?= view('partials/breadcrumb', ['nodo' => 'carta_servicio_list']) ?>
 *       → Inicio › Hospitalario › Ambulatorio › Carta de servicio
 *   <?= view('partials/breadcrumb', ['nodo' => 'carta_servicio_list', 'actual' => 'Importar Excel']) ?>
 *       → Inicio › Hospitalario › Ambulatorio › Carta de servicio (enlace) › Importar Excel
 *
 * Variables:
 *   $nodo   — clave del árbol de navegación (pantalla del menú en la que se está)
 *   $actual — (opcional) acción dentro de esa pantalla: 'Nuevo registro', 'Importar Excel'...
 *   $params — (opcional) parámetros de la ruta del nodo cuando lleva id (ej. grupos de un usuario)
 */
helper('navegacion');

$camino = navegacion_camino($nodo ?? null);
$actual = $actual ?? null;
$params = $params ?? [];
$ultimo = count($camino) - 1;
?>
<div class="bl-breadcrumb">
    <!-- Casita como enlace al inicio (gris, verde agua al pasar el mouse) -->
    <a href="<?= base_url(route_to('inicio_views')); ?>" class="fas fa-home" title="Inicio" aria-label="Inicio"></a>
    <?php if ($camino === [] && $actual === null): ?>
        <strong>Inicio</strong>
    <?php else: ?>
        <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a>
    <?php endif; ?>

    <?php foreach ($camino as $i => $paso): ?>
        ›
        <?php if ($i === $ultimo && $actual === null): ?>
            <strong><?= esc($paso['nombre']) ?></strong>
        <?php elseif ($paso['ruta'] !== null): ?>
            <a href="<?= base_url(route_to($paso['ruta'], ...($i === $ultimo ? $params : []))); ?>"><?= esc($paso['nombre']) ?></a>
        <?php else: ?>
            <?= esc($paso['nombre']) ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($actual !== null): ?>
        › <strong><?= esc($actual) ?></strong>
    <?php endif; ?>
</div>