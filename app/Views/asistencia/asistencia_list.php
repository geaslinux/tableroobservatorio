<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> Asistencias · Ministerio de Salud <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    /*3333*/
    /* ── ASISTENCIA_LIST SPECIFIC STYLES ── */
    .at-breadcrumb {
        font-size: 12px; color: var(--text-muted);
        margin-bottom: 16px; display: flex; align-items: center; gap: 6px;
    }
    .at-breadcrumb i { color: var(--teal); font-size: 13px; }
    .at-breadcrumb a { color: var(--text-muted); text-decoration: none; }
    .at-breadcrumb a:hover { color: var(--teal); }
    .at-breadcrumb strong { color: var(--text-main); font-weight: 600; }

    .at-kpi-cards {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 16px;
        justify-content: center;
    }
    .at-kpi-card {
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
        height: 90px;
        box-sizing: border-box;
    }
    .at-kpi-content {
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
    
    .at-kpi-icon {
        width: 60px; height:60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px;
    }
    .kpi-teal .at-kpi-icon { background: #e6fffa; color: #319795; }
    .kpi-blue .at-kpi-icon { background: #ebf8ff; color: #3182ce; }
    .kpi-red  .at-kpi-icon { background: #fff5f5; color: #e53e3e; }
    .kpi-amber .at-kpi-icon { background: #fffaf0; color: #dd6b20; }
    .kpi-purple .at-kpi-icon { background: #faf5ff; color: #805ad5; }
    .kpi-navy .at-kpi-icon { background: #eef3f8; color: var(--navy); }
    
    .at-kpi-label { font-size: 15px; font-weight: 700; color: #718096; text-transform: uppercase; }
    .at-kpi-value { font-size: 25px; font-weight: 700; color: #2d3748; line-height: 1; margin-top: 2px; }
    .at-panel {
        background: var(--white);
        border-radius: 12px;
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .at-panel-header {
        padding: 14px 20px;
        background: var(--navy);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px;
    }
    .at-panel-title {
        font-size: 15px; font-weight: 700; color: #fff;
        display: flex; align-items: center; gap: 10px;
    }
    .at-panel-title i { color: #fff; font-size: 17px; }
    .at-panel-body { padding: 18px 20px; }
    .at-toolbar {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 14px;
    }
    .at-filtro-bar {
        background: #f4f6f9;
        border: 0.5px solid var(--border);
        border-radius: 10px;
        padding: 14px 16px;
        display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .at-filtro-group { display: flex; flex-direction: column; gap: 5px; }
    .at-filtro-label {
        font-size: 9.5px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
        color: var(--text-muted);
    }
    .at-filtro-select {
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
    .at-filtro-select:focus { border-color: var(--teal); }

    .at-stats-body {
        padding: 25px;
        display: grid;
        grid-template-columns: 1.1fr 1.3fr 0.9fr;
        gap: 30px;
    }
    .at-stats-table-wrap { 
        grid-area: table; 
        max-height: 1000px;
        overflow-y: auto;
    }
    .at-stats-table { width: 90%; border-collapse: collapse; font-size: 12.5px; }
    .at-stats-table thead th {
        background: var(--teal-bg); color: var(--teal);
        font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        padding: 8px 5px; text-align: left;
    }
    .at-stats-table tbody td { padding: 7px 5px; border-bottom: 1px solid #eef0f3; color: var(--text-main); }
    .at-stats-table tbody tr:hover { background: #f8f9fb; }
    .at-chart-box { position: relative; width: 100%; height: 550px; padding: 30px; }
    .at-chart-box.is-large { height: 500px; }
    .at-chart-box canvas { height: 100% !important; width: 100% !important; }
    .at-chart-title {
        font-size: 20px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    .at-stats-header {
        padding: 10px 16px;
        font-size: 12px; font-weight: 700; color: var(--text-muted);
        display: flex; align-items: center; justify-content: space-between;
        background: #f4f6f9;
        border-bottom: 0.5px solid var(--border);
        border-radius: 12px 12px 0 0;
    }
    .at-stats-header i { color: var(--teal); }
    .at-stats-panel { margin-bottom: 16px; box-shadow: none; transition: margin-bottom 0.15s; }
    .at-stats-panel.is-collapsed { margin-bottom: 12px; }
    .at-stats-panel.is-collapsed .at-stats-header { border-radius: 12px; border-bottom: none; padding: 10px 16px; }
    
    .at-stats-body.is-hidden { display: none; }
    
    .at-kpi-badge.green { background: #f0fff4; color: #38a169; }
    .at-kpi-badge.orange { background: #fffaf0; color: #dd6b20; }
    .at-kpi-badge.teal { background: #e6fffa; color: #319795; }


    /* ── Formato de los paneles de Hospitalario ── */
    /* El botón "Ocultar estadísticas" marca #statsBody con is-hidden */
    #statsBody.is-hidden { display: none; }
    .at-kpi-card  { flex: 0 1 260px; max-width: 320px; }
    .at-kpi-icon  { flex-shrink: 0; }
    .at-kpi-label { font-size: 14px; text-align: center; }
    .at-filtro-group { min-width: 0; }
    .at-stats-header { gap: 10px; flex-wrap: wrap; }
    .at-stats-body {
        padding: 18px;
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        grid-template-areas: none;
        gap: 18px;
        align-items: start;
    }
    /* Columna derecha: los dos gráficos uno debajo del otro */
    .at-stats-graficos { display: flex; flex-direction: column; gap: 28px; min-width: 0; }
    .at-stats-table-wrap { overflow-x: auto; grid-area: auto; }
    .at-stats-table thead th { white-space: nowrap; }
    .at-stats-table .num { text-align: right; white-space: nowrap; }
    .at-stats-table thead th.num { text-align: right; }
    .at-stats-table tfoot td {
        padding: 8px 10px; font-weight: 700; color: var(--text-main);
        border-top: 2px solid var(--border);
    }
    .at-stats-vacio { padding: 14px 10px; color: var(--text-muted); font-style: italic; text-align: center; }
    .at-chart-box {
        display: flex; flex-direction: column; min-width: 0;
        position: static; height: auto; padding: 0; grid-area: auto;
    }
    .at-chart-box.is-large { height: auto; }
    .at-chart-canvas { position: relative; width: 100%; height: 330px; }
    .at-chart-canvas canvas { height: 100% !important; width: 100% !important; }
    .at-chart-title {
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px; text-align: center;
    }
    @media (max-width: 992px) {
        .at-stats-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .at-panel-body { padding: 14px 12px; }
        .at-stats-body { padding: 12px; }
        .at-filtro-group { flex: 1 1 100%; }
        .at-filtro-select { width: 100%; }
        .at-kpi-card { flex: 1 1 100%; max-width: none; }
        .at-chart-canvas { height: 280px; }
    }
</style>

<!-- BREADCRUMB -->
<div class="at-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('base_views')); ?>">Prehospitalario</a> ›
    <strong>Asistencias — Listado</strong>
</div>

<!-- PANEL PRINCIPAL -->
<div class="at-panel">
    <div class="at-panel-header">
        <span class="at-panel-title">
            <i class="fas fa-clipboard-list"></i> ASISTENCIAS
        </span>
    </div>

    <div class="at-panel-body">
        <!-- ── TOOLBAR ── -->
        <div class="at-toolbar">
            <a href="<?= base_url(route_to('base_views')); ?>" class="bl-btn ghost">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="<?= base_url(route_to('asistencia_export')); ?>?<?= http_build_query([
                'ejercicio' => $filtro_ejercicio,
                'mes'       => $filtro_mes,
                'nombre'    => $filtro_nombre,
                'estado'    => $filtro_estado,
            ]) ?>" class="bl-btn green">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </a>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="<?= base_url(route_to('asistencia_list')); ?>" id="searchForm">
            <div class="at-filtro-bar">

                <!-- EJERCICIO -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Ejercicio</span>
                    <select name="ejercicio" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($ejercicios as $ej): ?>
                            <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- MES -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Mes</span>
                    <select name="mes" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($meses as $m): ?>
                            <option value="<?= $m ?>" <?= $filtro_mes == $m ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- BASE / NOMBRE -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">Base / Nombre</span>
                    <select name="nombre" class="at-filtro-select">
                        <option value="">Todos</option>
                        <?php foreach ($nombres as $n): ?>
                            <option value="<?= $n ?>" <?= $filtro_nombre == $n ? 'selected' : '' ?>><?= $n ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ESTADO — toggle -->
                <div class="at-filtro-group campo-toggle" id="col-estado" style="display:none;">
                    <span class="at-filtro-label">Estado</span>
                    <select name="estado" class="at-filtro-select">
                        <option value="">Todos</option>
                        <option value="activo"      <?= $filtro_estado == 'activo'      ? 'selected' : '' ?>>Activo</option>
                        <option value="desactivado" <?= $filtro_estado == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                    </select>
                </div>

                <!-- ACCIONES FILTRO -->
                <div class="at-filtro-group">
                    <span class="at-filtro-label">&nbsp;</span>
                    <div style="display:flex; gap:6px;">
                        <button type="submit" class="bl-btn teal" style="height:36px; padding:0 16px;">
                            <i class="fas fa-filter"></i>
                        </button>
                        <a href="<?= base_url(route_to('asistencia_list')); ?>" class="bl-btn ghost"
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
        <div class="at-kpi-cards">
            <div class="at-kpi-card kpi-teal">
                <div class="at-kpi-icon"><i class="fas fa-user-md"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">EMERG. CON MÉDICO</div>
                    <div class="at-kpi-value"><?= number_format($totalEmConMedico, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="at-kpi-card kpi-blue">
                <div class="at-kpi-icon"><i class="fas fa-ambulance"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">EMERG. SIN MÉDICO</div>
                    <div class="at-kpi-value"><?= number_format($totalEmSinMedico, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="at-kpi-card kpi-red">
                <div class="at-kpi-icon"><i class="fas fa-heartbeat"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">URGENCIAS</div>
                    <div class="at-kpi-value"><?= number_format($totalUrgencias, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="at-kpi-card kpi-amber">
                <div class="at-kpi-icon"><i class="fas fa-clinic-medical"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">BASES</div>
                    <div class="at-kpi-value"><?= number_format($totalBases, 0, ',', '.') ?></div>
                </div>
            </div>
            
            <div class="at-kpi-card kpi-navy">
                <div class="at-kpi-icon"><i class="fas fa-chart-bar"></i></div>
                <div class="at-kpi-content">
                    <div class="at-kpi-label">TOTAL</div>
                    <div class="at-kpi-value"><?= number_format($totalGeneral, 0, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <!-- ── PANEL ESTADÍSTICAS ── -->
        <div class="at-panel at-stats-panel" id="statsPanel">
            <div class="at-stats-header">
                <span><i class="fas fa-chart-pie"></i> ESTADÍSTICAS DE ASISTENCIAS</span>
                <button type="button" class="bl-btn ghost sm" id="btnToggleStats" title="Mostrar/ocultar estadísticas">
                    <i class="fas fa-eye-slash" id="iconoToggleStats"></i> <span id="textoToggleStats">Ocultar estadísticas</span>
                </button>
            </div>
            <div id="statsBody">
                <div class="at-stats-body">
                    <!-- Tabla detalle -->
                    <div class="at-stats-table-wrap">
                        <div class="at-chart-title" style="text-align:left;">Total por base / ejercicio</div>
                        <table class="at-stats-table">
                            <thead>
                                <tr>
                                    <th>Base</th>
                                    <th>Ejercicio</th>
                                    <?php if ($mostrarMesEnTabla): ?><th>Mes</th><?php endif; ?>
                                    <th class="num">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($statsFilas)): ?>
                                    <tr><td colspan="<?= $mostrarMesEnTabla ? 4 : 3 ?>" class="at-stats-vacio">Sin asistencias para los filtros elegidos</td></tr>
                                <?php endif; ?>
                                <?php foreach ($statsFilas as $f): ?>
                                <tr>
                                    <td><?= esc($f['nombre']) ?></td>
                                    <td><?= esc($f['ejercicio']) ?></td>
                                    <?php if ($mostrarMesEnTabla): ?><td><?= esc($f['mes']) ?></td><?php endif; ?>
                                    <td class="num"><?= number_format((int) $f['cantidad'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($statsFilas)): ?>
                            <tfoot>
                                <tr>
                                    <td colspan="<?= $mostrarMesEnTabla ? 3 : 2 ?>">Total</td>
                                    <td class="num"><?= number_format((int) array_sum(array_map('intval', array_column($statsFilas, 'cantidad'))), 0, ',', '.') ?></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                    <!-- Gráficos apilados: barras arriba, dona abajo -->
                    <div class="at-stats-graficos">
                        <!-- Barras comparativas (paginadas de a 10 bases) -->
                        <div class="at-chart-box" id="containerBarras">
                            <div class="at-chart-title">Comparativo por base (últimos ejercicios)</div>
                            <div class="at-chart-controls" style="display:flex; justify-content:center; gap:10px; align-items:center; margin-bottom:8px;">
                                <button type="button" id="btnPrev" class="bl-btn ghost sm" disabled>Anterior</button>
                                <span id="pageInfo" style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Página 1</span>
                                <button type="button" id="btnNext" class="bl-btn ghost sm">Siguiente</button>
                            </div>
                            <div class="at-chart-canvas" style="height:400px;"><canvas id="chartBarras"></canvas></div>
                        </div>

                        <!-- Torta distribución -->
                        <div class="at-chart-box" id="containerTorta">
                            <div class="at-chart-title">Distribución por ejercicio</div>
                            <div class="at-chart-canvas"><canvas id="chartTorta"></canvas></div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- /panel-stats -->
    </div><!-- /panel-body -->
</div><!-- /panel -->
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script>
    // ── ASISTENCIA_LIST SCRIPTS ──
    (function () {
        var colEstado = document.getElementById('col-estado');
        var btn       = document.getElementById('btnToggle');
        var icono     = document.getElementById('iconoToggle');

        var params  = new URLSearchParams(window.location.search);
        var visible = !!params.get('estado');

        function aplicar() {
            colEstado.style.display = visible ? '' : 'none';
            icono.className = visible ? 'fas fa-times' : 'fas fa-sliders-h';
            btn.title = visible ? 'Ocultar Estado' : 'Mostrar Estado';
        }

        aplicar();

        btn.addEventListener('click', function () {
            visible = !visible;
            aplicar();
        });
    })();

    // ── Datos para gráficos ──
    var datosBarras         = <?= json_encode($statsBarras) ?>;
    var datosTorta          = <?= json_encode($statsTorta) ?>;
    var todasLasBases       = <?= json_encode($todasLasBases ?? []) ?>;
    var baseColorMapServidor = <?= json_encode($baseColorMap ?? []) ?>;
    var filtroEjercicio     = <?= json_encode($filtro_ejercicio ?? '') ?>;
    var filtroMes           = <?= json_encode($filtro_mes ?? '') ?>;
    var filtroNombre        = <?= json_encode($filtro_nombre ?? '') ?>;
    var todosLosEjercicios  = <?= json_encode($ejercicios ?? []) ?>;
    var ordenMeses          = <?= json_encode($meses ?? []) ?>;

    var ejerciciosRecientes = filtroEjercicio 
        ? [filtroEjercicio] 
        : <?= json_encode($statsEjercicios) ?>;

    (function () {
        var statsPanel = document.getElementById('statsPanel');
        var statsBody  = document.getElementById('statsBody');
        var btnStats   = document.getElementById('btnToggleStats');
        var iconoStats = document.getElementById('iconoToggleStats');
        var textoStats = document.getElementById('textoToggleStats');
        var statsVisible = true;
        var chartsInicializados = false;

        var paleta = [
            '#ce93d8', '#7fd8be', '#85c1e9', '#f8c471', '#d2b4de', '#f1948a',
            '#82e0aa', '#f9e79f', '#ffb7b2', '#76d7c4', '#edbb99', '#7dcea0',
            '#bb8fce', '#7fb3d5', '#f8c471', '#b5ead7', '#a3e4d7', '#e6b0aa',
            '#c7ceea', '#e2f0cb', '#ef9a9a', '#ffdac1', '#90caf9', '#a5d6a7'
        ];

        var colorMesMap = {
            'ENERO':      '#ce93d8',
            'FEBRERO':    '#7fd8be',
            'MARZO':      '#85c1e9',
            'ABRIL':      '#f8c471',
            'MAYO':       '#d2b4de',
            'JUNIO':      '#f1948a',
            'JULIO':      '#82e0aa',
            'AGOSTO':     '#f9e79f',
            'SEPTIEMBRE': '#ffb7b2',
            'OCTUBRE':    '#76d7c4',
            'NOVIEMBRE':  '#edbb99',
            'DICIEMBRE':  '#7dcea0'
        };

        function colorParaMes(mes) {
            return colorMesMap[String(mes).toUpperCase()] || '#95a5a6';
        }
        
        var listaEjerciciosOrdenada = todosLosEjercicios.slice().sort(function(a, b) {
            return parseInt(a, 10) - parseInt(b, 10);
        });

        // Colores por ejercicio: 10 tonos de azul (el más viejo primero)
        var paletaEjercicios = [
            '#4FB3E6', '#1A5FA8', '#0B2E59', '#8FD3F4', '#2F80C8',
            '#123F73', '#6EC6EA', '#1E4E8C', '#A9DDF5', '#0F5E9C'
        ];

        var ejercicioColorMap = {};
        listaEjerciciosOrdenada.forEach(function (ejercicio, index) {
            ejercicioColorMap[String(ejercicio)] = paletaEjercicios[index % paletaEjercicios.length];
        });

        function colorParaEjercicio(ejercicio) {
            return ejercicioColorMap[String(ejercicio)] || '#95a5a6';
        }

        var baseColorMap = {};
        (todasLasBases || []).forEach(function (base, index) {
            if (base) {
                var clave = String(base).trim().toUpperCase();
                baseColorMap[clave] = paleta[index % paleta.length];
            }
        });

        function colorParaBase(base) {
            if (!base) return '#95a5a6';
            var clave = String(base).trim().toUpperCase();
            if (baseColorMapServidor && baseColorMapServidor[clave]) {
                return baseColorMapServidor[clave];
            }
            return baseColorMap[clave] || '#95a5a6';
        }

        var chartBarrasInstance;

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
        var paginaActual = 0;
        var tamanoPagina = 10;

        function inicializarCharts() {
            if (chartsInicializados) return;
            if (typeof ChartDataLabels !== 'undefined') Chart.register(ChartDataLabels);
            chartsInicializados = true;

            function obtenerDatosParaPagina(nombresBase, datosOriginales, ejercicios) {
                var inicio = paginaActual * tamanoPagina;
                var fin = inicio + tamanoPagina;
                var labelsPaginados = nombresBase.slice(inicio, fin);
                
                var datasetsPaginados = ejercicios.map(function (ej) {
                    var colorDelAno = colorParaEjercicio(ej);
                    return {
                        label: String(ej),
                        backgroundColor: filtroEjercicio 
                            ? labelsPaginados.map(function (nom) { return colorParaBase(nom); })
                            : colorDelAno,
                        borderColor: 'transparent',
                        borderWidth: 0,
                        maxBarThickness: 90,
                        data: labelsPaginados.map(function (nom) {
                            var fila = datosOriginales.find(function (d) { return d.nombre === nom && d.ejercicio == ej; });
                            return fila ? parseInt(fila.cantidad, 10) : 0;
                        }),
                        legendColor: colorDelAno
                    };
                });
                return { labels: labelsPaginados, datasets: datasetsPaginados };
            }

            var chartLabels = [];
            var datasets = [];

            // CASO 1: Filtrado simultáneo por Año y por Base (Desglose mensual)
            if (filtroEjercicio && filtroNombre) {
                chartLabels = ordenMeses;
                var colorBaseExacto = colorParaBase(filtroNombre);

                datasets = [{
                    label: filtroNombre + ' (' + filtroEjercicio + ')',
                    backgroundColor: ordenMeses.map(function(m) { return colorParaMes(m); }),
                    borderColor: 'transparent',
                    borderWidth: 0,
                    maxBarThickness: 90,
                    data: ordenMeses.map(function(m) {
                        var fila = datosBarras.find(function(d) {
                            return String(d.mes).toUpperCase() === m.toUpperCase();
                        });
                        return fila ? parseInt(fila.cantidad, 10) : 0;
                    })
                }];

            } else {
                // CASO 2: Comparativo normal por Bases
                var baseTotals = {};
                datosBarras.forEach(function(d) {
                    if (!baseTotals[d.nombre]) baseTotals[d.nombre] = 0;
                    baseTotals[d.nombre] += parseInt(d.cantidad, 10);
                });

                var nombresBase = [];
                datosBarras.forEach(function (d) {
                    if (nombresBase.indexOf(d.nombre) === -1) nombresBase.push(d.nombre);
                });

                nombresBase.sort(function(a, b) {
                    var totalA = baseTotals[a] || 0;
                    var totalB = baseTotals[b] || 0;
                    return totalB - totalA;
                });

                // Inicializar paginación
                paginaActual = 0;
                var pagData = obtenerDatosParaPagina(nombresBase, datosBarras, ejerciciosRecientes);
                chartLabels = pagData.labels;
                datasets = pagData.datasets;

                // Controles paginación UI
                var btnPrev = document.getElementById('btnPrev');
                var btnNext = document.getElementById('btnNext');
                var pageInfo = document.getElementById('pageInfo');
                var totalPaginas = Math.ceil(nombresBase.length / tamanoPagina);

                function actualizarPaginacionUI() {
                    pageInfo.textContent = 'Página ' + (paginaActual + 1) + ' de ' + Math.max(1, totalPaginas);
                    btnPrev.disabled = (paginaActual === 0);
                    btnNext.disabled = (paginaActual >= totalPaginas - 1);
                }
                actualizarPaginacionUI();

                btnPrev.addEventListener('click', function() {
                if (paginaActual > 0) {
                    paginaActual--;

                    var newData = obtenerDatosParaPagina(
                        nombresBase,
                        datosBarras,
                        ejerciciosRecientes
                    );

                    chartBarrasInstance.data.labels = newData.labels;
                    chartBarrasInstance.data.datasets = newData.datasets;

                    chartBarrasInstance.update();

                    actualizarPaginacionUI();

                    actualizarGraficoTorta();
                }
            });

            btnNext.addEventListener('click', function() {
                if (paginaActual < totalPaginas - 1) {
                    paginaActual++;

                    var newData = obtenerDatosParaPagina(
                        nombresBase,
                        datosBarras,
                        ejerciciosRecientes
                    );

                    chartBarrasInstance.data.labels = newData.labels;
                    chartBarrasInstance.data.datasets = newData.datasets;

                    chartBarrasInstance.update();

                    actualizarPaginacionUI();

                    actualizarGraficoTorta();
                }
            });
            }

            // Gráfico de Barras
            chartBarrasInstance = crearGrafico(document.getElementById('chartBarras'), 'Sin asistencias por base para los filtros elegidos', {
                type: 'bar',
                data: { labels: chartLabels, datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    maxBarThickness: 90,
                    plugins: { 
                        legend: { 
                            position: 'top',
                            labels: {
                                generateLabels: function(chart) {
                                    if (filtroEjercicio && filtroNombre) {
                                        var colorAnoExacto = colorParaEjercicio(filtroEjercicio);
                                        var colorBaseExacto = colorParaBase(filtroNombre);
                                        

                                        return [
                                            {
                                                text: 'Año: ' + filtroEjercicio,
                                                fillStyle: colorAnoExacto,
                                                strokeStyle: colorAnoExacto,
                                                lineWidth: 1,
                                                hidden: false,
                                                datasetIndex: 0
                                            },
                                            {
                                                text: 'Base: ' + filtroNombre,
                                                fillStyle: colorBaseExacto,
                                                strokeStyle: colorBaseExacto,
                                                lineWidth: 1,
                                                hidden: false,
                                                datasetIndex: 0
                                            }
                                        ];
                                    }

                                    return chart.data.datasets.map(function(dataset, i) {
                                        return {
                                            text: dataset.label,
                                            fillStyle: dataset.legendColor || dataset.borderColor,
                                            strokeStyle: dataset.borderColor,
                                            lineWidth: 1,
                                            hidden: !chart.isDatasetVisible(i),
                                            datasetIndex: i
                                        };
                                    });
                                }
                            }
                        },
                        datalabels: { display: false }
                    },
                    scales: {
                        x: {
                            categoryPercentage: 0.95,
                            barPercentage: 0.95
                        },
                        y: { beginAtZero: true }
                    }
                }
            });


                        // Gráfico de Torta / Anillo
            var elTorta = document.getElementById('chartTorta');
            var chartTortaInstance;

            var labels = [];
            var data = [];
            var colors = [];

            /*
             * ============================================================
             * FUNCIÓN PARA OBTENER LOS DATOS DE LA DONA SEGÚN LA PÁGINA
             * ACTUAL DEL GRÁFICO DE BARRAS
             * ============================================================
             */
            function obtenerDatosTortaParaPagina() {

                /*
                 * ========================================================
                 * CASO 0:
                 * Todo en "Todos" (vacío)
                 *
                 * Muestra distribución por Ejercicio.
                 * ========================================================
                 */
                if (!filtroEjercicio && !filtroMes && !filtroNombre) {
                    var ejercicioMapTorta = {};
                    var totalGeneralTorta = 0;

                    datosBarras.forEach(function(d) {
                        var cantidad = parseInt(d.cantidad, 10) || 0;
                        if (!ejercicioMapTorta[d.ejercicio]) {
                            ejercicioMapTorta[d.ejercicio] = 0;
                        }
                        ejercicioMapTorta[d.ejercicio] += cantidad;
                        totalGeneralTorta += cantidad;
                    });

                    var ejercicios = Object.keys(ejercicioMapTorta);
                    ejercicios.sort();

                    var labels = [];
                    var data = [];
                    var colors = [];

                    ejercicios.forEach(function(ej) {
                        labels.push(ej);
                        data.push(ejercicioMapTorta[ej]);
                        colors.push(colorParaEjercicio(ej));
                    });

                    return {
                        labels: labels,
                        data: data,
                        colors: colors,
                        total: totalGeneralTorta
                    };
                }

                /*
                 * Mes seleccionado sin ejercicio ni base:
                 * mostrar el total de ese mes agrupado por ejercicio.
                 */
                if (filtroMes && !filtroEjercicio && !filtroNombre) {
                    var ejerciciosDelMes = datosTorta.slice().sort(function(a, b) {
                        return Number(a.ejercicio) - Number(b.ejercicio);
                    });

                    var totalMesPorEjercicio = ejerciciosDelMes.reduce(function(total, fila) {
                        return total + (parseInt(fila.total, 10) || 0);
                    }, 0);

                    return {
                        labels: ejerciciosDelMes.map(function(fila) {
                            return fila.ejercicio;
                        }),
                        data: ejerciciosDelMes.map(function(fila) {
                            return parseInt(fila.total, 10) || 0;
                        }),
                        colors: ejerciciosDelMes.map(function(fila) {
                            return colorParaEjercicio(fila.ejercicio);
                        }),
                        total: totalMesPorEjercicio
                    };
                }

                /*
                 * ========================================================
                 * CASO 1:
                 * Año + Base
                 *
                 * Se mantiene el desglose mensual.
                 * ========================================================
                 */
                if (filtroEjercicio && filtroNombre) {

                    var labelsMensuales = ordenMeses;

                    var dataMensual = ordenMeses.map(function(m) {
                        var fila = datosBarras.find(function(d) {
                            return String(d.mes).toUpperCase() === m.toUpperCase()
                                && d.ejercicio == filtroEjercicio
                                && d.nombre == filtroNombre;
                        });

                        return fila ? parseInt(fila.cantidad, 10) : 0;
                    });

                    var colorsMensuales = ordenMeses.map(function(m) {
                        return colorParaMes(m);
                    });

                    var totalMensual = dataMensual.reduce(function(a, b) {
                        return a + b;
                    }, 0);

                    return {
                        labels: labelsMensuales,
                        data: dataMensual,
                        colors: colorsMensuales,
                        total: totalMensual
                    };
                }


                /*
                 * ========================================================
                 * CASO 2:
                 * Año seleccionado
                 *
                 * La dona muestra SOLAMENTE las mismas bases que están
                 * visibles en la página actual de las barras.
                 *
                 * NO SE AGREGA "OTROS".
                 * ========================================================
                 */
                if (filtroEjercicio) {

                    var baseMapTorta = {};
                    var totalEjercicio = 0;

                    datosBarras.forEach(function(d) {

                        if (String(d.ejercicio) == String(filtroEjercicio)) {

                            var cantidad = parseInt(d.cantidad, 10) || 0;

                            if (!baseMapTorta[d.nombre]) {
                                baseMapTorta[d.nombre] = 0;
                            }

                            baseMapTorta[d.nombre] += cantidad;
                            totalEjercicio += cantidad;
                        }
                    });


                    /*
                     * Mismo orden utilizado por el gráfico de barras.
                     */
                    var nombresBasesTorta = Object.keys(baseMapTorta);

                    nombresBasesTorta.sort(function(a, b) {
                        return (baseMapTorta[b] || 0) - (baseMapTorta[a] || 0);
                    });


                    /*
                     * Misma página que el gráfico de barras.
                     */
                    var inicioTorta = paginaActual * tamanoPagina;
                    var finTorta = inicioTorta + tamanoPagina;

                    var basesPaginaTorta = nombresBasesTorta.slice(
                        inicioTorta,
                        finTorta
                    );


                    var labelsTorta = [];
                    var dataTorta = [];
                    var colorsTorta = [];


                    /*
                     * SOLAMENTE las bases visibles en la página.
                     */
                    basesPaginaTorta.forEach(function(nombre) {

                        var valor = baseMapTorta[nombre] || 0;

                        labelsTorta.push(nombre);
                        dataTorta.push(valor);
                        colorsTorta.push(colorParaBase(nombre));
                    });


                    return {
                        labels: labelsTorta,
                        data: dataTorta,
                        colors: colorsTorta,

                        /*
                         * IMPORTANTE:
                         * El total sigue siendo TODO el ejercicio,
                         * no solamente la página actual.
                         */
                        total: totalEjercicio
                    };
                }


                /*
                 * ========================================================
                 * CASO 3:
                 * Sin filtro de año
                 *
                 * La dona muestra SOLAMENTE las mismas bases que están
                 * visibles en la página actual de las barras.
                 *
                 * NO SE AGREGA "OTROS".
                 * ========================================================
                 */
                if (filtroNombre && !filtroEjercicio) {
                    var map = {};
                    var total = 0;
                    datosBarras.forEach(function(d) {
                        var cant = parseInt(d.cantidad, 10) || 0;
                        if (!map[d.ejercicio]) map[d.ejercicio] = 0;
                        map[d.ejercicio] += cant;
                        total += cant;
                    });
                    var years = Object.keys(map).sort();
                    return {
                        labels: years,
                        data: years.map(function(y) { return map[y]; }),
                        colors: years.map(function(y) { return colorParaEjercicio(y); }),
                        total: total
                    };
                }

                var baseMapGeneral = {};
                var totalGeneralTorta = 0;

                datosBarras.forEach(function(d) {

                    var cantidad = parseInt(d.cantidad, 10) || 0;

                    if (!baseMapGeneral[d.nombre]) {
                        baseMapGeneral[d.nombre] = 0;
                    }

                    baseMapGeneral[d.nombre] += cantidad;
                    totalGeneralTorta += cantidad;
                });


                /*
                 * Mismo orden utilizado por las barras:
                 * mayor cantidad primero.
                 */
                var nombresBasesGeneral = Object.keys(baseMapGeneral);

                nombresBasesGeneral.sort(function(a, b) {
                    return (baseMapGeneral[b] || 0) - (baseMapGeneral[a] || 0);
                });


                /*
                 * Misma página que el gráfico de barras.
                 */
                var inicioGeneral = paginaActual * tamanoPagina;
                var finGeneral = inicioGeneral + tamanoPagina;

                var basesPaginaGeneral = nombresBasesGeneral.slice(
                    inicioGeneral,
                    finGeneral
                );


                var labelsGeneral = [];
                var dataGeneral = [];
                var colorsGeneral = [];


                /*
                 * SOLAMENTE las bases visibles en la página.
                 */
                basesPaginaGeneral.forEach(function(nombre) {

                    var valor = baseMapGeneral[nombre] || 0;

                    labelsGeneral.push(nombre);
                    dataGeneral.push(valor);
                    colorsGeneral.push(colorParaBase(nombre));
                });


                return {
                    labels: labelsGeneral,
                    data: dataGeneral,
                    colors: colorsGeneral,

                    /*
                     * El centro conserva el total completo.
                     */
                    total: totalGeneralTorta
                };
            }


            /*
             * ============================================================
             * CREAR / ACTUALIZAR DONA
             * ============================================================
             */
            function actualizarGraficoTorta() {

                var datosTortaActuales = obtenerDatosTortaParaPagina();

                labels = datosTortaActuales.labels;
                data = datosTortaActuales.data;
                colors = datosTortaActuales.colors;

                var totalSum = datosTortaActuales.total;

                // Contar cuántos segmentos tienen un valor mayor a 0
                var seccionesConDatos = data.filter(function(value) {
                    return Number(value) > 0;
                }).length;

                /*
                 * Si la dona ya existe, actualizamos los datos y la configuración dinámica
                 */
                if (chartTortaInstance) {

                    chartTortaInstance.data.labels = labels;
                    chartTortaInstance.data.datasets[0].data = data;
                    chartTortaInstance.data.datasets[0].backgroundColor = colors;
                    chartTortaInstance.data.datasets[0].borderWidth = seccionesConDatos > 1 ? 3 : 0;
                    chartTortaInstance.data.datasets[0].hoverOffset = seccionesConDatos > 1 ? 8 : 0;
                    chartTortaInstance.data.datasets[0].borderRadius = 4;

                    chartTortaInstance.options.plugins.centerTotal = totalSum;

                    chartTortaInstance.update();

                    return;
                }


                /*
                 * ========================================================
                 * TEXTO DEL CENTRO
                 * ========================================================
                 */
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

                var donutSeparatorPlugin = {
                    id: 'donutSeparator',

                    afterDatasetDraw: function(chart, args) {

                        if (chart.config.type !== 'doughnut' || args.index !== 0) return;

                        var arcs = chart.getDatasetMeta(0).data;
                        if (!arcs.length) return;

                        var ctx = chart.ctx;
                        ctx.save();
                        ctx.strokeStyle = '#ffffff';
                        ctx.lineWidth = 1;
                        ctx.lineCap = 'butt';

                        arcs.forEach(function(arc) {
                            var angle = arc.endAngle;

                            ctx.beginPath();
                            ctx.moveTo(
                                arc.x + Math.cos(angle) * arc.innerRadius,
                                arc.y + Math.sin(angle) * arc.innerRadius
                            );
                            ctx.lineTo(
                                arc.x + Math.cos(angle) * arc.outerRadius,
                                arc.y + Math.sin(angle) * arc.outerRadius
                            );
                            ctx.stroke();
                        });

                        ctx.restore();
                    }
                };

                /*
                 * ========================================================
                 * CREAR DONA
                 * ========================================================
                 */
                chartTortaInstance = crearGrafico(elTorta, 'Sin asistencias por ejercicio para los filtros elegidos', {

                    type: 'doughnut',

                    data: {
                        labels: labels,

                        datasets: [{
                            data: data,
                            backgroundColor: colors,
                            borderColor: '#ffffff',
                            // Separación con borde blanco: con "spacing", Chart.js dibuja las
                            // porciones muy chicas como un aro completo
                            borderWidth: seccionesConDatos > 1 ? 3 : 0,
                            hoverOffset: seccionesConDatos > 1 ? 8 : 0,
                            borderRadius: 4
                        }]
                    },
                    plugins: [centerTextPlugin, donutSeparatorPlugin],

                    options: {

                        responsive: true,
                        maintainAspectRatio: false,

                        cutout: '60%',

                        plugins: {

                            centerTotal: totalSum,

                            legend: {
                                position: window.matchMedia('(max-width: 600px)').matches ? 'bottom' : 'right',

                                labels: {
                                    boxWidth: 12,
                                    padding: 8,
                                    font: { size: 11 }
                                }
                            },

                            datalabels: {
                            color: function (ctx) {
                                // Texto blanco sobre fondos oscuros (azules de los ejercicios), gris sobre los claros
                                var bg = ctx.dataset.backgroundColor;
                                bg = Array.isArray(bg) ? bg[ctx.dataIndex] : bg;
                                var m = /^#([0-9a-f]{6})/i.exec(bg || '');
                                if (!m) return '#5a5858';
                                var n = parseInt(m[1], 16);
                                return (0.299 * (n >> 16) + 0.587 * ((n >> 8) & 255) + 0.114 * (n & 255)) < 150 ? '#ffffff' : '#5a5858';
                            },
                            font: {
                                weight: 'bold',
                                size: 13
                            },
                            // 1. CONTROL DE VISIBILIDAD NATIVO:
                            // Oculta completamente el contenedor del datalabel si el valor es 0 o negativo
                            display: function(context) {
                                var val = context.dataset.data[context.dataIndex];
                                return val !== null && Number(val) > 0;
                            },
                            anchor: 'center',
                            align: 'center',

                            // 2. CÁLCULO DINÁMICO Y FORMATO SEGURO:
                            formatter: function(value, context) {
                                // Obtiene los datos del dataset actual de forma segura
                                var dataset = context.chart.data.datasets[context.datasetIndex].data;
                                
                                // Suma el total dinámicamente en tiempo real
                                var totalSum = dataset.reduce(function(acc, curr) {
                                    var num = Number(curr);
                                    return acc + (isNaN(num) ? 0 : num);
                                }, 0);

                                // Si la suma total es 0, no dibuja nada
                                if (totalSum === 0) return null;

                                var percentage = (Number(value) / totalSum) * 100;

                                // Oculta porcentajes menores o iguales al 2% para evitar que el texto
                                // se encime en sectores demasiado angostos
                                if (percentage <= 2) return null;

                                // Formato con localización local (Argentina / Latam)
                                // Redondea a entero si es exacto, o muestra máximo 1 decimal si es necesario
                                return percentage.toLocaleString('es-AR', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 1
                                }) + '%';
                            }
                        }
                        }
                    }
                });
            }


            actualizarGraficoTorta();
        }

        btnStats.addEventListener('click', function () {
            statsVisible = !statsVisible;
            if (statsVisible) {
                statsBody.classList.remove('is-hidden');
                statsPanel.classList.remove('is-collapsed');
                iconoStats.className = 'fas fa-eye-slash';
                textoStats.textContent = 'Ocultar estadísticas';
                inicializarCharts();
            } else {
                statsBody.classList.add('is-hidden');
                statsPanel.classList.add('is-collapsed');
                iconoStats.className = 'fas fa-eye';
                textoStats.textContent = 'Mostrar estadísticas';
            }
        });

        inicializarCharts();
    })();//111
</script>
<?= $this->endSection() ?>