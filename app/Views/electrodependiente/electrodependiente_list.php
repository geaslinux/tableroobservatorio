<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> ELECTRODEPENDIENTES <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="/css/ejesa.css?v=<?= @filemtime(FCPATH . 'css/ejesa.css') ?: '1' ?>">

<style>
    /* Listado de pacientes electrodependientes, SOLO CONSULTA (sin alta, edición ni baja).
       Usa la base visual de ejesa.css (panel EJESA). */
    .ep-filtros .ej-filtro-fila { grid-template-columns: 1fr 1fr; }
    .ep-filtros .ep-buscar { grid-column: 1 / -1; }
    .ep-buscar-input { position: relative; }
    .ep-buscar-input i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px; }
    .ep-buscar-input input { padding-left: 30px !important; }
    @media (min-width: 768px) {
        .ep-filtros .ej-filtro-fila { grid-template-columns: 2fr repeat(6, minmax(0, 1fr)) auto; }
        .ep-filtros .ep-buscar { grid-column: auto; }
        .ep-tabla td.ep-paciente { min-width: 230px; }
        .ep-tabla td.ep-servicio { min-width: 150px; }
        .ep-tabla .ep-acciones { flex-wrap: nowrap; }
    }

    /* Aviso de novedades desde la última visita */
    .ep-novedades {
        display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;
        background: #fffaf0; border: 1px solid #f6d9a8; border-left: 5px solid #F2A272; border-radius: 10px;
        padding: 10px 12px; margin-bottom: 14px; font-size: 13px; color: #5b3a12;
    }
    .ep-novedades .chips { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .ep-chip-nuevo      { background: #e3f6ec; color: #2f855a; }
    .ep-chip-modificado { background: #fef6dc; color: #a86b00; }

    /* Tabla */
    .ep-paciente strong { display: block; color: #13304d; font-size: 13px; }
    .ep-paciente small { display: block; color: var(--text-muted); font-size: 11.5px; line-height: 1.5; }
    .ep-marca-fila { display: inline-block; margin-top: 3px; }
    .ep-tabla tr.es-nuevo td:first-child      { box-shadow: inset 4px 0 0 #5BB8A6; }
    .ep-tabla tr.es-modificado td:first-child { box-shadow: inset 4px 0 0 #E9B949; }
    .ep-tabla tr.es-nuevo td      { background: #f4fbf8; }
    .ep-tabla tr.es-modificado td { background: #fffbef; }
    .ep-tabla tr.desactivado td { color: #9aa5b5; background: #f7f8fa; }
    .ep-tabla tr.desactivado .ep-paciente strong { color: #9aa5b5; }
    .ep-tabla tr.fila-highlight td { background: #fff8cc !important; }
    .ep-equipo { padding: 3px 0 3px 8px; border-left: 3px solid #cfe0f3; margin-bottom: 4px; font-size: 12px; line-height: 1.45; text-align: left; }
    .ep-equipo strong { color: #13304d; font-weight: 700; }
    .ep-equipo small { display: block; color: var(--text-muted); font-size: 11px; }
    .ep-servicio { font-size: 12px; line-height: 1.5; }
    .ep-servicio .nro { font-weight: 700; font-variant-numeric: tabular-nums; }
    .ep-celda { min-width: 0; }
    .ep-celda small { display: block; color: var(--text-muted); font-size: 11.5px; line-height: 1.45; }
    .ep-acciones { display: flex; gap: 4px; flex-wrap: wrap; justify-content: flex-end; }
    @media (max-width: 767px) {
        .ep-tabla.tarjetas td .ep-celda { text-align: right; max-width: 60%; }
        .ep-tabla.tarjetas td .ep-equipo { text-align: left; }
        .ep-tabla.tarjetas td[data-label=""] { justify-content: flex-start; }
        .ep-acciones { justify-content: flex-start; }
    }
    .ep-acciones a, .ep-acciones button {
        display: inline-flex; align-items: center; gap: 4px; height: 28px; padding: 0 9px; border-radius: 7px;
        font-size: 11.5px; font-weight: 700; border: 1px solid var(--border); background: #fff; color: var(--text-main);
        cursor: pointer; text-decoration: none; white-space: nowrap; font-family: inherit;
    }
    .ep-acciones a:hover, .ep-acciones button:hover { border-color: var(--ej-secundario); color: var(--ej-secundario); }

    /* Mapa */
    #mapa-electro { height: 420px; width: 100%; border-radius: 10px; z-index: 1; }
    @media (min-width: 768px) { #mapa-electro { height: 560px; } }
    /* Fondo en blanco y negro (como el mapa de Bases): solo las teselas; departamentos y pines conservan su color */
    #mapa-electro .leaflet-tile-pane { filter: grayscale(100%); }
    .ep-leyenda { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; font-size: 12px; color: var(--text-muted); margin-bottom: 10px; }
    .ep-leyenda span { display: inline-flex; align-items: center; gap: 5px; font-weight: 600; }
    .ep-leyenda i.punto { width: 11px; height: 11px; border-radius: 50%; display: inline-block; }
    .ep-capa { display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--border); background: #fff; border-radius: 999px; padding: 3px 10px; font: inherit; font-size: 12px; font-weight: 700; color: var(--text-main); cursor: pointer; }
    .ep-capa small { color: var(--text-muted); font-weight: 600; }
    .ep-grupo { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .ep-capa[aria-pressed="true"] { background: #e8f0fb; border-color: var(--ej-secundario); box-shadow: inset 0 0 0 1px var(--ej-secundario); }
    .ep-grupo.con-seleccion .ep-capa[aria-pressed="false"] { opacity: .45; }
    #col-mapa.ep-cargando .ep-leyenda { opacity: .6; pointer-events: none; cursor: progress; }
    #epListado, #epKpis { transition: opacity .15s; }
    #epListado.ep-buscando, #epKpis.ep-buscando { opacity: .5; }
    .ep-buscar-input i.ep-spinner { left: auto; right: 30px; display: none; }
    .ep-buscar-input.buscando i.ep-spinner { display: inline-block; }
    .ep-limpiar { border: 0; background: none; font: inherit; font-size: 12px; font-weight: 700; color: var(--ej-secundario); cursor: pointer; text-decoration: underline; padding: 3px 4px; }
    .ep-capa:disabled { opacity: .35; cursor: default; }
    .ep-leyenda-sep { width: 1px; height: 20px; background: var(--border); }
    .ep-leyenda-nota { margin-left: auto; font-size: 11.5px; }
    .leaflet-popup-content strong { color: #13304d; font-size: 0.92rem; }
    .leaflet-popup-content table  { font-size: 0.80rem; margin-top: 4px; }
    .leaflet-popup-content td     { padding: 1px 4px; }

    /* Paginador compacto (mismo estilo que el resto del sistema) */
    .ep-pager { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-top: 1px solid var(--border); }
    .ep-pager-info { font-size: 12.5px; color: var(--text-muted); }
    .ep-pager-info strong { color: var(--text-main); }
    .ep-pager nav { margin-left: auto; }
    .ep-pager .pagination { display: flex; flex-wrap: wrap; gap: 4px; justify-content: flex-end; list-style: none; margin: 0; padding: 0; }
    .ep-pager .pagination li { margin: 0; }
    .ep-pager .pagination a, .ep-pager .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px; border: 1px solid var(--border); background: #fff; color: var(--text-main); font-size: 13px; font-weight: 600; text-decoration: none; }
    .ep-pager .pagination li a span { border: 0; padding: 0; min-width: 0; height: auto; background: none; color: inherit; }
    .ep-pager .pagination a:hover { border-color: var(--ej-secundario); color: var(--ej-secundario); }
    .ep-pager .pagination li.active a { background: #1a2b45; border-color: #1a2b45; color: #fff; }
    @media (max-width: 600px) { .ep-pager { justify-content: center; } .ep-pager nav { margin: 0; } .ep-pager .pagination { justify-content: center; } }
</style>

<?php
$num = static fn ($n) => number_format((float) $n, 0, ',', '.');
$k   = $kpis ?? [];
$pct = static fn ($n, $t) => $t > 0 ? round($n * 100 / $t) . '%' : '—';

// Filtros vigentes (para el Excel, los chips y el enlace de cada chip)
$filtros = array_filter([
    'buscar'        => $filtro_buscar,
    'tipo'          => $filtro_tipo,
    'localidad'     => $filtro_localidad,
    'efector'       => $filtro_efector,
    'factor_riesgo' => $filtro_factor,
    'cud'           => $filtro_cud,
    'estado'        => $filtro_estado,
    'obra_social'   => $filtro_obra_social,
    'diagnostico'   => $filtro_diagnostico,
    'seguimiento'   => $filtro_seguimiento,
    'm_riesgo'      => implode(',', $mapaSel['riesgo'] ?? []),
    'm_estado'      => implode(',', $mapaSel['estado'] ?? []),
], static fn ($v) => $v !== null && $v !== '');
$urlLista  = base_url(route_to('electrodependiente_list'));
$sinFiltro = static function (string $clave) use ($filtros, $urlLista) {
    $q = http_build_query(array_diff_key($filtros, [$clave => 1]));

    return $urlLista . ($q ? '?' . $q : '');
};

$nombreDe = static function ($lista, $id, $campoId, $campoNombre) {
    foreach ($lista as $x) {
        $x = (array) $x;
        if ((string) ($x[$campoId] ?? '') === (string) $id) return $x[$campoNombre];
    }

    return '#' . $id;
};
$chips = [];
if ($filtro_buscar)      $chips[] = ['Buscar: “' . $filtro_buscar . '”', 'buscar'];
if ($filtro_tipo)        $chips[] = ['Tipo: ' . $filtro_tipo, 'tipo'];
if ($filtro_localidad)   $chips[] = ['Localidad: ' . $nombreDe($localidades, $filtro_localidad, 'localidad_id', 'nombre'), 'localidad'];
if ($filtro_efector)     $chips[] = ['Hospital: ' . $nombreDe($efectores, $filtro_efector, 'efector_id', 'nombre'), 'efector'];
if ($filtro_factor)      $chips[] = ['Riesgo: ' . ($filtro_factor === 'SIN_DATO' ? 'Sin dato' : $filtro_factor), 'factor_riesgo'];
if ($filtro_cud)         $chips[] = ['CUD: ' . $filtro_cud, 'cud'];
if ($filtro_estado)      $chips[] = ['Estado: ' . ucfirst($filtro_estado), 'estado'];
if ($filtro_obra_social) $chips[] = ['Obra social: ' . $nombreDe($obras_sociales, $filtro_obra_social, 'obra_social_id', 'nombre'), 'obra_social'];
if ($filtro_diagnostico) $chips[] = ['Diagnóstico: ' . $nombreDe($diagnosticos, $filtro_diagnostico, 'diagnostico_id', 'nombre'), 'diagnostico'];
if ($filtro_seguimiento) $chips[] = ['Seguimiento: ' . $filtro_seguimiento, 'seguimiento'];
// Selección hecha en la leyenda del mapa
$selR = $mapaSel['riesgo'] ?? [];
$selE = $mapaSel['estado'] ?? [];
$txtR = ['ALTO' => 'ALTO', 'MEDIANO' => 'MEDIANO', 'BAJO' => 'BAJO', 'SD' => 'Sin dato'];
$txtE = ['activo' => 'Activos', 'fallecido' => 'Fallecidos', 'desactivado' => 'Desactivados'];
if ($selR) $chips[] = ['Mapa · riesgo: ' . implode(' o ', array_map(static fn ($v) => $txtR[$v], $selR)), 'm_riesgo'];
if ($selE) $chips[] = ['Mapa · estado: ' . implode(' o ', array_map(static fn ($v) => $txtE[$v], $selE)), 'm_estado'];

// Riesgo con los colores fijos del módulo (ALTO rojo, MEDIANO amarillo, BAJO verde, sin dato gris)
$chipRiesgo = static function ($r) {
    $r = strtoupper((string) $r);

    return in_array($r, ['ALTO', 'MEDIANO', 'BAJO'], true)
        ? '<span class="ej-chip ' . $r . '">' . $r . '</span>'
        : '<span class="ej-chip PENDIENTE">Sin dato</span>';
};
$total = $k['total'] ?? 0;
?>

<div class="ej-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Salud mental</a> ›
    <strong>Electrodependientes</strong>
</div>

<div class="ej-panel">
    <div class="ej-panel-header">
        <span class="ej-panel-title"><i class="fas fa-plug"></i> ELECTRODEPENDIENTES · PACIENTES</span>
        <span class="ej-datos-al" id="epTotalCab"><i class="fas fa-users"></i> <?= $num($total) ?> paciente<?= $total == 1 ? '' : 's' ?><?= $chips ? ' con los filtros' : '' ?></span>
    </div>

    <div class="ej-panel-body">

        <?php if (session()->has('msg')): $msg = session('msg'); ?>
            <div class="notification is-<?= esc($msg['type'], 'attr') ?> is-light"><?= $msg['body'] ?></div>
        <?php endif; ?>

        <div class="ej-toolbar">
            <a class="bl-btn ghost" href="<?= base_url(route_to('saludmental_views')) ?>"><i class="fas fa-arrow-left"></i> Volver</a>
            <a class="bl-btn green" id="epExcel" href="<?= base_url(route_to('electrodependiente_export')) . ($filtros ? '?' . http_build_query($filtros) : '') ?>"><i class="fas fa-file-excel"></i> Excel</a>
            <a class="bl-btn navy" href="<?= base_url(route_to('ejesa_views')) ?>" title="Cortes programados, afectados y auditoría de EJESA"><i class="fas fa-bolt"></i> EJESA · Cortes</a>
            <button type="button" class="bl-btn ghost" id="btnToggleMapa"><i class="fas fa-map-marked-alt"></i> <span id="lblToggleMapa">Mostrar mapa</span></button>
        </div>

        <?php if ($nuevas > 0 || $modificadas > 0): ?>
            <div class="ep-novedades">
                <div class="chips">
                    <i class="fas fa-bell"></i>
                    <strong>Desde tu última visita</strong>
                    <span>(<?= $ultimaVista == '2000-01-01 00:00:00' ? 'primera vez' : date('d/m/Y H:i', strtotime($ultimaVista)) ?>):</span>
                    <?php if ($nuevas > 0): ?><span class="ej-chip ep-chip-nuevo"><?= $num($nuevas) ?> nuevo<?= $nuevas > 1 ? 's' : '' ?></span><?php endif; ?>
                    <?php if ($modificadas > 0): ?><span class="ej-chip ep-chip-modificado"><?= $num($modificadas) ?> modificado<?= $modificadas > 1 ? 's' : '' ?></span><?php endif; ?>
                    <small style="color:#8a6a3c;">En la tabla llevan una barra verde (nuevo) o amarilla (modificado).</small>
                </div>
                <a href="<?= base_url(route_to('electrodependiente_visto')) ?>" class="bl-btn sm navy"><i class="fas fa-check"></i> Marcar como visto</a>
            </div>
        <?php endif; ?>

        <!-- ── Indicadores (con los filtros aplicados) ── -->
        <div class="ej-kpi-cards" id="epKpis">
            <div class="ej-kpi-card navy">
                <div class="ej-kpi-icono"><i class="fas fa-plug"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Pacientes</div>
                    <div class="ej-kpi-val"><?= $num($total) ?></div>
                    <div class="ej-kpi-sub"><?= $num($k['activos'] ?? 0) ?> activos · <?= $num($total - ($k['activos'] ?? 0)) ?> desactivados</div>
                </div>
            </div>
            <a class="ej-kpi-card rojo" href="<?= $urlLista . '?' . http_build_query(['factor_riesgo' => 'ALTO'] + $filtros) ?>" title="Ver solo los de riesgo ALTO">
                <div class="ej-kpi-icono"><i class="fas fa-heartbeat"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Riesgo alto</div>
                    <div class="ej-kpi-val"><?= $num($k['alto'] ?? 0) ?> <small>(<?= $pct($k['alto'] ?? 0, $total) ?>)</small></div>
                    <div class="ej-kpi-sub"><?= $num($k['sin_riesgo'] ?? 0) ?> sin riesgo cargado</div>
                </div>
            </a>
            <div class="ej-kpi-card teal">
                <div class="ej-kpi-icono"><i class="fas fa-bolt"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Servicio eléctrico verificado</div>
                    <div class="ej-kpi-val"><?= $num($k['verificados'] ?? 0) ?> <small>(<?= $pct($k['verificados'] ?? 0, $total) ?>)</small></div>
                    <div class="ej-kpi-sub"><?= $num($k['con_servicio'] ?? 0) ?> con Nº cargado · EJESA solo encuentra los verificados</div>
                </div>
            </div>
            <div class="ej-kpi-card azul">
                <div class="ej-kpi-icono"><i class="fas fa-map-marker-alt"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Con ubicación</div>
                    <div class="ej-kpi-val"><?= $num($k['con_ubicacion'] ?? 0) ?> <small>(<?= $pct($k['con_ubicacion'] ?? 0, $total) ?>)</small></div>
                    <div class="ej-kpi-sub">sin coordenadas quedan "a revisar" en los cortes</div>
                </div>
            </div>
        </div>

        <!-- ── Filtros ── -->
        <form method="GET" action="<?= $urlLista ?>" class="ej-filtro-bar ep-filtros" id="epFiltros">
            <div class="ej-filtro-fila">
                <div class="ej-filtro ep-buscar <?= $filtro_buscar ? 'activo' : '' ?>">
                    <label for="f-buscar">Buscar</label>
                    <div class="ep-buscar-input"><i class="fas fa-search"></i>
                        <input type="search" id="f-buscar" name="buscar" placeholder="Nombre, DNI o Nº de servicio…" value="<?= esc($filtro_buscar) ?>" autocomplete="off">
                        <i class="fas fa-circle-notch fa-spin ep-spinner" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="ej-filtro <?= $filtro_tipo ? 'activo' : '' ?>">
                    <label for="f-tipo">Tipo</label>
                    <select id="f-tipo" name="tipo"><option value="">Todos</option><?php foreach ($tipos as $t): ?><option value="<?= esc($t, 'attr') ?>" <?= $filtro_tipo == $t ? 'selected' : '' ?>><?= esc($t) ?></option><?php endforeach; ?></select>
                </div>
                <div class="ej-filtro <?= $filtro_localidad ? 'activo' : '' ?>">
                    <label for="f-loc">Localidad</label>
                    <select id="f-loc" name="localidad"><option value="">Todas</option><?php foreach ($localidades as $l): ?><option value="<?= $l['localidad_id'] ?>" <?= $filtro_localidad == $l['localidad_id'] ? 'selected' : '' ?>><?= esc($l['nombre']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="ej-filtro <?= $filtro_efector ? 'activo' : '' ?>">
                    <label for="f-efector">Hospital</label>
                    <select id="f-efector" name="efector"><option value="">Todos</option><?php foreach ($efectores as $ef): ?><option value="<?= $ef->efector_id ?>" <?= $filtro_efector == $ef->efector_id ? 'selected' : '' ?>><?= esc($ef->nombre) ?></option><?php endforeach; ?></select>
                </div>
                <div class="ej-filtro <?= $filtro_factor ? 'activo' : '' ?>">
                    <label for="f-riesgo">Riesgo</label>
                    <select id="f-riesgo" name="factor_riesgo"><option value="">Todos</option><?php foreach ($factores as $fr): ?><option value="<?= esc($fr, 'attr') ?>" <?= $filtro_factor == $fr ? 'selected' : '' ?>><?= esc($fr) ?></option><?php endforeach; ?><option value="SIN_DATO" <?= $filtro_factor === 'SIN_DATO' ? 'selected' : '' ?>>Sin dato</option></select>
                </div>
                <div class="ej-filtro <?= $filtro_cud ? 'activo' : '' ?>">
                    <label for="f-cud">CUD</label>
                    <select id="f-cud" name="cud"><option value="">Todos</option><option value="SI" <?= $filtro_cud == 'SI' ? 'selected' : '' ?>>Sí</option><option value="NO" <?= $filtro_cud == 'NO' ? 'selected' : '' ?>>No</option></select>
                </div>
                <div class="ej-filtro <?= $filtro_estado ? 'activo' : '' ?>">
                    <label for="f-estado">Estado</label>
                    <select id="f-estado" name="estado"><option value="">Todos</option><option value="activo" <?= $filtro_estado == 'activo' ? 'selected' : '' ?>>Activo</option><option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option></select>
                </div>
                <div class="ej-filtro-acciones">
                    <button type="submit" class="bl-btn teal" title="Filtrar"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="<?= $urlLista ?>" class="bl-btn ghost" id="btnLimpiar" title="Limpiar filtros"><i class="fas fa-times"></i> Limpiar</a>
                </div>
            </div>
            <?php if ($selR): ?><input type="hidden" name="m_riesgo" value="<?= esc(implode(',', $selR)) ?>"><?php endif; ?>
            <?php if ($selE): ?><input type="hidden" name="m_estado" value="<?= esc(implode(',', $selE)) ?>"><?php endif; ?>
        </form>

        <div id="epChips">
        <?php if ($chips): ?>
            <div class="ej-activos" aria-label="Filtros aplicados">
                <span><i class="fas fa-filter"></i> Filtros aplicados:</span>
                <?php foreach ($chips as [$texto, $clave]): ?>
                    <a class="ej-activo" href="<?= $sinFiltro($clave) ?>" title="Quitar este filtro"><?= esc($texto) ?> <span><i class="fas fa-times"></i></span></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>

        <?php
        // Pacientes georreferenciados (todos los que cumplen los filtros). Coordenadas "lat, lng";
        // las que no son válidas (por ejemplo, cargadas sin punto decimal) no se dibujan y se avisa.
        $puntosMapa = [];
        $coordInvalidas = 0;
        $cuentaMapa = ['ALTO' => 0, 'MEDIANO' => 0, 'BAJO' => 0, 'SD' => 0];
        $cuentaBaja = ['fallecido' => 0, 'desactivado' => 0];
        foreach ($pacientesMapa ?? [] as $p) {
            $c = array_map('trim', explode(',', (string) $p['coordenadas']));
            $lat = isset($c[0]) && is_numeric($c[0]) ? (float) $c[0] : null;
            $lng = isset($c[1]) && is_numeric($c[1]) ? (float) $c[1] : null;
            if ($lat === null || $lng === null || abs($lat) > 90 || abs($lng) > 180 || ($lat == 0 && $lng == 0)) {
                $coordInvalidas++;
                continue;
            }
            $r = strtoupper((string) $p['riesgo']);
            $r = in_array($r, ['ALTO', 'MEDIANO', 'BAJO'], true) ? $r : 'SD';
            $cuentaMapa[$r]++;
            // Un fallecido cuenta solo como fallecido aunque también esté desactivado
            $baja = ($p['estado_vital'] ?? '') === 'fallecido' ? 'fallecido' : ($p['estado'] !== 'activo' ? 'desactivado' : null);
            if ($baja) {
                $cuentaBaja[$baja]++;
            }
            $puntosMapa[] = [
                'id' => (int) $p['id'], 'paciente' => $p['paciente'], 'dni' => $p['dni'] ?? '', 'edad' => $p['edad'] ?? '',
                'localidad' => $p['localidad_nombre'] ?? '', 'efector' => $p['efector_nombre'] ?? '', 'diagnostico' => $p['diagnostico_nombre'] ?? '',
                'riesgo' => $r, 'fallecido' => ($p['estado_vital'] ?? '') === 'fallecido', 'activo' => $p['estado'] === 'activo', 'baja' => $baja,
                'servicio' => (string) ($p['nro_servicio'] ?? ''),
                'lat' => $lat, 'lng' => $lng,
                'historial' => base_url(route_to('electrodependiente_historial', (int) $p['id'])),
            ];
        }
        $cuentaActivos = count($puntosMapa) - $cuentaBaja['fallecido'] - $cuentaBaja['desactivado'];
        ?>
        <!-- ── Mapa (se abre a pedido) ── -->
        <div class="ej-bloque" id="col-mapa" hidden>
            <div class="ej-bloque-titulo">
                <span><i class="fas fa-map-marked-alt"></i> <em id="epCuentaMapa" style="font-style:normal;">Pacientes georreferenciados (<?= $num(count($puntosMapa)) ?>)</em></span>
                <span class="ej-chip neutro"><?= $chips ? 'con los filtros aplicados' : 'todos los pacientes' ?> · departamentos por región sanitaria</span>
            </div>
            <div class="ej-bloque-body">
                <div class="ep-leyenda">
                    <span class="ep-grupo" data-grupo="riesgo">
                    <span style="color:var(--text-main);">Riesgo:</span>
                    <?php foreach (['ALTO' => ['#D96C6C', 'ALTO'], 'MEDIANO' => ['#F2A272', 'MEDIANO'], 'BAJO' => ['#5BB8A6', 'BAJO'], 'SD' => ['#708090', 'Sin dato']] as $r => [$color, $txt]): ?>
                        <button type="button" class="ep-capa" data-riesgo="<?= $r ?>" aria-pressed="<?= in_array($r, $selR, true) ? 'true' : 'false' ?>" title="Ver solo este nivel (se puede combinar)" <?= $cuentaMapa[$r] ? '' : 'disabled' ?>>
                            <i class="punto" style="background:<?= $color ?>;"></i><?= $txt ?> <small><?= $cuentaMapa[$r] ?></small>
                        </button>
                    <?php endforeach; ?>
                    </span>
                    <span class="ep-leyenda-sep" aria-hidden="true"></span>
                    <span class="ep-grupo" data-grupo="estado">
                    <span style="color:var(--text-main);">Estado:</span>
                    <button type="button" class="ep-capa" data-estado="activo" aria-pressed="<?= in_array('activo', $selE, true) ? 'true' : 'false' ?>" title="Ver solo los pacientes activos" <?= $cuentaActivos ? '' : 'disabled' ?>>
                        <i class="fas fa-user-check" style="color:#2f855a;"></i>Activos <small><?= $cuentaActivos ?></small>
                    </button>
                    <button type="button" class="ep-capa" data-estado="fallecido" aria-pressed="<?= in_array('fallecido', $selE, true) ? 'true' : 'false' ?>" title="Ver solo los fallecidos (pin atenuado)" <?= $cuentaBaja['fallecido'] ? '' : 'disabled' ?>>
                        <i class="fas fa-cross" style="color:#4a5568;"></i>Fallecidos <small><?= $cuentaBaja['fallecido'] ?></small>
                    </button>
                    <button type="button" class="ep-capa" data-estado="desactivado" aria-pressed="<?= in_array('desactivado', $selE, true) ? 'true' : 'false' ?>" title="Ver solo los desactivados (pin atenuado)" <?= $cuentaBaja['desactivado'] ? '' : 'disabled' ?>>
                        <i class="fas fa-user-slash" style="color:#718096;"></i>Desactivados <small><?= $cuentaBaja['desactivado'] ?></small>
                    </button>
                    </span>
                    <button type="button" class="ep-limpiar" id="btnVerTodos" hidden><i class="fas fa-undo"></i> Ver todos</button>
                    <span class="ep-leyenda-nota">
                        <?= $num(max(0, ($totalMapa ?? ($k['total'] ?? 0)) - count($puntosMapa) - $coordInvalidas)) ?> sin coordenadas cargadas<?= $coordInvalidas ? ' · <strong style="color:#c05621;">' . $coordInvalidas . ' con coordenadas inválidas</strong> (por ejemplo, sin punto decimal): no se dibujan' : '' ?>
                    </span>
                </div>
                <div id="mapa-electro"></div>
            </div>
        </div>

        <!-- ── Listado ── -->
        <div class="ej-bloque" id="epListado">
            <div class="ej-bloque-titulo">
                <span><i class="fas fa-user-injured"></i> Pacientes (<?= $num($total) ?>)</span>
                <span class="ej-chip neutro">ordenado por nombre · página <?= $pager->getCurrentPage() ?> de <?= max(1, $pager->getPageCount()) ?></span>
            </div>
            <div class="ej-tabla-wrap">
            <table class="ej-tabla tarjetas ep-tabla">
                <thead><tr>
                    <th>Paciente</th><th>Localidad / hospital</th><th>Diagnóstico</th><th>CUD</th><th>Riesgo</th>
                    <th>Servicio eléctrico</th><th>Equipos</th><th>Estado</th><th></th>
                </tr></thead>
                <tbody>
                <?php foreach ($registros as $r):
                    $esNuevo      = $r->created_at > $ultimaVista;
                    $esModificado = !$esNuevo && ($r->updated_at > $ultimaVista) && ($r->updated_at != $r->created_at);
                    $tieneGeo     = !empty($r->coordenadas);
                    $equipos      = $r->equipos ?? [];
                    $clase        = $r->estado === 'desactivado' ? 'desactivado' : ($esNuevo ? 'es-nuevo' : ($esModificado ? 'es-modificado' : ''));
                ?>
                    <tr id="fila-<?= $r->electrodependiente_id ?>" class="<?= $clase ?>">
                        <td class="ej-td-titulo ep-paciente" data-label="Paciente">
                            <strong><?= esc($r->paciente) ?></strong>
                            <small>DNI <?= esc($r->dni ?: '—') ?> · <?= $r->edad !== null && $r->edad !== '' ? (int) $r->edad . ' años' : 'edad s/d' ?> · <?= esc($r->tipo ?: '—') ?> · #<?= $r->electrodependiente_id ?></small>
                            <?php if ($esNuevo): ?><span class="ej-chip ep-chip-nuevo ep-marca-fila">Nuevo</span><?php elseif ($esModificado): ?><span class="ej-chip ep-chip-modificado ep-marca-fila">Modificado</span><?php endif; ?>
                        </td>
                        <td data-label="Localidad / hospital"><div class="ep-celda"><?= esc($r->localidad_nombre ?? '—') ?><small><?= esc($r->efector_nombre ?? 'Sin hospital') ?></small></div></td>
                        <td data-label="Diagnóstico"><div class="ep-celda"><?= esc($r->diagnostico_nombre ?? '—') ?><small><?= esc($r->obra_social_nombre ?? 'Sin obra social') ?><?= $r->seguimiento ? ' · ' . esc($r->seguimiento) : '' ?></small></div></td>
                        <td data-label="CUD"><?= $r->cud === 'SI' ? '<span class="ej-chip ok">Sí</span>' : '<span class="ej-chip neutro">' . esc($r->cud ?: '—') . '</span>' ?></td>
                        <td data-label="Riesgo"><?= $chipRiesgo($r->factor_riesgo) ?></td>
                        <td data-label="Servicio eléctrico" class="ep-servicio"><div class="ep-celda">
                            <?php if (!empty($r->nro_servicio)): ?>
                                <span class="nro"><?= esc($r->nro_servicio) ?></span>
                                <?= !empty($r->servicio_verificado) ? '<span class="ej-chip ok" title="EJESA lo encuentra por número">verificado</span>' : '<span class="ej-chip VENCIDO" title="Sin verificar: EJESA no lo encuentra por número">sin verificar</span>' ?>
                                <?php if (!empty($r->titular_servicio)): ?><small>Titular: <?= esc($r->titular_servicio) ?></small><?php endif; ?>
                            <?php else: ?><span class="ej-sin-dato">Sin Nº cargado</span><?php endif; ?>
                        </div></td>
                        <td data-label="Equipos"><div class="ep-celda">
                            <?php if ($equipos): foreach ($equipos as $eq): ?>
                                <?php
                                $det = array_filter([
                                    trim(($eq['marca'] ?? '') . ' ' . ($eq['modelo'] ?? '')),
                                    !empty($eq['serie']) ? 'Serie ' . $eq['serie'] : '',
                                    !empty($eq['fecha_entrega']) ? 'Entrega ' . date('d/m/Y', strtotime($eq['fecha_entrega'])) : '',
                                    !empty($eq['medico_tratante']) ? 'Médico: ' . $eq['medico_tratante'] : '',
                                ]);
                                ?>
                                <div class="ep-equipo">
                                    <strong><?= esc($eq['equipamiento'] ?? '—') ?></strong><?= !empty($eq['tiempo_uso']) ? ' · ' . esc($eq['tiempo_uso']) : '' ?>
                                    <?php if ($det): ?><small><?= esc(implode(' · ', $det)) ?></small><?php endif; ?>
                                </div>
                            <?php endforeach; else: ?><span class="ej-sin-dato">Sin equipos</span><?php endif; ?>
                        </div></td>
                        <td data-label="Estado"><?php
                            if (($r->estado_vital ?? '') === 'fallecido') echo '<span class="ej-chip CANCELADO">Fallecido</span>';
                            elseif ($r->estado === 'activo') echo '<span class="ej-chip ok">Activo</span>';
                            else echo '<span class="ej-chip neutro">Desactivado</span>';
                        ?></td>
                        <td data-label="">
                            <div class="ep-acciones">
                                <?php if ($tieneGeo): ?><button type="button" class="btn-ir-mapa" data-id="<?= $r->electrodependiente_id ?>" title="Ver en el mapa"><i class="fas fa-map-pin"></i></button><?php endif; ?>
                                <a href="<?= base_url(route_to('electrodependiente_historial', $r->electrodependiente_id)) ?>" title="Historial de cambios"><i class="fas fa-history"></i> Historial</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($registros)): ?><tr><td colspan="9" class="ej-vacio">Sin pacientes electrodependientes para los filtros elegidos.</td></tr><?php endif; ?>
                </tbody>
            </table>
            </div>
            <?php
            // Paginador en español con "Mostrando X–Y de N"
            $enlaces = str_replace(
                ['aria-label="First"', 'aria-label="Previous"', 'aria-label="Next"', 'aria-label="Last"', '>First<', '>Previous<', '>Next<', '>Last<', 'aria-label="Page navigation"'],
                ['aria-label="Primera página"', 'aria-label="Anterior"', 'aria-label="Siguiente"', 'aria-label="Última página"', '>« Primera<', '>‹ Anterior<', '>Siguiente ›<', '>Última »<', 'aria-label="Páginas"'],
                $pager->links()
            );
            $porPagina = $pager->getPerPage();
            $desde = $total ? ($pager->getCurrentPage() - 1) * $porPagina + 1 : 0;
            $hasta = min($total, $desde + count($registros) - 1);
            ?>
            <div class="ep-pager">
                <span class="ep-pager-info">Mostrando <strong><?= $num($desde) ?>–<?= $num(max($desde, $hasta)) ?></strong> de <strong><?= $num($total) ?></strong></span>
                <?= $enlaces ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    'use strict';

    // Pacientes georreferenciados que cumplen los filtros (todas las páginas)
    var pacientesGeo = <?= json_encode($puntosMapa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    var mapa = null, marcadores = {}, registros = [], capaVisible = null;
    var bloqueMapa = document.getElementById('col-mapa');
    var lblMapa = document.getElementById('lblToggleMapa');
    var COLOR_RIESGO = { ALTO: '#D96C6C', MEDIANO: '#F2A272', BAJO: '#5BB8A6', SD: '#708090' };

    // Regiones sanitarias sobre los departamentos de Jujuy (mismos colores que Salud mental y Bases)
    var regionColores = { 'CENTRO': '#90CAF9', 'VALLE': '#A5D6A7', 'RAMAL I': '#F48FB1', 'RAMAL II': '#CE93D8', 'QUEBRADA': '#FFAB91', 'PUNA': '#FFF59D' };
    var regionBordes  = { 'CENTRO': '#4c6bb8', 'VALLE': '#4c8f74', 'RAMAL I': '#b36693', 'RAMAL II': '#976cc2', 'QUEBRADA': '#d88e6b', 'PUNA': '#e2b464' };
    var deptoRegion = {
        'Dr. Manuel Belgrano': 'CENTRO',
        'Palpala': 'VALLE', 'El Carmen': 'VALLE', 'San Antonio': 'VALLE',
        'Ledesma': 'RAMAL II', 'Valle Grande': 'RAMAL II',
        'San Pedro': 'RAMAL I', 'Santa Barbara': 'RAMAL I',
        'Humahuaca': 'QUEBRADA', 'Tilcara': 'QUEBRADA', 'Tumbaya': 'QUEBRADA', 'Susques': 'QUEBRADA',
        'Yavi': 'PUNA', 'Cochinoca': 'PUNA', 'Rinconada': 'PUNA', 'Santa Catalina': 'PUNA'
    };

    function escapar(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : String(t); return d.innerHTML; }
    function crearIcono(r, apagado) {
        var c = COLOR_RIESGO[r] || COLOR_RIESGO.SD;
        return L.divIcon({ html: '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="36" viewBox="0 0 28 38" style="opacity:' + (apagado ? .55 : 1) + '"><path d="M14 0C6.27 0 0 6.27 0 14c0 9.75 14 24 14 24S28 23.75 28 14C28 6.27 21.73 0 14 0z" fill="' + c + '" stroke="#fff" stroke-width="2"/><circle cx="14" cy="14" r="6" fill="white" opacity="0.9"/></svg>', className: '', iconSize: [26, 36], iconAnchor: [13, 36], popupAnchor: [0, -34] });
    }
    function resaltar(id, si) {
        var el = document.getElementById('fila-' + id);
        if (!el) return;
        el.classList.toggle('fila-highlight', si);
    }

    function cargarDepartamentos() {
        fetch('<?= base_url('geojson/jujuy_departamentos.json') ?>')
            .then(function (r) { return r.json(); })
            .then(function (geojson) {
                var capa = L.geoJSON(geojson, {
                    style: function (f) {
                        var region = deptoRegion[f.properties.nam] || 'CENTRO';
                        return { color: regionBordes[region], weight: 1.5, opacity: .9, fillColor: regionColores[region], fillOpacity: .28 };
                    },
                    onEachFeature: function (f, layer) {
                        var nombre = f.properties.nam || f.properties.fna || 'Departamento';
                        var region = deptoRegion[f.properties.nam] || 'CENTRO';
                        layer.bindTooltip('<b>' + escapar(nombre) + '</b><br>Región ' + escapar(region), { sticky: true });
                        layer.on({
                            mouseover: function (e) { e.target.setStyle({ fillOpacity: .12 }); },
                            mouseout: function (e) { e.target.setStyle({ fillOpacity: .28 }); },
                            click: function (e) { mapa.flyToBounds(e.target.getBounds(), { padding: [30, 30], duration: .6 }); }
                        });
                    }
                }).addTo(mapa);
                capa.bringToBack();
                if (!pacientesGeo.length) mapa.fitBounds(capa.getBounds());
            })
            .catch(function (error) { console.error('Error al cargar los departamentos:', error); });
    }

    function iniciarMapa() {
        if (mapa || typeof L === 'undefined') return;
        mapa = L.map('mapa-electro', { zoomControl: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© <a href="https://www.openstreetmap.org/">OpenStreetMap</a>', maxZoom: 18 }).addTo(mapa);
        mapa.setView([-23.3, -65.6], 7);
        cargarDepartamentos();

        var todos = L.featureGroup();
        capaVisible = L.layerGroup().addTo(mapa);
        pacientesGeo.forEach(function (p) {
            var apagado = p.fallecido || !p.activo;
            var m = L.marker([p.lat, p.lng], { icon: crearIcono(p.riesgo, apagado), title: p.paciente, riseOnHover: true });
            m.bindPopup('<strong>' + escapar(p.paciente) + '</strong>' + (p.fallecido ? ' <em style="color:#718096;">(fallecido)</em>' : (!p.activo ? ' <em style="color:#718096;">(desactivado)</em>' : '')) + '<table>'
                + '<tr><td>DNI:</td><td>' + escapar(p.dni || '—') + '</td></tr>'
                + '<tr><td>Edad:</td><td>' + escapar(p.edad || '—') + '</td></tr>'
                + '<tr><td>Localidad:</td><td>' + escapar(p.localidad || '—') + '</td></tr>'
                + '<tr><td>Hospital:</td><td>' + escapar(p.efector || '—') + '</td></tr>'
                + '<tr><td>Diagnóstico:</td><td>' + escapar(p.diagnostico || '—') + '</td></tr>'
                + '<tr><td>Riesgo:</td><td><strong style="color:' + COLOR_RIESGO[p.riesgo] + '">' + (p.riesgo === 'SD' ? 'Sin dato' : p.riesgo) + '</strong></td></tr></table>'
                + '<a href="' + escapar(p.historial) + '" style="display:inline-block;margin-top:6px;font-weight:700;"><i class="fas fa-history"></i> Ver historial</a>', { maxWidth: 260 });
            m.on('popupopen', function () {
                document.querySelectorAll('.fila-highlight').forEach(function (el) { el.classList.remove('fila-highlight'); });
                resaltar(p.id, true);
            });
            m.on('popupclose', function () { resaltar(p.id, false); });
            marcadores[p.id] = m;
            registros.push({ m: m, p: p });
            todos.addLayer(m);
        });
        aplicarCapas();
        if (!textoBusqueda() && pacientesGeo.length) mapa.fitBounds(todos.getBounds().pad(0.12), { maxZoom: 14 });
        else encuadrar();
    }
    function mostrarMapa(si) {
        bloqueMapa.hidden = !si;
        lblMapa.textContent = si ? 'Ocultar mapa' : 'Mostrar mapa';
        if (si) {
            iniciarMapa();
            setTimeout(function () { if (mapa) mapa.invalidateSize(); }, 200);
        }
    }

    // Leyenda como filtro: sin nada seleccionado se ven todos. Al seleccionar botones se ven solo
    // los pacientes que cumplen lo elegido: dentro de un grupo vale cualquiera de los seleccionados
    // (ALTO o MEDIANO) y entre grupos tienen que cumplirse los dos (ALTO y Desactivado).
    var btnVerTodos = document.getElementById('btnVerTodos');
    function seleccion(attr) {
        var sel = {}, hay = false;
        document.querySelectorAll('.ep-capa[data-' + attr + ']').forEach(function (b) {
            if (b.getAttribute('aria-pressed') === 'true') { sel[b.dataset[attr]] = true; hay = true; }
        });
        return hay ? sel : null;
    }
    // Buscador: mismo criterio que el servidor (nombre, DNI o Nº de servicio), sin distinguir acentos
    var inputBuscar = document.getElementById('f-buscar');
    var ACENTOS = new RegExp('[' + String.fromCharCode(0x300) + '-' + String.fromCharCode(0x36f) + ']', 'g');
    function normalizar(t) { return String(t == null ? '' : t).normalize('NFD').replace(ACENTOS, '').toLowerCase().trim(); }
    function textoBusqueda() { return normalizar(inputBuscar ? inputBuscar.value : ''); }
    function coincide(p, q) {
        if (!q) return true;
        if (p._txt === undefined) p._txt = normalizar(p.paciente) + '|' + normalizar(p.dni) + '|' + normalizar(p.servicio);
        return p._txt.indexOf(q) !== -1;
    }
    function estadoDe(p) { return p.baja || 'activo'; }
    // Conteos de la leyenda y del título según lo que coincide con el buscador
    function actualizarCuentas() {
        var q = textoBusqueda(), c = { ALTO: 0, MEDIANO: 0, BAJO: 0, SD: 0, activo: 0, fallecido: 0, desactivado: 0 }, n = 0;
        pacientesGeo.forEach(function (p) { if (coincide(p, q)) { c[p.riesgo]++; c[estadoDe(p)]++; n++; } });
        document.querySelectorAll('.ep-capa').forEach(function (b) {
            var k = b.dataset.riesgo || b.dataset.estado, sm = b.querySelector('small');
            if (sm) sm.textContent = c[k];
            b.disabled = !c[k] && b.getAttribute('aria-pressed') !== 'true';
        });
        var t = document.getElementById('epCuentaMapa');
        if (t) t.textContent = 'Pacientes georreferenciados (' + n.toLocaleString('es-AR') + ')';
    }
    // Con texto en el buscador, el mapa se acerca a los pacientes que coinciden
    function encuadrar() {
        if (!mapa || !textoBusqueda()) return;
        var vis = [];
        capaVisible.eachLayer(function (m) { vis.push(m.getLatLng()); });
        mapa.invalidateSize();
        if (vis.length === 1) mapa.setView(vis[0], 14, { animate: false });
        else if (vis.length) mapa.fitBounds(L.latLngBounds(vis).pad(0.2), { maxZoom: 14, animate: false });
    }
    function aplicarCapas() {
        var selR = seleccion('riesgo'), selE = seleccion('estado'), q = textoBusqueda();
        actualizarCuentas();
        document.querySelector('.ep-grupo[data-grupo="riesgo"]').classList.toggle('con-seleccion', !!selR);
        document.querySelector('.ep-grupo[data-grupo="estado"]').classList.toggle('con-seleccion', !!selE);
        btnVerTodos.hidden = !selR && !selE;
        if (!capaVisible) return;
        registros.forEach(function (x) {
            var ver = coincide(x.p, q) && (!selR || selR[x.p.riesgo]) && (!selE || selE[estadoDe(x.p)]);
            if (ver && !capaVisible.hasLayer(x.m)) capaVisible.addLayer(x.m);
            if (!ver && capaVisible.hasLayer(x.m)) capaVisible.removeLayer(x.m);
        });
    }
    // La tabla, los indicadores y el Excel se filtran en el servidor (?m_riesgo=…&m_estado=…),
    // así abarca todas las páginas. Al volver, el mapa se reabre (?mapa=1).
    function lista(attr) {
        var v = [];
        document.querySelectorAll('.ep-capa[data-' + attr + '][aria-pressed="true"]').forEach(function (b) { v.push(b.dataset[attr]); });
        return v.join(',');
    }
    function recargarTabla() {
        var url = new URL(window.location.href);
        [['m_riesgo', lista('riesgo')], ['m_estado', lista('estado')]].forEach(function (x) {
            if (x[1]) url.searchParams.set(x[0], x[1]); else url.searchParams.delete(x[0]);
        });
        url.searchParams.delete('page');
        url.searchParams.set('mapa', '1');
        url.hash = '';
        document.getElementById('col-mapa').classList.add('ep-cargando');
        window.location.href = url.toString();
    }
    document.querySelectorAll('.ep-capa').forEach(function (b) {
        b.addEventListener('click', function () {
            b.setAttribute('aria-pressed', b.getAttribute('aria-pressed') === 'true' ? 'false' : 'true');
            aplicarCapas();
            recargarTabla();
        });
    });
    function verTodos() {
        document.querySelectorAll('.ep-capa').forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
        aplicarCapas();
        recargarTabla();
    }
    btnVerTodos.addEventListener('click', verTodos);

    document.getElementById('btnToggleMapa').addEventListener('click', function () { mostrarMapa(bloqueMapa.hidden); });
    // Vuelve de un clic en la leyenda (o trae una selección): abrir el mapa y llevar la vista hasta él
    if (/[?&]mapa=1(&|$)/.test(window.location.search) || <?= ($selR || $selE) ? 'true' : 'false' ?>) {
        mostrarMapa(true);
        if (/[?&]mapa=1(&|$)/.test(window.location.search)) bloqueMapa.scrollIntoView({ block: 'start' });
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.btn-ir-mapa') : null;
        if (!btn) return;
        (function () {
            var id = parseInt(btn.dataset.id, 10);
            mostrarMapa(true);
            setTimeout(function () {
                var m = marcadores[id];
                if (!m) return;
                if (!capaVisible.hasLayer(m)) capaVisible.addLayer(m);
                mapa.setView(m.getLatLng(), 15, { animate: true });
                m.openPopup();
                bloqueMapa.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 260);
        })();
    });

    // Búsqueda mientras se escribe: el mapa se filtra al instante y la tabla, los indicadores,
    // los chips y el enlace del Excel se piden al servidor (todas las páginas) sin recargar.
    var esperaBusqueda = null, pedido = null;
    function buscarEnServidor() {
        var url = new URL(window.location.href), valor = inputBuscar.value.trim();
        if (valor) url.searchParams.set('buscar', valor); else url.searchParams.delete('buscar');
        url.searchParams.delete('page');
        if (!bloqueMapa.hidden) url.searchParams.set('mapa', '1');
        url.hash = '';
        if (pedido) pedido.abort();
        pedido = typeof AbortController !== 'undefined' ? new AbortController() : null;
        var caja = inputBuscar.parentNode;
        caja.classList.add('buscando');
        ['epListado', 'epKpis'].forEach(function (id) { var el = document.getElementById(id); if (el) el.classList.add('ep-buscando'); });
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: pedido ? pedido.signal : undefined })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                ['epTotalCab', 'epKpis', 'epChips', 'epListado'].forEach(function (id) {
                    var nuevo = doc.getElementById(id), viejo = document.getElementById(id);
                    if (nuevo && viejo) viejo.replaceWith(nuevo);
                });
                var exN = doc.getElementById('epExcel'), exV = document.getElementById('epExcel');
                if (exN && exV) exV.href = exN.getAttribute('href');
                caja.parentNode.classList.toggle('activo', !!valor);
                try { history.replaceState(null, '', url.toString()); } catch (e) { /* sin historial */ }
            })
            .catch(function (err) { if (!err || err.name !== 'AbortError') { console.error('Error al buscar:', err); } })
            .then(function () {
                caja.classList.remove('buscando');
                ['epListado', 'epKpis'].forEach(function (id) { var el = document.getElementById(id); if (el) el.classList.remove('ep-buscando'); });
            });
    }
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            aplicarCapas();
            encuadrar();
            clearTimeout(esperaBusqueda);
            esperaBusqueda = setTimeout(buscarEnServidor, 350);
        });
        // Enter busca enseguida, sin recargar la página
        document.getElementById('epFiltros').addEventListener('submit', function (e) {
            if (document.activeElement === inputBuscar) {
                e.preventDefault();
                clearTimeout(esperaBusqueda);
                buscarEnServidor();
            }
        });
    }
    actualizarCuentas();

    // Los desplegables filtran apenas se cambian
    document.querySelectorAll('#epFiltros select').forEach(function (s) {
        s.addEventListener('change', function () { document.getElementById('epFiltros').submit(); });
    });

})();
</script>
<?= $this->endSection() ?>
