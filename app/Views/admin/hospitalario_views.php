<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Hospitalario · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── HOSPITALARIO_PANEL SPECIFIC STYLES ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── FILTRO DE PERÍODO ── */
    .ml-filtro-bar {
        background: var(--white);
        border: 0.5px solid var(--border);
        border-radius: 12px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 18px;
    }
    .ml-filtro-group { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
    .ml-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .ml-filtro-select {
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 7px 12px;
        font-size: 13px;
        color: var(--text-main);
        background: var(--white);
        outline: none;
        height: 36px;
        max-width: 100%;
    }
    .ml-filtro-select:focus { border-color: var(--teal); }
    .ml-filtro-nota {
        font-size: 11.5px; color: var(--text-muted);
        display: flex; align-items: center; gap: 6px;
        margin-left: auto; align-self: center;
    }
    .ml-filtro-nota i { color: var(--teal); }

    /* ── BLOQUE DE MÓDULO ── */
    .hv-modulo {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 18px;
    }
    .hv-modulo-header {
        padding: 12px 18px;
        background: var(--navy);
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .hv-modulo-titulo {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .hv-modulo-titulo i { color: var(--teal); font-size: 17px; }
    .hv-periodo {
        font-size: 10.5px; font-weight: 700; letter-spacing: 0.4px;
        padding: 3px 10px; border-radius: 10px;
        background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.85);
        text-transform: uppercase;
    }
    .hv-ver {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12.5px; font-weight: 700;
        padding: 7px 14px; border-radius: 8px;
        background: var(--teal); color: #fff; text-decoration: none;
        transition: filter 0.15s;
    }
    .hv-ver:hover { filter: brightness(1.1); color: #fff; text-decoration: none; }
    .hv-modulo-body { padding: 16px 18px 18px; }

    /* ── KPIS ── */
    .ml-kpi-cards {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }
    .ml-kpi-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 12px;
        border: 1px solid #d0d7e0;
        display: flex; align-items: center; gap: 12px;
        min-height: 88px;
        box-sizing: border-box;
        min-width: 0;
    }
    .ml-kpi-content { display: flex; flex-direction: column; gap: 3px; min-width: 0; flex: 1; }
    .kpi-teal   { border-left: 6px solid #81e6d9; }
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-purple { border-left: 6px solid #d6bcfa; }
    .kpi-red    { border-left: 6px solid #feb2b2; }
    .kpi-navy   { border-left: 6px solid #a0aec0; }
    .ml-kpi-icon {
        width: 50px; height: 50px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 24px; flex-shrink: 0;
    }
    .kpi-teal   .ml-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue   .ml-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-purple .ml-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-red    .ml-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-navy   .ml-kpi-icon { background: #f7fafc; color: #4a5568; }
    .ml-kpi-label { font-size: 11.5px; font-weight: 700; color: #718096; text-transform: uppercase; letter-spacing: 0.3px; }
    .ml-kpi-value { font-size: 23px; font-weight: 700; color: #2d3748; line-height: 1.05; }
    .ml-kpi-sub {
        font-size: 11px; color: var(--text-muted);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ml-kpi-sub.sube { color: #2f855a; font-weight: 700; }
    .ml-kpi-sub.baja { color: #c53030; font-weight: 700; }

    /* ── GRÁFICOS ── */
    .hv-graficos {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 18px;
    }
    .ml-chart-box {
        display: flex; flex-direction: column; min-width: 0;
        background: #fafbfc; border: 0.5px solid var(--border); border-radius: 10px;
        padding: 12px;
    }
    .ml-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .ml-chart-canvas { position: relative; width: 100%; height: 260px; }
    .ml-sin-datos {
        height: 260px; display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: var(--text-muted); font-size: 13px; gap: 8px;
    }
    .ml-sin-datos i { font-size: 28px; opacity: 0.35; }

    /* ── ACCESOS RÁPIDOS ── */
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
        transition: transform 0.18s ease, box-shadow 0.2s;
    }
    .ql-card:hover { box-shadow: 0 8px 18px rgba(0,0,0,0.08); transform: translateY(-2px); text-decoration: none; color: var(--text-main); }
    .ql-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0; }
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

    /* ── PESTAÑAS POR MÓDULO ── */
    .ml-tabs {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 14px;
        padding: 6px;
        background: var(--white);
        border: 0.5px solid var(--border);
        border-radius: 12px;
    }
    .ml-tab {
        flex: 1 1 160px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 11px 14px;
        border: none; border-radius: 9px;
        background: transparent;
        font-size: 13px; font-weight: 700; letter-spacing: 0.3px;
        text-transform: uppercase;
        color: var(--text-muted);
        cursor: pointer;
        transition: background 0.15s, color 0.15s, box-shadow 0.15s;
    }
    .ml-tab:hover { background: #e9edf2; color: var(--text-main); }
    .ml-tab.is-active {
        background: var(--navy); color: #fff;
        box-shadow: 0 2px 6px rgba(14,42,77,0.18);
    }
    .ml-tab.is-active i { color: var(--teal); }
    .ml-tab-pane { display: none; }
    .ml-tab-pane.is-active { display: block; }

    @media (max-width: 1100px) {
        .ml-kpi-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
        .hv-graficos { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .hv-modulo-body { padding: 12px; }
        .ml-kpi-cards { grid-template-columns: 1fr; }
        .ml-filtro-group { flex: 1 1 100%; }
        .ml-filtro-select { width: 100%; }
        .ml-filtro-nota { margin-left: 0; }
        .ml-chart-canvas, .ml-sin-datos { height: 240px; }
        .ml-tab { flex: 1 1 45%; font-size: 11.5px; padding: 10px 8px; }
        .ql-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>

<?php
    $fmt = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $dec = function ($n, $d = 1) { return number_format((float) $n, $d, ',', '.'); };

    $textoPeriodo = $ejercicio . ($semestre !== '' ? ' · ' . $semestre : '');

    // Tarjeta KPI
    $kpi = function ($color, $icono, $label, $valor, $sub = '', $claseSub = '') {
        return '<div class="ml-kpi-card kpi-' . $color . '">
                    <div class="ml-kpi-icon"><i class="fas ' . $icono . '"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">' . esc($label) . '</div>
                        <div class="ml-kpi-value">' . $valor . '</div>'
                        . ($sub !== '' ? '<div class="ml-kpi-sub ' . $claseSub . '" title="' . esc(strip_tags($sub)) . '">' . $sub . '</div>' : '') .
                    '</div>
                </div>';
    };

    // Caja de gráfico (o mensaje si no hay datos)
    $grafico = function ($id, $titulo, $hayDatos) {
        return '<div class="ml-chart-box">
                    <div class="ml-chart-title">' . esc($titulo) . '</div>'
                    . ($hayDatos
                        ? '<div class="ml-chart-canvas"><canvas id="' . $id . '"></canvas></div>'
                        : '<div class="ml-sin-datos"><i class="fas fa-chart-bar"></i>Sin datos para el período</div>') .
                '</div>';
    };

    // Variación de guardia contra el mismo período del año anterior
    if ($gua['no_comparable']) {
        $subVariacion = 'carga distinta a ' . ($ejercicio - 1) . ': no comparable';
        $claseVariacion = '';
    } elseif ($gua['variacion'] === null) {
        $subVariacion = 'sin datos de ' . ($ejercicio - 1);
        $claseVariacion = '';
    } else {
        $flecha = $gua['variacion'] >= 0 ? '▲' : '▼';
        $subVariacion = $flecha . ' ' . $dec(abs($gua['variacion'])) . '% vs ' . ($ejercicio - 1);
        $claseVariacion = $gua['variacion'] >= 0 ? 'sube' : 'baja';
    }
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <strong>Hospitalario — Resumen</strong>
</div>

<!-- ── ACCESOS RÁPIDOS ── -->
<div class="ql-grid">
    <a href="<?= base_url(route_to('ambulatorio_list')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-walking"></i></div>
        <div><div class="ql-title">Ambulatorio</div><div class="ql-desc">Carta de servicio y RRHH</div></div>
    </a>
    <a href="<?= base_url(route_to('guardia_views')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-briefcase-medical"></i></div>
        <div><div class="ql-title">Guardia</div><div class="ql-desc">Atenciones por servicio</div></div>
    </a>
    <a href="<?= base_url(route_to('internacion_views')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-procedures"></i></div>
        <div><div class="ql-title">Internación</div><div class="ql-desc">Camas y rendimiento</div></div>
    </a>
    <a href="<?= base_url(route_to('quirofano_views')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-syringe"></i></div>
        <div><div class="ql-title">Quirófano</div><div class="ql-desc">Producción quirúrgica</div></div>
    </a>
    <a href="<?= base_url(route_to('inicio_views')); ?>" class="ql-card">
        <div class="ql-icon"><i class="fas fa-th-large"></i></div>
        <div><div class="ql-title">Panel inicio</div><div class="ql-desc">Volver al inicio</div></div>
    </a>
</div>

<!-- ── FILTRO DE PERÍODO ── -->
<form method="GET" action="<?= base_url(route_to('hospitalario_views')); ?>">
    <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
    <div class="ml-filtro-bar">
        <div class="ml-filtro-group">
            <span class="ml-filtro-label">Ejercicio</span>
            <select name="ejercicio" class="ml-filtro-select">
                <?php foreach ($ejercicios as $ej): ?>
                    <option value="<?= esc($ej) ?>" <?= $ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ml-filtro-group">
            <span class="ml-filtro-label">Semestre</span>
            <select name="semestre" class="ml-filtro-select">
                <option value="">Año completo</option>
                <?php foreach ($semestres as $s): ?>
                    <option value="<?= esc($s) ?>" <?= $semestre == $s ? 'selected' : '' ?>><?= esc($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ml-filtro-group">
            <span class="ml-filtro-label">&nbsp;</span>
            <div style="display:flex; gap:6px;">
                <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="<?= base_url(route_to('hospitalario_views')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar" style="height:36px; padding:0 14px;" title="Volver al último año cerrado">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </div>
        <div class="ml-filtro-nota">
            <i class="fas fa-info-circle"></i>
            Por defecto se muestra el último año cerrado. El semestre aplica a Guardia y Rendimiento.
        </div>
    </div>
</form>

<!-- ── PESTAÑAS ── -->
<div class="ml-tabs" role="tablist">
    <?php foreach (['amb' => ['fa-walking', 'Ambulatorio'], 'gua' => ['fa-briefcase-medical', 'Guardia'],
                    'int' => ['fa-procedures', 'Internación'], 'qui' => ['fa-syringe', 'Quirófano']] as $clave => [$icono, $titulo]): ?>
        <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab">
            <i class="fas <?= $icono ?>"></i> <?= $titulo ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- ══════════════ AMBULATORIO ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'amb' ? 'is-active' : '' ?>" data-pane="amb">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-walking"></i> AMBULATORIO · CARTA DE SERVICIO
            <span class="hv-periodo">Oferta vigente</span>
        </span>
        <a href="<?= base_url(route_to('ambulatorio_list')); ?>" class="hv-ver">Ver panel <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
        <div class="ml-kpi-cards">
            <?= $kpi('teal', 'fa-calendar-check', 'Turnos ofrecidos', $fmt($amb['turnos']), 'en la agenda vigente') ?>
            <?= $kpi('blue', 'fa-user-md', 'Profesionales en agenda', $fmt($amb['profesionales']), $fmt($amb['hospitales']) . ' hospital(es)') ?>
            <?= $kpi('purple', 'fa-stethoscope', 'Especialidades', $fmt($amb['especialidades']), 'con turnos cargados') ?>
            <?= $kpi('navy', 'fa-users', 'RRHH', $fmt($amb['rrhh']), 'profesionales registrados') ?>
        </div>
        <div class="hv-graficos">
            <?= $grafico('chartAmbEspecialidad', 'Turnos por especialidad', !empty($ambPorEspecialidad)) ?>
            <?= $grafico('chartAmbTipo', 'Turnos por tipo de profesional', !empty($ambPorTipo)) ?>
        </div>
    </div>
</div>

</div>

<!-- ══════════════ GUARDIA ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'gua' ? 'is-active' : '' ?>" data-pane="gua">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-briefcase-medical"></i> GUARDIA
            <span class="hv-periodo"><?= esc($textoPeriodo) ?></span>
            <span class="hv-periodo"><?= esc($gua['cobertura']['texto']) ?></span>
        </span>
        <a href="<?= base_url(route_to('guardia_views')) . '?anio=' . $ejercicio . ($semestre !== '' ? '&semestre=' . urlencode($semestre) : ''); ?>" class="hv-ver">Ver panel <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
        <div class="ml-kpi-cards">
            <?= $kpi('teal', 'fa-briefcase-medical', 'Atenciones', $fmt($gua['total']), $subVariacion, $claseVariacion) ?>
            <?= $kpi('blue', 'fa-hospital', 'Hospitales', $fmt($gua['hospitales']), 'con guardia informada') ?>
            <?= $kpi('purple', 'fa-chart-line', 'Promedio x hospital', $fmt($gua['promedio']), 'atenciones') ?>
            <?= $kpi('navy', 'fa-stethoscope', 'Servicio principal', '<span style="font-size:17px;">' . esc($gua['servicio_principal']) . '</span>', $fmt($gua['servicios']) . ' servicios con datos') ?>
        </div>
        <div class="hv-graficos">
            <?= $grafico('chartGuaHospitales', 'Top 5 hospitales', !empty($guaTopHospitales)) ?>
            <?= $grafico('chartGuaServicios', 'Atenciones por servicio', !empty($guaPorServicio)) ?>
        </div>
    </div>
</div>

</div>

<!-- ══════════════ INTERNACIÓN ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'int' ? 'is-active' : '' ?>" data-pane="int">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-procedures"></i> INTERNACIÓN
            <span class="hv-periodo">Rendimiento <?= esc($textoPeriodo) ?></span>
            <span class="hv-periodo"><?= esc($int['cobertura']) ?></span>
            <span class="hv-periodo">Camas: oferta vigente</span>
        </span>
        <a href="<?= base_url(route_to('internacion_views')) . '?tab=rend&ejercicio=' . $ejercicio . ($semestre !== '' ? '&semestre=' . urlencode($semestre) : ''); ?>" class="hv-ver">Ver panel <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
        <div class="ml-kpi-cards">
            <?= $kpi('navy', 'fa-sign-out-alt', 'Egresos', $fmt($int['egresos']), 'estada prom. ' . $dec($int['estada']) . ' días') ?>
            <?= $kpi('teal', 'fa-bed', '% Ocupacional', $dec($int['ocupacion']) . '%', 'mortalidad ' . $dec($int['mortalidad'], 2) . '%') ?>
            <?= $kpi('blue', 'fa-procedures', 'Camas disponibles', $fmt($int['camas']['disponibles']), 'UTI ' . $fmt($int['camas']['uti']) . ' · UTIN ' . $fmt($int['camas']['utin']) . ' · salud mental ' . $fmt($int['salud_mental'])) ?>
            <?= $kpi('red', 'fa-user-clock', 'Lista de espera', $fmt($int['espera']), 'pacientes quirúrgicos ' . $ejercicio) ?>
        </div>
        <div class="hv-graficos">
            <?= $grafico('chartIntEvolucion', 'Evolución de egresos y % ocupacional', !empty($intEvolucion)) ?>
            <?= $grafico('chartIntCamas', 'Camas disponibles por tipo', $int['camas']['disponibles'] > 0) ?>
        </div>
    </div>
</div>

</div>

<!-- ══════════════ QUIRÓFANO ══════════════ -->
<div class="ml-tab-pane <?= $tab === 'qui' ? 'is-active' : '' ?>" data-pane="qui">
<div class="hv-modulo">
    <div class="hv-modulo-header">
        <span class="hv-modulo-titulo">
            <i class="fas fa-syringe"></i> QUIRÓFANO
            <span class="hv-periodo"><?= esc($ejercicio) ?> · anual</span>
        </span>
        <a href="<?= base_url(route_to('quirofano_views')) . '?tab=hosp&ejercicio=' . $ejercicio; ?>" class="hv-ver">Ver panel <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="hv-modulo-body">
        <div class="ml-kpi-cards">
            <?= $kpi('navy', 'fa-syringe', 'Total cirugías', $fmt($qui['total']), $fmt($qui['hospitales']) . ' hospital(es) informaron') ?>
            <?= $kpi('red', 'fa-ambulance', 'Urgencia', $fmt($qui['urgencia']), $qui['total'] > 0 ? $dec($qui['urgencia'] * 100 / $qui['total']) . '% del total' : '') ?>
            <?= $kpi('teal', 'fa-calendar-check', 'Programadas', $fmt($qui['programadas']), $qui['total'] > 0 ? $dec($qui['programadas'] * 100 / $qui['total']) . '% del total' : '') ?>
            <?= $kpi('purple', 'fa-door-open', 'Quirófanos', $fmt($qui['quirofanos']), $qui['quirofanos'] > 0 ? $fmt(round($qui['total'] / $qui['quirofanos'])) . ' cirugías por quirófano' : '') ?>
        </div>
        <div class="hv-graficos">
            <?= $grafico('chartQuiHospitales', 'Top 5 hospitales · urgencia / programadas', !empty($quiTopHospitales)) ?>
            <?= $grafico('chartQuiComplejidad', 'Cirugías por complejidad', ($qui['alta'] + $qui['mediana'] + $qui['baja'] + $qui['desconocido']) > 0) ?>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    var ambPorEspecialidad = <?= json_encode($ambPorEspecialidad) ?>;
    var ambPorTipo         = <?= json_encode($ambPorTipo) ?>;
    var guaTopHospitales   = <?= json_encode($guaTopHospitales) ?>;
    var guaPorServicio     = <?= json_encode($guaPorServicio) ?>;
    var intEvolucion       = <?= json_encode($intEvolucion) ?>;
    var intCamas           = <?= json_encode($int['camas']) ?>;
    var qui                = <?= json_encode($qui) ?>;
    var quiTopHospitales   = <?= json_encode($quiTopHospitales) ?>;

    // Paleta pastel (misma línea que Móviles)
    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99'
    ];

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
    }

    function formatoNumero(n, decimales) {
        return Number(n).toLocaleString('es-AR', { maximumFractionDigits: decimales || 0 });
    }

    function existe(id) {
        return !!document.getElementById(id);
    }

    function acortar(texto, max) {
        texto = String(texto || '');
        return texto.length > max ? texto.slice(0, max - 1) + '…' : texto;
    }

    // ── Plugin: total en el centro de las donas ──
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function (chart) {
            if (chart.config.type !== 'doughnut') return;
            var ctx = chart.ctx;
            var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
            var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
            var total = chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = 'bold 13px sans-serif';
            ctx.fillStyle = '#555555';
            ctx.fillText('TOTAL', centerX, centerY - 11);
            ctx.font = 'bold 17px sans-serif';
            ctx.fillStyle = '#222222';
            ctx.fillText(formatoNumero(total), centerX, centerY + 11);
            ctx.restore();
        }
    };

    function crearDona(id, etiquetas, valores, colores) {
        if (!existe(id)) return;
        var seccionesConDatos = valores.filter(function (v) { return Number(v) > 0; }).length;

        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: colores || etiquetas.map(function (e, i) { return paleta[i % paleta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    spacing: seccionesConDatos > 1 ? 5 : 0,
                    offset: seccionesConDatos > 1 ? 8 : 0,
                    borderRadius: 5
                }]
            },
            plugins: [centerTextPlugin],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: esMovil() ? 'bottom' : 'right',
                        labels: { boxWidth: 12, padding: 8, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return ctx.label + ': ' + formatoNumero(ctx.parsed); }
                        }
                    },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function (value, ctx) {
                            var total = ctx.chart.data.datasets[0].data.reduce(function (a, b) { return a + b; }, 0);
                            if (total === 0) return '';
                            var pct = Math.round((value / total) * 100);
                            return pct > 4 ? pct + '%' : '';
                        }
                    }
                }
            }
        });
    }

    // Barras horizontales; con más de un dataset se apilan
    function crearBarrasH(id, etiquetas, datasets) {
        if (!existe(id)) return;
        var apiladas = datasets.length > 1;

        new Chart(document.getElementById(id), {
            type: 'bar',
            data: {
                labels: etiquetas.map(function (e) { return acortar(e, 28); }),
                datasets: datasets.map(function (d) {
                    return { label: d.label, data: d.data, backgroundColor: d.color, borderRadius: 4, maxBarThickness: 24 };
                })
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: apiladas, position: 'top', labels: { boxWidth: 12 } },
                    tooltip: {
                        callbacks: {
                            title: function (items) { return etiquetas[items[0].dataIndex]; },
                            label: function (ctx) { return (apiladas ? ctx.dataset.label + ': ' : '') + formatoNumero(ctx.parsed.x); }
                        }
                    },
                    datalabels: apiladas ? {
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v, ctx) {
                            // Solo se muestra si el tramo es visible
                            var max = Math.max.apply(null, ctx.chart.data.datasets[0].data.map(function (x, i) {
                                return ctx.chart.data.datasets.reduce(function (s, d) { return s + (d.data[i] || 0); }, 0);
                            }));
                            return v > max * 0.08 ? formatoNumero(v) : '';
                        }
                    } : {
                        anchor: 'end',
                        align: 'end',
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return formatoNumero(v); }
                    }
                },
                scales: {
                    x: { stacked: apiladas, beginAtZero: true, grace: apiladas ? 0 : '18%', ticks: { callback: function (v) { return formatoNumero(v); } } },
                    y: { stacked: apiladas, ticks: { autoSkip: false, font: { size: 10.5 } } }
                },
                layout: { padding: { right: 16 } }
            }
        });
    }

    // ══ Gráficos por pestaña ══
    var graficos = {};

    // ══ AMBULATORIO ══
    graficos.amb = function () {
    crearBarrasH(
        'chartAmbEspecialidad',
        ambPorEspecialidad.map(function (d) { return d.etiqueta; }),
        [{ label: 'Turnos', data: ambPorEspecialidad.map(function (d) { return parseInt(d.valor, 10); }), color: '#81e6d9' }]
    );
    crearDona(
        'chartAmbTipo',
        ambPorTipo.map(function (d) { return d.etiqueta; }),
        ambPorTipo.map(function (d) { return parseInt(d.valor, 10); })
    );
    };

    // ══ GUARDIA ══
    graficos.gua = function () {
    crearBarrasH(
        'chartGuaHospitales',
        guaTopHospitales.map(function (d) { return d.etiqueta; }),
        [{ label: 'Atenciones', data: guaTopHospitales.map(function (d) { return parseInt(d.valor, 10); }), color: '#90cdf4' }]
    );
    crearDona(
        'chartGuaServicios',
        guaPorServicio.map(function (d) { return d.etiqueta; }),
        guaPorServicio.map(function (d) { return parseInt(d.valor, 10); })
    );
    };

    // ══ INTERNACIÓN ══
    graficos.int = function () {
    if (existe('chartIntEvolucion')) {
        new Chart(document.getElementById('chartIntEvolucion'), {
            type: 'bar',
            data: {
                labels: intEvolucion.map(function (d) { return d.etiqueta; }),
                datasets: [
                    {
                        type: 'bar',
                        label: 'Egresos',
                        data: intEvolucion.map(function (d) { return parseInt(d.egresos, 10); }),
                        backgroundColor: '#90cdf4',
                        borderRadius: 4,
                        maxBarThickness: 40,
                        yAxisID: 'y',
                        order: 2
                    },
                    {
                        type: 'line',
                        label: '% Ocupacional',
                        data: intEvolucion.map(function (d) { return parseFloat(d.ocupacion); }),
                        borderColor: '#319795',
                        backgroundColor: '#319795',
                        tension: 0.3,
                        pointRadius: 4,
                        yAxisID: 'y1',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12 } },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ctx.dataset.label + ': ' + (ctx.dataset.yAxisID === 'y1'
                                    ? formatoNumero(ctx.parsed.y, 1) + '%'
                                    : formatoNumero(ctx.parsed.y));
                            }
                        }
                    }
                },
                scales: {
                    x:  { ticks: { font: { size: 10 } } },
                    y:  { beginAtZero: true, position: 'left', ticks: { callback: function (v) { return formatoNumero(v); } } },
                    y1: { beginAtZero: true, max: 100, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: function (v) { return v + '%'; } } }
                }
            }
        });
    }
    crearDona(
        'chartIntCamas',
        ['UTI', 'UTIN', 'Básicas'],
        [intCamas.uti || 0, intCamas.utin || 0, intCamas.basicas || 0],
        ['#feb2b2', '#d6bcfa', '#81e6d9']
    );
    };

    // ══ QUIRÓFANO ══
    graficos.qui = function () {
    crearBarrasH(
        'chartQuiHospitales',
        quiTopHospitales.map(function (d) { return d.etiqueta; }),
        [
            { label: 'Urgencia',    data: quiTopHospitales.map(function (d) { return parseInt(d.urgencia, 10) || 0; }),    color: '#feb2b2' },
            { label: 'Programadas', data: quiTopHospitales.map(function (d) { return parseInt(d.programadas, 10) || 0; }), color: '#81e6d9' }
        ]
    );
    (function () {
        var etiquetas = ['Alta', 'Mediana', 'Baja'];
        var valores   = [qui.alta || 0, qui.mediana || 0, qui.baja || 0];
        var colores   = ['#feb2b2', '#90cdf4', '#81e6d9'];
        if ((qui.desconocido || 0) > 0) {
            etiquetas.push('Desconocida');
            valores.push(qui.desconocido);
            colores.push('#cbd5e0');
        }
        crearDona('chartQuiComplejidad', etiquetas, valores, colores);
    })();
    };

    // ══ Pestañas ══
    // Los gráficos se dibujan la primera vez que se muestra su pestaña
    // (Chart.js no puede medir un canvas oculto).
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
            inputTab.value = tab;
            btnLimpiar.href = btnLimpiar.href.split('?')[0] + '?tab=' + tab;
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

        mostrar(inputTab.value || 'amb');
    })();
</script>
<?= $this->endSection() ?>
