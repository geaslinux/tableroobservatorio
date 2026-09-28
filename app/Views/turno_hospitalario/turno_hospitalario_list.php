<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Turnos Hospitalarios · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── TURNOS_HOSPITALARIOS SPECIFIC STYLES ── */
    .th-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .th-breadcrumb i { color: var(--teal); font-size: 13px; }
    .th-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .th-breadcrumb a:hover { color: var(--teal); }
    .th-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .th-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .th-kpi-card {
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
    .th-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
        align-items: center;
    }
    .kpi-teal   { border-left: 6px solid #81e6d9; } 
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-green  { border-left: 6px solid #9ae6b4; }
    .kpi-red    { border-left: 6px solid #feb2b2; } 
    .kpi-amber  { border-left: 6px solid #fbd38d; } 
    .kpi-purple { border-left: 6px solid #d6bcfa; } 
    .kpi-navy   { border-left: 6px solid #a0aec0; } 

    .th-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .th-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .th-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .th-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .th-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .th-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .th-kpi-icon { background: #eef3f8; color: var(--navy); }

    .th-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .th-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .th-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .th-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .th-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .th-panel-title i { color: #fff; font-size: 17px; }
    .th-panel-body { padding: 18px 20px; }

    .th-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .th-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .th-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .th-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .th-filtro-select {
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
    .th-filtro-select:focus { border-color: var(--teal); }

    .th-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .th-stats-table-wrap {
        grid-area: table;
        max-height: 1000px;
        overflow-y: auto;
    }
    .th-stats-table { width: 90%; border-collapse: collapse; font-size: 12.5px; }
    .th-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .th-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .th-stats-table tbody tr:hover { background: #f8f9fb; }
    .th-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .th-chart-box.is-large { height: 500px; }
    .th-chart-box canvas { height: 100% !important; width: 100% !important; }
    .th-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .th-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .th-stats-header i { color: var(--teal); }
    .th-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .th-stats-panel.is-collapsed { margin-bottom: 12px; }
    .th-stats-panel.is-collapsed .th-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }

    .th-stats-body.is-hidden { display: none; }

    .th-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .th-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .th-kpi-badge.teal { background: #e6fffa; color: #319795; }

    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }

    .th-filtro-group  { min-width: 0; }
    .th-filtro-select { max-width: 100%; }
    .th-filtro-efector { width: 260px; }

    /* ── Tarjetas (diseño de la planilla: franja gris con etiqueta de color) ── */
    .th-banda {
        background: #eef1f5; border-radius: 16px;
        padding: 30px 18px 20px; margin-bottom: 18px;
        display: flex; flex-wrap: wrap; justify-content: center; gap: 30px 20px;
    }
    .th-card {
        position: relative; background: #fff; border-radius: 12px;
        box-shadow: 0 3px 8px rgba(0,0,0,.12);
        flex: 0 1 200px; min-height: 92px;
        padding: 24px 14px 12px; text-align: center; box-sizing: border-box;
    }
    .th-pill {
        position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
        padding: 4px 18px; border-radius: 999px; white-space: nowrap;
        color: #fff; font-weight: 700; font-size: 14px; letter-spacing: .3px;
    }
    .th-valor { font-size: 32px; line-height: 1.1; color: #3aafbf; }
    .th-sub   { font-size: 11px; color: #718096; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .th-card.es-principal .th-valor { color: #263a5a; }
    .th-card.es-total { background: #263a5a; }
    .th-card.es-total .th-valor { color: #fff; font-weight: 600; }
    .th-card.es-total .th-sub   { color: #cbd5e0; }

    /* Estadísticas: tablas a la izquierda, gráficos a la derecha */
    .th-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.3fr);
        gap: 22px;
        align-items: start;
    }
    .th-stats-tablas   { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
    .th-stats-graficos { display: flex; flex-direction: column; gap: 26px; min-width: 0; }
    .th-stats-table-wrap { overflow-x: auto; grid-area: auto; }
    .th-stats-table thead th { background: #3aafbf; color: #fff; white-space: normal; vertical-align: bottom; font-size: 11px; }
    .th-stats-table.es-pct thead th { background: #263a5a; }
    .th-stats-table .num { text-align: right; white-space: nowrap; }
    .th-stats-table thead th.num { text-align: right; }
    .th-stats-table td.th-efector { min-width: 150px; }
    .th-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .th-stats-table td.th-total { font-weight: 700; }
    .th-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    .th-paginador {
        display: flex; align-items: center; justify-content: flex-end; gap: 8px;
        padding: 6px 4px 0; font-size: 12px; color: var(--text-muted);
    }
    .th-paginador button {
        border: 1px solid var(--border); background: var(--white); color: var(--text-main);
        border-radius: 6px; width: 28px; height: 26px; cursor: pointer;
    }
    .th-paginador button:disabled { opacity: 0.35; cursor: default; }

    .th-chart-box { display: flex; flex-direction: column; min-width: 0; position: static; height: auto; padding: 0; grid-area: auto; }
    .th-chart-canvas { position: relative; width: 100%; height: 330px; }
    .th-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .th-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 992px) {
        .th-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .th-panel-body { padding: 14px 12px; }
        .th-stats-body { padding: 12px; }
        .th-filtro-group { flex: 1 1 100%; }
        .th-filtro-select, .th-filtro-efector { width: 100%; }
        .th-card { flex: 1 1 100%; }
        .th-chart-canvas { height: 280px; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $pct = function ($parte, $total) { return $total > 0 ? number_format($parte * 100 / $total, 0, ',', '.') . '%' : '—'; };
    $pctKpi = function ($p) { return number_format($p, 1, ',', '.') . '%'; };

    $queryExport = http_build_query(array_filter([
        'ejercicio' => $filtro_ejercicio,
        'mes'       => $filtro_mes,
        'efector'   => $filtro_efector,
        'region'    => $filtro_region,
        'estado'    => $filtro_estado,
    ], 'strlen'));
?>

<!-- BREADCRUMB -->
<div class="th-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Gestión de Especialidades — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="th-panel">
    <div class="th-panel-header">
        <span class="th-panel-title">
            <i class="fas fa-notes-medical"></i> GESTIÓN DE ESPECIALIDADES
        </span>
    </div>

    <div class="th-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="th-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('turno_hospitalario_export')) . ($queryExport !== '' ? '?' . $queryExport : ''); ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('turno_hospitalario_list')) ?>" id="searchForm">
            <div class="th-filtro-bar">
                <div class="th-filtro-group">
                    <span class="th-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="th-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="th-filtro-group">
                    <span class="th-filtro-label">Mes</span>
                    <select name="mes" class="th-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= esc($m) ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= esc($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="th-filtro-group">
                    <span class="th-filtro-label">Región</span>
                    <select name="region" class="th-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="th-filtro-group">
                    <span class="th-filtro-label">Efector</span>
                    <select name="efector" class="th-filtro-select th-filtro-efector">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $e): ?>
                            <option value="<?= $e->efector_id ?>" <?= $filtro_efector == $e->efector_id ? 'selected' : '' ?>><?= esc($e->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ESTADO — toggle -->
                <div class="th-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="th-filtro-label">Estado</span>
                    <select name="estado" class="th-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="th-filtro-group">
                    <span class="th-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('turno_hospitalario_list')); ?>" class="bl-btn ghost"
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

        <!-- ── TARJETAS ── -->
        <div class="th-banda">
            <div class="th-card es-principal">
                <span class="th-pill" style="background:#263a5a;">ATENDIDOS</span>
                <div class="th-valor"><?= $fmt($kpi['atendidos']) ?></div>
                <div class="th-sub"><?= $pctKpi($kpi['pct_atendidos']) ?> de los otorgados</div>
            </div>
            <div class="th-card">
                <span class="th-pill" style="background:#3aafbf;">AUSENTES</span>
                <div class="th-valor"><?= $fmt($kpi['ausentes']) ?></div>
                <div class="th-sub"><?= $pctKpi($kpi['pct_ausentes']) ?> de los otorgados</div>
            </div>
            <div class="th-card">
                <span class="th-pill" style="background:#3aafbf;">CANCELADOS</span>
                <div class="th-valor"><?= $fmt($kpi['cancelados']) ?></div>
                <div class="th-sub"><?= $pctKpi($kpi['pct_cancelados']) ?> de los otorgados</div>
            </div>
            <div class="th-card">
                <span class="th-pill" style="background:#3aafbf;">SIN CODIFICAR</span>
                <div class="th-valor"><?= $fmt($kpi['sin_codificar']) ?></div>
                <div class="th-sub"><?= $pctKpi($kpi['pct_sin_codificar']) ?> de los otorgados</div>
            </div>
            <div class="th-card es-total">
                <span class="th-pill" style="background:#3aafbf;">TOTAL OTORGADOS</span>
                <div class="th-valor"><?= $fmt($kpi['total']) ?></div>
                <div class="th-sub" title="<?= esc($kpi['efector_top'] ?? '') ?>">
                    <?= $kpi['efectores'] ? $fmt($kpi['efectores']) . ' efector(es) · más turnos: ' . esc($kpi['efector_top']) : 'sin datos' ?>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS ── -->
        <div class="th-panel th-stats-panel" id="statsPanel">
            <div class="th-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="th-stats-body">

                    <!-- ════ TABLAS ════ -->
                    <div class="th-stats-tablas">

                        <!-- 1. Cantidades por mes y efector -->
                        <div class="th-stats-table-wrap">
                            <div class="th-chart-title" style="text-align:left;">Turnos por mes y efector</div>
                            <table class="th-stats-table" data-paginar="10">
                                <thead>
                                    <tr>
                                        <th>Mes</th><th>Efector</th>
                                        <th class="num">Turnos atendidos</th><th class="num">Ausentes</th>
                                        <th class="num">Cancelados</th><th class="num">Sin codificar</th>
                                        <th class="num">Total otorgados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porFila)): ?>
                                        <tr><td colspan="7" class="th-stats-vacio">Sin turnos para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porFila as $f): ?>
                                    <tr>
                                        <td><?= esc($f['mes']) ?><?= count($todosLosEjercicios) > 1 && $filtro_ejercicio === '' ? ' ' . esc($f['ejercicio']) : '' ?></td>
                                        <td class="th-efector"><?= esc($f['efector']) ?></td>
                                        <td class="num"><?= $fmt($f['atendidos']) ?></td>
                                        <td class="num"><?= $fmt($f['ausentes']) ?></td>
                                        <td class="num"><?= $fmt($f['cancelados']) ?></td>
                                        <td class="num"><?= $fmt($f['sin_codificar']) ?></td>
                                        <td class="num th-total"><?= $fmt($f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porFila)): ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td class="num"><?= $fmt($kpi['atendidos']) ?></td>
                                        <td class="num"><?= $fmt($kpi['ausentes']) ?></td>
                                        <td class="num"><?= $fmt($kpi['cancelados']) ?></td>
                                        <td class="num"><?= $fmt($kpi['sin_codificar']) ?></td>
                                        <td class="num"><?= $fmt($kpi['total']) ?></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- 2. Porcentajes por mes y efector -->
                        <div class="th-stats-table-wrap">
                            <div class="th-chart-title" style="text-align:left;">% sobre los turnos otorgados</div>
                            <table class="th-stats-table es-pct" data-paginar="10">
                                <thead>
                                    <tr>
                                        <th>Mes</th><th>Efector</th>
                                        <th class="num">% de atendidos</th><th class="num">% de ausentes</th>
                                        <th class="num">% cancelados</th><th class="num">% sin codificar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porFila)): ?>
                                        <tr><td colspan="6" class="th-stats-vacio">Sin turnos para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porFila as $f): ?>
                                        <?php $pa = $f['total'] > 0 ? $f['atendidos'] / $f['total'] : 0; ?>
                                    <tr>
                                        <td><?= esc($f['mes']) ?><?= count($todosLosEjercicios) > 1 && $filtro_ejercicio === '' ? ' ' . esc($f['ejercicio']) : '' ?></td>
                                        <td class="th-efector"><?= esc($f['efector']) ?></td>
                                        <!-- Cuanto más alto el % de atendidos, más intenso el turquesa -->
                                        <td class="num" style="background: rgba(58,175,191,<?= number_format(0.1 + $pa * 0.7, 2, '.', '') ?>);"><?= $pct($f['atendidos'], $f['total']) ?></td>
                                        <td class="num"><?= $pct($f['ausentes'], $f['total']) ?></td>
                                        <td class="num"><?= $pct($f['cancelados'], $f['total']) ?></td>
                                        <td class="num"><?= $pct($f['sin_codificar'], $f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porFila)): ?>
                                <tfoot>
                                    <tr>
                                        <td colspan="2">Total</td>
                                        <td class="num"><?= $pct($kpi['atendidos'], $kpi['total']) ?></td>
                                        <td class="num"><?= $pct($kpi['ausentes'], $kpi['total']) ?></td>
                                        <td class="num"><?= $pct($kpi['cancelados'], $kpi['total']) ?></td>
                                        <td class="num"><?= $pct($kpi['sin_codificar'], $kpi['total']) ?></td>
                                    </tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- ════ GRÁFICOS ════ -->
                    <div class="th-stats-graficos">
                        <!-- Dona: total otorgados por efector -->
                        <div class="th-chart-box">
                            <div class="th-chart-title">Total de turnos otorgados por efector</div>
                            <div class="th-chart-canvas" style="height:380px;"><canvas id="chartEfector"></canvas></div>
                        </div>

                        <!-- Barras agrupadas por mes -->
                        <div class="th-chart-box">
                            <div class="th-chart-title">Turnos por mes</div>
                            <div class="th-chart-canvas"><canvas id="chartMeses"></canvas></div>
                        </div>

                        <!-- Barras horizontales 100%: composición de cada mes -->
                        <div class="th-chart-box">
                            <div class="th-chart-title">Composición de los turnos otorgados por mes (%)</div>
                            <div class="th-chart-canvas" id="contenedorPct"><canvas id="chartPct"></canvas></div>
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

    // ── Paginador de tablas ("1 - 10 / 26" con flechas) ──
    document.querySelectorAll('table[data-paginar]').forEach(function (tabla) {
        var porPagina = parseInt(tabla.dataset.paginar, 10);
        var filas = Array.prototype.slice.call(tabla.tBodies[0].rows);
        if (filas.length <= porPagina) return;

        var pagina = 0;
        var nav = document.createElement('div');
        nav.className = 'th-paginador';
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
    var porMes     = <?= json_encode($porMes) ?>;       // orden cronológico
    var porEfector = <?= json_encode($porEfector) ?>;   // de mayor a menor

    // Colores de la planilla de referencia
    var SERIES = [
        { clave: 'atendidos',     nombre: 'TURNOS ATENDIDOS', color: '#263a5a' },
        { clave: 'ausentes',      nombre: 'AUSENTES',         color: '#e98d69' },
        { clave: 'cancelados',    nombre: 'CANCELADOS',       color: '#2a9d8f' },
        { clave: 'sin_codificar', nombre: 'SIN CODIFICAR',    color: '#9aa4ad' }
    ];
    var COLOR_TOTAL  = '#3aafbf';
    var coloresTorta = ['#263a5a', '#f0a57a', '#e98d69', '#9aa4ad', '#3aafbf', '#8fd3f4', '#2a9d8f', '#f1c27d', '#bb8fce', '#7fb3d5'];

    var variosAnios  = porMes.some(function (m) { return m.ejercicio !== porMes[0].ejercicio; });
    var etiquetasMes = porMes.map(function (m) { return m.mes + (variosAnios ? ' ' + m.ejercicio : ''); });

    var chartsInicializados = false;

    function inicializarCharts() {
        if (chartsInicializados) return;
        chartsInicializados = true;

        // 1. Dona por efector: los 8 con más turnos y el resto agrupado en "OTROS"
        var nombres = Object.keys(porEfector);
        var MAX_PORCIONES = 8;
        var etiquetasEf = nombres.slice(0, MAX_PORCIONES);
        var valoresEf   = etiquetasEf.map(function (n) { return porEfector[n].total; });
        if (nombres.length > MAX_PORCIONES) {
            etiquetasEf.push('OTROS (' + (nombres.length - MAX_PORCIONES) + ' efectores)');
            valoresEf.push(nombres.slice(MAX_PORCIONES).reduce(function (s, n) { return s + porEfector[n].total; }, 0));
        }
        var totalEf = valoresEf.reduce(function (a, b) { return a + b; }, 0);
        var coloresEf = etiquetasEf.map(function (n, i) {
            return i === MAX_PORCIONES ? '#cbd5e0' : coloresTorta[i % coloresTorta.length];
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

        crearGrafico(document.getElementById('chartEfector'), 'Sin turnos otorgados por efector para los filtros elegidos', {
            type: 'doughnut',
            plugins: [centerTextPlugin],
            data: {
                labels: etiquetasEf,
                datasets: [{
                    data: valoresEf,
                    backgroundColor: coloresEf,
                    borderColor: '#ffffff',
                    borderWidth: etiquetasEf.length > 1 ? 3 : 0,
                    hoverOffset: etiquetasEf.length > 1 ? 8 : 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                layout: { padding: 8 },
                plugins: {
                    legend: {
                        position: window.matchMedia('(max-width: 600px)').matches ? 'bottom' : 'right',
                        labels: {
                            usePointStyle: true, boxWidth: 10, font: { size: 11 },
                            // Nombres largos recortados en la leyenda (el tooltip muestra el completo)
                            generateLabels: function (chart) {
                                var items = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart);
                                items.forEach(function (it) { if (it.text.length > 32) it.text = it.text.slice(0, 30) + '…'; });
                                return items;
                            }
                        }
                    },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.label + ': ' + formatoNumero(ctx.parsed) + ' (' + porcentaje(ctx.parsed, totalEf) + ')'; } } },
                    datalabels: {
                        color: function (ctx) { return colorTexto(ctx.dataset.backgroundColor[ctx.dataIndex]); },
                        font: { size: 11, weight: 'bold' },
                        formatter: function (v) { return totalEf && v * 100 / totalEf >= 4 ? porcentaje(v, totalEf) : ''; }
                    }
                }
            }
        });

        // 2. Barras agrupadas por mes: total otorgados + cada estado
        crearGrafico(document.getElementById('chartMeses'), 'Sin turnos por mes para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: etiquetasMes,
                datasets: [{ label: 'TOTAL OTORGADOS', data: porMes.map(function (m) { return m.total; }), backgroundColor: COLOR_TOTAL, maxBarThickness: 34 }]
                    .concat(SERIES.map(function (s) {
                        return { label: s.nombre, data: porMes.map(function (m) { return m[s.clave]; }), backgroundColor: s.color, maxBarThickness: 34 };
                    }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { boxWidth: 22, boxHeight: 10, font: { size: 11 } } },
                    tooltip: { mode: 'index', callbacks: { label: function (ctx) { return ctx.dataset.label + ': ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grace: '8%', ticks: { callback: formatoEje } }
                }
            }
        });

        // 3. Barras horizontales 100%: qué parte de lo otorgado fue atendido, ausente, etc.
        var contenedorPct = document.getElementById('contenedorPct');
        if (contenedorPct) contenedorPct.style.height = Math.max(200, 90 + porMes.length * 38) + 'px';
        crearGrafico(document.getElementById('chartPct'), 'Sin turnos por mes para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: etiquetasMes,
                datasets: SERIES.map(function (s) {
                    return {
                        label: '% ' + s.nombre,
                        data: porMes.map(function (m) { return m.total ? m[s.clave] * 100 / m.total : 0; }),
                        cantidades: porMes.map(function (m) { return m[s.clave]; }),
                        backgroundColor: s.color,
                        maxBarThickness: 34
                    };
                })
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { boxWidth: 22, boxHeight: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) {
                        return ctx.dataset.label + ': ' + porcentaje(ctx.parsed.x, 100) + ' (' + formatoNumero(ctx.dataset.cantidades[ctx.dataIndex]) + ')';
                    } } },
                    datalabels: {
                        color: function (ctx) { return colorTexto(ctx.dataset.backgroundColor); },
                        font: { size: 10, weight: 'bold' },
                        formatter: function (v) { return v >= 6 ? porcentaje(v, 100) : ''; }
                    }
                },
                scales: {
                    x: { stacked: true, min: 0, max: 100, ticks: { callback: function (v) { return v + '%'; } } },
                    y: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
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