<?php
/**
 * Encabezado común de las pantallas de usuarios, grupos y permisos (solo consulta):
 * carga los estilos de panel (public/css/panel.css, igual que en sistemabasec) y arma el breadcrumb.
 *
 * Uso:
 *   <?= view('auth/_encabezado', ['camino' => [
 *       ['Usuarios', base_url(route_to('list_users'))],
 *       ['Permisos del usuario', null],   // último paso, sin enlace
 *   ]]) ?>
 */
$camino = $camino ?? [];
$ultimo = count($camino) - 1;
?>
<link rel="stylesheet" href="/css/panel.css">

<div class="bl-breadcrumb">
    <a href="<?= base_url(route_to('inicio_views')); ?>" class="fas fa-home" title="Inicio" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a>
    › Administración
    <?php foreach ($camino as $i => [$texto, $url]): ?>
        ›
        <?php if ($i === $ultimo || $url === null): ?>
            <strong><?= esc($texto) ?></strong>
        <?php else: ?>
            <a href="<?= $url ?>"><?= esc($texto) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
