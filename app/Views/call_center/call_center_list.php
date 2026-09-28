<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Call Center · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── CALL_CENTER SPECIFIC STYLES ── */
    .cc-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .cc-breadcrumb i { color: var(--teal); font-size: 13px; }
    .cc-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .cc-breadcrumb a:hover { color: var(--teal); }
    .cc-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .cc-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .cc-kpi-card {
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
    .cc-kpi-content {
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

    .cc-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .cc-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .cc-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .cc-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .cc-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .cc-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .cc-kpi-icon { background: #eef3f8; color: var(--navy); }

    .cc-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .cc-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .cc-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .cc-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .cc-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .cc-panel-title i { color: #fff; font-size: 17px; }
    .cc-panel-body { padding: 18px 20px; }

    .cc-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .cc-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .cc-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .cc-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .cc-filtro-select {
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
    .cc-filtro-select:focus { border-color: var(--teal); }

    .cc-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .cc-stats-table-wrap {
        grid-area: table;
        max-height: 1000px;
        overflow-y: auto;
    }
    .cc-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .cc-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .cc-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .cc-stats-table tbody tr:hover { background: #f8f9fb; }
    .cc-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .cc-chart-box.is-large { height: 500px; }
    .cc-chart-box canvas { height: 100% !important; width: 100% !important; }
    .cc-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .cc-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .cc-stats-header i { color: var(--teal); }
    .cc-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .cc-stats-panel.is-collapsed { margin-bottom: 12px; }
    .cc-stats-panel.is-collapsed .cc-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }

    .cc-stats-body.is-hidden { display: none; }

    .cc-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .cc-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .cc-kpi-badge.teal { background: #e6fffa; color: #319795; }

    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }

    /* ── Formato de los paneles de Hospitalario ── */
    .cc-kpi-card  { flex: 0 1 240px; max-width: 300px; height: auto; min-height: 80px; }
    .cc-kpi-icon  { flex-shrink: 0; }
    .cc-kpi-label { font-size: 13px; text-align: center; }
    .cc-kpi-value { font-size: 23px; }
    .cc-kpi-sub   {
        font-size: 11px; color: #718096; text-align: center;
        max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .kpi-green .cc-kpi-icon { background: #f0fff4; color: #38a169; }
    .cc-filtro-group  { min-width: 0; }
    .cc-filtro-select { max-width: 100%; }
    .cc-filtro-fecha  { width: 150px; }

    /* Estadísticas: tablas a la izquierda, gráficos a la derecha (diseño de la planilla de referencia) */
    .cc-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr);
        gap: 22px;
        align-items: start;
    }
    .cc-stats-tablas   { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
    .cc-stats-graficos { display: flex; flex-direction: column; gap: 26px; min-width: 0; }
    .cc-graficos-fila  { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 18px; }
    .cc-stats-table-wrap { overflow-x: auto; grid-area: auto; }
    .cc-stats-table thead th { white-space: nowrap; }
    .cc-stats-table .num { text-align: right; white-space: nowrap; }
    .cc-stats-table thead th.num { text-align: right; }
    .cc-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .cc-stats-table td.cc-total-mes { background: #3aafbf; color: #fff; font-weight: 700; }
    .cc-stats-table td.cc-total-dia { background: #f4c7b8; color: #2d3748; font-weight: 700; }
    .cc-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    /* Paginador de las tablas por día */
    .cc-paginador {
        display: flex; align-items: center; justify-content: flex-end; gap: 8px;
        padding: 6px 4px 0; font-size: 12px; color: var(--text-muted);
    }
    .cc-paginador button {
        border: 1px solid var(--border); background: var(--white); color: var(--text-main);
        border-radius: 6px; width: 28px; height: 26px; cursor: pointer;
    }
    .cc-paginador button:disabled { opacity: 0.35; cursor: default; }

    .cc-chart-box { display: flex; flex-direction: column; min-width: 0; position: static; height: auto; padding: 0; grid-area: auto; }
    .cc-chart-canvas { position: relative; width: 100%; height: 330px; }
    .cc-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .cc-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    @media (max-width: 1200px) {
        .cc-graficos-fila { grid-template-columns: 1fr; }
    }
    @media (max-width: 992px) {
        .cc-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .cc-panel-body { padding: 14px 12px; }
        .cc-stats-body { padding: 12px; }
        .cc-filtro-group { flex: 1 1 100%; }
        .cc-filtro-select, .cc-filtro-fecha { width: 100%; }
        .cc-kpi-card { flex: 1 1 100%; max-width: none; }
        .cc-chart-canvas { height: 280px; }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $pct = function ($parte, $total) { return $total > 0 ? number_format($parte * 100 / $total, 1, ',', '.') . '%' : '—'; };
    $fechaCorta = function ($f) {
        if (empty($f)) return 'Sin fecha';
        $m = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
        $t = strtotime($f);
        return date('j', $t) . ' ' . $m[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
    };
    $queryExport = http_build_query(array_filter([
        'ejercicio' => $filtro_ejercicio,
        'mes'       => $filtro_mes,
        'desde'     => $filtro_desde,
        'hasta'     => $filtro_hasta,
        'estado'    => $filtro_estado,
    ], 'strlen'));
?>

<!-- BREADCRUMB -->
<div class="cc-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('paciente_views')); ?>">Gestión Paciente</a> ›
    <strong>Llamadas — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="cc-panel">
    <div class="cc-panel-header">
        <span class="cc-panel-title">
            <i class="fas fa-headset"></i> CALL CENTER 0800 - LLAMADAS
        </span>
    </div>

    <div class="cc-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="cc-toolbar">
            <a href="<?= base_url(route_to('paciente_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('call_center_export')) . ($queryExport !== '' ? '?' . $queryExport : ''); ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('call_center_list')) ?>" class="cc-filters-form">
            <div class="cc-filtro-bar">
                <div class="cc-filtro-group">
                    <span class="cc-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="cc-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cc-filtro-group">
                    <span class="cc-filtro-label">Mes</span>
                    <select name="mes" class="cc-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= esc($m) ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= esc($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- PERÍODO (fechas) -->
                <div class="cc-filtro-group">
                    <span class="cc-filtro-label">Desde</span>
                    <input type="date" name="desde" class="cc-filtro-select cc-filtro-fecha" value="<?= esc($filtro_desde) ?>">
                </div>
                <div class="cc-filtro-group">
                    <span class="cc-filtro-label">Hasta</span>
                    <input type="date" name="hasta" class="cc-filtro-select cc-filtro-fecha" value="<?= esc($filtro_hasta) ?>">
                </div>

                <!-- ESTADO — toggle -->
                <div class="cc-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="cc-filtro-label">Estado</span>
                    <select name="estado" class="cc-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="cc-filtro-group">
                    <span class="cc-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('call_center_list')); ?>" class="bl-btn ghost"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                        <button type="button" class="bl-btn ghost" id="btnToggle"
                                style="height:36px; padding:0 14px;"
                                title="Mostrar/ocultar Estado">
                            <i class="fas fa-sliders-h" id="iconoToggle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── TARJETAS KPI ── -->
        <div class="cc-kpi-cards">
            <div class="cc-kpi-card kpi-navy">
                <div class="cc-kpi-icon"><i class="fas fa-phone-alt"></i></div>
                <div class="cc-kpi-content">
                    <div class="cc-kpi-label">Total llamadas</div>
                    <div class="cc-kpi-value"><?= $fmt($kpi['total']) ?></div>
                    <div class="cc-kpi-sub">en <?= $fmt($kpi['dias']) ?> día(s) con datos</div>
                </div>
            </div>
            <div class="cc-kpi-card kpi-teal">
                <div class="cc-kpi-icon"><i class="fas fa-user-check"></i></div>
                <div class="cc-kpi-content">
                    <div class="cc-kpi-label">Atendidas</div>
                    <div class="cc-kpi-value"><?= $fmt($kpi['atendidos']) ?></div>
                    <div class="cc-kpi-sub"><?= number_format($kpi['pct_atendidos'], 1, ',', '.') ?>% del total</div>
                </div>
            </div>
            <div class="cc-kpi-card kpi-red">
                <div class="cc-kpi-icon"><i class="fas fa-phone-slash"></i></div>
                <div class="cc-kpi-content">
                    <div class="cc-kpi-label">Abandonadas</div>
                    <div class="cc-kpi-value"><?= $fmt($kpi['abandonadas']) ?></div>
                    <div class="cc-kpi-sub"><?= number_format($kpi['pct_abandonadas'], 1, ',', '.') ?>% del total</div>
                </div>
            </div>
            <div class="cc-kpi-card kpi-blue">
                <div class="cc-kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div class="cc-kpi-content">
                    <div class="cc-kpi-label">Promedio diario</div>
                    <div class="cc-kpi-value"><?= $fmt($kpi['promedio_diario']) ?></div>
                    <div class="cc-kpi-sub">llamadas por día</div>
                </div>
            </div>
            <div class="cc-kpi-card kpi-amber">
                <div class="cc-kpi-icon"><i class="fas fa-arrow-up"></i></div>
                <div class="cc-kpi-content">
                    <div class="cc-kpi-label">Día con más llamadas</div>
                    <div class="cc-kpi-value"><?= $kpi['dia_pico'] ? $fmt($kpi['dia_pico']['total']) : '—' ?></div>
                    <div class="cc-kpi-sub"><?= $kpi['dia_pico'] ? esc($fechaCorta($kpi['dia_pico']['fecha'])) : 'sin datos' ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS (abierto por defecto) ── -->
        <div class="cc-panel cc-stats-panel" id="statsPanel">
            <div class="cc-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE LLAMADAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="cc-stats-body">

                    <!-- ════ TABLAS ════ -->
                    <div class="cc-stats-tablas">

                        <!-- 1. Por mes -->
                        <div class="cc-stats-table-wrap">
                            <div class="cc-chart-title" style="text-align:left;">Cantidad de atenciones por mes</div>
                            <table class="cc-stats-table">
                                <thead>
                                    <tr><th>Ejercicio</th><th>Mes</th><th class="num">Atendidos</th><th class="num">Abandonadas</th><th class="num">Total</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porMes)): ?>
                                        <tr><td colspan="5" class="cc-stats-vacio">Sin llamadas para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach (array_reverse($porMes) as $f): ?>
                                    <tr>
                                        <td><?= esc($f['ejercicio']) ?></td>
                                        <td><?= esc($f['mes']) ?></td>
                                        <td class="num"><?= $fmt($f['atendidos']) ?></td>
                                        <td class="num"><?= $fmt($f['abandonadas']) ?></td>
                                        <td class="num cc-total-mes"><?= $fmt($f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porMes)): ?>
                                <tfoot>
                                    <tr><td colspan="2">Total</td><td class="num"><?= $fmt($kpi['atendidos']) ?></td><td class="num"><?= $fmt($kpi['abandonadas']) ?></td><td class="num"><?= $fmt($kpi['total']) ?></td></tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- 2. Por día -->
                        <div class="cc-stats-table-wrap">
                            <div class="cc-chart-title" style="text-align:left;">Cantidad de atenciones por día</div>
                            <table class="cc-stats-table" data-paginar="15">
                                <thead>
                                    <tr><th>Ejercicio</th><th>Fecha</th><th class="num">Atendidos</th><th class="num">Abandonadas</th><th class="num">Total</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porDia)): ?>
                                        <tr><td colspan="5" class="cc-stats-vacio">Sin llamadas por día para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porDia as $f): ?>
                                    <tr>
                                        <td><?= esc($f['ejercicio']) ?></td>
                                        <td><?= esc($fechaCorta($f['fecha'])) ?></td>
                                        <td class="num"><?= $fmt($f['atendidos']) ?></td>
                                        <td class="num"><?= $fmt($f['abandonadas']) ?></td>
                                        <td class="num cc-total-dia"><?= $fmt($f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porDia)): ?>
                                <tfoot>
                                    <tr><td colspan="2">Total</td><td class="num"><?= $fmt($kpi['atendidos']) ?></td><td class="num"><?= $fmt($kpi['abandonadas']) ?></td><td class="num"><?= $fmt($kpi['total']) ?></td></tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>

                        <!-- 3. % por día -->
                        <div class="cc-stats-table-wrap">
                            <div class="cc-chart-title" style="text-align:left;">% de la cantidad de atenciones por día</div>
                            <table class="cc-stats-table" data-paginar="15">
                                <thead>
                                    <tr><th>Ejercicio</th><th>Fecha</th><th class="num">Total</th><th class="num">% Atendidos</th><th class="num">% Abandonados</th></tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($porDia)): ?>
                                        <tr><td colspan="5" class="cc-stats-vacio">Sin llamadas por día para los filtros elegidos</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($porDia as $f): ?>
                                        <?php
                                            // Intensidad del color según el porcentaje (verde atendidos, rojo abandonados)
                                            $pa = $f['total'] > 0 ? $f['atendidos'] * 100 / $f['total'] : 0;
                                            $pb = $f['total'] > 0 ? $f['abandonadas'] * 100 / $f['total'] : 0;
                                        ?>
                                    <tr>
                                        <td><?= esc($f['ejercicio']) ?></td>
                                        <td><?= esc($fechaCorta($f['fecha'])) ?></td>
                                        <td class="num"><?= $fmt($f['total']) ?></td>
                                        <td class="num" style="background: rgba(56,161,105,<?= number_format(0.08 + $pa / 100 * 0.55, 2, '.', '') ?>);"><?= $pct($f['atendidos'], $f['total']) ?></td>
                                        <td class="num" style="background: rgba(229,62,62,<?= number_format(0.08 + $pb / 100 * 0.55, 2, '.', '') ?>);"><?= $pct($f['abandonadas'], $f['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <?php if (!empty($porDia)): ?>
                                <tfoot>
                                    <tr><td colspan="2">Total</td><td class="num"><?= $fmt($kpi['total']) ?></td><td class="num"><?= $pct($kpi['atendidos'], $kpi['total']) ?></td><td class="num"><?= $pct($kpi['abandonadas'], $kpi['total']) ?></td></tr>
                                </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <!-- ════ GRÁFICOS ════ -->
                    <div class="cc-stats-graficos">
                        <!-- Barras agrupadas por mes: total / atendidos / abandonadas -->
                        <div class="cc-chart-box">
                            <div class="cc-chart-title">Llamadas por mes</div>
                            <div class="cc-chart-canvas" style="height:380px;"><canvas id="chartMeses"></canvas></div>
                        </div>

                        <div class="cc-graficos-fila">
                            <!-- Torta: participación de cada mes -->
                            <div class="cc-chart-box">
                                <div class="cc-chart-title">Distribución por mes</div>
                                <div class="cc-chart-canvas"><canvas id="chartTortaMes"></canvas></div>
                            </div>

                            <!-- Dona: participación de cada ejercicio -->
                            <div class="cc-chart-box">
                                <div class="cc-chart-title">Distribución por ejercicio</div>
                                <div class="cc-chart-canvas"><canvas id="chartDonaEjercicio"></canvas></div>
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

    // ── Paginador de las tablas por día ("1 - 15 / 61" con flechas) ──
    document.querySelectorAll('table[data-paginar]').forEach(function (tabla) {
        var porPagina = parseInt(tabla.dataset.paginar, 10);
        var filas = Array.prototype.slice.call(tabla.tBodies[0].rows);
        if (filas.length <= porPagina) return;

        var pagina = 0;
        var nav = document.createElement('div');
        nav.className = 'cc-paginador';
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
    var porMes       = <?= json_encode($porMes) ?>;         // orden cronológico
    var porEjercicio = <?= json_encode($porEjercicio) ?>;   // {ejercicio: total}
    var todosLosEjercicios = <?= json_encode($todosLosEjercicios) ?>;

    // Colores de la planilla de referencia
    var COLOR_TOTAL = '#3aafbf', COLOR_ATENDIDOS = '#263a5a', COLOR_ABANDONADAS = '#e98d69';
    var coloresTorta = ['#263a5a', '#3aafbf', '#e98d69', '#9aa4ad', '#8fd3f4', '#f1c27d', '#82e0aa', '#bb8fce', '#f1948a', '#76d7c4', '#edbb99', '#7fb3d5'];

    // Colores por ejercicio: 10 tonos de azul (el más viejo primero), como en el resto del sistema
    var paletaEjercicios = ['#4FB3E6', '#1A5FA8', '#0B2E59', '#8FD3F4', '#2F80C8', '#123F73', '#6EC6EA', '#1E4E8C', '#A9DDF5', '#0F5E9C'];
    function colorParaEjercicio(ej) {
        var i = todosLosEjercicios.indexOf(parseInt(ej, 10));
        return paletaEjercicios[(i < 0 ? 0 : i) % paletaEjercicios.length];
    }

    var variosAnios = porMes.some(function (m) { return m.ejercicio !== porMes[0].ejercicio; });
    var etiquetasMes = porMes.map(function (m) { return m.mes + (variosAnios ? ' ' + m.ejercicio : ''); });

    var chartsInicializados = false;

    function inicializarCharts() {
        if (chartsInicializados) return;
        chartsInicializados = true;

        // 1. Barras agrupadas por mes
        crearGrafico(document.getElementById('chartMeses'), 'Sin llamadas por mes para los filtros elegidos', {
            type: 'bar',
            data: {
                labels: etiquetasMes,
                datasets: [
                    { label: 'TOTAL',       data: porMes.map(function (m) { return m.total; }),       backgroundColor: COLOR_TOTAL,       maxBarThickness: 40 },
                    { label: 'ATENDIDOS',   data: porMes.map(function (m) { return m.atendidos; }),   backgroundColor: COLOR_ATENDIDOS,   maxBarThickness: 40 },
                    { label: 'ABANDONADAS', data: porMes.map(function (m) { return m.abandonadas; }), backgroundColor: COLOR_ABANDONADAS, maxBarThickness: 40 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', align: 'start', labels: { boxWidth: 28, boxHeight: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.dataset.label + ': ' + formatoNumero(ctx.parsed.y); } } },
                    datalabels: { display: false }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 45 } },
                    y: { beginAtZero: true, grace: '8%', ticks: { callback: formatoEje } }
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

        // 2. Dona: participación de cada mes en el total
        var valoresMes = porMes.map(function (m) { return m.total; });
        crearGrafico(document.getElementById('chartTortaMes'), 'Sin llamadas por mes para los filtros elegidos', {
            type: 'doughnut',
            plugins: [centerTextPlugin],
            data: {
                labels: etiquetasMes,
                datasets: [{
                    data: valoresMes,
                    backgroundColor: porMes.map(function (m, i) { return coloresTorta[i % coloresTorta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: opcionesTorta(valoresMes)
        });

        // 3. Dona: participación de cada ejercicio
        var ejercicios = Object.keys(porEjercicio);
        var valoresEj  = ejercicios.map(function (e) { return porEjercicio[e]; });
        crearGrafico(document.getElementById('chartDonaEjercicio'), 'Sin llamadas por ejercicio para los filtros elegidos', {
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