<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> ELECTRODEPENDIENTES <?= $this->endSection() ?>
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
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Salud mental</a> ›
    <strong>Electrodependientes</strong>
</div>

<div class="notification" style="background-color: #13304d; padding-bottom: 0.75rem;">
    <h1 class="title" style="color:white; margin-bottom:0.5rem;">ELECTRODEPENDIENTES</h1>
    <hr style="background-color:#2a5298; margin:0.5rem 0;"/>

    <?php if ($nuevas > 0 || $modificadas > 0): ?>
    <div class="notification" style="background-color:#fff3cd; border-left:5px solid #f0a500; color:#333; margin-bottom:1rem;">
        <div class="columns is-vcentered is-mobile">
            <div class="column">
                <strong>📋 Desde tu última visita
                    (<?= $ultimaVista == '2000-01-01 00:00:00' ? 'primera vez' : date('d/m/Y H:i', strtotime($ultimaVista)) ?>)
                </strong><br><br>
                <?php if ($nuevas > 0): ?>
                    <span class="tag is-success is-medium" style="margin-right:8px;">🟢 <?= $nuevas ?> nuevo<?= $nuevas > 1 ? 's' : '' ?></span>
                <?php endif; ?>
                <?php if ($modificadas > 0): ?>
                    <span class="tag is-warning is-medium">🟡 <?= $modificadas ?> modificado<?= $modificadas > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>
            <div class="column is-narrow">
                <a href="<?= base_url(route_to('electrodependiente_visto')) ?>" class="button is-dark is-small">
                    <span class="icon"><i class="fas fa-check"></i></span>
                    <span>VISTO</span>
                </a>
            </div>
        </div>
        <div style="margin-top:0.4rem; font-size:0.78em; display:flex; gap:8px; flex-wrap:wrap;">
            <span style="background:#d4edda; border-left:4px solid #28a745; padding:2px 8px; border-radius:3px;">🟢 nuevo</span>
            <span style="background:#fff8cc; border-left:4px solid #f0c040; padding:2px 8px; border-radius:3px;">🟡 modificado</span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:0.75rem;">
        <a class="button is-info is-small" href="<?= base_url(route_to('saludmental_views')) ?>">
            <span class="icon"><i class="fas fa-arrow-left"></i></span><span>Volver</span>
        </a>
        <a class="button is-warning is-small" href="<?= base_url(route_to('electrodependiente_create')) ?>">
            <span class="icon"><i class="fas fa-plus"></i></span><span>Nuevo</span>
        </a>
        <a class="button is-success is-small" href="<?= base_url(route_to('electrodependiente_export')) ?>?<?= http_build_query([
            'tipo'         => $filtro_tipo,
            'localidad'    => $filtro_localidad,
            'efector'      => $filtro_efector,
            'obra_social'  => $filtro_obra_social,
            'diagnostico'  => $filtro_diagnostico,
            'factor_riesgo'=> $filtro_factor,
            'seguimiento'  => $filtro_seguimiento,
            'cud'          => $filtro_cud,
            'buscar'       => $filtro_buscar,
        ]) ?>">
            <span class="icon"><i class="fas fa-file-excel"></i></span><span>Excel</span>
        </a>
        <button class="button is-light is-small" id="btnToggleMapa">
            <span class="icon"><i class="fas fa-map-marked-alt"></i></span>
            <span id="lblToggleMapa">Mostrar Mapa</span>
        </button>
    </div>

    <form method="GET" action="<?= base_url(route_to('electrodependiente_list')) ?>">
        <div class="columns is-multiline is-vcentered is-mobile" style="margin-bottom:0;">
            <div class="column is-12-mobile is-3-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Buscar paciente / DNI</label>
                    <div class="control has-icons-left">
                        <input class="input is-small" type="text" name="buscar" placeholder="Nombre o DNI..." value="<?= esc($filtro_buscar) ?>">
                        <span class="icon is-left is-small"><i class="fas fa-search"></i></span>
                    </div>
                </div>
            </div>
            <div class="column is-6-mobile is-1-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Tipo</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="tipo"><option value="">Todos</option><?php foreach ($tipos as $t): ?><option value="<?= $t ?>" <?= $filtro_tipo==$t?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></div></div>
                </div>
            </div>
            <div class="column is-6-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Localidad</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="localidad"><option value="">Todas</option><?php foreach ($localidades as $l): ?><option value="<?= $l['localidad_id'] ?>" <?= $filtro_localidad==$l['localidad_id']?'selected':'' ?>><?= esc($l['nombre']) ?></option><?php endforeach; ?></select></div></div>
                </div>
            </div>
            <div class="column is-6-mobile is-2-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Hospital</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="efector"><option value="">Todos</option><?php foreach ($efectores as $ef): ?><option value="<?= $ef->efector_id ?>" <?= $filtro_efector==$ef->efector_id?'selected':'' ?>><?= esc($ef->nombre) ?></option><?php endforeach; ?></select></div></div>
                </div>
            </div>
            <div class="column is-6-mobile is-1-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Riesgo</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="factor_riesgo"><option value="">Todos</option><?php foreach ($factores as $f): ?><option value="<?= $f ?>" <?= $filtro_factor==$f?'selected':'' ?>><?= $f ?></option><?php endforeach; ?></select></div></div>
                </div>
            </div>
            <div class="column is-6-mobile is-1-tablet">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">CUD</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="cud"><option value="">Todos</option><option value="SI" <?= $filtro_cud=='SI'?'selected':'' ?>>SI</option><option value="NO" <?= $filtro_cud=='NO'?'selected':'' ?>>NO</option></select></div></div>
                </div>
            </div>
            <div class="column is-6-mobile is-1-tablet" id="col-estado" style="display:none;">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">Estado</label>
                    <div class="control"><div class="select is-fullwidth is-small"><select name="estado"><option value="">Todos</option><option value="activo" <?= $filtro_estado=='activo'?'selected':'' ?>>Activo</option><option value="desactivado" <?= $filtro_estado=='desactivado'?'selected':'' ?>>Desactivado</option></select></div></div>
                </div>
            </div>
            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white; font-size:0.85rem;">&nbsp;</label>
                    <div class="control" style="display:flex; gap:5px;">
                        <button class="button is-link is-small" type="submit"><span class="icon"><i class="fas fa-filter"></i></span></button>
                        <button class="button is-light is-small" id="btnToggle" type="button"><span class="icon"><i class="fas fa-sliders-h" id="iconoToggle"></i></span></button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="col-mapa" style="margin-bottom:0.6rem; display:none;">
        <div id="mapa-electro"></div>
        <div class="leyenda-mapa">
            <strong style="color:#ffe08a;">Leyenda:</strong>
            <span><span class="leyenda-dot" style="background:#e53e3e;"></span>ALTO</span>
            <span><span class="leyenda-dot" style="background:#d69e2e;"></span>MEDIANO</span>
            <span><span class="leyenda-dot" style="background:#38a169;"></span>BAJO</span>
            <span><span class="leyenda-dot" style="background:#718096;"></span>S/D</span>
            <span class="leyenda-nota" style="color:#aaa; font-size:0.73rem; margin-left:auto;">Solo pacientes con coordenadas cargadas.</span>
        </div>
    </div>

    <div id="col-tabla">
        <div class="table-container">
            <table class="table is-fullwidth is-hoverable is-bordered tabla-electro" style="font-size:0.80rem;">
                <thead>
                    <tr style="background-color:#c8d8f0;">
                        <th colspan="13" style="text-align:center; background:#e8f0fe; color:#13304d; font-size:0.75rem; letter-spacing:0.05em;">📋 DATOS DEL PACIENTE</th>
                        <th colspan="9" class="th-equipo-group">🔌 EQUIPAMIENTO</th>
                        <th colspan="4" style="text-align:center; background:#e8f0fe; color:#13304d; font-size:0.75rem; letter-spacing:0.05em;">ACCIONES</th>
                    </tr>
                    <tr style="background-color:#e8f0fe;">
                        <th>#</th><th>Paciente</th><th>DNI</th><th>Edad</th><th>Tipo</th><th>Localidad</th><th>Hospital</th><th>Obra Social</th><th>CUD</th><th>Diagnóstico</th><th>Riesgo</th><th>Seguimiento</th><th>Estado</th>
                        <th class="th-equipo">Equipo</th><th class="th-equipo">Marca</th><th class="th-equipo">Serie</th><th class="th-equipo">Modelo</th><th class="th-equipo">F. Entrega</th><th class="th-equipo">Tiempo Uso</th><th class="th-equipo">Médico Tratante</th><th class="th-equipo">Titular Servicio</th><th class="th-equipo">Nro Servicio</th>
                        <th>📍</th><th>Editar</th><th>Eliminar</th><th>Historial</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($registros as $r):
                    $esNuevo      = $r->created_at > $ultimaVista;
                    $esModificado = ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
                    $tieneGeo     = !empty($r->coordenadas);
                    $equipos = $r->equipos ?? [];
                    $eqNombres = []; $eqMarcas = []; $eqSeries = []; $eqModelos = []; $eqFEntrega = []; $eqTiempoUso = []; $eqMedicos = []; $eqTitulares = []; $eqNroServ = [];
                    foreach ($equipos as $eq) {
                        $eqNombres[]  = $eq['equipamiento'] ?? '—';
                        $eqMarcas[]   = $eq['marca'] ?? '—';
                        $eqSeries[]   = $eq['serie'] ?? '—';
                        $eqModelos[]  = $eq['modelo'] ?? '—';
                        $eqFEntrega[] = $eq['fecha_entrega'] ?? '—';
                        $eqTiempoUso[] = $eq['tiempo_uso'] ?? '—';
                        $eqMedicos[]  = $eq['medico_tratante'] ?? '—';
                        $eqTitulares[] = $eq['titular_servicio'] ?? '—';
                        $eqNroServ[]  = $eq['nro_servicio'] ?? '—';
                    }
                    if (empty($equipos)) {
                        $eqNombres = $eqMarcas = $eqSeries = $eqModelos = $eqFEntrega = $eqTiempoUso = $eqMedicos = $eqTitulares = $eqNroServ = ['—'];
                    }
                    $sep = '<span class="equipo-sep"></span>';
                ?>
                    <tr id="fila-<?= $r->electrodependiente_id ?>" <?php if ($r->estado == 'desactivado') echo 'style="opacity:0.45;background:#ccc;"'; elseif ($esNuevo) echo 'style="background-color:#d4edda;border-left:4px solid #28a745;"'; elseif ($esModificado) echo 'style="background-color:#fff8cc;border-left:4px solid #f0c040;"'; ?>>
                        <td><?= $r->electrodependiente_id ?></td>
                        <td><strong><?= esc($r->paciente) ?></strong></td>
                        <td><?= esc($r->dni ?? '—') ?></td>
                        <td><?= $r->edad ?? '—' ?></td>
                        <td><span class="tag <?= $r->tipo=='NIÑO'?'is-info':'is-dark' ?>"><?= esc($r->tipo??'—') ?></span></td>
                        <td><?= esc($r->localidad_nombre ?? '—') ?></td>
                        <td><?= esc($r->efector_nombre ?? '—') ?></td>
                        <td><?= esc($r->obra_social_nombre ?? '—') ?></td>
                        <td><span class="tag <?= $r->cud=='SI'?'is-success':'is-light' ?>"><?= esc($r->cud??'—') ?></span></td>
                        <td><?= esc($r->diagnostico_nombre ?? '—') ?></td>
                        <td><span class="tag <?php switch($r->factor_riesgo){ case 'ALTO': echo 'is-danger'; break; case 'MEDIANO': echo 'is-warning'; break; case 'BAJO': echo 'is-success'; break; default: echo 'is-light'; } ?>"><?= esc($r->factor_riesgo??'—') ?></span></td>
                        <td><?= esc($r->seguimiento ?? '—') ?></td>
                        <td><?= $r->estado=='activo' ? '<span class="tag is-success">Activo</span>' : '<span class="tag is-danger">Desactivado</span>' ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqNombres)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqMarcas)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqSeries)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqModelos)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqFEntrega)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqTiempoUso)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqMedicos)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqTitulares)) ?></td>
                        <td class="celda-equipo"><?= implode($sep, array_map('esc', $eqNroServ)) ?></td>
                        <td class="has-text-centered"><?php if ($tieneGeo): ?><button class="button is-small is-link is-outlined btn-ir-mapa" data-id="<?= $r->electrodependiente_id ?>" title="Ver en mapa"><span class="icon"><i class="fas fa-map-pin"></i></span></button><?php else: ?><span class="sin-geo">—</span><?php endif; ?></td>
                        <td><?= $r->getEditLink() ?></td>
                        <td><?= $r->getDeleteLink() ?></td>
                        <td><a href="<?= base_url(route_to('electrodependiente_historial', $r->electrodependiente_id)) ?>" class="button is-small is-info is-outlined"><span class="icon"><i class="fas fa-history"></i></span><span>Historial</span></a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($registros)): ?><tr><td colspan="27" class="has-text-centered">No hay registros cargados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="cards-mobile">
        <?php foreach ($registros as $r):
            $esNuevo      = $r->created_at > $ultimaVista;
            $esModificado = ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
            $tieneGeo     = !empty($r->coordenadas);
            $equipos      = $r->equipos ?? [];
            $rc = match(strtoupper($r->factor_riesgo??'')){ 'ALTO' => 'riesgo-alto', 'MEDIANO' => 'riesgo-mediano', 'BAJO' => 'riesgo-bajo', default => '' };
            $sc = $r->estado=='desactivado' ? 'desactivado' : ($esNuevo ? 'es-nuevo' : ($esModificado ? 'es-modificado' : ''));
        ?>
            <div class="card-paciente <?= $rc ?> <?= $sc ?>" id="card-<?= $r->electrodependiente_id ?>">
                <div class="card-header-pac"><span class="nombre"><?= esc($r->paciente) ?></span><span class="id-badge">#<?= $r->electrodependiente_id ?></span></div>
                <div class="card-body-pac">
                    <div><div class="lbl">DNI</div><div><?= esc($r->dni??'—') ?></div></div>
                    <div><div class="lbl">Edad / Tipo</div><div><?= $r->edad??'—' ?> &nbsp;<span class="tag is-small <?= $r->tipo=='NIÑO'?'is-info':'is-dark' ?>"><?= esc($r->tipo??'—') ?></span></div></div>
                    <div><div class="lbl">Localidad</div><div><?= esc($r->localidad_nombre??'—') ?></div></div>
                    <div><div class="lbl">Hospital</div><div style="font-size:0.78rem;"><?= esc($r->efector_nombre??'—') ?></div></div>
                    <div><div class="lbl">Obra Social</div><div style="font-size:0.78rem;"><?= esc($r->obra_social_nombre??'—') ?></div></div>
                    <div><div class="lbl">CUD</div><span class="tag is-small <?= $r->cud=='SI'?'is-success':'is-light' ?>"><?= esc($r->cud??'—') ?></span></div>
                    <div class="full"><div class="lbl">Diagnóstico</div><div><?= esc($r->diagnostico_nombre??'—') ?></div></div>
                    <div><div class="lbl">Riesgo</div><span class="tag is-small <?php switch($r->factor_riesgo){ case 'ALTO': echo 'is-danger'; break; case 'MEDIANO': echo 'is-warning'; break; case 'BAJO': echo 'is-success'; break; default: echo 'is-light'; } ?>"><?= esc($r->factor_riesgo??'—') ?></span></div>
                    <div><div class="lbl">Seguimiento</div><div style="font-size:0.76rem;"><?= esc($r->seguimiento??'—') ?></div></div>
                    <?php if (!empty($equipos)): ?>
                    <div class="full" style="margin-top:6px;">
                        <div class="lbl" style="margin-bottom:4px;">🔌 Equipamiento (<?= count($equipos) ?>)</div>
                        <?php foreach ($equipos as $idx => $eq): ?>
                        <div class="equipo-card-bloque">
                            <strong><?= esc($eq['equipamiento'] ?? '—') ?></strong>
                            <?php if (!empty($eq['marca']) || !empty($eq['modelo'])): ?><span class="eq-fila"> · <?= esc($eq['marca']??'') ?><?= !empty($eq['modelo']) ? ' / '.esc($eq['modelo']) : '' ?></span><?php endif; ?>
                            <?php if (!empty($eq['serie'])): ?><br><span class="eq-fila">Serie: <?= esc($eq['serie']) ?></span><?php endif; ?>
                            <?php if (!empty($eq['medico_tratante'])): ?><br><span class="eq-fila">Médico: <?= esc($eq['medico_tratante']) ?></span><?php endif; ?>
                            <?php if (!empty($eq['titular_servicio'])): ?><br><span class="eq-fila">Titular: <?= esc($eq['titular_servicio']) ?></span><?php endif; ?>
                            <?php if (!empty($eq['nro_servicio'])): ?><br><span class="eq-fila">Nro Serv.: <?= esc($eq['nro_servicio']) ?></span><?php endif; ?>
                            <?php if (!empty($eq['fecha_entrega'])): ?><br><span class="eq-fila">Entrega: <?= esc($eq['fecha_entrega']) ?></span><?php endif; ?>
                            <?php if (!empty($eq['tiempo_uso'])): ?><br><span class="eq-fila">Tiempo uso: <?= esc($eq['tiempo_uso']) ?></span><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="full" style="margin-top:4px;"><div class="lbl">🔌 Equipamiento</div><div style="color:#aaa; font-size:0.76rem;">Sin equipos registrados</div></div>
                    <?php endif; ?>
                </div>
                <div class="card-footer-pac">
                    <?php if ($tieneGeo): ?><button class="button is-small is-link is-outlined btn-ir-mapa" data-id="<?= $r->electrodependiente_id ?>"><span class="icon"><i class="fas fa-map-pin"></i></span><span>Mapa</span></button><?php endif; ?>
                    <?= $r->getEditLink() ?>
                    <?= $r->getDeleteLink() ?>
                    <?= $r->estado=='activo' ? '<span class="tag is-success is-small" style="align-self:center;">Activo</span>' : '<span class="tag is-danger is-small" style="align-self:center;">Desactivado</span>' ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($registros)): ?><p class="has-text-centered" style="color:white; padding:1rem;">No hay registros.</p><?php endif; ?>
        </div>

        <?= $pager->links() ?>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var pacientesGeo = <?= json_encode(array_values(array_filter(array_map(function($r) {
        if (empty($r->coordenadas)) return null;
        $coords = array_map('trim', explode(',', $r->coordenadas));
        if (count($coords) < 2) return null;
        $lat = (float)$coords[0]; $lng = (float)$coords[1];
        if ($lat == 0 && $lng == 0) return null;
        return ['id' => $r->electrodependiente_id, 'paciente' => $r->paciente, 'dni' => $r->dni ?? '', 'edad' => $r->edad ?? '', 'tipo' => $r->tipo ?? '', 'localidad' => $r->localidad_nombre ?? '', 'efector' => $r->efector_nombre ?? '', 'diagnostico' => $r->diagnostico_nombre ?? '', 'riesgo' => $r->factor_riesgo ?? '', 'seguimiento' => $r->seguimiento ?? '', 'lat' => $lat, 'lng' => $lng];
    }, $registros)))) ?>;

    var mapaIniciado = false;
    var mapa, marcadores = {}, grupo;

    function iniciarMapa() {
        if (mapaIniciado) return;
        mapaIniciado = true;
        mapa = L.map('mapa-electro', { zoomControl: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>', maxZoom: 18 }).addTo(mapa);
        grupo = L.featureGroup();
        pacientesGeo.forEach(function(p) {
            var marker = L.marker([p.lat, p.lng], { icon: crearIcono(p.riesgo) });
            marker.bindPopup('<strong>'+p.paciente+'</strong><table>' + '<tr><td>DNI:</td><td>'+(p.dni||'—')+'</td></tr>' + '<tr><td>Edad:</td><td>'+(p.edad||'—')+'</td></tr>' + '<tr><td>Localidad:</td><td>'+(p.localidad||'—')+'</td></tr>' + '<tr><td>Hospital:</td><td>'+(p.efector||'—')+'</td></tr>' + '<tr><td>Diagnóstico:</td><td>'+(p.diagnostico||'—')+'</td></tr>' + '<tr><td>Riesgo:</td><td><strong style="color:'+colorRiesgo(p.riesgo)+'">'+(p.riesgo||'—')+'</strong></td></tr>' + '</table>', { maxWidth: 240 });
            marker.on('popupopen', function() {
                document.querySelectorAll('.fila-highlight').forEach(function(el){ el.classList.remove('fila-highlight'); });
                var el = document.getElementById('fila-'+p.id) || document.getElementById('card-'+p.id);
                if (el) { el.classList.add('fila-highlight'); el.scrollIntoView({behavior:'smooth',block:'center'}); }
            });
            marker.on('popupclose', function() {
                var el = document.getElementById('fila-'+p.id) || document.getElementById('card-'+p.id);
                if (el) el.classList.remove('fila-highlight');
            });
            marcadores[p.id] = marker;
            grupo.addLayer(marker);
        });
        grupo.addTo(mapa);
        if (pacientesGeo.length > 0) mapa.fitBounds(grupo.getBounds().pad(0.15));
        else mapa.setView([-24.185, -65.299], 9);
    }

    function colorRiesgo(r) {
        switch ((r||'').toUpperCase()) {
            case 'ALTO': return '#e53e3e';
            case 'MEDIANO': return '#d69e2e';
            case 'BAJO': return '#38a169';
            default: return '#718096';
        }
    }

    function crearIcono(r) {
        var c = colorRiesgo(r);
        return L.divIcon({ html:'<svg xmlns="http://www.w3.org/2000/svg" width="28" height="38" viewBox="0 0 28 38">' + '<path d="M14 0C6.27 0 0 6.27 0 14c0 9.75 14 24 14 24S28 23.75 28 14C28 6.27 21.73 0 14 0z" fill="'+c+'" stroke="#fff" stroke-width="2"/>' + '<circle cx="14" cy="14" r="6" fill="white" opacity="0.9"/></svg>', className:'', iconSize:[28,38], iconAnchor:[14,38], popupAnchor:[0,-38] });
    }

    document.querySelectorAll('.btn-ir-mapa').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = parseInt(this.dataset.id);
            var cm = document.getElementById('col-mapa');
            var lbl = document.getElementById('lblToggleMapa');
            if (cm.style.display === 'none' || cm.style.display === '') {
                cm.style.display = 'block';
                lbl.textContent = 'Ocultar Mapa';
            }
            iniciarMapa();
            setTimeout(function() {
                mapa.invalidateSize();
                var marker = marcadores[id];
                if (!marker) return;
                mapa.setView(marker.getLatLng(), 15, {animate:true});
                marker.openPopup();
                document.getElementById('mapa-electro').scrollIntoView({behavior:'smooth', block:'start'});
            }, 220);
        });
    });

    document.getElementById('btnToggleMapa').addEventListener('click', function() {
        var el = document.getElementById('col-mapa');
        var lbl = document.getElementById('lblToggleMapa');
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
            lbl.textContent = 'Ocultar Mapa';
            iniciarMapa();
            setTimeout(function(){ mapa.invalidateSize(); }, 220);
        } else {
            el.style.display = 'none';
            lbl.textContent = 'Mostrar Mapa';
        }
    });

    (function () {
        var ce = document.getElementById('col-estado');
        var btn = document.getElementById('btnToggle');
        var ic = document.getElementById('iconoToggle');
        var visible = !!(new URLSearchParams(window.location.search)).get('estado');
        function ap() {
            ce.style.display = visible ? '' : 'none';
            ic.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
        }
        ap();
        btn.addEventListener('click', function(){ visible = !visible; ap(); });
    })();
</script>
<?= $this->endSection() ?>