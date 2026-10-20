<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= esc(mb_strtoupper($pagina->titulo)) ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>
<?= $this->section('css') ?><link rel="stylesheet" href="/css/panel.css"><link rel="stylesheet" href="/css/manual.css?v=9"><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$validaciones = $pagina->getValidacionesLista();
$tagRol       = ['administrativo' => 'teal', 'funcionario' => 'blue', 'ambos' => 'navy'];
?>

<div class="base-list-wrap">

    <?= view('partials/breadcrumb', ['nodo' => 'manual_inicio', 'actual' => 'Manual ' . $rolNombre]) ?>

    <!-- ROL ELEGIDO -->
    <div class="mn-hero">
        <div class="mn-hero-titulo">
            <i class="fas fa-<?= $rol === 'administrativo' ? 'user-tie' : 'user' ?>"></i>
            <div>
                <strong>Manual — <?= esc($rolNombre) ?></strong>
                <span><?= esc($pagina->modulo_nombre) ?></span>
            </div>
        </div>
        <div class="bl-kpi-group">
            <a href="<?= base_url(route_to('manual_pagina_pdf', $rol, $pagina->documentacion_id)) ?>" class="bl-btn teal sm"><i class="fas fa-file-pdf"></i> PDF de esta página</a>
            <a href="<?= base_url(route_to('manual_pdf', $rol)) ?>" class="bl-btn ghost sm"><i class="fas fa-book"></i> Manual completo en PDF</a>
            <button type="button" class="bl-btn ghost sm" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        </div>
    </div>

    <div class="mn-layout">

        <?= view('documentacion/_manual_indice', compact('rol', 'indice', 'paginaId', 'buscar')) ?>

        <article class="mn-doc">

            <!-- ENCABEZADO -->
            <div class="mn-ruta">
                <?= esc($pagina->modulo_nombre) ?><i class="fas fa-chevron-right"></i><?= esc($pagina->vista_nombre) ?>
            </div>
            <h1><?= esc($pagina->titulo) ?></h1>
            <div class="mn-meta">
                <span class="bl-tag <?= $tagRol[$pagina->rol] ?? 'gray' ?>"><i class="fas fa-user"></i> <?= esc($pagina->getRolNombre()) ?></span>
                <span class="bl-tag gray"><i class="fas fa-window-maximize"></i> <?= esc($pagina->getTipoVistaNombre()) ?></span>
                <?php if ($pagina->version): ?>
                    <span class="bl-tag gray"><i class="fas fa-code-branch"></i> Versión <?= esc($pagina->version) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($pagina->resumen): ?>
                <p class="mn-resumen"><?= esc($pagina->resumen) ?></p>
            <?php endif; ?>

            <!-- CAPTURA -->
            <?php if ($pagina->imagen): ?>
                <h2 class="mn-h2"><i class="fas fa-image"></i> Vista de la pantalla</h2>
                <figure class="mn-captura" style="margin:0;">
                    <div class="mn-captura-barra">
                        <span></span><span></span><span></span>
                        <em><?= esc($pagina->modulo_nombre) ?> › <?= esc($pagina->vista_nombre) ?></em>
                    </div>
                    <img src="<?= esc($pagina->getImagenUrl(), 'attr') ?>" alt="Captura de <?= esc($pagina->vista_nombre, 'attr') ?>" id="mnCaptura">
                </figure>
                <div class="mn-captura-pie no-print"><i class="fas fa-magnifying-glass-plus"></i> Hacé clic en la imagen para verla en tamaño completo</div>
            <?php endif; ?>

            <!-- DESCRIPCIÓN (HTML ya depurado al guardar) -->
            <?php if ($pagina->descripcion): ?>
                <h2 class="mn-h2"><i class="fas fa-file-lines"></i> Descripción</h2>
                <div class="mn-prose"><?= $pagina->descripcion ?></div>
            <?php endif; ?>

            <!-- VALIDACIONES -->
            <?php if ($validaciones): ?>
                <h2 class="mn-h2"><i class="fas fa-list-check"></i> Validaciones (<?= count($validaciones) ?>)</h2>
                <div class="bl-table-wrap">
                    <table class="bl-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Campo</th>
                                <th>Regla</th>
                                <th>Mensaje que ve el usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($validaciones as $i => $v): ?>
                                <tr>
                                    <td class="muted"><?= $i + 1 ?></td>
                                    <td class="mn-validacion-campo"><?= esc($v['campo'] ?? '') ?></td>
                                    <td><?= esc($v['regla'] ?? '') ?></td>
                                    <td><?php if (!empty($v['mensaje'])): ?><span class="mn-validacion-msg"><i class="fas fa-circle-exclamation"></i> <?= esc($v['mensaje']) ?></span><?php endif; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if (!$pagina->imagen && !$pagina->descripcion && !$validaciones): ?>
                <div class="bl-empty"><i class="fas fa-pen"></i> Esta página todavía no tiene contenido.</div>
            <?php endif; ?>

            <p class="mn-pie-pagina">
                <i class="fas fa-clock"></i> Última actualización: <?= $pagina->updated_at ? $pagina->updated_at->format('d/m/Y H:i') : '—' ?>
            </p>

            <!-- ANTERIOR / SIGUIENTE -->
            <?php if ($anterior || $siguiente): ?>
                <nav class="mn-nav-paginas">
                    <?php if ($anterior): ?>
                        <a href="<?= base_url(route_to('manual_pagina', $rol, $anterior->documentacion_id)) ?>">
                            <small><i class="fas fa-arrow-left"></i> Anterior</small>
                            <strong><?= esc($anterior->titulo) ?></strong>
                        </a>
                    <?php endif; ?>
                    <?php if ($siguiente): ?>
                        <a class="siguiente" href="<?= base_url(route_to('manual_pagina', $rol, $siguiente->documentacion_id)) ?>">
                            <small>Siguiente <i class="fas fa-arrow-right"></i></small>
                            <strong><?= esc($siguiente->titulo) ?></strong>
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </article>
    </div>
</div>

<!-- Visor de la captura -->
<div class="mn-lightbox" id="mnLightbox" aria-hidden="true">
    <button type="button" aria-label="Cerrar"><i class="fas fa-times"></i></button>
    <img src="" alt="Captura ampliada">
</div>

<script>
(function () {
    var captura = document.getElementById('mnCaptura');
    var visor   = document.getElementById('mnLightbox');
    if (!captura) return;

    captura.addEventListener('click', function () {
        visor.querySelector('img').src = captura.src;
        visor.classList.add('is-open');
    });
    visor.addEventListener('click', function () { visor.classList.remove('is-open'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') visor.classList.remove('is-open'); });
})();
</script>

<?= $this->endSection() ?>
