<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Bases · Prehospitalario <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Pestañas e íconos de tarjetas (compartido con el otro panel) -->
<link rel="stylesheet" href="/css/panel-pestanas.css">
<style>
    /* CSS maintained from original base_views.php */
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
        background: #fbf8f8;
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
        background: #f5f0f0; color: var(--text-muted);
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

    /* Cobertura por base */
    .coverage-list { display: flex; flex-direction: column; gap: 10px; }
    .coverage-row { display: flex; align-items: center; gap: 10px; }
    .coverage-name {
        font-size: 12px; font-weight: 600; color: var(--text-main);
        width: 110px; flex-shrink: 0; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .coverage-bar-wrap {
        flex: 1; background: #f3eeee; border-radius: 4px; height: 10px; overflow: hidden;
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
        background: #f9f4f4; border-radius: 8px;
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
        gap: 12px;
        margin-bottom: 16px;
    }
    .ql-card {
        background: var(--white); border-radius: 12px; padding: 12px 16px;
        display: flex; align-items: center; gap: 12px;
        text-decoration: none; color: var(--text-main);
        border: 1px solid var(--border); min-height: 64px;
        transition: transform 0.18s ease, box-shadow 0.2s, background 0.15s, border-color 0.15s;
    }
    .ql-card:hover { box-shadow: 0 8px 18px rgba(0,0,0,0.08); transform: translateY(-2px); text-decoration: none; color: var(--text-main); }
    .ql-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; color: var(--teal); flex-shrink: 0; }
    .ql-title { font-size: 13.5px; font-weight: 800; color: var(--text-main); line-height: 1.15; }
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

    /* ── RESPONSIVE ── */
    @media (max-width: 1200px) {
        .kpi-row { grid-template-columns: repeat(2, 1fr); }
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

        .kpi-row { gap: 10px; }
        .kpi-card { padding: 14px 14px 12px; }
        .kpi-value { font-size: 28px; }
        .kpi-badge { white-space: normal; }

        /* Distribución en cuadrados: punto arriba, nombre y valor centrados */
        .distrib-grid { grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .distrib-item {
            aspect-ratio: 1 / 1;
            flex-direction: column;
            justify-content: center;
            text-align: center;
            gap: 6px;
            padding: 8px 6px;
            border-radius: 10px;
        }
        .distrib-info { flex: 0 0 auto; width: 100%; }
        .distrib-name {
            white-space: normal;
            font-size: 9px;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .distrib-val { font-size: 18px; }
        .panel-header { padding: 12px 14px 8px; }
        .panel-body, .mini-stat-grid { padding: 12px 14px; }
        .chart-wrap { padding: 8px 10px 12px; }

        .coverage-name { width: 95px; }
        .mini-stat-row { gap: 8px; }
        .mini-stat { padding: 10px 6px; }
        .mini-stat-val { font-size: 22px; }

        .ql-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    }

    @media (max-width: 360px) {
        .kpi-row { grid-template-columns: 1fr; }
        .distrib-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <strong>Prehospitalario</strong>
</div>

<!-- ACCESOS RÁPIDOS -->
<div class="section-header">Accesos rápidos</div>
<div class="ql-grid">
    <a href="<?= base_url(route_to('base_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-database"></i></div>
        <div><div class="ql-title">Listado bases</div><div class="ql-desc">Ver y gestionar</div></div>
    </a>
    <a href="<?= base_url(route_to('movil_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-car-side"></i></div>
        <div><div class="ql-title">Móviles</div><div class="ql-desc">Flota registrada</div></div>
    </a>
    <a href="<?= base_url(route_to('atencion_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-notes-medical"></i></div>
        <div><div class="ql-title">Atenciones</div><div class="ql-desc">Realizadas</div></div>
    </a>
    <a href="<?= base_url(route_to('identificacion_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-home"></i></div>
        <div><div class="ql-title">Int. domiciliaria</div><div class="ql-desc">Pacientes activos</div></div>
    </a>
    <a href="<?= base_url(route_to('asistencia_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-hands-helping"></i></div>
        <div><div class="ql-title">Asistencias</div><div class="ql-desc">Realizadas</div></div>
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
    <?php foreach (['asis'   => ['fa-hands-helping', 'Asistencias'],
                    'flota'  => ['fa-ambulance', 'Bases y móviles'],
                    'intdom' => ['fa-house-user', 'Internación domiciliaria']] as $clave => [$icono, $titulo]): ?>
        <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab">
            <i class="fas <?= $icono ?>"></i> <?= $titulo ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- ══════════════ ASISTENCIAS ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'asis' ? 'is-active' : '' ?>" data-pane="asis">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-hands-helping"></i> ASISTENCIAS
            <span class="hv-periodo"><?= $meses_nombres[$mes_desde] ?> – <?= $meses_nombres[$mes_hasta] ?> <?= $ejercicio_actual ?></span>
        </span>
        <a href="<?= base_url(route_to('asistencia_list')); ?>" class="hv-ver">Ver asistencias <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('green', 'fa-hands-helping', 'Total asistencias', number_format($total_asistencias),
                 $meses_nombres[$mes_desde] . ' – ' . $meses_nombres[$mes_hasta] . ' · ' . $ejercicio_actual,
                 'Ejercicio ' . $ejercicio_actual, 'green', 'fa-calendar-check') ?>
        <?= $kpi('teal', 'fa-heartbeat', 'Emergencias', number_format($emerg_con_medico + $emerg_sin_medico),
                 number_format($emerg_con_medico) . ' con médico · ' . number_format($emerg_sin_medico) . ' sin médico') ?>
        <?= $kpi('blue', 'fa-first-aid', 'Urgencias', number_format($urgencias),
                 $total_asistencias > 0 ? round($urgencias * 100 / $total_asistencias, 1) . '% del total' : 'sin asistencias') ?>
        <?= $kpi('orange', 'fa-exchange-alt', 'Derivaciones', number_format($derivacion_publica + $derivacion_privada),
                 number_format($derivacion_publica) . ' públicas · ' . number_format($derivacion_privada) . ' privadas') ?>
    </div>

    <div class="tab-grid">
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Distribución de asistencias</span>
            </div>
            <div class="panel-body">
                <?php if ($total_asistencias > 0): ?>
                <div class="distrib-grid">
                    <?php foreach ([
                        ['Emerg. c/ médico',   $emerg_con_medico,   '#0B2E59'],
                        ['Emerg. s/ médico',   $emerg_sin_medico,   '#4FB3E6'],
                        ['Urgencias',          $urgencias,          '#1A5FA8'],
                        ['Derivación pública', $derivacion_publica, '#8FD3F4'],
                        ['Derivación privada', $derivacion_privada, '#2F80C8'],
                        ['Int. domiciliaria',  $asist_internacion,  '#6EC6EA'],
                    ] as [$nombre, $valor, $color]): ?>
                    <div class="distrib-item">
                        <div class="distrib-dot" style="background:<?= $color ?>"></div>
                        <div class="distrib-info">
                            <div class="distrib-name"><?= $nombre ?></div>
                            <div class="distrib-val"><?= number_format($valor) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-data"><i class="fas fa-inbox"></i> Sin datos de asistencias</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">
                    Asistencias mensuales —
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
</div>

<!-- ══════════════ BASES Y MÓVILES ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'flota' ? 'is-active' : '' ?>" data-pane="flota">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-ambulance"></i> BASES Y MÓVILES
            <span class="hv-periodo">Estado actual</span>
            <span class="hv-periodo"><?= $bases_total ?> bases · <?= $moviles_total ?> móviles</span>
        </span>
        <a href="<?= base_url(route_to('base_list')); ?>" class="hv-ver">Ver bases <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('teal', 'fa-hospital', 'Bases activas', $bases_activas, 'de ' . $bases_total . ' registradas',
                 $pct_bases . '% operativas', 'green', 'fa-arrow-up') ?>
        <?= $kpi('blue', 'fa-ambulance', 'Móviles en servicio', $moviles_op, 'total flota: ' . $moviles_total,
                 $pct_moviles . '% disponibles', 'blue', 'fa-arrow-up') ?>
        <?= $kpi('green', 'fa-tools', 'En mantenimiento', $moviles_mantenimiento, 'móviles en taller') ?>
        <?= $kpi('orange', 'fa-ban', 'Fuera de servicio', $moviles_baja, 'móviles dados de baja') ?>
    </div>

    <div class="tab-grid">
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Cobertura por base — Top 5</span>
            </div>
            <div class="panel-body">
                <?php
                    $bar_classes    = ['bar-1','bar-2','bar-3','bar-4','bar-5'];
                    $top_bases_data = (!empty($top_bases)) ? $top_bases : [];
                ?>
                <?php if (!empty($top_bases_data)): ?>
                    <?php $max_total = max(array_column(
                        array_map(fn($r) => ['total' => $r->total], $top_bases_data), 'total'
                    )); ?>
                    <div class="coverage-list">
                        <?php foreach ($top_bases_data as $i => $b): ?>
                        <div class="coverage-row">
                            <div class="coverage-name" title="<?= esc($b->nombre) ?>"><?= esc($b->nombre) ?></div>
                            <div class="coverage-bar-wrap">
                                <div class="coverage-bar <?= $bar_classes[$i % count($bar_classes)] ?>"
                                     style="width:<?= $max_total > 0 ? round(($b->total / $max_total) * 100) : 0 ?>%"></div>
                            </div>
                            <div class="coverage-num"><?= number_format($b->total) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-data"><i class="fas fa-chart-bar"></i> Sin datos por base.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Estado de la flota y las bases</span>
            </div>
            <div class="mini-stat-grid">
                <div class="mini-stat-titulo"><i class="fas fa-ambulance"></i> Móviles</div>
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= $moviles_op ?></div>
                        <div class="mini-stat-label">Móviles<br>operativos</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= $moviles_mantenimiento ?></div>
                        <div class="mini-stat-label">En<br>mantenimiento</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= $moviles_baja ?></div>
                        <div class="mini-stat-label">Fuera de<br>servicio</div>
                    </div>
                </div>
                <hr class="mini-sep">
                <div class="mini-stat-titulo"><i class="fas fa-hospital"></i> Bases</div>
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= $bases_activas ?></div>
                        <div class="mini-stat-label">Bases<br>activas</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= $bases_total ?></div>
                        <div class="mini-stat-label">Bases<br>registradas</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= $bases_total - $bases_activas ?></div>
                        <div class="mini-stat-label">Bases<br>inactivas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
</div>

<!-- ══════════════ INTERNACIÓN DOMICILIARIA ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'intdom' ? 'is-active' : '' ?>" data-pane="intdom">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-house-user"></i> INTERNACIÓN DOMICILIARIA
            <span class="hv-periodo">Pacientes registrados</span>
        </span>
        <a href="<?= base_url(route_to('identificacion_list')); ?>" class="hv-ver">Ver pacientes <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
    <div class="ml-kpi-cards">
        <?= $kpi('orange', 'fa-house-user', 'Internación domiciliaria', number_format($internacion_total), 'pacientes registrados',
                 $pct_adultos . '% adultos / ' . $pct_pediatrico . '% pediátrico', 'orange', 'fa-user-friends') ?>
        <?= $kpi('teal', 'fa-user', 'Adultos', number_format($internacion_adulto), $pct_adultos . '% del total') ?>
        <?= $kpi('blue', 'fa-child', 'Pediátricos', number_format($internacion_pediatrico), $pct_pediatrico . '% del total') ?>
        <?= $kpi('green', 'fa-hands-helping', 'Asistencias de int. dom.', number_format($asist_internacion),
                 $meses_nombres[$mes_desde] . ' – ' . $meses_nombres[$mes_hasta] . ' · ' . $ejercicio_actual) ?>
    </div>

    <div class="tab-grid">
        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Pacientes en internación domiciliaria</span>
            </div>
            <div class="mini-stat-grid">
                <div class="mini-stat-row">
                    <div class="mini-stat">
                        <div class="mini-stat-val teal"><?= $internacion_adulto ?></div>
                        <div class="mini-stat-label">Int. dom.<br>adultos</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val blue"><?= $internacion_pediatrico ?></div>
                        <div class="mini-stat-label">Int. dom.<br>pediátrico</div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-val orange"><?= $internacion_total ?></div>
                        <div class="mini-stat-label">Total<br>registrados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <div class="panel-header">
                <span class="panel-title">Adultos y pediátricos</span>
            </div>
            <div class="panel-body">
                <?php if ($internacion_total > 0): ?>
                    <div class="proporcion-barra" role="img"
                         aria-label="<?= $pct_adultos ?>% adultos, <?= $pct_pediatrico ?>% pediátricos">
                        <div class="proporcion-seg adultos" style="width:<?= $pct_adultos ?>%"></div>
                        <div class="proporcion-seg pediatricos" style="width:<?= $pct_pediatrico ?>%"></div>
                    </div>
                    <div class="proporcion-leyenda">
                        <span><i class="proporcion-punto adultos"></i> Adultos <strong><?= $pct_adultos ?>%</strong></span>
                        <span><i class="proporcion-punto pediatricos"></i> Pediátricos <strong><?= $pct_pediatrico ?>%</strong></span>
                    </div>
                <?php else: ?>
                    <div class="no-data"><i class="fas fa-house-user"></i> Sin pacientes registrados</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
    // Gráficos por pestaña: se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
    var graficos = {
        asis: function () {
            <?php if (array_sum($datos_mensuales) > 0): ?>
            const labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            const datos  = <?= json_encode(array_values($datos_mensuales)) ?>;

            new Chart(document.getElementById('chartMensual'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Asistencias',
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

        mostrar((inputTab && inputTab.value) || 'asis');
    })();
</script>
<?= $this->endSection() ?>
