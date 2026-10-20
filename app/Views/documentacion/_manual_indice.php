<?php
/**
 * Índice lateral del manual (lo usan manual_rol y manual_pagina).
 *
 * @var string $rol       rol elegido
 * @var array  $indice    ['clave_modulo' => ['nombre' => ..., 'paginas' => [Documentacion, ...]], ...]
 * @var int|null $paginaId página abierta (para resaltarla)
 * @var string $buscar    texto buscado
 */
?>
<aside class="mn-sidebar" id="mnSidebar">
    <div class="mn-buscar">
        <form method="GET" action="<?= base_url(route_to('manual_rol', $rol)) ?>">
            <i class="fas fa-search"></i>
            <input type="search" name="q" placeholder="Buscar en el manual..." value="<?= esc($buscar ?? '') ?>" aria-label="Buscar en el manual">
        </form>
    </div>

    <button type="button" class="mn-indice-toggle" id="mnIndiceToggle" aria-expanded="false">
        <span><i class="fas fa-list" style="color:var(--navy);"></i> Índice del manual</span>
        <i class="fas fa-chevron-down"></i>
    </button>

    <nav class="mn-indice">
        <a href="<?= base_url(route_to('manual_rol', $rol)) ?>" class="<?= $paginaId === null && ($buscar ?? '') === '' ? 'is-active' : '' ?>">
            <i class="fas fa-house" style="color:var(--navy);"></i> Portada
        </a>
        <?php foreach ($indice as $modulo): ?>
            <div class="mn-indice-modulo">
                <span><?= esc($modulo['nombre']) ?></span>
                <span><?= count($modulo['paginas']) ?></span>
            </div>
            <?php foreach ($modulo['paginas'] as $p): ?>
                <a href="<?= base_url(route_to('manual_pagina', $rol, $p->documentacion_id)) ?>"
                   class="<?= $paginaId === $p->documentacion_id ? 'is-active' : '' ?>">
                    <?= esc($p->titulo) ?>
                    <small><?= esc($p->vista_nombre) ?></small>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if (!$indice): ?>
            <p class="muted" style="padding:10px 16px;">Todavía no hay páginas publicadas para este rol.</p>
        <?php endif; ?>
    </nav>
</aside>

<script>
// En celular el índice se pliega
document.getElementById('mnIndiceToggle').addEventListener('click', function () {
    var abierto = document.getElementById('mnSidebar').classList.toggle('is-open');
    this.setAttribute('aria-expanded', abierto ? 'true' : 'false');
});
</script>