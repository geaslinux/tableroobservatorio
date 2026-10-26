<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Atenciones de consulta · Ministerio de Salud<?= $this->endSection() ?>
<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('css/panel.css') ?>">
<link rel="stylesheet" href="<?= base_url('css/consulta-tablero.css') ?>">
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
$url = static function (array $cambios = [], string $ruta = 'consulta_tablero') use ($filtros): string {
    return base_url(route_to($ruta)) . '?' . http_build_query(array_filter(array_merge($filtros, $cambios), static fn($v) => $v !== ''));
};
$numero = static fn($v) => number_format((int) $v, 0, ',', '.');
$fecha = static fn($v) => $v ? date('d/m/Y', strtotime($v)) : 'Sin datos';
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
$graficos = [];
?>
<div class="base-list-wrap consulta-tablero">
    <nav class="bl-breadcrumb" aria-label="Ruta de navegación"><a href="<?= base_url(route_to('inicio_views')) ?>">Inicio</a><span aria-hidden="true">/</span><strong>Atenciones de consulta</strong></nav>
    <h1 class="title is-4">Atenciones de consulta</h1>
    <p class="mb-4">Actividad asistencial por efector y servicio/formulario de atención.</p>
    <nav class="buttons" aria-label="Secciones del tablero">
        <?php foreach (['filtros' => 'Filtros', 'evolucion' => 'Evolución', 'medicos' => 'Médicos', 'distribuciones' => 'Distribuciones', 'detalle' => 'Detalle'] as $ancla => $texto): ?><a class="button is-small" href="#consulta-<?= $ancla ?>"><?= $texto ?></a><?php endforeach ?>
    </nav>
    <?php if ($error): ?><div class="notification is-danger is-light" role="alert"><?= esc($error) ?> <a href="<?= esc($url(), 'attr') ?>">Volver a intentar</a></div><?php endif ?>
    <section class="bl-panel" id="consulta-filtros" aria-labelledby="titulo-filtros">
        <div class="bl-panel-header"><h2 class="bl-panel-title" id="titulo-filtros">Filtros de atención</h2></div>
        <div class="bl-panel-body">
            <form method="get" action="<?= base_url(route_to('consulta_tablero')) ?>" id="consulta-form">
                <div class="columns is-multiline">
                <?php foreach ($etiquetas as $key => $label): ?>
                    <div class="column is-12-mobile is-6-tablet is-4-desktop">
                        <label class="label" for="consulta-<?= $key ?>"><?= esc($label) ?></label>
                        <div class="select is-fullwidth"><select id="consulta-<?= $key ?>" name="<?= $key ?>"><option value="">Todos los permitidos</option>
                            <?php foreach ($opciones[$key] as $id => $nombre): ?><option value="<?= esc((string) $id, 'attr') ?>" <?= (string) $id === $filtros[$key] ? 'selected' : '' ?>><?= esc($nombre) ?></option><?php endforeach ?>
                        </select></div>
                    </div>
                <?php endforeach ?>
                <?php foreach (['desde' => 'Fecha desde (inclusive)', 'hasta' => 'Fecha hasta (inclusive)'] as $key => $label): ?>
                    <div class="column is-12-mobile is-6-tablet is-4-desktop"><label class="label" for="consulta-<?= $key ?>"><?= esc($label) ?></label><input class="input" type="date" id="consulta-<?= $key ?>" name="<?= $key ?>" value="<?= esc($filtros[$key], 'attr') ?>"></div>
                <?php endforeach ?>
                    <div class="column is-12"><label class="checkbox"><input type="checkbox" name="con_procedimiento" value="1" <?= $filtros['con_procedimiento'] === '1' ? 'checked' : '' ?>> Solo atenciones con procedimiento</label></div>
                </div>
                <p class="help mb-3">Los filtros de origen identifican quién generó la interconsulta; no indican el servicio que atendió al paciente.</p>
                <div class="buttons"><button class="button is-link" type="submit">Aplicar filtros</button><a class="button" href="<?= base_url(route_to('consulta_tablero')) ?>">Restablecer filtros</a></div>
                <p id="consulta-carga" role="status" hidden>Consultando datos…</p>
            </form>
            <div class="consulta-aplicados" aria-label="Filtros aplicados">
                <strong>Filtros aplicados:</strong>
                <?php $aplicados = 0; foreach ($filtros as $key => $valor): if ($valor === '') continue; $aplicados++; ?>
                    <span class="tag is-light"><?= esc(($etiquetas[$key] ?? ['desde' => 'Desde', 'hasta' => 'Hasta', 'con_procedimiento' => 'Con procedimiento'][$key]) . ': ' . ($opciones[$key][$valor] ?? ($key === 'con_procedimiento' ? 'Sí' : $valor))) ?></span>
                <?php endforeach ?>
                <?php if (!$aplicados): ?><span>Todo el historial autorizado.</span><?php endif ?>
            </div>
        </div>
    </section>
    <?php if ($datos !== null): $k = $datos['kpi']; $total = (int) $k['atenciones']; ?>
        <?php if (!$catalogos['formularios']): ?><div class="notification is-info is-light">No tiene formularios asociados a sus grupos. Solicite al administrador que revise su acceso.</div>
        <?php elseif (!$total): ?><div class="notification is-info is-light" role="status">Sin atenciones para los filtros aplicados.</div><?php endif ?>
        <?= view('partials/kpi_cards', ['tarjetas' => [
            ['label' => 'Atenciones', 'valor' => $total, 'icono' => 'fa-notes-medical', 'color' => 'teal', 'sub' => 'Registros vigentes del período'],
            ['label' => 'Pacientes únicos', 'valor' => $k['pacientes'], 'icono' => 'fa-users', 'color' => 'blue', 'sub' => 'Personas distintas en el universo filtrado'],
            ['label' => 'Médicos con actividad', 'valor' => $k['medicos'], 'icono' => 'fa-user-doctor', 'color' => 'purple', 'sub' => 'Profesionales distintos'],
            ['label' => 'Atenciones con procedimiento', 'valor' => $k['procedimientos'], 'icono' => 'fa-file-medical', 'color' => 'navy', 'sub' => $total ? number_format(100 * $k['procedimientos'] / $total, 1, ',', '.') . '% de ' . $numero($total) . ' atenciones' : 'Sin denominador: 0 atenciones'],
        ]]) ?>
        <p class="mb-4">Período solicitado: <?= $filtros['desde'] ? esc($fecha($filtros['desde'])) : 'inicio del historial' ?> a <?= $filtros['hasta'] ? esc($fecha($filtros['hasta'])) : 'fin del historial' ?>. Datos encontrados: <?= esc($fecha($k['desde'])) ?> a <?= esc($fecha($k['hasta'])) ?>. Los pacientes únicos no se suman entre médicos o servicios.</p>
        <?php $graficos[] = ['id' => 'consulta-serie', 'tipo' => 'line', 'filas' => $datos['serie']]; ?>
        <section class="bl-panel" id="consulta-evolucion" aria-labelledby="titulo-evolucion">
            <div class="bl-panel-header"><h2 class="bl-panel-title" id="titulo-evolucion">Evolución mensual · cantidad de atenciones</h2></div>
            <div class="bl-panel-body">
                <?php if ($datos['serie']): ?><div class="consulta-chart"><canvas id="consulta-serie" role="img" aria-label="Atenciones por mes; valores en la tabla siguiente"></canvas></div><?php else: ?><p>Sin datos.</p><?php endif ?>
                <details><summary>Ver tabla completa de evolución</summary><div class="table-container"><table class="table is-fullwidth is-striped"><caption>Atenciones por mes (año-mes)</caption><thead><tr><th scope="col">Mes</th><th scope="col">Atenciones</th></tr></thead><tbody><?php foreach ($datos['serie'] as $fila): ?><tr><td><?= esc($fila['nombre']) ?></td><td><?= $numero($fila['cantidad']) ?></td></tr><?php endforeach ?></tbody></table></div></details>
            </div>
        </section>
        <?php $graficos[] = ['id' => 'consulta-medicos-chart', 'tipo' => 'bar', 'filas' => array_slice($datos['medicos'], 0, 10)]; ?>
        <section class="bl-panel" id="consulta-medicos" aria-labelledby="titulo-medicos">
            <div class="bl-panel-header"><h2 class="bl-panel-title" id="titulo-medicos">Actividad por médico</h2></div>
            <div class="bl-panel-body">
                <p class="mb-3">Primeros <?= min(10, count($datos['medicos'])) ?> de <?= count($datos['medicos']) ?> profesionales, ordenados por atenciones. La tabla incluye todos. El nombre mostrado es una de sus denominaciones históricas; el detalle conserva cada registro original.</p>
                <?php if ($datos['medicos']): ?><div class="consulta-chart"><canvas id="consulta-medicos-chart" role="img" aria-label="Atenciones por médico; valores en la tabla siguiente"></canvas></div><?php endif ?>
                <div class="table-container"><table class="table is-fullwidth is-striped"><caption>Profesionales del mismo universo filtrado</caption><thead><tr><?php foreach (['Médico', 'Atenciones', 'Pacientes únicos', 'Atenciones con procedimiento', 'Acción'] as $t): ?><th scope="col"><?= $t ?></th><?php endforeach ?></tr></thead><tbody>
                    <?php foreach ($datos['medicos'] as $fila): ?><tr><td><?= esc($fila['nombre']) ?></td><td><?= $numero($fila['cantidad']) ?></td><td><?= $numero($fila['pacientes']) ?></td><td><?= $numero($fila['procedimientos']) ?></td><td><a href="<?= esc($url(['medico' => $fila['id']]), 'attr') ?>#consulta-detalle">Filtrar médico</a></td></tr><?php endforeach ?>
                    <?php if (!$datos['medicos']): ?><tr><td colspan="5">Sin datos.</td></tr><?php endif ?>
                </tbody></table></div>
            </div>
        </section>
        <section id="consulta-distribuciones" aria-labelledby="titulo-distribuciones"><h2 class="title is-5" id="titulo-distribuciones">Distribuciones de las atenciones</h2><p class="mb-3">Diagnósticos y procedimientos son textos registrados, sin clasificación clínica normalizada. Porcentajes sobre <?= $numero($total) ?> atenciones filtradas.</p>
            <div class="columns is-multiline">
            <?php $indice = 0; foreach ($datos['distribuciones'] as $titulo => $filas): $id = 'consulta-distribucion-' . $indice++; $graficos[] = ['id' => $id, 'tipo' => 'bar', 'filas' => array_slice($filas, 0, 10)]; ?>
                <div class="column is-12-mobile is-6-tablet"><section class="bl-panel"><div class="bl-panel-header"><h3 class="bl-panel-title"><?= esc($titulo) ?></h3></div><div class="bl-panel-body">
                    <?php if ($filas): ?><p>Primeras <?= min(10, count($filas)) ?> de <?= count($filas) ?> categorías.</p><div class="consulta-chart"><canvas id="<?= $id ?>" role="img" aria-label="<?= esc($titulo, 'attr') ?>; valores en la tabla siguiente"></canvas></div><?php else: ?><p>Sin datos.</p><?php endif ?>
                    <details><summary>Ver tabla completa (<?= count($filas) ?> categorías)</summary><div class="table-container"><table class="table is-fullwidth is-striped"><caption><?= esc($titulo) ?></caption><thead><tr><th scope="col">Categoría</th><th scope="col">Atenciones</th><th scope="col">% de <?= $numero($total) ?> atenciones</th></tr></thead><tbody><?php foreach ($filas as $fila): ?><tr><td><?= esc($fila['nombre']) ?></td><td><?= $numero($fila['cantidad']) ?></td><td><?= $total ? number_format(100 * $fila['cantidad'] / $total, 1, ',', '.') . '%' : 'No aplica' ?></td></tr><?php endforeach ?></tbody></table></div></details>
                </div></section></div>
            <?php endforeach ?>
            </div>
        </section>
        <section class="bl-panel" id="consulta-detalle" aria-labelledby="titulo-detalle"><div class="bl-panel-header"><h2 class="bl-panel-title" id="titulo-detalle">Detalle verificable de atenciones</h2></div><div class="bl-panel-body">
            <p class="mb-3"><?= $numero($total) ?> atenciones · 25 por página. Se omiten identificables de pacientes.</p>
            <div class="buttons"><a class="button is-small" href="<?= esc($url([], 'consulta_tablero_exportar'), 'attr') ?>">Exportar CSV con estos filtros</a><a class="button is-small" href="<?= esc($url(['con_procedimiento' => '1']), 'attr') ?>#consulta-detalle">Consultar atenciones con procedimiento</a></div>
            <div class="table-container"><table class="table is-fullwidth is-striped"><caption>Registros correspondientes a los filtros aplicados</caption><thead><tr><?php foreach (['Fecha/hora', 'Formulario', 'Profesional', 'Diagnóstico registrado', 'Procedimiento registrado', 'Hospital de origen', 'Servicio de origen', 'Detalle'] as $t): ?><th scope="col"><?= $t ?></th><?php endforeach ?></tr></thead><tbody>
            <?php foreach ($datos['detalle'] as $fila): ?><tr><td><?= esc($fecha($fila['fecha']) . ' ' . substr($fila['hora'], 0, 5)) ?></td><td><?= esc($fila['formulario']) ?></td><td><?= esc($fila['profesional']) ?></td><td><?= esc($fila['diagnostico']) ?></td><td><?= esc($fila['procedimiento'] ?: 'Sin informar') ?></td><td><?= esc($fila['hospital'] ?: 'Sin informar') ?></td><td><?= esc($fila['servicio'] ?: 'Sin informar') ?></td><td><a href="<?= esc(base_url(route_to('consulta_tablero_detalle', $fila['atencion_id'])) . '?' . http_build_query($filtros), 'attr') ?>">Ver atención <?= (int) $fila['atencion_id'] ?></a></td></tr><?php endforeach ?>
            <?php if (!$datos['detalle']): ?><tr><td colspan="8">Sin atenciones para estos filtros.</td></tr><?php endif ?>
            </tbody></table></div>
            <?php $paginas = max(1, (int) ceil($total / 25)); ?>
            <nav class="buttons" aria-label="Paginación de atenciones"><?php if ($pagina > 1): ?><a class="button is-small" href="<?= esc($url(['pagina' => $pagina - 1]), 'attr') ?>#consulta-detalle">Anterior</a><?php endif ?><span>Página <?= (int) $pagina ?> de <?= $paginas ?></span><?php if ($pagina < $paginas): ?><a class="button is-small" href="<?= esc($url(['pagina' => $pagina + 1]), 'attr') ?>#consulta-detalle">Siguiente</a><?php endif ?></nav>
        </div></section>
    <?php endif ?>
</div>
<script type="application/json" id="consulta-graficos"><?= json_encode($graficos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<script type="application/json" id="consulta-formularios"><?= json_encode($catalogos['formularios'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
<?= $this->endSection() ?>
<?= $this->section('js') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= base_url('js/consulta-tablero.js') ?>"></script>
<?= $this->endSection() ?>
