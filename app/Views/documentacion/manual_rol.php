<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> DOCUMENTACIÓN - <?= mb_strtoupper($rolNombre) ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>
<?= $this->section('css') ?><link rel="stylesheet" href="/css/panel.css"><link rel="stylesheet" href="/css/manual.css?v=9"><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="base-list-wrap">

    <?= view('partials/breadcrumb', ['nodo' => 'manual_inicio', 'actual' => 'Manual ' . $rolNombre]) ?>

    <!-- ROL ELEGIDO -->
    <div class="mn-hero">
        <div class="mn-hero-titulo">
            <i class="fas fa-<?= $rol === 'administrativo' ? 'user-tie' : 'user' ?>"></i>
            <div>
                <strong>Manual — <?= esc($rolNombre) ?></strong>
                <span><?= $total ?> página<?= $total == 1 ? '' : 's' ?> publicadas para este rol</span>
            </div>
        </div>
        <div class="bl-kpi-group">
            <?php if ($total > 0): ?>
                <a href="<?= base_url(route_to('manual_pdf', $rol)) ?>" class="bl-btn teal sm"><i class="fas fa-file-pdf"></i> Descargar manual en PDF</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="mn-layout">

        <?= view('documentacion/_manual_indice', compact('rol', 'indice', 'paginaId', 'buscar')) ?>

        <main class="mn-doc">
            <?php if ($resultados !== null): ?>

                <!-- RESULTADOS DE BÚSQUEDA -->
                <div class="mn-ruta">Búsqueda</div>
                <h1>Resultados para “<?= esc($buscar) ?>”</h1>
                <p class="muted"><?= count($resultados) ?> resultado<?= count($resultados) == 1 ? '' : 's' ?></p>

                <?php foreach ($resultados as $p): ?>
                    <div class="mn-resultado">
                        <a href="<?= base_url(route_to('manual_pagina', $rol, $p->documentacion_id)) ?>"><?= esc($p->titulo) ?></a>
                        <div class="mn-meta" style="margin:6px 0 0;">
                            <span class="bl-tag navy"><?= esc($p->modulo_nombre) ?></span>
                            <span class="bl-tag gray"><?= esc($p->vista_nombre) ?></span>
                        </div>
                        <?php if ($p->resumen): ?><p><?= esc($p->resumen) ?></p><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$resultados): ?>
                    <div class="bl-empty"><i class="fas fa-magnifying-glass"></i> No encontramos páginas con ese texto. Probá con otra palabra.</div>
                <?php endif; ?>

            <?php else: ?>

                <!-- PORTADA -->
                <div class="mn-ruta">Manual de usuario</div>
                <h1>Bienvenido al manual <?= $rol === 'administrativo' ? 'administrativo' : 'del funcionario' ?></h1>
                <p class="mn-resumen">
                    Acá encontrás cómo usar cada pantalla del sistema: para qué sirve, cómo se completa,
                    qué validaciones tiene y una captura de referencia. Elegí una página del índice o buscá por palabra.
                </p>

                <?php if ($indice): ?>
                    <div class="mn-modulos">
                        <?php foreach ($indice as $claveModulo => $modulo): ?>
                            <div class="mn-modulo-card">
                                <h3>
                                    <?= esc($modulo['nombre']) ?>
                                    <span>
                                        <span class="bl-tag teal"><?= count($modulo['paginas']) ?></span>
                                        <a href="<?= base_url(route_to('manual_pdf', $rol)) ?>?modulo=<?= urlencode($claveModulo) ?>"
                                           class="bl-icon-link" title="Descargar este módulo en PDF" style="margin-left:4px;"><i class="fas fa-file-pdf"></i></a>
                                    </span>
                                </h3>
                                <ul>
                                    <?php foreach ($modulo['paginas'] as $p): ?>
                                        <li>
                                            <a href="<?= base_url(route_to('manual_pagina', $rol, $p->documentacion_id)) ?>"><?= esc($p->titulo) ?></a>
                                            <span><?= esc($p->resumen ?: $p->vista_nombre) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bl-empty"><i class="fas fa-book"></i> Todavía no hay páginas publicadas para este rol.</div>
                <?php endif; ?>

            <?php endif; ?>
        </main>
    </div>
</div>

<?= $this->endSection() ?>
