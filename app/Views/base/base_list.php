<?= $this->extend('layout/main'); ?> //3333
<?= $this->section('title') ?> Bases · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* CSS specific to base_list, extracted from original */
    .bl-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .bl-breadcrumb i { color: var(--teal); font-size: 13px; }
    .bl-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .bl-breadcrumb a:hover { color: var(--teal); }
    .bl-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .bl-kpi-cards {
        display: flex; 
        gap: 16px; 
        flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .bl-kpi-cards--compact {
        gap: 40px !important;
    }
    .bl-kpi-cards--compact .bl-kpi-card {
        max-width: 350px;
        padding: 25px;
        gap: 25px;
    }
    .bl-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #e2e8f0;
        flex: 1 1 160px;
        max-width: 300px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 90px;

        box-sizing: border-box; 
    }
    @media (min-width: 1200px) {
        .bl-kpi-card {
            flex: 1 1 150px;
            max-width: none;
        }
    }
    @media (max-width: 768px) {
        .bl-kpi-card {
            flex: 1 1 calc(50% - 8px);
        }
    }
    @media (max-width: 480px) {
        .bl-kpi-card {
            flex: 1 1 100%;
        }
    }
    .bl-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
    }
    .bl-kpi-label { 
        font-size: 11px; font-weight: 700; color: #718096; text-transform: uppercase; 
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .kpi-teal { border-left: 6px solid #38b2ac; }
    .kpi-orange { border-left: 6px solid #ed8936; }
    .kpi-blue { border-left: 6px solid #4299e1; }
    .kpi-green { border-left: 6px solid #48bb78; }
    .kpi-red { border-left: 6px solid #f56565; }
     .kpi-purple { border-left: 6px solid #9f7aea; }

     .bl-kpi-icon {
        width: 70px; height:70px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .bl-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-orange .bl-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-blue .bl-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-green .bl-kpi-icon { background: #f0fff4; color: #38a169; }
    .kpi-red .bl-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-purple .bl-kpi-icon { background: #faf5ff; color: #805ad5; }

    .bl-kpi-label { font-size: 11px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .bl-kpi-value { font-size: 25px; 
        font-weight: 700; 
        color: #2d3748;
        padding-top: 0;
        padding-bottom: 0; 
        line-height: 1;
        margin-top: 0px;
        margin-bottom: 2px;
    }
    .bl-kpi-sub { font-size: 12px; color: #a0aec0; margin-bottom: 8px; }
    .bl-kpi-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 6px;
        width: fit-content;
    }

    @media (max-width: 500px) {
        .bl-kpi-icon { width: 45px; height: 45px; font-size: 20px; }
        .bl-kpi-label { font-size: 9px; }
        .bl-kpi-value { font-size: 18px; }
        .bl-kpi-badge { font-size: 9px; padding: 2px 5px; }
        .bl-kpi-card { padding: 8px; gap: 8px; height: 70px; }
    }
    .bl-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .bl-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .bl-kpi-badge.teal { background: #e6fffa; color: #319795; }

    .buton-empty {
        color: var(--navy);
        background: #ececec;
        border-radius: 8px;
        padding: 10px 0;
        font-size: 15px; font-weight: 700;
        color: var(--teal);

        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .bl-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .bl-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .bl-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .bl-panel-title i { color: var(--teal); font-size: 17px; }
    .bl-panel-body { padding: 18px 20px; }

    .bl-kpi-group { display: flex; gap: 8px; flex-wrap: wrap; }

    .bl-alert {
        border-radius: 10px;
        border-left: 4px solid #f0a500;
        background: #fff9e6;
        padding: 14px 18px;
        margin-bottom: 16px;
        font-size: 13px; color: var(--text-main);
    }
    .bl-alert-head { font-weight: 700; margin-bottom: 8px; }
    .bl-alert-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .bl-alert-legend { display: flex; gap: 12px; font-size: 11px; flex-wrap: wrap; }
    .bl-alert-legend span {
        padding: 2px 10px; border-radius: 4px;
    }
    .bl-alert-actions { margin-top: 10px; }

    .bl-tag {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11.5px; font-weight: 600;
        padding: 4px 11px; border-radius: 10px;
    }
    .bl-tag.green  { background: rgba(39,174,96,0.13);  color: #27ae60; }
    .bl-tag.yellow { background: rgba(240,165,0,0.15);  color: #b07d00; }
    .bl-tag.red    { background: rgba(231,76,60,0.13);  color: #c0392b; }
    .bl-tag.teal   { background: var(--teal-bg);        color: var(--teal); }
    .bl-tag.blue   { background: rgba(52,152,219,0.13); color: #2980b9; }

    .bl-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 8px 16px; border-radius: 8px; border: none;
        font-size: 13px; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: filter 0.15s, opacity 0.15s;
        white-space: nowrap;
    }
    .bl-btn:hover { filter: brightness(1.1); text-decoration: none; }
    .bl-btn.teal    { background: var(--teal);    color: #fff; }
    .bl-btn.navy    { background: var(--navy);    color: #fff; }
    .bl-btn.green   { background: #27ae60;        color: #fff; }
    .bl-btn.ghost   { background: #f0f2f5; color: var(--text-muted); border: 1px solid var(--border); }
    .bl-btn.ghost:hover { background: #e4e7ed; color: var(--text-main); }
    .bl-btn.pushed { background: #cbd5e0 !important; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2) !important; color: #333 !important; border-color: #a0aec0 !important; }
    
    .btn-status.pushed[data-modo="activo"] {
        background: rgba(39,174,96,0.2) !important;
        color: #27ae60 !important;
        border-color: rgba(39,174,96,0.4) !important;
    }
    .btn-status.pushed[data-modo="inactivo"] {
        background: rgba(231,76,60,0.2) !important;
        color: #c0392b !important;
        border-color: rgba(231,76,60,0.4) !important;
    }

    .bl-btn.sm { padding: 5px 11px; font-size: 12px; }
    .bl-btn.danger-ghost { background: rgba(231,76,60,0.09); color: #c0392b; border: 1px solid rgba(231,76,60,0.25); }
    .bl-btn.danger-ghost:hover { background: rgba(231,76,60,0.18); }

    .bl-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .bl-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .bl-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .bl-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .bl-filtro-input,
    .bl-filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: var(--white);
        outline: none;
        transition: border-color 0.15s;
        height: 36px;
    }
    .bl-filtro-input:focus,
    .bl-filtro-select:focus { border-color: var(--teal); }

    .bl-map-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; gap: 8px;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
    }
    .bl-map-header i { color: var(--teal); }
    .bl-map-header { justify-content: space-between; cursor: default; border-radius: 12px 12px 0 0; }
    .bl-map-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .bl-map-panel.is-collapsed { margin-bottom: 12px; }
    .bl-map-panel.is-collapsed .bl-map-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    .bl-map-wrap {
        height: 800px;
        border-radius: 0 0 10px 10px;
        overflow: hidden;
    }
    .bl-map-wrap.is-hidden { display: none; }
    #mapaBases { width: 100%; height: 100%; }
    .bl-map-popup b { color: var(--navy); }
    .bl-map-popup .bl-map-tag {
        display: inline-block; margin-top: 4px;
        font-size: 10px; padding: 2px 8px; border-radius: 8px;
        background: var(--teal-bg); color: var(--teal); font-weight: 700;
    }
    .bl-map-empty {
        padding: 40px 20px; text-align: center;
        color: var(--text-muted); font-size: 13px;
    }

    .bl-list-container {
        display: flex; flex-direction: column; gap: 10px;
        max-height: 800px; overflow-y: auto; padding-right: 5px;
        width: 100%;
    }
    .bl-base-item {
        background: #f9f9f9; border: 1px solid var(--border);
        border-radius: 8px; padding: 12px;
        display: flex; flex-direction: column; gap: 4px;
        transition: background 0.15s;
    }
    .bl-base-item:hover { background: #f0f5f9; }
    .bl-base-name { font-weight: 700; color: var(--text-main); font-size: 14px; }
    .bl-base-info { font-size: 12px; color: var(--text-muted); }
    .bl-base-tags { display: flex; gap: 6px; margin-top: 4px; }
    .bl-base-tag { font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; background: #e0e0e0; }

    /* Grid de configuración para ancho de lista y mapa */
    .bl-view-grid {
        display: grid;
        grid-template-columns: var(--list-width, 350px) var(--map-width, 1fr);
        gap: 15px;
        align-items: start;
    }

    /* Asegurar que los controles de Leaflet estén por encima del sidebar */
    .leaflet-control-container {
        z-index: 6000 !important;
    }
    .leaflet-top, .leaflet-bottom {
        z-index: 6000 !important;
    }

    /* Asegurar que el sidebar esté por encima del mapa */
    .sidebar {
        z-index: 7000 !important;
    }
    body.sidebar-collapsed .sidebar:hover {
        z-index: 7000 !important;
    }

    /* Leyendas */
    .bl-legend-group { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: center;}
    .bl-legend-item { display: flex; align-items: center; gap: 6px; font-size: 11px; }
    
    /* Filtro mapa B&W */
    .leaflet-tile-pane { filter: grayscale(100%); }
    .bl-legend-color { width: 12px; height: 12px; border-radius: 3px; }
</style>

<!-- BREADCRUMB -->
<div class="bl-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('base_views')); ?>">Prehospitalario</a> ›
    <strong>Bases — Listado</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="bl-panel">
    <div class="bl-panel-header">
        <span class="bl-panel-title">
            <i class="fas fa-database"></i> BASES
        </span>
        <div class="bl-kpi-group">
            <span class="bl-tag green">
                <i class="fas fa-circle" style="font-size:7px;"></i>
                <?= $totalActivasGlobal ?> activa<?= $totalActivasGlobal == 1 ? '' : 's' ?>
            </span>
            <span class="bl-tag red">
                <i class="fas fa-circle" style="font-size:7px;"></i>
                <?= $totalDesactivadasGlobal ?> desactivada<?= $totalDesactivadasGlobal == 1 ? '' : 's' ?>
            </span>
        </div>
    </div>
    <div class="bl-panel-body">

        <!-- ── BANNER ALERTA ACTIVIDAD ── -->
       <!--  <?php if ($nuevas > 0 || $modificadas > 0): ?>
        <div class="bl-alert">
            <div class="bl-alert-head">
                <i class="fas fa-bell" style="color:#f0a500;"></i>
                Actividad desde tu última visita
                (<?= $ultimaVista == '2000-01-01 00:00:00'
                    ? 'primera vez'
                    : date('d/m/Y H:i', strtotime($ultimaVista)) ?>)
            </div>
        </div>
        <?php endif; ?> -->

        <!-- ── TOOLBAR ── -->
        <div class="bl-toolbar">
            <a href="<?= base_url(route_to('base_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('base_export')); ?>?<?= http_build_query([
                'search'    => $search,
                'tipo'      => $filtro_tipo,
                'region'    => $filtro_region,
                'provincia' => $filtro_provincia,
                'estado'    => $filtro_estado,
            ]) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('base_list')); ?>" id="searchForm">
            <div class="bl-filtro-bar">
                <div class="bl-filtro-group">
                    <span class="bl-filtro-label">Tipo</span>
                    <select name="tipo" class="bl-filtro-select">
                        <option value="">Todos</option>
                        <option value="BASE" <?= $filtro_tipo == 'BASE' ? 'selected' : '' ?>>BASE</option>
                        <option value="USE"  <?= $filtro_tipo == 'USE'  ? 'selected' : '' ?>>USE</option>
                    </select>
                </div>
                <div class="bl-filtro-group">
                    <span class="bl-filtro-label">Región</span>
                    <select name="region" class="bl-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= $r ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="bl-filtro-group">
                    <span class="bl-filtro-label">Provincia</span>
                    <select name="provincia" class="bl-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($provincias as $p): ?>
                            <option value="<?= $p ?>" <?= $filtro_provincia == $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
               
                <div class="bl-filtro-group">
                    <span class="bl-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button type="button" class="bl-btn ghost" id="btnToggle"
                                style="height:36px; padding:0 14px;"
                                title="Mostrar/ocultar Buscar y Estado">
                            <i class="fas fa-sliders-h" id="iconoToggle"></i>
                        </button>
                    </div>
                </div>
                 <!-- Filtro Estado (Botones) -->
                <div class="bl-filtro-group" id="col-estado">
                    <span class="bl-filtro-label">Estado</span>

                    <!-- Estado real que utiliza el filtro -->
                    <input type="hidden"
                        name="estado"
                        id="input-estado"
                        value="<?= esc($filtro_estado ?? '') ?>">

                    <!--
                        Modo visual del filtro:
                        todos     = Todos seleccionado
                        activo    = Activo seleccionado
                        inactivo  = Inactivo seleccionado
                        ninguno   = Ningún botón seleccionado
                    -->
                    <input type="hidden"
                        name="estado_modo"
                        id="input-estado-modo"
                        value="<?= esc($estado_modo ?? 'todos') ?>">

                    <div style="display:flex; gap:4px; height:36px; align-items:center;">

                        <button type="button"
                                class="bl-btn ghost sm btn-status <?= (($estado_modo ?? 'todos') === 'todos') ? 'pushed' : '' ?>"
                                data-val=""
                                data-modo="todos">
                            Todos
                        </button>

                        <button type="button"
                                class="bl-btn ghost sm btn-status <?= (($estado_modo ?? '') === 'activo') ? 'pushed' : '' ?>"
                                data-val="activo"
                                data-modo="activo">
                            Activo
                        </button>

                        <button type="button"
                                class="bl-btn ghost sm btn-status <?= (($estado_modo ?? '') === 'inactivo') ? 'pushed' : '' ?>"
                                data-val="desactivado"
                                data-modo="inactivo">
                            Inactivo
                        </button>

                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS KPI: BASES / USES / TOTAL + ACTIVIDAD ── -->
        <?php
            $filtroActivo = !empty($search) || !empty($filtro_tipo) || !empty($filtro_region) || !empty($filtro_provincia) || !empty($filtro_estado);
            $mostrarTodasKpi = $filtroActivo || ($estado_modo ?? 'todos') === 'todos';
        ?>
        <div class="bl-kpi-cards <?= $mostrarTodasKpi ? '' : 'bl-kpi-cards--compact' ?>">
            <div class="bl-kpi-card kpi-teal">
                <div class="bl-kpi-icon"><i class="fas fa-hospital"></i></div>
                <div class="bl-kpi-content">
                    <div class="bl-kpi-label">Bases Operativas</div>
                    <div class="bl-kpi-value"><?= $totalBases ?></div>
                    <span class="bl-kpi-badge green">
                        <i class="fas fa-arrow-up"></i> 
                        <?= $totalRedGlobal > 0 ? round(($totalBases / $totalRedGlobal) * 100) : 0 ?>% de la red
                    </span>
                </div>
            </div>
            <div class="bl-kpi-card kpi-orange">
                <div class="bl-kpi-icon"><i class="fas fa-clock"></i></div>
                <div class="bl-kpi-content">
                    <div class="bl-kpi-label">Uses</div>
                    <div class="bl-kpi-value"><?= $totalUses ?></div>
                    <span class="bl-kpi-badge orange">
                        <i class="fas fa-arrow-up"></i> 
                        <?= $totalRedGlobal > 0 ? round(($totalUses / $totalRedGlobal) * 100) : 0 ?>% de la red
                    </span>
                </div>
            </div>
            <div class="bl-kpi-card kpi-blue">
                <div class="bl-kpi-icon"><i class="fas fa-database"></i></div>
                <div class="bl-kpi-content">
                    <div class="bl-kpi-label">Total</div>
                    <div class="bl-kpi-value"><?= $totalGeneral ?></div>
                    <span class="bl-kpi-badge teal">
                        <i class="fas fa-map-marker-alt"></i> <?= count($regiones) ?> regiones cubiertas
                    </span>
                </div>
            </div>

            <?php if ($mostrarTodasKpi): ?>
                <div class="bl-kpi-card kpi-green">
                    <div class="bl-kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="bl-kpi-content">
                        <div class="bl-kpi-label">Activo</div>
                        <div class="bl-kpi-value"><?= $totalActivas ?></div>
                        <span class="bl-kpi-badge green">
                            <i class="fas fa-percentage"></i> 
                            <?= $totalRedGlobal > 0 ? round(($totalActivas / $totalRedGlobal) * 100) : 0 ?>% del total general
                        </span>
                    </div>
                </div>
                <div class="bl-kpi-card kpi-red">
                    <div class="bl-kpi-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="bl-kpi-content">
                        <div class="bl-kpi-label">Inactivo</div>
                        <div class="bl-kpi-value"><?= $totalDesactivadas ?></div>
                        <span class="bl-kpi-badge orange">
                            <i class="fas fa-tools"></i> 
                            <?= $totalRedGlobal > 0 ? round(($totalDesactivadas / $totalRedGlobal) * 100) : 0 ?>% del total general
                        </span>
                    </div>
                </div>
                <div class="bl-kpi-card kpi-blue">
                    <div class="bl-kpi-icon"><i class="fas fa-list"></i></div>
                    <div class="bl-kpi-content">
                        <div class="bl-kpi-label">Todos</div>
                        <div class="bl-kpi-value"><?= $totalActivas + $totalDesactivadas ?></div>
                        <span class="bl-kpi-badge teal">
                            <i class="fas fa-map-marked-alt"></i> <?= count($regiones) ?> regiones cubiertas
                        </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── LISTA + MAPA ── -->
<div class="bl-view-grid">

    <!-- COLUMNA IZQUIERDA: LISTA -->
    <div>
        <!-- Leyenda Tipos (Sobre la lista) -->
        <div class="bl-legend-group" style="margin-bottom: 10px;">
            <div class="bl-legend-item">
                <div class="bl-legend-color" style="background:#132f57;"></div>
                BASE
            </div>
            <div class="bl-legend-item">
                <div class="bl-legend-color" style="background:#ee6f00;"></div>
                USE
            </div>
        </div>

        <div class="bl-list-container">
            <?php if (!empty($basesMap)): ?>
                <?php foreach ($basesMap as $b): ?>
                    <div class="bl-base-item">
                        <div class="bl-base-name"><?= esc($b->nombre) ?></div>
                        <div class="bl-base-info">
                            <i class="fas fa-map-marker-alt"></i> <?= esc($b->ubicacion) ?> | 
                            <i class="fas fa-globe"></i> <?= esc($b->region) ?>
                        </div>
<div class="bl-base-tags">
    <span class="bl-base-tag" style="background: <?= $b->tipo == 'BASE' ? '#132f57' : '#ee6f00' ?>; color: #fff;">
        <?= esc($b->tipo) ?>
    </span>
    <span class="bl-base-tag" style="background: <?= $b->estado == 'activo' ? '#d4edda' : '#f8d7da' ?>; color: <?= $b->estado == 'activo' ? '#155724' : '#721c24' ?>;">
        <?= esc(ucfirst($b->estado)) ?>
    </span>
</div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="buton-empty">No se encontraron Uses y Bases.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- COLUMNA DERECHA: MAPA -->
    <div>
        <!-- Leyenda Regiones (Sobre el mapa) -->
        <div class="bl-legend-group" style="margin-bottom: 10px; ">
            <?php 
            $colors = [
                    'CENTRO'   => '#90CAF9',
                    'VALLE'    => '#A5D6A7',
                    'RAMAL I'  => '#F48FB1',
                    'RAMAL II' => '#CE93D8',
                    'QUEBRADA' => '#FFAB91',
                    'PUNA'     => '#f1e571'
                ];
                foreach($colors as $name => $color): ?>
                <div class="bl-legend-item">
                    <div class="bl-legend-color" style="background:<?= $color ?>;"></div>
                    <?= $name ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bl-panel bl-map-panel" id="mapaPanel" style="margin-bottom:0;">
            <div class="bl-map-header">
                <span><i class="fas fa-map-marked-alt"></i> UBICACIÓN DE BASES</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleMapa" title="Mostrar/ocultar mapa">
                    <i class="fas fa-eye-slash" id="iconoToggleMapa"></i> 
                    <span id="textoToggleMapa">Ocultar mapa</span>
                </button>
            </div>
            <div class="bl-map-wrap" id="mapaWrap">
                <div id="mapaBases"></div>
            </div>
        </div>
    </div>

</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<!-- Leaflet or other specific libs if needed -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
    var basesParaMapa = [
        <?php foreach ($basesMap as $b): ?>
            <?php
                $coords = trim((string) $b->coordenadas);
                $lat = $lng = null;
                if ($coords !== '' && strpos($coords, ',') !== false) {
                    [$latRaw, $lngRaw] = array_map('trim', explode(',', $coords, 2));
                    if (is_numeric($latRaw) && is_numeric($lngRaw)) {
                        $lat = (float) $latRaw;
                        $lng = (float) $lngRaw;
                    }
                }
            ?>
            <?php if ($lat !== null && $lng !== null): ?>
            {
                lat: <?= $lat ?>,
                lng: <?= $lng ?>,
                nombre: <?= json_encode($b->nombre) ?>,
                tipo: <?= json_encode($b->tipo) ?>,
                ubicacion: <?= json_encode($b->ubicacion) ?>,
                region: <?= json_encode($b->region) ?>,
                estado: <?= json_encode($b->estado) ?>
            },
            <?php endif; ?>
        <?php endforeach; ?>
    ];

    (function () {
        var defaultCenter = [-23.3128, -65.3097]; // Centro de Jujuy
        var map = null;
        var mapaInicializado = false;

        // Mapeo por Región (Fondo):
        var regionColors = {
        'CENTRO': '#90CAF9',   // Azul un poco más claro (era #90CAF9)
        'VALLE': '#A5D6A7',    // Verde un poco más claro (era #A5D6A7)
        'RAMAL I': '#F48FB1',  // Rosa un poco más claro (era #F48FB1)
        'RAMAL II': '#CE93D8', // Violeta un poco más claro (era #CE93D8)
        'QUEBRADA': '#FFAB91', // Naranja/Coral un poco más claro (era #FFAB91)
        'PUNA': '#FFF59D'      // Amarillo un poco más claro (era #FFF59D)
    };

        // Mapeo por Región (Bordes):
        var regionBorderColors = {
        'CENTRO': '#4c6bb8',   // Azul oscuro
        'VALLE': '#4c8f74',    // Verde oscuro
        'RAMAL I': '#b36693',  // Rosa / Carmín oscuro
        'RAMAL II': '#976cc2', // Púrpura oscuro
        'QUEBRADA': '#d88e6b', // Coral / Naranja oscuro
        'PUNA': '#e2b464'      // Amarillo / Ámbar oscuro
    };

        var deptToRegion = {
            'Dr. Manuel Belgrano': 'CENTRO',
            'Palpala': 'VALLE',
            'El Carmen': 'VALLE',
            'San Antonio': 'VALLE',
            'Ledesma': 'RAMAL II',
            'Valle Grande': 'RAMAL II',
            'San Pedro': 'RAMAL I',
            'Santa Barbara': 'RAMAL I',
            'Humahuaca': 'QUEBRADA',
            'Tilcara': 'QUEBRADA',
            'Tumbaya': 'QUEBRADA',
            'Yavi': 'PUNA',
            'Cochinoca': 'PUNA',
            'Rinconada': 'PUNA',
            'Santa Catalina': 'PUNA',
            'Susques': 'QUEBRADA'
        };

        function inicializarMapa() {
            if (mapaInicializado) return;
            mapaInicializado = true;

            // 1. Inicializar mapa
            map = L.map('mapaBases').setView(defaultCenter, 7);

            // 2. Capa base OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap',
                maxZoom: 18
            }).addTo(map);

            // 3. Cargar la capa vectorial GeoJSON de Departamentos de Jujuy
            fetch('<?= base_url('geojson/jujuy_departamentos.json') ?>')
                .then(function(response) {
                    return response.json();
                })
                .then(function(geojsonData) {
                    L.geoJSON(geojsonData, {
                        style: function(feature) {
                            var dep = feature.properties.nam;
                            var region = deptToRegion[dep] || 'CENTRO';
                            var bgColor = regionColors[region] || '#008080';
                            var borderColor = regionBorderColors[region] || '#000000';
                            return {
                                color: borderColor,
                                weight: 2,
                                opacity: 1,
                                fillColor: bgColor,
                                fillOpacity: 0.5
                            };
                        },
                        onEachFeature: function(feature, layer) {
                            var nombreDep = feature.properties.nam || feature.properties.fna || 'Departamento';
                            layer.bindTooltip('<b>' + nombreDep + '</b>', { sticky: true });
                            
                            layer.on({
                                mouseover: function(e) {
                                    e.target.setStyle({ fillOpacity: 0.2, weight: 1 });
                                },
                                mouseout: function(e) {
                                    e.target.setStyle({ fillOpacity: 0.5, weight: 2 });
                                }
                            });
                        }
                    }).addTo(map);
                })
                .catch(function(error) {
                    console.error('Error al cargar la capa GeoJSON:', error);
                });

    // Mapeo de tipo a color (BASE/USE)
    var typeColors = {
        'BASE': '#132f57',
        'USE': '#ee6f00'
    };

    // Mapeo de estado a color
    var statusColors = {
        'activo': '#27ae60',
        'desactivado': '#e74c3c'
    };

    // 4. Cargar los marcadores de las Bases / USEs
    if (basesParaMapa.length > 0) {
        var bounds = [];
        basesParaMapa.forEach(function (b) {
            var borderColor = '#ffffff';

            // Determinar color de relleno basado en estado
           var estadoModo = <?= json_encode($estado_modo ?? 'todos') ?>;

        var fillColor;

        if (estadoModo === 'todos') {

            // TODOS:
            // El color representa el ESTADO.

            if (b.estado === 'activo') {
                fillColor = '#27ae60';
            } else if (b.estado === 'desactivado') {
                fillColor = '#e74c3c';
            } else {
                fillColor = '#ccc';
            }

        } else if (estadoModo === 'activo') {

            // ACTIVO:
            // Todo lo que aparece es verde.

            fillColor = '#27ae60';

        } else if (estadoModo === 'inactivo') {

            // INACTIVO:
            // Todo lo que aparece es rojo.

            fillColor = '#e74c3c';

        } else if (estadoModo === 'ninguno') {

            // NINGUNO:
            // El color representa el TIPO.

            if (b.tipo === 'BASE') {
                fillColor = '#132f57';
            } else if (b.tipo === 'USE') {
                fillColor = '#ee6f00';
            } else {
                fillColor = '#ccc';
            }

        } else {

            fillColor = '#ccc';

        }

var icon = L.divIcon({
    className: '',
    html: '<div style="position:relative; width:24px; height:24px; background:' + fillColor + '; border-radius:50% 50% 50% 0; border:2px solid ' + borderColor + '; transform:rotate(-45deg); display:flex; align-items:center; justify-content:center; box-shadow:0 0 4px rgba(0,0,0,0.4);">' +
          '<div style="width:8px; height:8px; border-radius:50%; transform:rotate(45deg);"></div>' +
          '</div>',
    iconSize: [24, 24],
    iconAnchor: [12, 24]
});
            var marker = L.marker([b.lat, b.lng], { icon: icon }).addTo(map);
            marker.bindPopup(
                '<div class="bl-map-popup">' +
                '<b>' + b.nombre + '</b><br>' +
                b.ubicacion + '<br>' +
                '<span class="bl-map-tag">' + b.tipo + '</span>' +
                '</div>'
            );
            bounds.push([b.lat, b.lng]);
        });
        map.fitBounds(bounds, { padding: [30, 30] });
    }

            setTimeout(function () { if (map) map.invalidateSize(); }, 150);
        }

        var mapaPanel   = document.getElementById('mapaPanel');
        var mapaWrap    = document.getElementById('mapaWrap');
        var btnMapa     = document.getElementById('btnToggleMapa');
        var iconoMapa   = document.getElementById('iconoToggleMapa');
        var textoMapa   = document.getElementById('textoToggleMapa');
        var mapaVisible = true;

        if (mapaVisible) {
            inicializarMapa();
        }

        btnMapa.addEventListener('click', function () {
            mapaVisible = !mapaVisible;
            if (mapaVisible) {
                mapaWrap.classList.remove('is-hidden');
                mapaPanel.classList.remove('is-collapsed');
                iconoMapa.className = 'fas fa-eye-slash';
                textoMapa.textContent = 'Ocultar mapa';
                inicializarMapa();
                setTimeout(function () { if (map) map.invalidateSize(); }, 150);
            } else {
                mapaWrap.classList.add('is-hidden');
                mapaPanel.classList.add('is-collapsed');
                iconoMapa.className = 'fas fa-eye';
                textoMapa.textContent = 'Mostrar mapa';
            }
        });
    })();

    // Lógica de toggle de filtros de búsqueda
    (function () {
        

        // Lógica Botones Estado
        var btnStatus = document.querySelectorAll('.btn-status');
        var inputStatus = document.getElementById('input-estado');
        var inputModo = document.getElementById('input-estado-modo');

        btnStatus.forEach(function(btn) {

            btn.addEventListener('click', function() {

                var modo = btn.getAttribute('data-modo');
                var valor = btn.getAttribute('data-val');

                // ¿El botón ya estaba seleccionado?
                var estabaSeleccionado = btn.classList.contains('pushed');

                // Quitamos selección de todos
                btnStatus.forEach(function(b) {
                    b.classList.remove('pushed');
                });

                if (estabaSeleccionado) {

                    /*
                    * Si se vuelve a pulsar el mismo botón:
                    * ninguno queda seleccionado.
                    */
                    inputStatus.value = '';
                    inputModo.value = 'ninguno';

                } else {

                    /*
                    * Se selecciona el botón pulsado.
                    */
                    btn.classList.add('pushed');

                    inputStatus.value = valor;
                    inputModo.value = modo;
                }

                document.getElementById('searchForm').submit();
            });

        });
    })();
</script>
<?= $this->endSection() ?>