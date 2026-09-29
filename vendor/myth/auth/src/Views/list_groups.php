<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Grupos <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    /* ── BREADCRUMB ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── MAPA ── */
    #mapa-electro {
        height: 280px;
        width: 100%;
        border-radius: 8px;
        border: 2px solid #2a5298;
        z-index: 1;
    }

    /* ── LEYENDA ── */
    .leyenda-mapa {
        background: rgba(19,48,77,0.92);
        border-radius: 6px;
        padding: 8px 12px;
        color: white;
        font-size: 0.78rem;
        line-height: 1.8;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 6px;
    }
    .leyenda-dot {
        display: inline-block;
        width: 12px; height: 12px;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }

    /* ── POPUP ── */
    .leaflet-popup-content strong { color: #13304d; font-size: 0.92rem; }
    .leaflet-popup-content table  { font-size: 0.80rem; margin-top: 4px; }
    .leaflet-popup-content td     { padding: 1px 4px; }

    /* ── TABLA ── */
    .tabla-electro td, .tabla-electro th {
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .tabla-electro td.celda-equipo {
        white-space: normal;
        min-width: 100px;
        max-width: 180px;
        font-size: 0.76rem;
        line-height: 1.5;
    }
    .equipo-sep { border-top: 1px dashed #c0c8d8; margin: 3px 0; }
    .th-equipo-group {
        background-color: #d0e4f7 !important;
        text-align: center;
        font-weight: 700;
        font-size: 0.75rem;
        color: #13304d;
        letter-spacing: 0.04em;
    }
    .th-equipo {
        background-color: #e8f4fb !important;
        font-size: 0.73rem;
        color: #2a5298;
    }
    tr.fila-highlight { outline: 2px solid #f0a500; background-color: #fff8cc !important; }
    .sin-geo { opacity: 0.4; font-size: 0.7rem; }
    .cards-mobile { display: none; }

    /* ── CARDS MOBILE ── */
    .card-paciente {
        background: white;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        overflow: hidden;
        border-left: 5px solid #718096;
    }
    .card-paciente.riesgo-alto    { border-left-color: #e53e3e; }
    .card-paciente.riesgo-mediano { border-left-color: #d69e2e; }
    .card-paciente.riesgo-bajo    { border-left-color: #38a169; }
    .card-paciente.es-nuevo       { background: #f0fff4; }
    .card-paciente.es-modificado  { background: #fffde7; }
    .card-paciente.desactivado    { opacity: 0.5; }
    .card-paciente.fila-highlight { outline: 2px solid #f0a500; background: #fff8cc !important; }
    .card-header-pac {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.6rem 0.75rem 0.3rem;
        background: #f5f7fa;
    }
    .card-header-pac .nombre {
        font-weight: 700;
        font-size: 0.95rem;
        color: #13304d;
        flex: 1;
        margin-right: 8px;
    }
    .card-header-pac .id-badge {
        font-size: 0.72rem;
        color: #888;
        background: #e2e8f0;
        border-radius: 10px;
        padding: 1px 7px;
        white-space: nowrap;
    }
    .card-body-pac {
        padding: 0.5rem 0.75rem 0.6rem;
        font-size: 0.83rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4px 12px;
        color: #333;
    }
    .card-body-pac .full { grid-column: 1 / -1; }
    .card-body-pac .lbl  { color: #888; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.03em; }
    .card-footer-pac {
        display: flex;
        gap: 6px;
        padding: 0.45rem 0.75rem;
        background: #f5f7fa;
        border-top: 1px solid #e2e8f0;
        flex-wrap: wrap;
        align-items: center;
    }
    .equipo-card-bloque {
        border-left: 3px solid #2a5298;
        padding: 4px 8px;
        margin-bottom: 5px;
        background: #f0f5ff;
        border-radius: 0 4px 4px 0;
        font-size: 0.76rem;
        line-height: 1.55;
    }
    .equipo-card-bloque strong { color: #13304d; font-size: 0.80rem; }
    .equipo-card-bloque .eq-fila { color: #555; }
</style>

<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('home')); ?>">Inicio</a> ›
    <strong>Grupos</strong>
</div>

<div class="notification" style="background-color: #13304d; padding-bottom: 0.75rem;">
    <h1 class="title" style="color:white; margin-bottom:0.5rem;">LISTADO DE GRUPOS</h1>
    <hr style="background-color:#2a5298; margin:0.5rem 0;"/>

    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:0.75rem;">
        <a class="button is-info is-small" href="<?= base_url(route_to('inicio')) ?>">
            <span class="icon"><i class="fas fa-arrow-left"></i></span>
            <span>Volver a Inicio</span>
        </a>
        <a class="button is-success is-small" href="<?= base_url(route_to('group_create')) ?>">
            <span class="icon"><i class="fas fa-user"></i></span>
            <span>Crear Grupo</span>
        </a>
    </div>

    <div id="col-tabla">
        <div class="table-container">
            <table class="table is-fullwidth is-hoverable is-bordered tabla-electro" style="font-size:0.80rem;">
                <thead>
                    <tr style="background-color:#c8d8f0;">
                        <th>Id</th>
                        <th>Grupo</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $v): ?>
                        <tr id="fila-<?= $v->id ?>">
                            <td><?= $v->id ?></td>
                            <td><?= $v->name ?></td>
                            <td><?= $v->description ?></td>
                            <td>
                                <a href="<?= base_url(route_to('groups_permisos_list', $v->id)) ?>">Ver Permisos</a> |
                                <form style="display:inline;" action="<?= base_url(route_to('destroy_group')) ?>" method="post">
                                    <input type="hidden" name="_method" value="DELETE" />
                                    <input type="hidden" name="id" value="<?= $v->id ?>" />
                                    <?= csrf_field() ?>
                                    <a onclick="this.closest('form').submit();return false;">Eliminar grupo</a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cards-mobile">
            <?php foreach ($grupos as $v): ?>
                <div class="card-paciente" id="card-<?= $v->id ?>">
                    <div class="card-header-pac">
                        <span class="nombre"><?= esc($v->name) ?></span>
                        <span class="id-badge">#<?= $v->id ?></span>
                    </div>
                    <div class="card-body-pac">
                        <div><div class="lbl">Descripción</div><div><?= esc($v->description) ?></div></div>
                        <div class="full" style="margin-top:6px;">
                            <div class="lbl">Acciones</div>
                            <div style="display:flex; gap:6px; margin-top:4px;">
                                <a href="<?= base_url(route_to('groups_permisos_list', $v->id)) ?>">Ver Permisos</a>
                                <form style="display:inline;" action="<?= base_url(route_to('destroy_group')) ?>" method="post">
                                    <input type="hidden" name="_method" value="DELETE" />
                                    <input type="hidden" name="id" value="<?= $v->id ?>" />
                                    <?= csrf_field() ?>
                                    <a onclick="this.closest('form').submit();return false;">Eliminar grupo</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?= $pager->links(); ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Mapa functionality (empty implementation since we're not using it for groups)
    var mapaIniciado = false;
    function iniciarMapa() {
        if (mapaIniciado) return;
        mapaIniciado = true;
        // Map initialization code would go here if needed
    }
</script>
<?= $this->endSection() ?>