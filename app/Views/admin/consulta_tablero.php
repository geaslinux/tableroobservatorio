<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Atenciones de consulta · Ministerio de Salud<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('css/panel.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/consulta-tablero.css') ?>?v=<?= @filemtime(FCPATH . 'css/consulta-tablero.css') ?: '1' ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$url = static function (array $cambios = [], string $ruta = 'consulta_tablero') use ($filtros): string {
    $query = http_build_query(array_filter(array_merge($filtros, $cambios), static fn($v) => $v !== ''));
    // Sin filtros se envía "desde=" vacío: una URL sin parámetros es la primera carga (año actual)
    return base_url(route_to($ruta)) . '?' . ($query !== '' ? $query : 'desde=');
};
$inicial = $inicial ?? false;
$numero = static fn($v) => number_format((int) $v, 0, ',', '.');
$fecha = static fn($v) => $v ? date('d/m/Y', strtotime($v)) : 'Sin datos';
$sinDato = static fn($v) => $v ? esc($v) : '<span class="ct-sin-dato">Sin informar</span>';
$opciones = ['efector' => [], 'formulario' => [], 'especialidad' => []];
foreach ($catalogos['formularios'] as $f) {
    $opciones['efector'][$f['efector_id']] = $f['efector'];
    if (!$filtros['efector'] || $filtros['efector'] == $f['efector_id']) {
        $opciones['formulario'][$f['formulario_id']] = $f['titulo'];
        $opciones['especialidad'][$f['especialidad_id']] = $f['especialidad'];
    }
}
foreach (['medico', 'hospital', 'servicio'] as $key) {
    $opciones[$key] = [];
    foreach ($catalogos[$key] as $item) if ((string) $item['id'] !== '') $opciones[$key][$item['id']] = $item['nombre'];
}
$etiquetas = ['efector' => 'Efector donde se realizó la atención', 'formulario' => 'Servicio/formulario de atención', 'especialidad' => 'Especialidad de atención', 'medico' => 'Médico', 'hospital' => 'Hospital de origen de la interconsulta', 'servicio' => 'Servicio de origen de la interconsulta'];
$etiquetasExtra = ['desde' => 'Desde', 'hasta' => 'Hasta', 'con_procedimiento' => 'Con procedimiento'];
$secciones = ['filtros' => ['Filtros', 'fa-filter'], 'evolucion' => ['Evolución', 'fa-chart-line'], 'medicos' => ['Médicos', 'fa-user-doctor'], 'distribuciones' => ['Distribuciones', 'fa-chart-bar'], 'detalle' => ['Detalle', 'fa-list']];
$graficos = [];
?>
<div class="base-list-wrap consulta-tablero">

    <?= view('partials/breadcrumb', ['nodo' => 'consulta_tablero']) ?>

    <!-- ENCABEZADO -->
    <header class="ct-hero">
        <div class="ct-hero-icon" aria-hidden="true"><i class="fas fa-notes-medical"></i></div>
        <div class="ct-hero-texto">
            <h1>Atenciones de consulta</h1>
            <p>Actividad asistencial por efector y servicio/formulario de atención.</p>
        </div>
        <?php if ($datos !== null): ?>
        <div class="ct-hero-acciones">
            <a class="bl-btn green" href="<?= esc($url([], 'consulta_tablero_exportar'), 'attr') ?>"><i class="fas fa-file-excel"></i> Exportar Excel</a>
        </div>
        <?php endif ?>
    </header>

    <!-- NAVEGACIÓN POR SECCIONES -->
    <nav class="ct-secciones" aria-label="Secciones del tablero">
        <?php foreach ($secciones as $ancla => [$texto, $icono]): ?>
            <a href="#consulta-<?= $ancla ?>"><i class="fas <?= $icono ?>" aria-hidden="true"></i> <?= $texto ?></a>
        <?php endforeach ?>
    </nav>

    <?php if ($error): ?>
        <div class="bl-alert is-danger" role="alert">
            <div class="bl-alert-head"><i class="fas fa-triangle-exclamation"></i> <?= esc($error) ?></div>
            <a class="bl-btn ghost sm" href="<?= esc($url(), 'attr') ?>"><i class="fas fa-rotate-right"></i> Volver a intentar</a>
        </div>
    <?php endif ?>

    <!-- FILTROS -->
    <section class="bl-panel" id="consulta-filtros" aria-labelledby="titulo-filtros">
        <div class="bl-panel-header">
            <h2 class="bl-panel-title" id="titulo-filtros"><i class="fas fa-filter" aria-hidden="true"></i> Filtros de atención</h2>
            <button type="button" class="bl-btn ghost sm" id="consulta-filtros-toggle" title="Mostrar/ocultar filtros" aria-expanded="true" aria-controls="consulta-form"<?= $error ? ' data-forzar-abierto' : '' ?>>
                <i class="fas fa-eye-slash" aria-hidden="true"></i> <span class="ct-filtros-toggle-texto">Ocultar filtros</span>
            </button>
        </div>
        <div class="bl-panel-body">
            <form method="get" action="<?= base_url(route_to('consulta_tablero')) ?>" id="consulta-form">
                <div class="ct-filtros">
                    <?php foreach ($etiquetas as $key => $label): ?>
                        <div class="bl-filtro-group">
                            <label class="bl-filtro-label" for="consulta-<?= $key ?>"><?= esc($label) ?></label>
                            <select class="bl-filtro-select" id="consulta-<?= $key ?>" name="<?= $key ?>">
                                <option value="">Todos los permitidos</option>
                                <?php foreach ($opciones[$key] as $id => $nombre): ?>
                                    <option value="<?= esc((string) $id, 'attr') ?>" <?= (string) $id === $filtros[$key] ? 'selected' : '' ?>><?= esc($nombre) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    <?php endforeach ?>
                    <?php foreach (['desde' => 'Fecha desde (inclusive)', 'hasta' => 'Fecha hasta (inclusive)'] as $key => $label): ?>
                        <div class="bl-filtro-group">
                            <label class="bl-filtro-label" for="consulta-<?= $key ?>"><?= esc($label) ?></label>
                            <?php $fechaCampo = $filtros[$key] !== '' ? \DateTime::createFromFormat('!Y-m-d', $filtros[$key]) : false; ?>
                            <div class="ct-fecha">
                                <input class="bl-filtro-input" type="text" id="consulta-<?= $key ?>" data-fecha="<?= $key ?>" data-min="<?= esc($catalogos['fechas']['min'] ?? '', 'attr') ?>" data-max="<?= date('Y-m-d') ?>" value="<?= esc($fechaCampo ? $fechaCampo->format('d/m/Y') : $filtros[$key], 'attr') ?>" placeholder="dd/mm/aaaa" inputmode="numeric" autocomplete="off" maxlength="10">
                                <i class="fas fa-calendar-days" aria-hidden="true"></i>
                                <input type="hidden" name="<?= $key ?>" value="<?= esc($filtros[$key], 'attr') ?>">
                            </div>
                        </div>
                    <?php endforeach ?>
                    <div class="bl-filtro-group ct-filtro-check">
                        <label class="bf-check"><input type="checkbox" name="con_procedimiento" value="1" <?= $filtros['con_procedimiento'] === '1' ? 'checked' : '' ?>> Solo atenciones con procedimiento</label>
                    </div>
                </div>

                <p class="ct-ayuda"><i class="fas fa-circle-info" aria-hidden="true"></i> Los filtros de origen identifican quién generó la interconsulta; no indican el servicio que atendió al paciente.</p>

                <div class="ct-filtros-pie">
                    <div class="bl-filtro-actions">
                        <button class="bl-btn navy" type="submit"><i class="fas fa-magnifying-glass"></i> Aplicar filtros</button>
                        <a class="bl-btn ghost" href="<?= base_url(route_to('consulta_tablero')) ?>"><i class="fas fa-eraser"></i> Restablecer filtros</a>
                    </div>
                    <p class="ct-carga" id="consulta-carga" role="status" hidden><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Consultando datos…</p>
                </div>
            </form>

            <div class="ct-aplicados" aria-label="Filtros aplicados">
                <span class="ct-aplicados-titulo">Filtros aplicados</span>
                <?php $aplicados = 0; foreach ($filtros as $key => $valor): if ($valor === '') continue; $aplicados++; ?>
                    <a class="bl-tag navy" href="<?= esc($url([$key => '']), 'attr') ?>" title="Quitar este filtro">
                        <?= esc(($etiquetas[$key] ?? $etiquetasExtra[$key] ?? $key) . ': ' . ($opciones[$key][$valor] ?? ($key === 'con_procedimiento' ? 'Sí' : $valor))) ?>
                        <i class="fas fa-xmark" aria-hidden="true"></i><span class="ct-sr">(quitar)</span>
                    </a>
                <?php endforeach ?>
                <?php if (!$aplicados): ?><span class="bl-tag gray">Todo el historial autorizado</span><?php endif ?>
                <?php if ($inicial): ?>
                    <span class="ct-toolbar-nota"><i class="fas fa-circle-info" aria-hidden="true"></i> Por defecto se muestra el año <?= date('Y') ?>.</span>
                    <a class="bl-btn ghost sm" href="<?= esc($url(['desde' => '']), 'attr') ?>"><i class="fas fa-clock-rotate-left"></i> Ver todo el historial</a>
                <?php endif ?>
            </div>
        </div>
    </section>

    <?php if ($datos !== null): $k = $datos['kpi']; $total = (int) $k['atenciones']; ?>

        <?php if (!$catalogos['formularios']): ?>
            <div class="bl-alert is-info" role="status"><div class="bl-alert-head"><i class="fas fa-lock"></i> No tiene formularios asociados a sus grupos. Solicite al administrador que revise su acceso.</div></div>
        <?php elseif (!$total): ?>
            <div class="bl-alert is-info" role="status"><div class="bl-alert-head"><i class="fas fa-inbox"></i> Sin atenciones para los filtros aplicados.</div></div>
        <?php endif ?>

        <!-- TARJETAS KPI -->
        <?php
        $conProcedimiento = (int) $k['procedimientos'];
        $sinProcedimiento = max(0, $total - $conProcedimiento);
        $porcentaje = static fn (int $n) => $total ? number_format(100 * $n / $total, 1, ',', '.') . '% de ' . $numero($total) . ' atenciones' : 'Sin denominador: 0 atenciones';
        ?>
        <?= view('partials/kpi_cards', ['tarjetas' => [
            ['label' => 'Pacientes únicos', 'valor' => $k['pacientes'], 'icono' => 'fa-users', 'color' => 'blue', 'sub' => 'Personas distintas en el universo filtrado'],
            ['label' => 'Médicos con actividad', 'valor' => $k['medicos'], 'icono' => 'fa-user-doctor', 'color' => 'purple', 'sub' => 'Profesionales distintos'],
            ['label' => 'Atenciones con procedimiento', 'valor' => $conProcedimiento, 'icono' => 'fa-file-medical', 'color' => 'navy', 'sub' => $porcentaje($conProcedimiento)],
            ['label' => 'Atenciones sin procedimiento', 'valor' => $sinProcedimiento, 'icono' => 'fa-file-circle-xmark', 'color' => 'amber', 'sub' => $porcentaje($sinProcedimiento)],
            ['label' => 'Atenciones', 'valor' => $total, 'icono' => 'fa-notes-medical', 'color' => 'teal', 'sub' => 'Registros vigentes del período'],
        ]]) ?>

        <!-- PERÍODO -->
        <div class="ct-periodo">
            <span><i class="fas fa-calendar-days" aria-hidden="true"></i>Período solicitado: <strong><?= $filtros['desde'] ? esc($fecha($filtros['desde'])) : 'inicio del historial' ?></strong> a <strong><?= $filtros['hasta'] ? esc($fecha($filtros['hasta'])) : 'fin del historial' ?></strong></span>
            <span><i class="fas fa-database" aria-hidden="true"></i>Datos encontrados: <strong><?= esc($fecha($k['desde'])) ?></strong> a <strong><?= esc($fecha($k['hasta'])) ?></strong></span>
            <span class="ct-nota">Los pacientes únicos no se suman entre médicos o servicios.</span>
        </div>

        <!-- EVOLUCIÓN -->
        <?php
        // Tabla accesible del gráfico: atenciones por mes de cada médico graficado, más el total de todos los médicos
        $sm = $datos['serie_medicos'];
        $totalMedicos = count($datos['medicos']);
        // El gráfico arranca con los primeros médicos; el resto viaja en la serie para agregarlo desde el desplegable
        $visibles = min(count($sm['series']), $sm['visibles'] ?? count($sm['series']));
        $restantes = array_slice($sm['series'], $visibles);
        // La tabla lleva todos los médicos de la serie; los que no entran entre los primeros van ocultos (ct-col-extra)
        // hasta que se despliega el resto en la leyenda del gráfico
        $seriesTabla = $sm['series'];
        $sm['series'] = array_slice($sm['series'], 0, $visibles);
        $porMes = [];
        foreach ($seriesTabla as $i => $s) {
            foreach ($s['datos'] as $d => $cantidad) {
                $mes = substr($sm['dias'][$d], 0, 7);
                $porMes[$mes][$i] = ($porMes[$mes][$i] ?? 0) + $cantidad;
            }
        }
        $totalMes = array_column($datos['serie'], 'cantidad', 'nombre');
        ?>
        <section class="bl-panel" id="consulta-evolucion" aria-labelledby="titulo-evolucion">
            <div class="bl-panel-header">
                <h2 class="bl-panel-title" id="titulo-evolucion"><i class="fas fa-chart-line" aria-hidden="true"></i> Atenciones por médico · temporal</h2>
                <div class="ct-panel-acciones">
                    <?php
                    // Rótulo con los primeros médicos y con el resto desplegado (lo cambia "N médicos más" de la leyenda)
                    $rotuloMedicos = static fn (int $n) => $n < $totalMedicos ? 'Los ' . $n . ' médicos con más atenciones' : 'Todos los médicos';
                    ?>
                    <span class="bl-panel-meta" id="consulta-tiempo-rotulo" data-plegado="<?= esc($rotuloMedicos(count($sm['series'])), 'attr') ?>" data-desplegado="<?= esc($rotuloMedicos(count($seriesTabla)), 'attr') ?>"><?= esc($rotuloMedicos(count($sm['series']))) ?></span>
                    <button type="button" class="bl-btn ghost sm" data-ocultar-panel="gráfico" title="Mostrar/ocultar gráfico" aria-expanded="true">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i> <span>Ocultar gráfico</span>
                    </button>
                </div>
            </div>
            <div class="bl-panel-body">
                <?php if ($sm['dias']): ?>
                    <div class="ct-agrupar" role="group" aria-label="Agrupar el gráfico por">
                        <span class="ct-agrupar-label">Agrupar por</span>
                        <?php foreach (['dia' => 'Día', 'semana' => 'Semana', 'mes' => 'Mes'] as $modo => $texto): ?>
                            <button type="button" class="ct-agrupar-btn" data-agrupar="<?= $modo ?>" aria-pressed="false"><?= $texto ?></button>
                        <?php endforeach ?>
                    </div>
                    <p class="ct-intro">
                        Atenciones de cada médico por <strong id="consulta-tiempo-unidad">día</strong>. Tocá un médico de la leyenda para ver solo su línea; tocá otros para sumarlos. <button type="button" class="bl-btn navy-ghost ct-ver-todos" id="consulta-tiempo-todos" hidden><i class="fas fa-users" aria-hidden="true"></i> Ver todos los médicos</button>
                    </p>
                    <!-- Leyenda armada por consulta-tablero.js: la flecha del final despliega los médicos restantes -->
                    <div class="ct-leyenda" id="consulta-tiempo-leyenda" role="group" aria-label="Médicos del gráfico"></div>
                    <?php if ($totalMedicos > $visibles + count($restantes)): ?><p class="ct-nota">El gráfico incluye <?= $visibles + count($restantes) ?> de <?= $totalMedicos ?> médicos; para ver los demás, filtrá por médico.</p><?php endif ?>
                    <div class="consulta-chart ct-chart-alto"><canvas id="consulta-medicos-tiempo" data-seccion-toggle="propio" role="img" aria-label="Atenciones por médico a lo largo del tiempo; totales mensuales en la tabla siguiente"></canvas></div>
                <?php else: ?>
                    <p class="ct-vacio"><i class="fas fa-chart-line" aria-hidden="true"></i> Sin datos.</p>
                <?php endif ?>
                <details class="ct-tabla-completa">
                    <summary><i class="fas fa-chevron-right" aria-hidden="true"></i> Ver tabla por mes y médico</summary>
                    <div class="bl-table-wrap">
                        <table class="bl-table ct-paginada ct-tabla-centrada ct-tabla-mes" id="consulta-tabla-mes" data-fijas>
                            <caption class="ct-sr">Atenciones por mes (año-mes) de cada médico graficado y total de todos los médicos</caption>
                            <thead><tr>
                                <th scope="col" data-fija="izq">Mes</th>
                                <?php foreach ($seriesTabla as $i => $s): ?><th scope="col" class="num<?= $i >= $visibles ? ' ct-col-extra' : '' ?>"><?= esc($s['nombre']) ?></th><?php endforeach ?>
                                <th scope="col" class="num" data-fija="der">Total (todos)</th>
                            </tr></thead>
                            <tbody>
                                <?php foreach ($datos['serie'] as $fila): ?>
                                    <tr>
                                        <td class="ct-nowrap"><?= esc($fila['nombre']) ?></td>
                                        <?php foreach ($seriesTabla as $i => $s): ?><td class="num<?= $i >= $visibles ? ' ct-col-extra' : '' ?>"><?= $numero($porMes[$fila['nombre']][$i] ?? 0) ?></td><?php endforeach ?>
                                        <td class="num strong"><?= $numero($totalMes[$fila['nombre']] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach ?>
                                <?php if (!$datos['serie']): ?><tr><td colspan="<?= count($seriesTabla) + 2 ?>" class="bl-empty">Sin datos.</td></tr><?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </section>

        <!-- MÉDICOS -->
        <?php $graficos[] = ['id' => 'consulta-medicos-chart', 'tipo' => 'bar', 'iniciales' => 10, 'nombres' => true, 'filas' => array_map(static fn($m) => ['nombre' => $m['nombre'], 'cantidad' => (int) $m['cantidad']], $datos['medicos'])]; ?>
        <section class="bl-panel" id="consulta-medicos" aria-labelledby="titulo-medicos">
            <div class="bl-panel-header">
                <h2 class="bl-panel-title" id="titulo-medicos"><i class="fas fa-user-doctor" aria-hidden="true"></i> Actividad por médico</h2>
                <div class="ct-panel-acciones">
                    <?php
                    // Igual que el gráfico temporal: cambia con el botón "Ver N más" de las barras
                    $rotuloBarras = $totalMedicos > 10 ? 'Los 10 médicos con más atenciones' : 'Todos los médicos';
                    ?>
                    <span class="bl-panel-meta" data-plegado="<?= esc($rotuloBarras, 'attr') ?>" data-desplegado="Todos los médicos"><?= esc($rotuloBarras) ?></span>
                    <button type="button" class="bl-btn ghost sm" data-ocultar-panel="gráfico" title="Mostrar/ocultar gráfico" aria-expanded="true">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i> <span>Ocultar gráfico</span>
                    </button>
                </div>
            </div>
            <div class="bl-panel-body">
                <p class="ct-intro">Primeros <?= min(10, count($datos['medicos'])) ?> de <?= count($datos['medicos']) ?> profesionales, ordenados por atenciones<?= count($datos['medicos']) > 10 ? '; el botón debajo del gráfico muestra el resto' : '' ?>. La tabla incluye todos. El nombre mostrado es una de sus denominaciones históricas; el detalle conserva cada registro original.</p>
                <?php if ($datos['medicos']): ?>
                    <div class="consulta-chart"><canvas id="consulta-medicos-chart" data-seccion-toggle="propio" role="img" aria-label="Atenciones por médico; valores en la tabla siguiente"></canvas></div>
                <?php endif ?>
                <div class="bl-table-wrap">
                    <table class="bl-table ct-ordenable ct-paginada ct-tabla-centrada">
                        <caption class="ct-sr">Profesionales del mismo universo filtrado. Los encabezados ordenan la tabla.</caption>
                        <thead><tr>
                            <th scope="col">Médico</th>
                            <th scope="col" class="num" data-tipo="num" aria-sort="descending">Atenciones</th>
                            <th scope="col" class="num" data-tipo="num">Pacientes únicos</th>
                            <th scope="col" class="num" data-tipo="num">Con procedimiento</th>
                            <th scope="col" class="center" data-orden="no">Acción</th>
                        </tr></thead>
                        <tbody>
                            <?php foreach ($datos['medicos'] as $fila): ?>
                                <tr>
                                    <td class="strong"><?= esc($fila['nombre']) ?></td>
                                    <td class="num"><?= $numero($fila['cantidad']) ?></td>
                                    <td class="num"><?= $numero($fila['pacientes']) ?></td>
                                    <td class="num"><?= $numero($fila['procedimientos']) ?></td>
                                    <td class="center"><a href="<?= esc($url(['medico' => $fila['id']]), 'attr') ?>#consulta-detalle"><i class="fas fa-filter" aria-hidden="true"></i> Filtrar médico</a></td>
                                </tr>
                            <?php endforeach ?>
                            <?php if (!$datos['medicos']): ?><tr><td colspan="5" class="bl-empty"><i class="fas fa-user-doctor"></i>Sin datos.</td></tr><?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- DISTRIBUCIONES -->
        <section class="bl-panel" id="consulta-distribuciones" aria-labelledby="titulo-distribuciones">
            <div class="bl-panel-header">
                <h2 class="bl-panel-title" id="titulo-distribuciones"><i class="fas fa-chart-bar" aria-hidden="true"></i> Distribuciones de las atenciones</h2>
                <div class="ct-panel-acciones">
                    <button type="button" class="bl-btn ghost sm" data-ocultar-panel="gráficos" title="Mostrar/ocultar gráficos" aria-expanded="true">
                        <i class="fas fa-eye-slash" aria-hidden="true"></i> <span>Ocultar gráficos</span>
                    </button>
                </div>
            </div>
            <div class="bl-panel-body">
                <p class="ct-intro">Diagnósticos y procedimientos son textos registrados, sin clasificación clínica normalizada. Porcentajes sobre <?= $numero($total) ?> atenciones filtradas.</p>
                <div class="bl-grid-2">
                <?php $indice = 0; foreach ($datos['distribuciones'] as $titulo => $filas): $id = 'consulta-distribucion-' . $indice++; $graficos[] = ['id' => $id, 'tipo' => in_array($titulo, ['Efector de atención', 'Servicio/formulario de atención'], true) ? 'doughnut' : 'bar', 'filas' => array_slice($filas, 0, 10)]; ?>
                    <section class="ct-dist" aria-labelledby="<?= $id ?>-titulo">
                        <h3 class="bl-section-header" id="<?= $id ?>-titulo"><span><?= esc($titulo) ?></span><span class="ct-dist-cuenta"><?= count($filas) ?> categorías</span></h3>
                        <div class="bl-section-body">
                            <?php if ($filas): ?>
                                <p class="ct-intro">Primeras <?= min(10, count($filas)) ?> de <?= count($filas) ?> categorías.</p>
                                <div class="consulta-chart"><canvas id="<?= $id ?>" data-seccion-toggle="propio" role="img" aria-label="<?= esc($titulo, 'attr') ?>; valores en la tabla siguiente"></canvas></div>
                            <?php else: ?>
                                <p class="ct-vacio"><i class="fas fa-chart-bar" aria-hidden="true"></i> Sin datos.</p>
                            <?php endif ?>
                            <details class="ct-tabla-completa">
                                <summary><i class="fas fa-chevron-right" aria-hidden="true"></i> Ver tabla completa (<?= count($filas) ?> categorías)</summary>
                                <div class="bl-table-wrap">
                                    <table class="bl-table ct-paginada">
                                        <caption class="ct-sr"><?= esc($titulo) ?></caption>
                                        <thead><tr><th scope="col">Categoría</th><th scope="col" class="num">Atenciones</th><th scope="col">% de <?= $numero($total) ?> atenciones</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($filas as $fila): $pct = $total ? 100 * $fila['cantidad'] / $total : null; ?>
                                                <tr>
                                                    <td><?= esc($fila['nombre']) ?></td>
                                                    <td class="num"><?= $numero($fila['cantidad']) ?></td>
                                                    <td>
                                                        <?php if ($pct === null): ?>No aplica<?php else: ?>
                                                            <div class="ct-pct"><span class="ct-pct-barra" aria-hidden="true"><span style="width: <?= number_format(min(100, $pct), 1, '.', '') ?>%"></span></span><?= number_format($pct, 1, ',', '.') ?>%</div>
                                                        <?php endif ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach ?>
                                            <?php if (!$filas): ?><tr><td colspan="3" class="bl-empty">Sin datos.</td></tr><?php endif ?>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </section>
                <?php endforeach ?>
                </div>
            </div>
        </section>

        <!-- DETALLE -->
        <?php $paginas = max(1, (int) ceil($total / 25)); ?>
        <section class="bl-panel" id="consulta-detalle" aria-labelledby="titulo-detalle">
            <div class="bl-panel-header">
                <h2 class="bl-panel-title" id="titulo-detalle"><i class="fas fa-list" aria-hidden="true"></i> Detalle verificable de atenciones</h2>
                <span class="bl-panel-meta"><?= $numero($total) ?> atenciones · 25 por página</span>
            </div>
            <div class="bl-panel-body">
                <div class="bl-toolbar">
                    <a class="bl-btn green sm" href="<?= esc($url([], 'consulta_tablero_exportar'), 'attr') ?>"><i class="fas fa-file-excel"></i> Exportar Excel con estos filtros</a>
                    <?php if ($filtros['con_procedimiento'] === '1'): ?>
                        <a class="bl-btn ghost sm" href="<?= esc($url(['con_procedimiento' => '']), 'attr') ?>#consulta-detalle"><i class="fas fa-list"></i> Ver todas las atenciones</a>
                    <?php else: ?>
                        <a class="bl-btn navy-ghost sm" href="<?= esc($url(['con_procedimiento' => '1']), 'attr') ?>#consulta-detalle"><i class="fas fa-file-medical"></i> Solo con procedimiento</a>
                    <?php endif ?>
                    <span class="ct-toolbar-nota"><i class="fas fa-user-shield" aria-hidden="true"></i> Se omiten identificables de pacientes.</span>
                </div>
                <div class="bl-table-wrap">
                    <table class="bl-table ct-tabla-detalle">
                        <caption class="ct-sr">Registros correspondientes a los filtros aplicados</caption>
                        <thead><tr>
                            <th scope="col">Fecha/hora</th>
                            <th scope="col">Formulario</th>
                            <th scope="col">Profesional</th>
                            <th scope="col">Diagnóstico registrado</th>
                            <th scope="col">Procedimiento registrado</th>
                            <th scope="col">Hospital de origen</th>
                            <th scope="col">Servicio de origen</th>
                            <th scope="col" class="center">Detalle</th>
                        </tr></thead>
                        <tbody>
                            <?php foreach ($datos['detalle'] as $fila): ?>
                                <tr>
                                    <td class="ct-nowrap"><strong><?= esc($fecha($fila['fecha'])) ?></strong><br><span class="ct-hora"><?= esc(substr($fila['hora'], 0, 5)) ?> h</span></td>
                                    <td><?= esc($fila['formulario']) ?></td>
                                    <td class="strong"><?= esc($fila['profesional']) ?></td>
                                    <td class="ct-texto"><?= esc($fila['diagnostico']) ?></td>
                                    <td class="ct-texto"><?= $sinDato($fila['procedimiento']) ?></td>
                                    <td><?= $sinDato($fila['hospital']) ?></td>
                                    <td><?= $sinDato($fila['servicio']) ?></td>
                                    <td class="center"><a class="bl-icon-link" href="<?= esc(base_url(route_to('consulta_tablero_detalle', $fila['atencion_id'])) . '?' . http_build_query($filtros), 'attr') ?>" title="Ver atención <?= (int) $fila['atencion_id'] ?>" aria-label="Ver atención <?= (int) $fila['atencion_id'] ?>"><i class="fas fa-eye" aria-hidden="true"></i></a></td>
                                </tr>
                            <?php endforeach ?>
                            <?php if (!$datos['detalle']): ?><tr><td colspan="8" class="bl-empty"><i class="fas fa-inbox"></i>Sin atenciones para estos filtros.</td></tr><?php endif ?>
                        </tbody>
                    </table>
                </div>
                <?php
                // Números visibles: primera, última y dos a cada lado de la actual; los saltos se muestran con "…"
                $pagina = (int) $pagina;
                $numerosPagina = array_values(array_unique(array_filter([1, $pagina - 2, $pagina - 1, $pagina, $pagina + 1, $pagina + 2, $paginas], static fn($n) => $n >= 1 && $n <= $paginas)));
                sort($numerosPagina);
                $enlacePagina = static fn(int $n): string => esc($url(['pagina' => $n]), 'attr') . '#consulta-detalle';
                ?>
                <div class="ct-pie-tabla ct-paginador">
                    <span class="ct-toolbar-nota">
                        <i class="fas fa-list-ol" aria-hidden="true"></i>
                        <?php if ($total): ?>
                            Mostrando <strong><?= $numero(($pagina - 1) * 25 + 1) ?>–<?= $numero(min($pagina * 25, $total)) ?></strong> de <strong><?= $numero($total) ?></strong> atenciones
                        <?php else: ?>
                            Sin atenciones
                        <?php endif ?>
                    </span>
                    <?php if ($paginas > 1): ?>
                        <nav class="bl-pager" aria-label="Paginación de atenciones">
                            <ul>
                                <?php if ($pagina > 1): ?>
                                    <li class="ct-pager-extremo"><a href="<?= $enlacePagina(1) ?>" title="Primera página" aria-label="Primera página"><i class="fas fa-angles-left" aria-hidden="true"></i></a></li>
                                    <li><a href="<?= $enlacePagina($pagina - 1) ?>" title="Página anterior" aria-label="Página anterior"><i class="fas fa-chevron-left" aria-hidden="true"></i><span class="ct-pager-texto">&nbsp;Anterior</span></a></li>
                                <?php else: ?>
                                    <li class="disabled ct-pager-extremo"><span aria-hidden="true"><i class="fas fa-angles-left"></i></span></li>
                                    <li class="disabled"><span><i class="fas fa-chevron-left" aria-hidden="true"></i><span class="ct-pager-texto">&nbsp;Anterior</span></span></li>
                                <?php endif ?>
                                <?php foreach ($numerosPagina as $i => $n): ?>
                                    <?php if ($i && $n - $numerosPagina[$i - 1] > 1): ?><li class="ct-pager-salto"><span aria-hidden="true">…</span></li><?php endif ?>
                                    <?php if ($n === $pagina): ?>
                                        <li class="active"><span aria-current="page"><?= $numero($n) ?></span></li>
                                    <?php else: ?>
                                        <li><a href="<?= $enlacePagina($n) ?>" aria-label="Página <?= $n ?>"><?= $numero($n) ?></a></li>
                                    <?php endif ?>
                                <?php endforeach ?>
                                <?php if ($pagina < $paginas): ?>
                                    <li><a href="<?= $enlacePagina($pagina + 1) ?>" title="Página siguiente" aria-label="Página siguiente"><span class="ct-pager-texto">Siguiente&nbsp;</span><i class="fas fa-chevron-right" aria-hidden="true"></i></a></li>
                                    <li class="ct-pager-extremo"><a href="<?= $enlacePagina($paginas) ?>" title="Última página" aria-label="Última página"><i class="fas fa-angles-right" aria-hidden="true"></i></a></li>
                                <?php else: ?>
                                    <li class="disabled"><span><span class="ct-pager-texto">Siguiente&nbsp;</span><i class="fas fa-chevron-right" aria-hidden="true"></i></span></li>
                                    <li class="disabled ct-pager-extremo"><span aria-hidden="true"><i class="fas fa-angles-right"></i></span></li>
                                <?php endif ?>
                            </ul>
                        </nav>
                        <form class="ct-ir-pagina" method="get" action="<?= esc(base_url(route_to('consulta_tablero')), 'attr') ?>#consulta-detalle">
                            <?php foreach (array_filter($filtros, static fn($v) => $v !== '') as $clave => $valor): ?>
                                <input type="hidden" name="<?= esc($clave, 'attr') ?>" value="<?= esc($valor, 'attr') ?>">
                            <?php endforeach ?>
                            <label for="consulta-ir-pagina">Página</label>
                            <input class="bl-filtro-input" type="number" id="consulta-ir-pagina" name="pagina" min="1" max="<?= $paginas ?>" value="<?= $pagina ?>" required>
                            <span>de <?= $numero($paginas) ?></span>
                            <button type="submit" class="ct-ir-boton" title="Ir a la página escrita">Ir <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
                        </form>
                    <?php endif ?>
                </div>
            </div>
        </section>
    <?php endif ?>
</div>
<script type="application/json" id="consulta-graficos"><?= json_encode($graficos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type="application/json" id="consulta-medicos-tiempo-datos"><?= json_encode($datos['serie_medicos'] ?? null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type="application/json" id="consulta-formularios"><?= json_encode($catalogos['formularios'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="<?= base_url('js/consulta-tablero.js') ?>?v=<?= @filemtime(FCPATH . 'js/consulta-tablero.js') ?: '1' ?>"></script>
<script src="<?= base_url('js/tablas-fijas.js') ?>?v=<?= @filemtime(FCPATH . 'js/tablas-fijas.js') ?: '1' ?>"></script>
<?= $this->endSection() ?>