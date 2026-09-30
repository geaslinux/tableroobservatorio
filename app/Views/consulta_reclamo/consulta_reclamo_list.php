<?= $this->extend('layout/main'); ?> //777
<?= $this->section('title') ?> Consultas y Reclamos · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── CONSULTA_RECLAMO_LIST SPECIFIC STYLES (MATCHING ATENCION_LIST) ── */
    .cr-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cr-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cr-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cr-breadcrumb a:hover { color: var(--teal); }
    .cr-breadcrumb strong { color: var(--text-main); font-weight: 600; }

.cr-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .cr-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #d0d7e0;
        flex: 0 1 300px;
        max-width: 260px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 80px;
        box-sizing: border-box;
    }

    .kpi-teal   { border-left: 6px solid #81e6d9; } 
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-green  { border-left: 6px solid #9ae6b4; }
    .kpi-red    { border-left: 6px solid #feb2b2; } 
    .kpi-amber  { border-left: 6px solid #fbd38d; } 
    .kpi-purple { border-left: 6px solid #d6bcfa; } 
    .kpi-navy   { border-left: 6px solid #a0aec0; } 

    .cr-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cr-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cr-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cr-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .cr-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .cr-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .cr-kpi-icon { background: #eef3f8; color: var(--navy); }

    .cr-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .cr-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .cr-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cr-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cr-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cr-panel-title i { color: #fff; font-size: 17px; }
    .cr-panel-body { padding: 18px 20px; }

    .cr-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .cr-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cr-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cr-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cr-filtro-select {
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
    .cr-filtro-select:focus { border-color: var(--teal); }

    .cr-stats-body {
        padding: 25px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 30px;
    }
    .cr-stats-table-wrap {
        grid-area: table;
        max-height: 1000px;
        overflow-y: auto;
    }
    .cr-stats-table { width: 90%; border-collapse: collapse; font-size: 12.5px; }
    .cr-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .cr-stats-table tbody td {
        padding: 7px 5px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .cr-stats-table tbody tr:hover { background: #f8f9fb; }
    .cr-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .cr-chart-box.is-large { height: 500px; }
    .cr-chart-box canvas { height: 100% !important; width: 100% !important; }
    .cr-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .cr-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .cr-stats-header i { color: var(--teal); }
    .cr-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .cr-stats-panel.is-collapsed { margin-bottom: 12px; }
    .cr-stats-panel.is-collapsed .cr-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }

    .cr-stats-body.is-hidden { display: none; }

    .cr-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .cr-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .cr-kpi-badge.teal { background: #e6fffa; color: #319795; }

    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }

    .cr-filtro-group  { min-width: 0; }
    .cr-filtro-select { max-width: 100%; }

    /* Filtro de categorías con selección múltiple */
    .cr-multi { position: relative; }
    .cr-multi-boton {
        width: 240px; display: flex; align-items: center; justify-content: space-between; gap: 8px;
        cursor: pointer; text-align: left; background: var(--white);
    }
    .cr-multi-texto { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-multi-panel {
        display: none; position: absolute; top: calc(100% + 4px); left: 0; z-index: 50;
        width: 340px; max-width: 90vw; max-height: 360px; overflow-y: auto;
        background: #263a5a; color: #fff; border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.25); padding: 8px 0;
    }
    .cr-multi.is-open .cr-multi-panel { display: block; }
    .cr-multi-opcion {
        display: flex; align-items: center; gap: 10px; padding: 7px 14px;
        font-size: 12.5px; cursor: pointer; margin: 0;
    }
    .cr-multi-opcion:hover { background: rgba(255,255,255,.08); }
    .cr-multi-opcion input { width: 16px; height: 16px; accent-color: #3aafbf; flex-shrink: 0; cursor: pointer; }
    .cr-multi-opcion span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cr-multi-todas { color: #7fd8e6; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,.15); margin-bottom: 4px; }
    .cr-multi-acciones { padding: 8px 14px 4px; border-top: 1px solid rgba(255,255,255,.15); margin-top: 4px; text-align: right; }

    /* ── Tarjetas por tipo (diseño de la planilla: franja gris con etiqueta de color) ── */
    .cr-tipos-banda {
        background: #eef1f5; border-radius: 16px;
        padding: 30px 18px 20px; margin-bottom: 18px;
        display: flex; flex-wrap: wrap; justify-content: center; gap: 30px 20px;
    }
    .cr-tipo-card {
        position: relative; background: #fff; border-radius: 12px;
        box-shadow: 0 3px 8px rgba(0,0,0,.12);
        flex: 0 1 200px; min-height: 92px;
        padding: 24px 14px 12px; text-align: center; box-sizing: border-box;
    }
    .cr-tipo-pill {
        position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
        padding: 4px 18px; border-radius: 999px; white-space: nowrap;
        color: #fff; font-weight: 700; font-size: 14px; letter-spacing: .3px;
    }
    .cr-tipo-valor { font-size: 32px; line-height: 1.1; color: #3aafbf; }
    .cr-tipo-sub   { font-size: 11px; color: #718096; margin-top: 4px; }
    .cr-tipo-card.es-total .cr-tipo-valor { color: #263a5a; font-weight: 600; }

    /* Estadísticas: tablas a la izquierda, gráficos a la derecha */
    .cr-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr);
        gap: 22px;
        align-items: start;
    }
    .cr-stats-tablas   { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
    .cr-stats-graficos { display: flex; flex-direction: column; gap: 26px; min-width: 0; }
    .cr-graficos-fila  { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px; }
    .cr-stats-table-wrap { overflow-x: auto; grid-area: auto; }
    .cr-stats-table thead th { white-space: nowrap; }
    .cr-stats-table .num { text-align: right; white-space: nowrap; }
    .cr-stats-table thead th.num { text-align: right; }
    .cr-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .cr-stats-table td.cr-total { background: #3aafbf; color: #fff; font-weight: 700; }
    .cr-punto { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 6px; vertical-align: middle; }
    .cr-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    .cr-paginador {
        display: flex; align-items: center; justify-content: flex-end; gap: 8px;
        padding: 6px 4px 0; font-size: 12px; color: var(--text-muted);
    }
    .cr-paginador button {
        border: 1px solid var(--border); background: var(--white); color: var(--text-main);
        border-radius: 6px; width: 28px; height: 26px; cursor: pointer;
    }
    .cr-paginador button:disabled { opacity: 0.35; cursor: default; }

    .cr-chart-box { display: flex; flex-direction: column; min-width: 0; position: static; height: auto; padding: 0; grid-area: auto; }
    .cr-chart-canvas { position: relative; width: 100%; height: 330px; }
    .cr-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .cr-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 1200px) {
        .cr-graficos-fila { grid-template-columns: 1fr; }
    }
    @media (max-width: 992px) {
        .cr-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .cr-panel-body { padding: 14px 12px; }
        .cr-stats-body { padding: 12px; }
        .cr-filtro-group { flex: 1 1 100%; }
        .cr-filtro-select { width: 100%; }
        .cr-tipo-card { flex: 1 1 100%; }
        .cr-chart-canvas { height: 280px; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $pct = function ($parte, $total) { return $total > 0 ? number_format($parte * 100 / $total, 1, ',', '.') . '%' : '—'; };

    // Colores por tipo (consultas verde, reclamos rojo, como la planilla)
    $coloresTipo = [
        'CONSULTA'     => '#7bc47f',
        'RECLAMO'      => '#e57373',
        'SUGERENCIA'   => '#f0a57a',
        'FELICITACION' => '#3aafbf',
    ];
    foreach ($tipos as $t) {
        if (!isset($coloresTipo[$t])) $coloresTipo[$t] = '#9aa4ad';
    }
    $nombreTipo = function ($t) {
        return ['CONSULTA' => 'Consultas', 'RECLAMO' => 'Reclamos', 'SUGERENCIA' => 'Sugerencias', 'FELICITACION' => 'Felicitaciones'][$t] ?? ucfirst(strtolower($t));
    };
    $abrevTipo = function ($t) {
        return ['CONSULTA' => 'Cons.', 'RECLAMO' => 'Recl.', 'SUGERENCIA' => 'Sug.', 'FELICITACION' => 'Felic.'][$t] ?? $t;
    };

    $porTipoOrdenado = $porTipo;
    arsort($porTipoOrdenado);

    $queryExport = http_build_query(array_filter([
        'ejercicio'    => $filtro_ejercicio,
        'mes'          => $filtro_mes,
        'tipo_llamado' => $filtro_tipo,
        'estado'       => $filtro_estado,
    ], 'strlen') + ($filtro_categoria ? ['categoria' => $filtro_categoria] : []));

    // Texto del botón del filtro de categorías
    $totalOpcionesCat = count($categorias) + 1;   // + "Sin categoría"
    if (empty($filtro_categoria) || count($filtro_categoria) >= $totalOpcionesCat) {
        $textoCategoria = 'Todas';
    } elseif (count($filtro_categoria) === 1) {
        $textoCategoria = 'Sin categoría';
        foreach ($categorias as $cat) {
            if ((int) $cat->categoria_id === $filtro_categoria[0]) $textoCategoria = $cat->nombre;
        }
    } else {
        $textoCategoria = count($filtro_categoria) . ' seleccionadas';
    }
    // Sin filtro = todas marcadas
    $catMarcada = function ($id) use ($filtro_categoria) {
        return empty($filtro_categoria) || in_array((int) $id, $filtro_categoria, true);
    };
?>

<!-- BREADCRUMB -->
<div class="cr-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Consultas y Reclamos — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="cr-panel">
    <div class="cr-panel-header">
        <span class="cr-panel-title">
            <i class="fas fa-headset"></i> CONSULTAS Y RECLAMOS
        </span>
    </div>

    <div class="cr-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="cr-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('consulta_reclamo_export')) . ($queryExport !== '' ? '?' . $queryExport : ''); ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('consulta_reclamo_list')) ?>" id="searchForm">
            <div class="cr-filtro-bar">
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Mes</span>
                    <select name="mes" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= esc($m) ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= esc($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Tipo</span>
                    <select name="tipo_llamado" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= esc($t) ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= esc($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- CATEGORÍA: selección múltiple (como en la planilla) -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">Categoría</span>
                    <div class="cr-multi" id="multiCategoria">
                        <button type="button" class="cr-filtro-select cr-multi-boton" title="<?= esc($textoCategoria) ?>">
                            <span class="cr-multi-texto"><?= esc($textoCategoria) ?></span>
                            <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="cr-multi-panel">
                            <label class="cr-multi-opcion cr-multi-todas">
                                <input type="checkbox" data-todas> <span>Seleccionar todas</span>
                            </label>
                            <label class="cr-multi-opcion">
                                <input type="checkbox" name="categoria[]" value="0" <?= $catMarcada(0) ? 'checked' : '' ?>>
                                <span><em>Sin categoría</em></span>
                            </label>
                            <?php foreach ($categorias as $cat): ?>
                            <label class="cr-multi-opcion" title="<?= esc($cat->nombre) ?>">
                                <input type="checkbox" name="categoria[]" value="<?= $cat->categoria_id ?>" <?= $catMarcada($cat->categoria_id) ? 'checked' : '' ?>>
                                <span><?= esc($cat->nombre) ?></span>
                            </label>
                            <?php endforeach; ?>
                            <div class="cr-multi-acciones">
                                <button type="submit" class="bl-btn teal sm"><i class="fas fa-check"></i> Aplicar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ESTADO — toggle -->
                <div class="cr-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="cr-filtro-label">Estado</span>
                    <select name="estado" class="cr-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="cr-filtro-group">
                    <span class="cr-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('consulta_reclamo_list')); ?>" class="bl-btn ghost"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                        <button type="button" class="bl-btn ghost" id="btnToggle"
                                style="height:36px; padding:0 14px;" title="Mostrar/ocultar Estado">
                            <i class="fas fa-sliders-h" id="iconoToggle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS POR TIPO ── -->
        <div class="cr-tipos-banda">
            <?php foreach ($tipos as $t): ?>
            <div class="cr-tipo-card">
                <span class="cr-tipo-pill" style="background:<?= $coloresTipo[$t] ?>;"><?= esc(mb_strtoupper($nombreTipo($t))) ?></span>
                <div class="cr-tipo-valor"><?= $fmt($porTipo[$t]) ?></div>
                <div class="cr-tipo-sub"><?= $pct($porTipo[$t], $kpi['total']) ?> del total</div>
            </div>
            <?php endforeach; ?>
            <div class="cr-tipo-card es-total">
                <span class="cr-tipo-pill" style="background:#263a5a;">TOTAL</span>
                <div class="cr-tipo-valor"><?= $fmt($kpi['total']) ?></div>
                <div class="cr-tipo-sub">
                    <?= $kpi['meses'] ? 'prom. ' . $fmt($kpi['promedio']) . ' por mes' : 'sin datos' ?>
                    <?php if ($kpi['mes_pico']): ?>
                        · pico <?= esc(ucfirst(strtolower($kpi['mes_pico']['mes']))) ?> <?= esc($kpi['mes_pico']['ejercicio']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS ── -->
        <div class="cr-panel cr-stats-panel" id="statsPanel">
            <div class="cr-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="cr-stats-body">

                    <!-- ════ TABLAS ════ -->
                    <div class="cr-stats-tablas">

                        <!-- 1. Por tipo -->
                        <div class="cr-stats-table-wrap">
                            <div class="cr-chart-title" style="text-align:left;">Cantidad de atenciones por tipo</div>
                            <table class="cr-stats-table">
                                <thead>
                                    <tr><th>Tipo</th><th class="num">Atenciones</th><th class="num">%</th></tr>
                                </thead>
                                <tbody>
                                    <?php if ($kpi['total'] == 0): ?>
                                        <tr><td colspan="3" class="cr-stats-vacio">Sin consultas ni reclamos para los filtros elegidos</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($porTipoOrdenado as $t => $cant): ?>
                                        <tr>
                                            <td><span class="cr-punto" style="background:<?= $coloresTipo[$t] ?>;"></span><?= esc($t) ?></td>
                                            <td class="num"><?= $fmt($cant) ?></td>
                                            <td class="num"><?= $pct($cant, $kpi['total']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                                <?php if ($kpi['total'] > 0): ?>
                                <tfoot>
                                    <tr><td>Total</td><td class="num"><?= $fmt($kpi['total']) ?></td><td class="num">100%</td></tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- 2. Por mes y tipo -->
                        <div class="cr-stats-table-wrap">
                            <div class="cr-chart-title" style="text-align:left;">Atenciones por mes y tipo</div>
                            <table class="cr-stats-table" data-paginar="12">
                                <thead>
                                    <tr>
                                        <th>Ejercicio</th><th>Mes</th>
                                        <?php foreach ($tipos as $t): ?>
                                            <th class="num" title="<?= esc($t) ?>"><?= esc($abrevTipo($t)) ?></th>
                                        <?php endforeach; ?>
                                        <th class="num">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porMes)): ?>
                                        <tr><td colspan="<?= count($tipos) + 3 ?>" class="cr-stats-vacio">Sin atenciones por mes para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach (array_reverse($porMes) as $f): ?>
                                    <tr>
                                        <td><?= esc($f['ejercicio']) ?></td>
                                        <td><?= esc($f['mes']) ?></td>
                                        <?php foreach ($tipos as $t): ?>
                                            <td class="num"><?= $fmt($f['tipos'][$t] ?? 0) ?></td>
                                        <?php endforeach; ?>
                                        <td class="num cr-total"><?= $fmt($f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porMes)): ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <?php foreach ($tipos as $t): ?>
                                            <td class="num"><?= $fmt($porTipo[$t]) ?></td>
                                        <?php endforeach; ?>
                                        <td class="num"><?= $fmt($kpi['total']) ?></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- 3. Por categoría -->
                        <div class="cr-stats-table-wrap">
                            <div class="cr-chart-title" style="text-align:left;">Atenciones por categoría</div>
                            <table class="cr-stats-table" data-paginar="10">
                                <thead>
                                    <tr><th>Categoría</th><th class="num">Atenciones</th><th class="num">%</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porCategoria)): ?>
                                        <tr><td colspan="3" class="cr-stats-vacio">Sin atenciones por categoría para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porCategoria as $cat => $cant): ?>
                                    <tr>
                                        <td><?= esc($cat) ?></td>
                                        <td class="num"><?= $fmt($cant) ?></td>
                                        <td class="num"><?= $pct($cant, $kpi['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porCategoria)): ?>
                                <tfoot>
                                    <tr><td>Total</td><td class="num"><?= $fmt($kpi['total']) ?></td><td class="num">100%</td></tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- ════ GRÁFICOS ════ -->
                    <div class="cr-stats-graficos">
                        <!-- Barras por tipo (como la planilla) -->
                        <div class="cr-chart-box">
                            <div class="cr-chart-title">Atenciones por tipo</div>
                            <div class="cr-chart-canvas" style="height:360px;"><canvas id="chartTipos"></canvas></div>
                        </div>

                        <!-- Evolución mensual apilada por tipo -->
                        <div class="cr-chart-box">
                            <div class="cr-chart-title">Evolución mensual por tipo</div>
                            <div class="cr-chart-canvas"><canvas id="chartMeses"></canvas></div>
                        </div>

                        <div class="cr-graficos-fila">
                            <div class="cr-chart-box">
                                <div class="cr-chart-title">Distribución por categoría</div>
                                <div class="cr-chart-canvas"><canvas id="chartCategoria"></canvas></div>
                            </div>
                            <div class="cr-chart-box">
                                <div class="cr-chart-title">Distribución por ejercicio</div>
                                <div class="cr-chart-canvas"><canvas id="chartDonaEjercicio"></canvas></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // ── Gráficos sin datos: en lugar del gráfico se muestra un aviso ──
    function sinDatos(valores) {
        return !(valores || []).some(function (v) { return Number(v) > 0; });
    }
    function mostrarSinDatos(canvas, mensaje) {
        if (typeof canvas === 'string') canvas = document.getElementById(canvas);
        if (!canvas) return;
        var aviso = document.createElement('div');
        aviso.style.cssText = 'height:100%;min-height:220px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;color:#718096;font-size:13px;font-style:italic;text-align:center;border:1px dashed #d8dee6;border-radius:10px;padding:16px;box-sizing:border-box;';
        aviso.innerHTML = '<i class="fas fa-chart-bar" style="font-size:28px;opacity:.4;font-style:normal;"></i>';
        aviso.appendChild(document.createTextNode(mensaje));
        canvas.replaceWith(aviso);
    }
    // Crea el gráfico solo si la configuración trae algún valor mayor a 0
    function crearGrafico(canvas, mensaje, config) {
        var valores = [];
        ((config.data && config.data.datasets) || []).forEach(function (ds) {
            (ds.data || []).forEach(function (v) { valores.push(v && typeof v === 'object' ? v.y : v); });
        });
        if (sinDatos(valores)) { mostrarSinDatos(canvas, mensaje); return null; }
        return new Chart(canvas, config);
    }

    function formatoNumero(n) { return Number(n).toLocaleString('es-AR'); }
    function formatoEje(v)    { return v >= 1000 ? formatoNumero(v / 1000) + ' mil' : formatoNumero(v); }
    function porcentaje(v, total) {
        var p = total ? v * 100 / total : 0;
        return p.toLocaleString('es-AR', { maximumFractionDigits: 1 }) + '%';
    }
    // Texto blanco sobre fondos oscuros, oscuro sobre los claros
    function colorTexto(hex) {
        var n = parseInt(String(hex).slice(1, 7), 16);
        return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 150 ? '#ffffff' : '#2d3748';
    }

    // ── Mostrar / ocultar el filtro de estado ──
    var colEstado   = document.getElementById('col-estado');
    var btnToggle   = document.getElementById('btnToggle');
    var iconoToggle = document.getElementById('iconoToggle');
    var estadoVisible = !!new URLSearchParams(window.location.search).get('estado');
    function aplicarVisibilidadEstado() {
        if (!colEstado || !iconoToggle || !btnToggle) return;
        colEstado.style.display = estadoVisible ? '' : 'none';
        iconoToggle.className = estadoVisible ? 'fas fa-times' : 'fas fa-sliders-h';
        btnToggle.title = estadoVisible ? 'Ocultar Estado' : 'Mostrar Estado';
    }
    aplicarVisibilidadEstado();
    if (btnToggle) {
        btnToggle.addEventListener('click', function () {
            estadoVisible = !estadoVisible;
            aplicarVisibilidadEstado();
        });
    }

    // ── Filtro de categorías con selección múltiple ──
    (function () {
        var multi = document.getElementById('multiCategoria');
        if (!multi) return;
        var boton  = multi.querySelector('.cr-multi-boton');
        var todas  = multi.querySelector('input[data-todas]');
        var checks = Array.prototype.slice.call(multi.querySelectorAll('input[name="categoria[]"]'));

        function actualizarTodas() {
            var marcadas = checks.filter(function (c) { return c.checked; }).length;
            todas.checked = marcadas === checks.length;
            todas.indeterminate = marcadas > 0 && marcadas < checks.length;
        }
        actualizarTodas();

        boton.addEventListener('click', function () { multi.classList.toggle('is-open'); });
        document.addEventListener('click', function (e) {
            if (!multi.contains(e.target)) multi.classList.remove('is-open');
        });
        todas.addEventListener('change', function () {
            checks.forEach(function (c) { c.checked = todas.checked; });
            actualizarTodas();
        });
        checks.forEach(function (c) { c.addEventListener('change', actualizarTodas); });

        // Si están todas (o ninguna) marcadas no se envía el filtro: equivale a "Todas"
        multi.closest('form').addEventListener('submit', function () {
            var marcadas = checks.filter(function (c) { return c.checked; }).length;
            if (marcadas === 0 || marcadas === checks.length) {
                checks.forEach(function (c) { c.disabled = true; });
            }
        });
    })();

    // ── Paginador de tablas largas ("1 - 12 / 40" con flechas) ──
    document.querySelectorAll('table[data-paginar]').forEach(function (tabla) {
        var porPagina = parseInt(tabla.dataset.paginar, 10);
        var filas = Array.prototype.slice.call(tabla.tBodies[0].rows);
        if (filas.length <= porPagina) return;

        var pagina = 0;
        var nav = document.createElement('div');
        nav.className = 'cr-paginador';
        nav.innerHTML = '<span></span><button type="button" title="Anterior">&lsaquo;</button><button type="button" title="Siguiente">&rsaquo;</button>';
        tabla.parentNode.appendChild(nav);
        var info = nav.querySelector('span');
        var btnPrev = nav.querySelectorAll('button')[0];
        var btnNext = nav.querySelectorAll('button')[1];

        function mostrar() {
            var desde = pagina * porPagina;
            var hasta = Math.min(desde + porPagina, filas.length);
            filas.forEach(function (tr, i) { tr.style.display = (i >= desde && i < hasta) ? '' : 'none'; });
            info.textContent = (desde + 1) + ' - ' + hasta + ' / ' + filas.length;
            btnPrev.disabled = pagina === 0;
            btnNext.disabled = hasta >= filas.length;
        }
        btnPrev.addEventListener('click', function () { pagina--; mostrar(); });
        btnNext.addEventListener('click', function () { pagina++; mostrar(); });
        mostrar();
    });

    // ── Datos desde PHP ──
    var tipos        = <?= json_encode(array_values($tipos)) ?>;
    var porTipo      = <?= json_encode($porTipoOrdenado) ?>;   // ordenado de mayor a menor
    var porMes       = <?= json_encode($porMes) ?>;            // orden cronológico
    var porCategoria = <?= json_encode($porCategoria) ?>;
    var porEjercicio = <?= json_encode($porEjercicio) ?>;
    var coloresTipo  = <?= json_encode($coloresTipo) ?>;
    var todosLosEjercicios = <?= json_encode($todosLosEjercicios) ?>;

    var COLOR_BARRA = '#263a5a';
    var coloresTorta = ['#263a5a', '#3aafbf', '#e98d69', '#9aa4ad', '#8fd3f4', '#f1c27d', '#82e0aa', '#bb8fce', '#f1948a', '#76d7c4', '#edbb99', '#7fb3d5'];

    // Colores por ejercicio: 10 tonos de azul (el más viejo primero), como en el resto del sistema
    var paletaEjercicios = ['#4FB3E6', '#1A5FA8', '#0B2E59', '#8FD3F4', '#2F80C8', '#123F73', '#6EC6EA', '#1E4E8C', '#A9DDF5', '#0F5E9C'];
    function colorParaEjercicio(ej) {
        var i = todosLosEjercicios.indexOf(parseInt(ej, 10));
        return paletaEjercicios[(i < 0 ? 0 : i) % paletaEjercicios.length];
    }

    var variosAnios  = porMes.some(function (m) { return m.ejercicio !== porMes[0].ejercicio; });
    var etiquetasMes = porMes.map(function (m) { return m.mes + (variosAnios ? ' ' + m.ejercicio : ''); });

    var chartsInicializados = false;

    function inicializarCharts() {
        if (chartsInicializados) return;
        chartsInicializados = true;

        // 1. Barras por tipo (azul marino, de mayor a menor)
        var nombresTipo = Object.keys(porTipo);
        crearGrafico(document.getElementById('chartTipos'), 'Sin consultas ni reclamos para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: nombresTipo,
                datasets: [{
                    label: 'ATENCIONES',
                    data: nombresTipo.map(function (t) { return porTipo[t]; }),
                    backgroundColor: COLOR_BARRA,
                    maxBarThickness: 160
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 18 } },
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { boxWidth: 28, boxHeight: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) { return 'Atenciones: ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: {
                        anchor: 'end', align: 'end', offset: 2,
                        color: '#2d3748', font: { size: 11, weight: 'bold' },
                        formatter: function (v) { return v > 0 ? formatoNumero(v) : ''; }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, grace: '10%', ticks: { callback: formatoEje } }
                }
            }
        });

        // 2. Evolución mensual apilada por tipo
        crearGrafico(document.getElementById('chartMeses'), 'Sin atenciones por mes para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: etiquetasMes,
                datasets: tipos.map(function (t) {
                    return {
                        label: t,
                        data: porMes.map(function (m) { return m.tipos[t] || 0; }),
                        backgroundColor: coloresTipo[t],
                        maxBarThickness: 44
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { usePointStyle: true, boxWidth: 10, font: { size: 11 } } },
                    tooltip: { mode: 'index', callbacks: { label: function (ctx) { return ctx.dataset.label + ': ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: { display: false }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { stacked: true, beginAtZero: true, grace: '8%', ticks: { callback: formatoEje } }
                }
            }
        });

        // Plugin: total en el centro de las donas
        var centerTextPlugin = {
            id: 'centerText',
            afterDraw: function (chart) {
                if (chart.config.type !== 'doughnut') return;
                var arco = chart.getDatasetMeta(0).data[0];
                if (!arco) return;
                // Total fijado por la vista (p. ej. dona paginada) o suma de las secciones visibles en la leyenda
                var total = chart.options.plugins.centerTotal;
                if (typeof total !== 'number') {
                    total = chart.data.datasets[0].data.reduce(function (a, v, i) {
                        return a + (chart.getDataVisibility(i) ? Number(v) || 0 : 0);
                    }, 0);
                }
                // Letra proporcional al hueco: se ve igual en donas grandes y chicas
                var tamNumero = Math.round(Math.max(12, Math.min(24, arco.innerRadius * 0.34)));
                var tamTitulo = Math.round(Math.max(10, Math.min(16, arco.innerRadius * 0.22)));
                var ctx = chart.ctx;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = 'bold ' + tamTitulo + 'px sans-serif';
                ctx.fillStyle = '#555555';
                ctx.fillText('TOTAL', arco.x, arco.y - tamNumero * 0.55);
                ctx.font = 'bold ' + tamNumero + 'px sans-serif';
                ctx.fillStyle = '#222222';
                ctx.fillText(total.toLocaleString('es-AR'), arco.x, arco.y + tamTitulo * 0.65);
                ctx.restore();
            }
        };

        // Opciones comunes de las donas
        function opcionesTorta(valores) {
            var total = valores.reduce(function (a, b) { return a + b; }, 0);
            return {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: window.matchMedia('(max-width: 600px)').matches ? 'bottom' : 'right',
                        labels: { usePointStyle: true, boxWidth: 10, font: { size: 11 } }
                    },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.label + ': ' + formatoNumero(ctx.parsed) + ' (' + porcentaje(ctx.parsed, total) + ')'; } } },
                    datalabels: {
                        color: function (ctx) { return colorTexto(ctx.dataset.backgroundColor[ctx.dataIndex]); },
                        font: { size: 11, weight: 'bold' },
                        formatter: function (v) { return total && v * 100 / total >= 4 ? porcentaje(v, total) : ''; }
                    }
                }
            };
        }

        // 3. Dona por categoría
        var cats      = Object.keys(porCategoria);
        var valoresCat = cats.map(function (c) { return porCategoria[c]; });
        crearGrafico(document.getElementById('chartCategoria'), 'Sin atenciones por categoría para los filtros elegidos', {
            type: 'doughnut',
            plugins: [centerTextPlugin],
            data: {
                labels: cats,
                datasets: [{
                    data: valoresCat,
                    backgroundColor: cats.map(function (c, i) { return coloresTorta[i % coloresTorta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: cats.length > 1 ? 2 : 0
                }]
            },
            options: opcionesTorta(valoresCat)
        });

        // 4. Dona por ejercicio
        var ejercicios = Object.keys(porEjercicio);
        var valoresEj  = ejercicios.map(function (e) { return porEjercicio[e]; });
        crearGrafico(document.getElementById('chartDonaEjercicio'), 'Sin atenciones por ejercicio para los filtros elegidos', {
            type: 'doughnut',
            plugins: [centerTextPlugin],
            data: {
                labels: ejercicios,
                datasets: [{
                    data: valoresEj,
                    backgroundColor: ejercicios.map(colorParaEjercicio),
                    borderColor: '#ffffff',
                    borderWidth: ejercicios.length > 1 ? 3 : 0,
                    hoverOffset: ejercicios.length > 1 ? 8 : 0
                }]
            },
            options: opcionesTorta(valoresEj)
        });
    }

    // ── Mostrar / ocultar estadísticas ──
    var statsPanel   = document.getElementById('statsPanel');
    var statsBody    = document.getElementById('statsBody');
    var btnStats     = document.getElementById('btnToggleStats');
    var iconoStats   = document.getElementById('iconoToggleStats');
    var textoStats   = document.getElementById('textoToggleStats');
    var statsVisible = true;

    if (btnStats) {
        btnStats.addEventListener('click', function () {
            statsVisible = !statsVisible;
            statsBody.classList.toggle('is-hidden', !statsVisible);
            statsPanel.classList.toggle('is-collapsed', !statsVisible);
            iconoStats.className   = statsVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
            textoStats.textContent = statsVisible ? 'Ocultar estadísticas' : 'Mostrar estadísticas';
            if (statsVisible) inicializarCharts();
        });
    }

    inicializarCharts();
});
</script>
<?= $this->endSection() ?>