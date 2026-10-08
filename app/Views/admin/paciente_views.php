<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Gestión Paciente · Prehospitalario <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Pestañas e íconos de tarjetas (compartido con el otro panel) -->
<link rel="stylesheet" href="/css/panel-pestanas.css">
<style>
    /* CSS maintained from original paciente_views.php structure (integrated into template) */
    .breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .breadcrumb i { color: var(--teal); font-size: 13px; }
    .breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .breadcrumb a:hover { color: var(--teal); }
    .breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .section-header {
        font-size: 10.5px; font-weight: 700; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 1px;
        margin-bottom: 10px; padding-bottom: 6px;
        border-bottom: 2px solid var(--teal); display: inline-block;
    }

    /* ── FILTRO ── */
    .filtro-bar {
        background: var(--white);
        border: 0.5px solid var(--border);
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        gap: 12px;
        align-items: flex-end;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: #f8f9fb;
        cursor: pointer;
        outline: none;
        transition: border-color 0.15s;
    }
    .filtro-select:focus { border-color: var(--teal); }
    .filtro-btn {
        background: var(--teal); color: #fff;
        border: none; border-radius: 7px;
        padding: 8px 18px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: flex; align-items: center; gap: 6px;
        transition: background 0.15s;
    }
    .filtro-btn:hover { background: var(--teal-light); }
    .filtro-btn-clear {
        background: #f0f2f5; color: var(--text-muted);
        border: 1px solid var(--border); border-radius: 7px;
        padding: 8px 14px; font-size: 13px; font-weight: 600;
        text-decoration: none; display: flex; align-items: center; gap: 6px;
        transition: background 0.15s;
    }
    .filtro-btn-clear:hover { background: #e4e7ed; color: var(--text-main); text-decoration: none; }
    .filtro-periodo-tag {
        font-size: 11px; color: var(--teal);
        background: var(--teal-bg);
        padding: 4px 10px; border-radius: 8px;
        font-weight: 600; align-self: flex-end; margin-bottom: 1px;
    }

    /* ── KPI CARDS ── */
    .kpi-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }
    .kpi-card {
        background: var(--white);
        border-radius: 12px;
        padding: 18px 20px 14px;
        border: 0.5px solid var(--border);
        position: relative;
        overflow: hidden;
    }
    .kpi-card::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
    }
    .kpi-card.kpi-teal::before   { background: var(--teal); }
    .kpi-card.kpi-blue::before   { background: #1A5FA8; }
    .kpi-card.kpi-green::before  { background: #2F80C8; }
    .kpi-card.kpi-orange::before { background: #4FB3E6; }

    .kpi-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted); margin-bottom: 6px;
    }
    .kpi-value {
        font-size: 36px; font-weight: 800;
        line-height: 1; color: var(--text-main);
        font-variant-numeric: tabular-nums;
        margin-bottom: 4px;
    }
    .kpi-sub {
        font-size: 11px; color: var(--text-muted); margin-bottom: 10px;
    }
    .kpi-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 10.5px; font-weight: 600;
        padding: 3px 9px; border-radius: 10px;
    }
    .kpi-badge i { font-size: 9px; }
    .kpi-badge.green  { background: rgba(39,174,96,0.12);  color: #27ae60; }
    .kpi-badge.blue   { background: rgba(52,152,219,0.12); color: #2980b9; }
    .kpi-badge.orange { background: rgba(230,126,34,0.12); color: #d35400; }
    .kpi-badge.teal   { background: var(--teal-bg);        color: var(--teal); }

    /* ── FILA CENTRAL ── */
    .mid-row {
        display: grid;
        grid-template-columns: 1fr 1.6fr;
        gap: 14px;
        margin-bottom: 20px;
    }
    .panel-card {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
    }
    .panel-header {
        padding: 14px 18px 10px;
        border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .panel-title {
        font-size: 10.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .panel-body { padding: 16px 18px; }

    .distrib-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .distrib-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px;
        background: #f4f6f9; border-radius: 8px;
    }
    .distrib-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .distrib-info { flex: 1; min-width: 0; }
    .distrib-name {
        font-size: 10px; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.5px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .distrib-val {
        font-size: 20px; font-weight: 700; color: var(--text-main);
        font-variant-numeric: tabular-nums; line-height: 1.1;
    }

    /* Cobertura por efector */
    .coverage-list { display: flex; flex-direction: column; gap: 10px; }
    .coverage-row { display: flex; align-items: center; gap: 10px; }
    .coverage-name {
        font-size: 12px; font-weight: 600; color: var(--text-main);
        width: 140px; flex-shrink: 0; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .coverage-bar-wrap {
        flex: 1; background: #eef0f3; border-radius: 4px; height: 10px; overflow: hidden;
    }
    .coverage-bar { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
    .coverage-num {
        font-size: 12px; font-weight: 700; color: var(--text-main);
        width: 34px; text-align: right; flex-shrink: 0;
    }
    .bar-1 { background: var(--teal); }
    .bar-2 { background: #1A5FA8; }
    .bar-3 { background: #2F80C8; }
    .bar-4 { background: #4FB3E6; }
    .bar-5 { background: #8FD3F4; }

    /* ── BOTTOM ROW ── */
    .bot-row {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 14px;
    }
    .chart-wrap { padding: 10px 18px 14px; }

    .mini-stat-grid {
        display: flex; flex-direction: column; gap: 10px;
        padding: 16px 18px;
    }
    .mini-stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .mini-stat {
        background: #f4f6f9; border-radius: 8px;
        padding: 12px 10px; text-align: center;
    }
    .mini-stat-val {
        font-size: 26px; font-weight: 800; line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .mini-stat-val.teal   { color: var(--teal); }
    .mini-stat-val.blue   { color: #1A5FA8; }
    .mini-stat-val.orange { color: #2F80C8; }
    .mini-stat-label {
        font-size: 9px; color: var(--text-muted);
        text-transform: uppercase; letter-spacing: 0.5px;
        margin-top: 4px; line-height: 1.2;
    }
    .mini-sep { border: none; border-top: 1px solid var(--border); margin: 0; }

    /* ── QUICK LINKS ── */
     .ql-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
        gap: 12px;
        margin-bottom: 16px;
    }
    .ql-card {
        background: var(--white); border-radius: 14px; padding: 18px 20px;
        background: var(--white); border-radius: 12px; padding: 12px 16px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; color: var(--text-main);
        border: 1px solid var(--border); min-height: 82px;
        border: 1px solid var(--border); min-height: 64px;
        transition: transform 0.18s ease, box-shadow 0.2s, background 0.15s, border-color 0.15s;
    }
    .ql-card:hover { box-shadow: 0 8px 18px rgba(0,0,0,0.08); transform: translateY(-2px); text-decoration: none; color: var(--text-main); }
    .ql-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--teal); flex-shrink: 0; }
    .ql-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; color: var(--teal); flex-shrink: 0; }
    .ql-title { font-size: 13.5px; font-weight: 800; color: var(--text-main); line-height: 1.15; }
    .ql-desc  { font-size: 11px; color: var(--text-muted); margin-top: 3px; }
    .ql-desc  { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .ql-card:nth-child(1) { background: linear-gradient(135deg, #f7fbff 0%, #eef7ff 100%); border-color: #bcd4f4; }
    .ql-card:nth-child(1) .ql-icon { background: #dbeafe; color: #3b82f6; }
    .ql-card:nth-child(2) { background: linear-gradient(135deg, #f7fff9 0%, #e9fbef 100%); border-color: #bafbda; }
    .ql-card:nth-child(2) .ql-icon { background: #d1fae5; color: #10b981; }
    .ql-card:nth-child(3) { background: linear-gradient(135deg, #fff9f4 0%, #fff1e7 100%); border-color: #fed7aa; }
    .ql-card:nth-child(3) .ql-icon { background: #fed7aa; color: #f97316; }
    .ql-card:nth-child(4) { background: linear-gradient(135deg, #fff8fb 0%, #fce7f3 100%); border-color: #fbcfe8; }
    .ql-card:nth-child(4) .ql-icon { background: #fbcfe8; color: #ec4899; }
    .ql-card:nth-child(5) { background: linear-gradient(135deg, #fbf8ff 0%, #f3e8ff 100%); border-color: #e9d5ff; }
    .ql-card:nth-child(5) .ql-icon { background: #e9d5ff; color: #a855f7; }
    .ql-card:nth-child(6) { background: linear-gradient(135deg, #f8fffe 0%, #e6fffb 100%); border-color: #99f6e4; }
    .ql-card:nth-child(6) .ql-icon { background: #99f6e4; color: #14b8a6; }

    .no-data { text-align: center; padding: 24px 16px; color: var(--text-muted); font-size: 13px; }
    .no-data i { font-size: 28px; display: block; margin-bottom: 8px; opacity: 0.35; }

    /* Evita que el contenido de las grillas las desborde */
    .kpi-row > *, .mid-row > *, .bot-row > * { min-width: 0; }
    .coverage-num { width: auto; min-width: 34px; }

    /* ── RESPONSIVE (igual que Prehospitalario y Hospitalario) ── */
    @media (max-width: 1200px) {
        .bot-row { grid-template-columns: 1fr; }
    }

    @media (max-width: 900px) {
        .mid-row { grid-template-columns: 1fr; }
    }

    @media (max-width: 576px) {
        .filtro-bar { padding: 12px 14px; gap: 10px; }
        .filtro-group { flex: 1 1 calc(33.333% - 10px); min-width: 90px; }
        .filtro-select { width: 100%; }
        .filtro-btn, .filtro-btn-clear { flex: 1 1 auto; justify-content: center; }
        .filtro-periodo-tag { flex-basis: 100%; text-align: center; }

        .distrib-grid { grid-template-columns: 1fr; }
        .panel-header { padding: 12px 14px 8px; }
        .panel-body, .mini-stat-grid { padding: 12px 14px; }
        .chart-wrap { padding: 8px 10px 12px; }

        .coverage-name { width: 95px; }
        .mini-stat-row { gap: 8px; }
        .mini-stat { padding: 10px 6px; }
        .mini-stat-val { font-size: 22px; }

        /* Accesos rápidos: 2 por fila, como en Hospitalario */
        .ql-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    }
</style>

<div class="breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <strong>Gestión paciente</strong>
</div>

<!-- ACCESOS RÁPIDOS -->
<div class="section-header">Accesos rápidos</div>
<div class="ql-grid">
    <a href="<?= base_url(route_to('consulta_reclamo_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-comments"></i></div>
        <div><div class="ql-title">Consultas y reclamos</div><div class="ql-desc">Ver y gestionar</div></div>
    </a>
    <a href="<?= base_url(route_to('chat_bot_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-robot"></i></div>
        <div><div class="ql-title">Chat bot</div><div class="ql-desc">Turnos otorgados</div></div>
    </a>
    <a href="<?= base_url(route_to('turno_hospitalario_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-calendar-alt"></i></div>
        <div><div class="ql-title">Especialidades</div><div class="ql-desc">Gestión de turnos</div></div>
    </a>
    <a href="<?= base_url(route_to('call_center_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-phone"></i></div>
        <div><div class="ql-title">0800 Call center</div><div class="ql-desc">Llamadas recibidas</div></div>
    </a>
    <a href="<?= base_url(route_to('inicio_views')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-reply"></i></div>
        <div><div class="ql-title">Volver</div><div class="ql-desc">Panel principal</div></div>
    </a>    
</div>

<!-- ── FILTRO PERÍODO ── -->
<?php
    $meses_nombres = [
        1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr',
        5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago',
        9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dic'
    ];
?>
<form method="GET" class="filtro-bar">
    <div class="filtro-group">
        <span class="filtro-label">Ejercicio</span>
        <select name="ejercicio" class="filtro-select">
            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
            <option value="<?= $y ?>" <?= $ejercicio_actual == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="filtro-group">
        <span class="filtro-label">Mes desde</span>
        <select name="mes_desde" class="filtro-select">
            <?php foreach ($meses_nombres as $num => $nombre): ?>
            <option value="<?= $num ?>" <?= $mes_desde == $num ? 'selected' : '' ?>><?= $nombre ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-group">
        <span class="filtro-label">Mes hasta</span>
        <select name="mes_hasta" class="filtro-select">
            <?php foreach ($meses_nombres as $num => $nombre): ?>
            <option value="<?= $num ?>" <?= $mes_hasta == $num ? 'selected' : '' ?>><?= $nombre ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="filtro-btn">
        <i class="fas fa-filter"></i> Filtrar
    </button>
    <!-- Pestaña activa: se conserva al filtrar -->
    <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab, 'attr') ?>">
    <a href="?ejercicio=<?= date('Y') ?>&mes_desde=1&mes_hasta=12&tab=<?= esc($tab, 'url') ?>" class="filtro-btn-clear" id="btnLimpiar">
        <i class="fas fa-undo"></i> Limpiar
    </a>
    <span class="filtro-periodo-tag">
        <i class="fas fa-calendar-alt"></i>
        <?= $meses_nombres[$mes_desde] ?> – <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?>
    </span>
</form>

<?php
    // Tarjeta KPI con el mismo modelo que el panel Hospitalario (ícono a la izquierda).
    // $badge se muestra como segunda línea destacada (verde, azul o naranja según $claseBadge).
    $kpi = function ($color, $icono, $label, $valor, $sub = '', $badge = '', $claseBadge = 'green', $iconoBadge = 'fa-info-circle') {
        return '<div class="ml-kpi-card kpi-' . $color . '">
                    <div class="ml-kpi-icon"><i class="fas ' . $icono . '"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">' . esc($label) . '</div>
                        <div class="ml-kpi-value">' . $valor . '</div>'
                        . ($sub !== '' ? '<div class="ml-kpi-sub" title="' . esc(strip_tags($sub)) . '">' . $sub . '</div>' : '')
                        . ($badge !== '' ? '<div class="ml-kpi-sub destacado ' . $claseBadge . '"><i class="fas ' . $iconoBadge . '"></i> ' . $badge . '</div>' : '') .
                    '</div>
                </div>';
    };
?>
<!-- ── PESTAÑAS ── -->
<div class="ml-tabs" role="tablist">
    <?php foreach (['act'    => ['fa-chart-line', 'Actividad mensual'],
                    'cons'   => ['fa-comments', 'Consultas y reclamos'],
                    'turnos' => ['fa-calendar-check', 'Turnos y llamadas']] as $clave => [$icono, $titulo]): ?>
        <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab">
            <i class="fas <?= $icono ?>"></i> <?= $titulo ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- ══════════════ ACTIVIDAD MENSUAL ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'act' ? 'is-active' : '' ?>" data-pane="act">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-chart-line"></i> ACTIVIDAD MENSUAL
            <span class="hv-periodo"><?= $meses_nombres[$mes_desde] ?> – <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?></span>
            <span class="hv-periodo">Todos los canales</span>
        </span>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('teal', 'fa-comments', 'Consultas y reclamos', number_format($total_consultas),
                 $meses_nombres[$mes_desde] . ' – ' . $meses_nombres[$mes_hasta] . ' · ' . $ejercicio_actual,
                 $pct_consultas . '% resueltas', 'green', 'fa-check') ?>
        <?= $kpi('blue', 'fa-robot', 'Turnos chat bot', number_format($turnos_chatbot), 'otorgados en el período',
                 'Canal automatizado', 'blue', 'fa-bolt') ?>
        <?= $kpi('green', 'fa-calendar-check', 'Turnos especialidades', number_format($turnos_otorgados_total),
                 number_format($turnos_atendidos_total) . ' atendidos', $pct_atendidos . '% asistencia', 'green', 'fa-user-check') ?>
        <?= $kpi('orange', 'fa-headset', '0800 Call center', number_format($call_total),
                 number_format($call_atendidos) . ' atendidas / ' . number_format($call_abandonadas) . ' abandonadas',
                 $pct_call_atencion . '% atención', 'orange', 'fa-phone-volume') ?>
    </div>

    <div class="panel-card">
        <div class="panel-header">
            <span class="panel-title">
                Actividad mensual combinada —
                <?= $meses_nombres[$mes_desde] ?> a <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?>
            </span>
        </div>
        <div class="chart-wrap">
            <?php if (array_sum($datos_mensuales) > 0): ?>
                <div class="chart-alto"><canvas id="chartMensual"></canvas></div>
            <?php else: ?>
                <div class="no-data" style="padding:40px 0;">
                    <i class="fas fa-chart-line"></i>
                    Sin datos mensuales para el período seleccionado
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
</div>
</div>

<!-- ══════════════ CONSULTAS Y RECLAMOS ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'cons' ? 'is-active' : '' ?>" data-pane="cons">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-comments"></i> CONSULTAS Y RECLAMOS
            <span class="hv-periodo"><?= $meses_nombres[$mes_desde] ?> – <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?></span>
        </span>
        <a href="<?= base_url(route_to('consulta_reclamo_list')); ?>" class="hv-ver">Ver consultas <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('teal', 'fa-comments', 'Consultas totales', number_format($total_consultas),
                 $meses_nombres[$mes_desde] . ' – ' . $meses_nombres[$mes_hasta] . ' · ' . $ejercicio_actual) ?>
        <?= $kpi('blue', 'fa-check-circle', 'Resueltas', number_format($consultas_resueltas), '',
                 $pct_consultas . '% del total', 'green', 'fa-check') ?>
        <?= $kpi('green', 'fa-hourglass-half', 'Pendientes', number_format($total_consultas - $consultas_resueltas), '',
                 (100 - $pct_consultas) . '% del total', 'orange', 'fa-clock') ?>
        <?= $kpi('orange', 'fa-tags', 'Tipos de llamado', count($tipos_llamado ?? []), 'categorías con consultas') ?>
    </div>

    <div class="tab-grid">
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Consultas por tipo de llamado</span>
            </div>
            <div class="panel-body">
                <?php $colores_tipo = ['#0B2E59', '#4FB3E6', '#1A5FA8', '#8FD3F4', '#2F80C8', '#6EC6EA']; ?>
                <?php if (!empty($tipos_llamado)): ?>
                <div class="distrib-grid">
                    <?php foreach ($tipos_llamado as $i => $t): ?>
                    <div class="distrib-item">
                        <div class="distrib-dot" style="background:<?= $colores_tipo[$i % count($colores_tipo)] ?>"></div>
                        <div class="distrib-info">
                            <div class="distrib-name"><?= esc($t['tipo_llamado'] ?? 'Sin tipo') ?></div>
                            <div class="distrib-val"><?= number_format($t['total']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-data"><i class="fas fa-inbox"></i> Sin datos de consultas</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Estado de las consultas</span>
            </div>
            <div class="mini-stat-grid">
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= number_format($total_consultas) ?></div>
                        <div class="mini-stat-label">Consultas<br>totales</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= number_format($consultas_resueltas) ?></div>
                        <div class="mini-stat-label">Consultas<br>resueltas</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= number_format($total_consultas - $consultas_resueltas) ?></div>
                        <div class="mini-stat-label">Consultas<br>pendientes</div>
                    </div>
                </div>
                <?php if ($total_consultas > 0): ?>
                    <div class="proporcion-barra" role="img"
                         aria-label="<?= $pct_consultas ?>% resueltas, <?= 100 - $pct_consultas ?>% pendientes">
                        <div class="proporcion-seg adultos" style="width:<?= $pct_consultas ?>%"></div>
                        <div class="proporcion-seg pediatricos" style="width:<?= 100 - $pct_consultas ?>%"></div>
                    </div>
                    <div class="proporcion-leyenda">
                        <span><i class="proporcion-punto adultos"></i> Resueltas <strong><?= $pct_consultas ?>%</strong></span>
                        <span><i class="proporcion-punto pediatricos"></i> Pendientes <strong><?= 100 - $pct_consultas ?>%</strong></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
</div>
</div>

<!-- ══════════════ TURNOS Y LLAMADAS ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'turnos' ? 'is-active' : '' ?>" data-pane="turnos">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-calendar-check"></i> TURNOS Y LLAMADAS
            <span class="hv-periodo"><?= $meses_nombres[$mes_desde] ?> – <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?></span>
            <span class="hv-periodo">Especialidades · 0800 · Chat bot</span>
        </span>
        <a href="<?= base_url(route_to('turno_hospitalario_list')); ?>" class="hv-ver">Ver turnos <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('green', 'fa-calendar-check', 'Turnos otorgados', number_format($turnos_otorgados_total),
                 'especialidades', $pct_atendidos . '% asistencia', 'green', 'fa-user-check') ?>
        <?= $kpi('teal', 'fa-user-times', 'Ausentes y cancelados', number_format($turnos_ausentes_total + $turnos_cancelados_total),
                 number_format($turnos_ausentes_total) . ' ausentes · ' . number_format($turnos_cancelados_total) . ' cancelados') ?>
        <?= $kpi('blue', 'fa-robot', 'Turnos chat bot', number_format($turnos_chatbot), 'otorgados en el período') ?>
        <?= $kpi('orange', 'fa-headset', 'Llamadas 0800', number_format($call_total),
                 number_format($call_atendidos) . ' atendidas', $pct_call_atencion . '% atención', 'orange', 'fa-phone-volume') ?>
    </div>

    <div class="tab-grid">
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Efectores — Top 5 turnos otorgados</span>
            </div>
            <div class="panel-body">
                <?php $bar_classes = ['bar-1','bar-2','bar-3','bar-4','bar-5']; ?>
                <?php if (!empty($top_efectores)): ?>
                    <?php $max_total = max(array_column($top_efectores, 'total')); ?>
                    <div class="coverage-list">
                        <?php foreach ($top_efectores as $i => $e): ?>
                        <div class="coverage-row">
                            <div class="coverage-name" title="<?= esc($e['nombre'] ?? 'Sin efector') ?>"><?= esc($e['nombre'] ?? 'Sin efector') ?></div>
                            <div class="coverage-bar-wrap">
                                <div class="coverage-bar <?= $bar_classes[$i % count($bar_classes)] ?>"
                                     style="width:<?= $max_total > 0 ? round(($e['total'] / $max_total) * 100) : 0 ?>%"></div>
                            </div>
                            <div class="coverage-num"><?= number_format($e['total']) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data"><i class="fas fa-chart-bar"></i> Sin datos por efector.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Turnos y llamadas</span>
            </div>
            <div class="mini-stat-grid">
                <div class="mini-stat-titulo"><i class="fas fa-calendar-check"></i> Turnos de especialidades</div>
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= number_format($turnos_atendidos_total) ?></div>
                        <div class="mini-stat-label">Turnos<br>atendidos</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= number_format($turnos_ausentes_total) ?></div>
                        <div class="mini-stat-label">Turnos<br>ausentes</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= number_format($turnos_cancelados_total) ?></div>
                        <div class="mini-stat-label">Turnos<br>cancelados</div>
                    </div>
                </div>
                <hr class="mini-sep">
                <div class="mini-stat-titulo"><i class="fas fa-headset"></i> 0800 y chat bot</div>
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= number_format($call_atendidos) ?></div>
                        <div class="mini-stat-label">Llamadas<br>atendidas</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= number_format($call_abandonadas) ?></div>
                        <div class="mini-stat-label">Llamadas<br>abandonadas</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= number_format($turnos_chatbot) ?></div>
                        <div class="mini-stat-label">Turnos<br>chat bot</div>
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
<!-- Chart.js: antes no se cargaba en esta vista y el gráfico mensual no aparecía -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    // Gráficos por pestaña: se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
    var graficos = {
        act: function () {
            <?php if (array_sum($datos_mensuales) > 0): ?>
            const labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            const datos  = <?= json_encode(array_values($datos_mensuales)) ?>;

            new Chart(document.getElementById('chartMensual'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Actividad',
                        data: datos,
                        borderColor: '#2F80C8',
                        backgroundColor: 'rgba(47,128,200,0.10)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#2F80C8',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a2b45',
                            titleFont: { size: 11 },
                            bodyFont: { size: 12, weight: '700' },
                            padding: 10,
                            cornerRadius: 6,
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#5a6a7e' } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 }, color: '#5a6a7e', precision: 0 } }
                    }
                }
            });
            <?php endif; ?>
        }
    };

    // ══ Pestañas (igual que el panel Hospitalario) ══
    (function () {
        var dibujados  = {};
        var inputTab   = document.getElementById('inputTab');
        var btnLimpiar = document.getElementById('btnLimpiar');

        function mostrar(tab) {
            document.querySelectorAll('.ml-tab').forEach(function (b) {
                b.classList.toggle('is-active', b.dataset.tab === tab);
            });
            document.querySelectorAll('.ml-tab-pane').forEach(function (p) {
                p.classList.toggle('is-active', p.dataset.pane === tab);
            });

            // Mantener la pestaña al filtrar, limpiar o recargar
            if (inputTab) inputTab.value = tab;
            if (btnLimpiar) btnLimpiar.href = btnLimpiar.href.replace(/([?&])tab=[^&]*/, '$1tab=' + tab);
            var params = new URLSearchParams(window.location.search);
            params.set('tab', tab);
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());

            if (!dibujados[tab] && graficos[tab]) {
                graficos[tab]();
                dibujados[tab] = true;
            }
        }

        document.querySelectorAll('.ml-tab').forEach(function (b) {
            b.addEventListener('click', function () { mostrar(b.dataset.tab); });
        });

        mostrar((inputTab && inputTab.value) || 'act');
    })();
</script>
<?= $this->endSection() ?>
