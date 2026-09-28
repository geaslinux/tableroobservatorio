<nav class="pagination" aria-label="Page navigation">

    <?php if ($pager->hasPreviousPage()) : ?>

        <a class="pagination-previous" href="<?= $pager->getFirst() ?>" aria-label="Primero">
                <span aria-hidden="true">Primero</span>
        </a>
        <a class="pagination-previous" href="<?= $pager->getPreviousPage() ?>" aria-label="Anterior">
            <span aria-hidden="true">Anterior</span>
        </a>

    <?php endif ?>

    <?php if ($pager->hasNextPage()) : ?>

            <a class="pagination-next" href="<?= $pager->getNextPage() ?>" aria-label="Siguiente">
                <span aria-hidden="true">Siguiente</span>
            </a>
            <a class="pagination-next" href="<?= $pager->getLast() ?>" aria-label="ultimo">
                <span aria-hidden="true">Ultimo</span>
            </a>

    <?php endif ?>


    <ul class="pagination-list">
    

    <?php foreach ($pager->links() as $link): ?>
        <li >
            <a class = "pagination-link <?= $link['active'] ? 'is-current': ''?>" href="<?= $link['uri'] ?>">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach ?>


    </ul>
</nav>