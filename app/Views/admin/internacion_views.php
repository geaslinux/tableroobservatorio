<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Internación · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /* ── INTERNACION_PANEL SPECIFIC STYLES ── */
    .ml-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .ml-breadcrumb i { color: var(--teal); font-size: 13px; }
    .ml-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .ml-breadcrumb a:hover { color: var(--teal); }
    .ml-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    /* ── KPIS ── */
    .ml-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .ml-kpi-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 13px;
        border: 1px solid #d0d7e0;
        flex: 0 1 260px;
        max-width: 320px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 13px;
        height: 90px;
        box-sizing: border-box;
    }
    .ml-kpi-content {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
        flex: 1;
        align-items: center;
    }
    .kpi-teal   { border-left: 6px solid #81e6d9; }
    .kpi-blue   { border-left: 6px solid #90cdf4; }
    .kpi-purple { border-left: 6px solid #d6bcfa; }
    .kpi-red    { border-left: 6px solid #feb2b2; }
    .kpi-navy   { border-left: 6px solid #a0aec0; }

    .ml-kpi-icon {
        width: 60px; height: 60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; flex-shrink: 0;
    }
    .kpi-teal   .ml-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue   .ml-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-purple .ml-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-red    .ml-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-navy   .ml-kpi-icon { background: #f7fafc; color: #4a5568; }

    .ml-kpi-label { font-size: 14px; font-weight: 700; color: #718096; text-transform: uppercase; text-align: center; }
    .ml-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }

    .ml-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .ml-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .ml-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .ml-panel-title i { color: var(--teal); font-size: 17px; }
    .ml-panel-body { padding: 18px 20px; }
    .ml-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .ml-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
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
        transition: border-color 0.15s;
        height: 36px;
        max-width: 100%;
    }
    .ml-filtro-select:focus { border-color: var(--teal); }

    .ml-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 18px;
    }
    .ml-stats-table-wrap { overflow-x: auto; }
    .ml-stats-table {
        width: 100%; border-collapse: collapse; font-size: 12.5px;
    }
    .ml-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 10px; text-align: left; white-space: nowrap;
    }
    .ml-stats-table tbody td {
        padding: 7px 10px; border-bottom: 1px solid #eef0f3; color: var(--text-main);
    }
    .ml-stats-table tbody tr:hover { background: #f8f9fb; }
    .ml-stats-table .num { text-align: right; white-space: nowrap; }
    .ml-stats-table thead th.num { text-align: right; }
    .ml-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .ml-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }

    /* Rango de estándar ocupacional */
    .ml-badge {
        display: inline-block; padding: 2px 8px; border-radius: 10px;
        font-size: 11px; font-weight: 700; color: #2d3748; white-space: nowrap;
    }

    .ml-chart-box { display: flex; flex-direction: column; min-width: 0; }
    .ml-chart-canvas { position: relative; width: 100%; height: 330px; }
    .ml-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }

    .ml-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .ml-stats-header i { color: var(--teal); }
    .ml-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .ml-stats-panel.is-collapsed { margin-bottom: 12px; }
    .ml-stats-panel.is-collapsed .ml-stats-header { border-radius: 12px; border-bottom: none; }
    .ml-stats-panel.is-collapsed .ml-stats-body { display: none; }

    /* ── PESTAÑAS ── */
    .ml-tabs {
        display: flex; gap: 8px; flex-wrap: wrap;
        margin-bottom: 18px;
        padding: 6px;
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 12px;
    }
    .ml-tab {
        flex: 1 1 160px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 11px 14px;
        border: none; border-radius: 9px;
        background: transparent;
        font-size: 12.5px; font-weight: 700; letter-spacing: 0.3px;
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

    @media (max-width: 992px) {
        .ml-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .ml-panel-body { padding: 14px 12px; }
        .ml-stats-body { padding: 12px; }
        .ml-filtro-group { flex: 1 1 100%; }
        .ml-filtro-select { width: 100%; }
        .ml-kpi-card { flex: 1 1 100%; max-width: none; }
        .ml-chart-canvas { height: 280px; }
        .ml-tab { flex: 1 1 45%; font-size: 11.5px; padding: 10px 8px; }
    }
</style>

<?php
    $fmt  = function ($n) { return number_format((int) $n, 0, ',', '.'); };
    $dec  = function ($n, $d = 1) { return number_format((float) $n, $d, ',', '.'); };

    // Colores por rango de estándar ocupacional
    $coloresEstandar = [
        '≤ 30%'    => '#feb2b2',
        '31 - 59%' => '#f8c471',
        '60 - 84%' => '#82e0aa',
        '≥ 85%'    => '#f1948a',
    ];
    $badge = function ($estandar) use ($coloresEstandar) {
        $color = $coloresEstandar[$estandar] ?? '#e2e8f0';
        return '<span class="ml-badge" style="background:' . $color . ';">' . esc($estandar) . '</span>';
    };

    // Link de Excel de cada pestaña, con los filtros que acepta su módulo
    $linkExcel = function ($ruta, array $params) {
        $q = http_build_query(array_filter($params, 'strlen'));
        return base_url(route_to($ruta)) . ($q !== '' ? '?' . $q : '');
    };
    $pHospRegion = ['efector_id' => $filtro_efector, 'region' => $filtro_region];
    $pPeriodo    = $pHospRegion + ['ejercicio' => $filtro_ejercicio, 'semestre' => $filtro_semestre];

    $excel = [
        'camas'     => $linkExcel('lista_espera_export', ['efector_id' => $filtro_efector, 'ejercicio' => $filtro_ejercicio]),
        'salud'     => $linkExcel('salud_mental_camas_export', $pHospRegion),
        'rend'      => $linkExcel('rendimiento_hospitalario_export', $pPeriodo),
        'uti'       => $linkExcel('rendimiento_hospitalario_uti_export', $pPeriodo),
        'materno'   => $linkExcel('rh_materno_export', $pPeriodo),
        'capacidad' => $linkExcel('capacidad_camas_export', $pHospRegion),
    ];

    $tabs = [
        'camas'     => ['icono' => 'fa-bed',            'titulo' => 'Camas hospitalarias'],
        'salud'     => ['icono' => 'fa-brain',          'titulo' => 'Salud mental'],
        'rend'      => ['icono' => 'fa-chart-line',     'titulo' => 'Rendimiento'],
        'uti'       => ['icono' => 'fa-heartbeat',      'titulo' => 'Rendimiento UTI'],
        'materno'   => ['icono' => 'fa-baby-carriage',  'titulo' => 'RH Materno'],
        'capacidad' => ['icono' => 'fa-procedures',     'titulo' => 'Capacidad de camas'],
    ];

    // Botón de ocultar/mostrar estadísticas (se repite en cada pestaña)
    $headerStats = function ($titulo) {
        return '<div class="ml-stats-header">
                    <span><i class="fas fa-chart-pie"></i> ' . esc($titulo) . '</span>
                    <button type="button" class="bl-btn ghost sm" data-stats-toggle title="Mostrar/ocultar estadísticas">
                        <i class="fas fa-eye-slash"></i> <span>Ocultar estadísticas</span>
                    </button>
                </div>';
    };

    $alto = function ($cantidad) { return max(330, $cantidad * 26 + 40); };
?>

<!-- BREADCRUMB -->
<div class="ml-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('hospitalario_views')); ?>">Hospitalario</a> ›
    <strong>Internación — Estadísticas</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="ml-panel">
    <div class="ml-panel-header">
        <span class="ml-panel-title">
            <i class="fas fa-procedures"></i> INTERNACIÓN
        </span>
    </div>

    <div class="ml-panel-body">

        <!-- ── TOOLBAR ── -->
        <div class="ml-toolbar">
            <a href="<?= base_url(route_to('hospitalario_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <?php foreach ($excel as $clave => $url): ?>
                <a href="<?= $url ?>" class="bl-btn green" data-tab-only="<?= $clave ?>" <?= $tab !== $clave ? 'style="display:none;"' : '' ?>>
                    <i class="fas fa-file-excel"></i> Descargar Excel
                </a>
            <?php endforeach; ?>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('internacion_views')); ?>" id="searchForm">
            <input type="hidden" name="tab" id="inputTab" value="<?= esc($tab) ?>">
            <div class="ml-filtro-bar">
                <!-- HOSPITAL -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Hospital</span>
                    <select name="efector_id" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($efectores as $e): ?>
                            <option value="<?= esc($e['efector_id']) ?>" <?= $filtro_efector == $e['efector_id'] ? 'selected' : '' ?>><?= esc($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- REGIÓN -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">Región</span>
                    <select name="region" class="ml-filtro-select">
                        <option value="">Todas</option>
                        <?php foreach ($regiones as $r): ?>
                            <option value="<?= esc($r) ?>" <?= $filtro_region == $r ? 'selected' : '' ?>><?= esc($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- EJERCICIO -->
                <div class="ml-filtro-group" data-filtro-tabs="camas rend uti materno">
                    <span class="ml-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= esc($ej) ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= esc($ej) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- SEMESTRE -->
                <div class="ml-filtro-group" data-filtro-tabs="rend uti materno">
                    <span class="ml-filtro-label">Semestre</span>
                    <select name="semestre" class="ml-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($semestres as $s): ?>
                            <option value="<?= esc($s) ?>" <?= $filtro_semestre == $s ? 'selected' : '' ?>><?= esc($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- ACCIONES -->
                <div class="ml-filtro-group">
                    <span class="ml-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;" title="Filtrar">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('internacion_views')) . '?tab=' . esc($tab, 'url'); ?>" class="bl-btn ghost" id="btnLimpiar"
                           style="height:36px; padding:0 14px;" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- ── PESTAÑAS ── -->
        <div class="ml-tabs" role="tablist">
            <?php foreach ($tabs as $clave => $t): ?>
                <button type="button" class="ml-tab <?= $tab === $clave ? 'is-active' : '' ?>" data-tab="<?= $clave ?>" role="tab">
                    <i class="fas <?= $t['icono'] ?>"></i> <?= esc($t['titulo']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ══════════════ 1. CAMAS HOSPITALARIAS (lista de espera) ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'camas' ? 'is-active' : '' ?>" data-pane="camas">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-user-clock"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Pacientes en espera</div>
                        <div class="ml-kpi-value"><?= $fmt($esperaKpi['pacientes'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Complejidad alta</div>
                        <div class="ml-kpi-value"><?= $fmt($esperaKpi['alta'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-procedures"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Complejidad mediana</div>
                        <div class="ml-kpi-value"><?= $fmt($esperaKpi['mediana'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-band-aid"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Complejidad baja</div>
                        <div class="ml-kpi-value"><?= $fmt($esperaKpi['baja'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE LISTA DE ESPERA') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Pacientes por hospital / especialidad</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th>Especialidad</th><th class="num">Pacientes</th><th class="num">Alta</th><th class="num">Med.</th><th class="num">Baja</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($esperaFilas)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin pacientes en espera para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($esperaFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td><?= esc($f['especialidad']) ?></td>
                                    <td class="num"><?= $fmt($f['pacientes']) ?></td>
                                    <td class="num"><?= $fmt($f['alta']) ?></td>
                                    <td class="num"><?= $fmt($f['mediana']) ?></td>
                                    <td class="num"><?= $fmt($f['baja']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($esperaFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td class="num"><?= $fmt($esperaKpi['pacientes']) ?></td>
                                    <td class="num"><?= $fmt($esperaKpi['alta']) ?></td>
                                    <td class="num"><?= $fmt($esperaKpi['mediana']) ?></td>
                                    <td class="num"><?= $fmt($esperaKpi['baja']) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Pacientes en espera por hospital</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(count($esperaPorHospital)) ?>px;"><canvas id="chartEsperaHospital"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Complejidad quirúrgica</div>
                        <div class="ml-chart-canvas"><canvas id="chartEsperaComplejidad"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ 2. SALUD MENTAL CAMAS ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'salud' ? 'is-active' : '' ?>" data-pane="salud">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-brain"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Total camas</div>
                        <div class="ml-kpi-value"><?= $fmt($saludKpi['total'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-user"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Adultos</div>
                        <div class="ml-kpi-value"><?= $fmt($saludKpi['adultos'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-child"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Pediátricas</div>
                        <div class="ml-kpi-value"><?= $fmt($saludKpi['pediatricas'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-landmark"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Públicas</div>
                        <div class="ml-kpi-value"><?= $fmt($saludKpi['publicas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE SALUD MENTAL') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Camas por hospital</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th>Modalidad</th><th>Tipo</th><th class="num">Adultos</th><th class="num">Pediát.</th><th class="num">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($saludFilas)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin datos para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($saludFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td><?= esc($f['modalidad']) ?></td>
                                    <td><?= esc($f['tipo']) ?></td>
                                    <td class="num"><?= $fmt($f['adultos']) ?></td>
                                    <td class="num"><?= $fmt($f['pediatricas']) ?></td>
                                    <td class="num"><strong><?= $fmt($f['total']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($saludFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="3">Total</td>
                                    <td class="num"><?= $fmt($saludKpi['adultos']) ?></td>
                                    <td class="num"><?= $fmt($saludKpi['pediatricas']) ?></td>
                                    <td class="num"><?= $fmt($saludKpi['total']) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Camas por modalidad</div>
                        <div class="ml-chart-canvas"><canvas id="chartSaludModalidad"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Público / privado</div>
                        <div class="ml-chart-canvas"><canvas id="chartSaludTipo"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ 3 y 4. RENDIMIENTO HOSPITALARIO (general y UTI) ══════════════ -->
        <?php foreach (['rend' => ['datos' => $rend, 'titulo' => 'RENDIMIENTO HOSPITALARIO'],
                        'uti'  => ['datos' => $uti,  'titulo' => 'RENDIMIENTO HOSPITALARIO UTI']] as $clave => $bloque):
            $k = $bloque['datos']['kpi'];
            $filasRend = $bloque['datos']['filas'];
        ?>
        <div class="ml-tab-pane <?= $tab === $clave ? 'is-active' : '' ?>" data-pane="<?= $clave ?>">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Egresos</div>
                        <div class="ml-kpi-value"><?= $fmt($k['egresos'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-bed"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">% Ocupacional</div>
                        <div class="ml-kpi-value"><?= $dec($k['ocupacion'] ?? 0) ?>%</div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-blue">
                    <div class="ml-kpi-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Prom. días estada</div>
                        <div class="ml-kpi-value"><?= $dec($k['estada'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-heart-broken"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Tasa mortalidad</div>
                        <div class="ml-kpi-value"><?= $dec($k['mortalidad'] ?? 0, 2) ?>%</div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE ' . $bloque['titulo']) ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Indicadores por hospital</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th class="num">Egresos</th><th class="num">% Ocup.</th><th>Estándar</th><th class="num">Estada</th><th class="num">Mort. %</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filasRend)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin datos cargados para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($filasRend as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td class="num"><?= $fmt($f['egresos']) ?></td>
                                    <td class="num"><?= $dec($f['ocupacion']) ?>%</td>
                                    <td><?= $badge($f['estandar']) ?></td>
                                    <td class="num"><?= $dec($f['estada']) ?></td>
                                    <td class="num"><?= $dec($f['mortalidad'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($filasRend)): ?>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td class="num"><?= $fmt($k['egresos']) ?></td>
                                    <td class="num"><?= $dec($k['ocupacion']) ?>%</td>
                                    <td></td>
                                    <td class="num"><?= $dec($k['estada']) ?></td>
                                    <td class="num"><?= $dec($k['mortalidad'], 2) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">% ocupacional por hospital</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(count($filasRend)) ?>px;"><canvas id="chartOcupacion_<?= $clave ?>"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Hospitales por estándar ocupacional</div>
                        <div class="ml-chart-canvas"><canvas id="chartEstandar_<?= $clave ?>"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- ══════════════ 5. RH MATERNO ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'materno' ? 'is-active' : '' ?>" data-pane="materno">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-sign-in-alt"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Ingresos</div>
                        <div class="ml-kpi-value"><?= $fmt($maternoKpi['ingresos'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-sign-out-alt"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Egresos</div>
                        <div class="ml-kpi-value"><?= $fmt($maternoKpi['egresos'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-bed"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">% Ocupacional</div>
                        <div class="ml-kpi-value"><?= $dec($maternoKpi['ocupacion'] ?? 0) ?>%</div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-heart-broken"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Tasa mortalidad</div>
                        <div class="ml-kpi-value"><?= $dec($maternoKpi['mortalidad'] ?? 0, 2) ?>%</div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE RH MATERNO') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Indicadores por servicio / sector</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Servicio</th><th>Sector</th><th class="num">Egresos</th><th class="num">% Ocup.</th><th class="num">Estada</th><th class="num">Mort. %</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($maternoFilas)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin datos para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($maternoFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['servicio']) ?></td>
                                    <td><?= esc($f['sector']) ?></td>
                                    <td class="num"><?= $fmt($f['egresos']) ?></td>
                                    <td class="num"><span class="ml-badge" style="background:<?= $coloresEstandar[$f['estandar']] ?>;"><?= $dec($f['ocupacion']) ?>%</span></td>
                                    <td class="num"><?= $dec($f['estada']) ?></td>
                                    <td class="num"><?= $dec($f['mortalidad'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($maternoFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td class="num"><?= $fmt($maternoKpi['egresos']) ?></td>
                                    <td class="num"><?= $dec($maternoKpi['ocupacion']) ?>%</td>
                                    <td class="num"><?= $dec($maternoKpi['estada']) ?></td>
                                    <td class="num"><?= $dec($maternoKpi['mortalidad'], 2) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">% ocupacional por sector</div>
                        <div class="ml-chart-canvas" style="height:<?= $alto(count($maternoFilas)) ?>px;"><canvas id="chartMaternoOcupacion"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Egresos por servicio</div>
                        <div class="ml-chart-canvas"><canvas id="chartMaternoServicio"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════ 6. CAPACIDAD DE CAMAS ══════════════ -->
        <div class="ml-tab-pane <?= $tab === 'capacidad' ? 'is-active' : '' ?>" data-pane="capacidad">
            <div class="ml-kpi-cards">
                <div class="ml-kpi-card kpi-navy">
                    <div class="ml-kpi-icon"><i class="fas fa-procedures"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Camas disponibles</div>
                        <div class="ml-kpi-value"><?= $fmt($capacidadKpi['disponibles'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-red">
                    <div class="ml-kpi-icon"><i class="fas fa-heartbeat"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">UTI</div>
                        <div class="ml-kpi-value"><?= $fmt($capacidadKpi['uti'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-purple">
                    <div class="ml-kpi-icon"><i class="fas fa-notes-medical"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">UTIN</div>
                        <div class="ml-kpi-value"><?= $fmt($capacidadKpi['utin'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="ml-kpi-card kpi-teal">
                    <div class="ml-kpi-icon"><i class="fas fa-bed"></i></div>
                    <div class="ml-kpi-content">
                        <div class="ml-kpi-label">Cuidados básicos</div>
                        <div class="ml-kpi-value"><?= $fmt($capacidadKpi['basicas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <div class="ml-panel ml-stats-panel" data-stats-panel>
                <?= $headerStats('ESTADÍSTICAS DE CAPACIDAD DE CAMAS') ?>
                <div class="ml-stats-body">
                    <div class="ml-stats-table-wrap">
                        <div class="ml-chart-title" style="text-align:left;">Camas por hospital</div>
                        <table class="ml-stats-table">
                            <thead>
                                <tr><th>Hospital</th><th>Tipo</th><th class="num">UTI</th><th class="num">UTIN</th><th class="num">Básicas</th><th class="num">Total</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($capacidadFilas)): ?>
                                    <tr><td colspan="6" class="ml-stats-vacio">Sin datos para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($capacidadFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['hospital']) ?></td>
                                    <td><?= esc($f['tipo']) ?></td>
                                    <td class="num"><?= $fmt($f['uti']) ?></td>
                                    <td class="num"><?= $fmt($f['utin']) ?></td>
                                    <td class="num"><?= $fmt($f['basicas']) ?></td>
                                    <td class="num"><strong><?= $fmt($f['disponibles']) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($capacidadFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td class="num"><?= $fmt($capacidadKpi['uti']) ?></td>
                                    <td class="num"><?= $fmt($capacidadKpi['utin']) ?></td>
                                    <td class="num"><?= $fmt($capacidadKpi['basicas']) ?></td>
                                    <td class="num"><?= $fmt($capacidadKpi['disponibles']) ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Camas por región</div>
                        <div class="ml-chart-canvas"><canvas id="chartCapacidadRegion"></canvas></div>
                    </div>

                    <div class="ml-chart-box">
                        <div class="ml-chart-title">Público / privado</div>
                        <div class="ml-chart-canvas"><canvas id="chartCapacidadTipo"></canvas></div>
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

    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    var esperaKpi          = <?= json_encode($esperaKpi) ?>;
    var esperaPorHospital  = <?= json_encode($esperaPorHospital) ?>;
    var saludPorModalidad  = <?= json_encode($saludPorModalidad) ?>;
    var saludPorTipo       = <?= json_encode($saludPorTipo) ?>;
    var rendimiento        = {
        rend: <?= json_encode($rend) ?>,
        uti:  <?= json_encode($uti) ?>
    };
    var maternoFilas       = <?= json_encode($maternoFilas) ?>;
    var maternoPorServicio = <?= json_encode($maternoPorServicio) ?>;
    var capacidadPorRegion = <?= json_encode($capacidadPorRegion) ?>;
    var capacidadPorTipo   = <?= json_encode($capacidadPorTipo) ?>;
    var coloresEstandar    = <?= json_encode($coloresEstandar) ?>;

    // Paleta pastel (misma línea que Móviles)
    var paleta = [
        '#81e6d9', '#90cdf4', '#d6bcfa', '#feb2b2', '#f8c471', '#82e0aa',
        '#f1948a', '#85c1e9', '#ce93d8', '#f9e79f', '#76d7c4', '#edbb99',
        '#bb8fce', '#7fb3d5', '#b5ead7', '#e6b0aa', '#c7ceea', '#ffdac1'
    ];

    // Colores fijos por categoría
    var coloresTipo = { 'PUBLICO': '#81e6d9', 'PRIVADO': '#d6bcfa', 'SIN DATO': '#cbd5e0' };

    function esMovil() {
        return window.matchMedia('(max-width: 600px)').matches;
    }

    function formatoNumero(n, decimales) {
        return Number(n).toLocaleString('es-AR', { maximumFractionDigits: decimales || 0 });
    }

    // ── Colapsar / expandir paneles de estadísticas ──
    document.querySelectorAll('[data-stats-panel]').forEach(function (panel) {
        var btn   = panel.querySelector('[data-stats-toggle]');
        var icono = btn.querySelector('i');
        var texto = btn.querySelector('span');

        btn.addEventListener('click', function () {
            var colapsado = panel.classList.toggle('is-collapsed');
            icono.className   = colapsado ? 'fas fa-eye' : 'fas fa-eye-slash';
            texto.textContent = colapsado ? 'Mostrar estadísticas' : 'Ocultar estadísticas';
        });
    });

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
            ctx.font = 'bold 16px sans-serif';
            ctx.fillStyle = '#555555';
            ctx.fillText('TOTAL', centerX, centerY - 13);
            ctx.font = 'bold 20px sans-serif';
            ctx.fillStyle = '#222222';
            ctx.fillText(formatoNumero(total), centerX, centerY + 14);
            ctx.restore();
        }
    };

    function crearDona(canvasId, etiquetas, valores, colores) {
        var seccionesConDatos = valores.filter(function (v) { return Number(v) > 0; }).length;

        new Chart(document.getElementById(canvasId), {
            type: 'doughnut',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: colores || etiquetas.map(function (e, i) { return paleta[i % paleta.length]; }),
                    borderColor: '#ffffff',
                    borderWidth: 0,
                    spacing: seccionesConDatos > 1 ? 6 : 0,
                    offset: seccionesConDatos > 1 ? 10 : 0,
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
                        font: { weight: 'bold', size: 12 },
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

    // Barras horizontales (una barra por fila)
    function crearBarrasH(canvasId, etiquetas, valores, colores, sufijo) {
        sufijo = sufijo || '';
        new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: etiquetas,
                datasets: [{
                    data: valores,
                    backgroundColor: colores,
                    borderRadius: 4,
                    maxBarThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) { return formatoNumero(ctx.parsed.x, 1) + sufijo; }
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        color: '#333333',
                        font: { weight: 'bold', size: 10 },
                        formatter: function (v) { return formatoNumero(v, 1) + sufijo; }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grace: '15%', ticks: { callback: function (v) { return formatoNumero(v) + sufijo; } } },
                    y: { ticks: { autoSkip: false, font: { size: 10 } } }
                },
                layout: { padding: { right: 20 } }
            }
        });
    }

    // Barras apiladas: series = { nombreSerie: { etiqueta: valor } }
    function crearBarrasApiladas(canvasId, etiquetas, series, colores) {
        var nombres = Object.keys(series);
        new Chart(document.getElementById(canvasId), {
            type: 'bar',
            data: {
                labels: etiquetas,
                datasets: nombres.map(function (n, i) {
                    return {
                        label: n,
                        backgroundColor: (colores && colores[n]) || paleta[i % paleta.length],
                        borderRadius: 4,
                        maxBarThickness: 60,
                        data: etiquetas.map(function (e) { return series[n][e] || 0; })
                    };
                })
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    datalabels: {
                        color: '#333333',
                        font: { weight: 'bold', size: 11 },
                        formatter: function (v) { return v > 0 ? formatoNumero(v) : ''; }
                    }
                },
                scales: {
                    x: { stacked: true, ticks: { autoSkip: false } },
                    y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                },
                layout: { padding: 10 }
            }
        });
    }

    // ══ Gráficos por pestaña ══
    var graficos = {
        // 1. Camas hospitalarias (lista de espera)
        camas: function () {
            crearBarrasH(
                'chartEsperaHospital',
                esperaPorHospital.map(function (h) { return h.hospital; }),
                esperaPorHospital.map(function (h) { return parseInt(h.pacientes, 10); }),
                '#90cdf4'
            );

            // Los pacientes que no tienen complejidad cargada van como "Sin clasificar"
            var clasificados = (esperaKpi.alta || 0) + (esperaKpi.mediana || 0) + (esperaKpi.baja || 0);
            var sinClasificar = Math.max(0, (esperaKpi.pacientes || 0) - clasificados);
            var etiquetas = ['Alta', 'Mediana', 'Baja'];
            var valores   = [esperaKpi.alta || 0, esperaKpi.mediana || 0, esperaKpi.baja || 0];
            var colores   = ['#feb2b2', '#90cdf4', '#81e6d9'];
            if (sinClasificar > 0) {
                etiquetas.push('Sin clasificar');
                valores.push(sinClasificar);
                colores.push('#cbd5e0');
            }
            crearDona('chartEsperaComplejidad', etiquetas, valores, colores);
        },

        // 2. Salud mental
        salud: function () {
            var modalidades = [];
            Object.keys(saludPorModalidad).forEach(function (tipo) {
                Object.keys(saludPorModalidad[tipo]).forEach(function (m) {
                    if (modalidades.indexOf(m) === -1) modalidades.push(m);
                });
            });
            crearBarrasApiladas('chartSaludModalidad', modalidades, saludPorModalidad, coloresTipo);

            var tipos = Object.keys(saludPorTipo);
            crearDona('chartSaludTipo', tipos, tipos.map(function (t) { return saludPorTipo[t]; }),
                tipos.map(function (t, i) { return coloresTipo[t] || paleta[i % paleta.length]; }));
        },

        // 5. RH Materno
        materno: function () {
            crearBarrasH(
                'chartMaternoOcupacion',
                maternoFilas.map(function (f) { return f.sector; }),
                maternoFilas.map(function (f) { return f.ocupacion; }),
                maternoFilas.map(function (f) { return coloresEstandar[f.estandar] || '#cbd5e0'; }),
                '%'
            );

            var servicios = Object.keys(maternoPorServicio);
            crearDona('chartMaternoServicio', servicios, servicios.map(function (s) { return maternoPorServicio[s]; }));
        },

        // 6. Capacidad de camas
        capacidad: function () {
            var regiones = Object.keys(capacidadPorRegion);
            var series = { 'UTI': {}, 'UTIN': {}, 'Básicas': {} };
            regiones.forEach(function (r) {
                series['UTI'][r]     = capacidadPorRegion[r].uti;
                series['UTIN'][r]    = capacidadPorRegion[r].utin;
                series['Básicas'][r] = capacidadPorRegion[r].basicas;
            });
            crearBarrasApiladas('chartCapacidadRegion', regiones, series, { 'UTI': '#feb2b2', 'UTIN': '#d6bcfa', 'Básicas': '#81e6d9' });

            var tipos = Object.keys(capacidadPorTipo);
            crearDona('chartCapacidadTipo', tipos, tipos.map(function (t) { return capacidadPorTipo[t]; }),
                tipos.map(function (t, i) { return coloresTipo[t] || paleta[i % paleta.length]; }));
        }
    };

    // 3 y 4. Rendimiento (general y UTI) comparten gráficos
    ['rend', 'uti'].forEach(function (clave) {
        graficos[clave] = function () {
            var datos = rendimiento[clave];
            crearBarrasH(
                'chartOcupacion_' + clave,
                datos.filas.map(function (f) { return f.hospital; }),
                datos.filas.map(function (f) { return f.ocupacion; }),
                datos.filas.map(function (f) { return coloresEstandar[f.estandar] || '#cbd5e0'; }),
                '%'
            );
            crearDona(
                'chartEstandar_' + clave,
                datos.estandares.map(function (e) { return e.estandar; }),
                datos.estandares.map(function (e) { return e.cantidad; }),
                datos.estandares.map(function (e) { return coloresEstandar[e.estandar]; })
            );
        };
    });

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
            // Excel de la pestaña activa
            document.querySelectorAll('[data-tab-only]').forEach(function (el) {
                el.style.display = el.dataset.tabOnly === tab ? '' : 'none';
            });
            // Filtros que aplican a la pestaña activa
            document.querySelectorAll('[data-filtro-tabs]').forEach(function (el) {
                el.style.display = el.dataset.filtroTabs.split(' ').indexOf(tab) !== -1 ? '' : 'none';
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

        mostrar(inputTab.value || 'camas');
    })();
</script>
<?= $this->endSection() ?>
