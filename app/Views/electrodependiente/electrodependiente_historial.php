<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> HISTORIAL — <?= esc(is_array($paciente) ? ($paciente['paciente'] ?? '') : ($paciente->paciente ?? '')) ?> <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="/css/ejesa.css?v=<?= @filemtime(FCPATH . 'css/ejesa.css') ?: '1' ?>">

<style>
    /* Historial de un paciente electrodependiente (solo consulta). Base visual: ejesa.css */
    .hp-datos { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .hp-datos .ej-chip { box-shadow: 0 0 0 1px rgba(255,255,255,.2); }
    .hp-datos .ej-chip.claro { background: rgba(255,255,255,.14); color: #fff; }
    .hp-nombre { font-size: 18px; font-weight: 800; color: #fff; letter-spacing: .2px; }
    .hp-sub { font-size: 10.5px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.7); }

    /* Cambios de datos: antes → después */
    .hp-cambio { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 12.5px; line-height: 1.45; }
    .hp-antes { color: #b91c1c; text-decoration: line-through; text-decoration-color: rgba(185,28,28,.45); word-break: break-word; }
    .hp-despues { color: #15803d; font-weight: 700; word-break: break-word; }
    .hp-flecha { color: #9aa5b5; font-size: 11px; }
    .hp-vacio { color: #9aa5b5; font-style: italic; font-weight: 400; text-decoration: none; }
    .hp-campo { background: #e6f0fa; color: #2b6cb0; }
    .hp-fecha { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .hp-fecha small { display: block; color: var(--text-muted); font-size: 11px; }

    /* Línea de tiempo de equipos */
    .hp-linea { list-style: none; margin: 0; padding: 4px 0 0 18px; border-left: 2px solid #d5dde8; }
    .hp-mov { position: relative; margin: 0 0 14px 12px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fff; }
    .hp-mov::before {
        content: ''; position: absolute; left: -32px; top: 12px; width: 14px; height: 14px; border-radius: 50%;
        background: #fff; border: 3px solid var(--ej-primario);
    }
    .hp-mov.alta::before         { border-color: #5BB8A6; }
    .hp-mov.modificacion::before { border-color: #E9B949; }
    .hp-mov.baja::before         { border-color: #D96C6C; }
    .hp-mov-cab {
        display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;
        padding: 9px 12px; border-bottom: 1px solid #eef0f3; background: #fbfcfd;
    }
    .hp-mov.alta .hp-mov-cab         { background: #f2fbf7; }
    .hp-mov.modificacion .hp-mov-cab { background: #fffbef; }
    .hp-mov.baja .hp-mov-cab         { background: #fff6f6; }
    .hp-mov-titulo { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #13304d; }
    .hp-mov-cuando { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; }
    .ej-chip.ALTA         { background: var(--ej-bajo-bg); color: var(--ej-bajo); }
    .ej-chip.MODIFICACION { background: var(--ej-mediano-bg); color: var(--ej-mediano); }
    .ej-chip.BAJA         { background: var(--ej-alto-bg); color: var(--ej-alto); }
    .hp-ficha { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 16px; padding: 12px; font-size: 13px; }
    .hp-ficha span.lbl { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .6px; color: var(--text-muted); font-weight: 700; margin-bottom: 1px; }
    .hp-ficha .hp-val { color: var(--text-main); }
    .hp-ficha.baja .hp-val { color: #9aa5b5; text-decoration: line-through; }
    .hp-tabla tr.hp-grupo td {
        background: #f4f6f9; color: #13304d; font-size: 12px; font-weight: 700; border-top: 2px solid #e2e8f0;
        padding-top: 8px; padding-bottom: 8px;
    }
    .hp-tabla tr.hp-grupo td i { color: var(--ej-primario); margin-right: 2px; }
    .hp-tabla tr.hp-grupo .ej-chip { margin-left: 6px; }
    .hp-tabla tbody:first-of-type tr.hp-grupo td { border-top: none; }
    .hp-ficha .hp-cambio { font-size: 13px; }
    @media (min-width: 768px) {
        .hp-ficha { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
</style>

<?php
// Lee indistintamente objeto o array
$get = static fn ($obj, $k) => is_array($obj) ? ($obj[$k] ?? null) : ($obj->$k ?? null);
$num = static fn ($n) => number_format((float) $n, 0, ',', '.');

// Valor de texto limpio (los imports traen espacios y saltos de línea de más)
$limpio = static fn ($v) => trim(preg_replace('/\s+/u', ' ', (string) $v));
$valor  = static function ($v, string $clase) use ($limpio) {
    $v = $limpio($v);

    return $v === '' ? '<span class="hp-vacio">vacío</span>' : '<span class="' . $clase . '">' . esc($v) . '</span>';
};
$fecha = static fn ($f) => $f ? date('d/m/Y', strtotime($f)) . '<small>' . date('H:i', strtotime($f)) . ' h</small>' : '—';

// Campos de equipo con etiqueta legible
$camposEquipo = [
    'equipamiento'       => 'Equipamiento',
    'marca'              => 'Marca',
    'modelo'             => 'Modelo',
    'serie'              => 'Serie',
    'tiempo_uso'         => 'Tiempo de uso',
    'fecha_entrega'      => 'Fecha de entrega',
    'medico_tratante'    => 'Médico tratante',
    'fecha_ingreso_recs' => 'Ingreso RECS',
    'ultima_evaluacion'  => 'Última evaluación',
    'titular_servicio'   => 'Titular del servicio',
    'nro_servicio'       => 'Nº de servicio',
    'estado'             => 'Estado',
];

// Nombres legibles de los campos del paciente (el historial guarda el nombre de la columna)
$nombresCampo = [
    'paciente' => 'Nombre', 'dni' => 'DNI', 'edad' => 'Edad', 'tipo' => 'Tipo', 'domicilio' => 'Domicilio',
    'contacto' => 'Contacto', 'localidad_id' => 'Localidad', 'efector_id' => 'Hospital', 'obra_social_id' => 'Obra social',
    'diagnostico_id' => 'Diagnóstico', 'dx_complementario' => 'Diagnóstico complementario', 'cud' => 'CUD',
    'factor_riesgo' => 'Factor de riesgo', 'seguimiento' => 'Seguimiento', 'estado' => 'Estado', 'estado_vital' => 'Estado vital',
    'fecha_fallecimiento' => 'Fecha de fallecimiento', 'motivo_baja' => 'Motivo de baja', 'observacion' => 'Observación',
    'coordenadas' => 'Coordenadas', 'nro_servicio' => 'Nº de servicio', 'titular_servicio' => 'Titular del servicio',
    'servicio_origen' => 'Origen del servicio eléctrico', 'servicio_verificado' => 'Servicio verificado',
    'servicio_verificado_at' => 'Fecha de verificación del servicio', 'servicio_verificado_por' => 'Servicio verificado por',
];
$nombreCampo = static function ($campo) use ($nombresCampo, $limpio) {
    $c = $limpio($campo);

    return $nombresCampo[$c] ?? ucfirst(str_replace('_', ' ', $c));
};

// Campos que guardan un código: se muestra el nombre (una consulta por tabla, solo con los códigos usados)
$tablasCodigo = [
    'localidad_id' => ['localidad', 'localidad_id'], 'efector_id' => ['efector', 'efector_id'],
    'obra_social_id' => ['obra_social', 'obra_social_id'], 'diagnostico_id' => ['diagnostico', 'diagnostico_id'],
];
$nombresCodigo = [];
foreach ($tablasCodigo as $campo => [$tabla, $pk]) {
    $ids = [];
    foreach ($historialPaciente as $h) {
        if ($limpio($h['campo_modificado']) !== $campo) continue;
        foreach ([$h['valor_anterior'], $h['valor_nuevo']] as $v) if (ctype_digit($limpio($v))) $ids[] = (int) $limpio($v);
    }
    if ($ids) {
        foreach (db_connect()->table($tabla)->select($pk . ' AS id, nombre')->whereIn($pk, array_unique($ids))->get()->getResultArray() as $fila) {
            $nombresCodigo[$campo][(int) $fila['id']] = $fila['nombre'];
        }
    }
}

// Usuarios que hicieron los cambios: una sola consulta para todo el historial
$idsUsuarios = array_values(array_unique(array_filter(array_map('intval', array_merge(
    array_column($historialPaciente, 'modificado_por'),
    array_column($historialEquipos, 'modificado_por')
)))));
$usuarios = [];
if ($idsUsuarios) {
    foreach (db_connect()->table('users')->select('id, username, email')->whereIn('id', $idsUsuarios)->get()->getResultArray() as $u) {
        $usuarios[(int) $u['id']] = $u['username'] ?: $u['email'];
    }
}
$quien = static fn ($id) => !$id ? 'Sistema / importación' : ($usuarios[(int) $id] ?? 'Usuario #' . (int) $id);

// Valor legible de un campo del paciente: nombre en vez de código, Sí/No, usuario en vez de id
$legible = static function ($campo, $v) use ($limpio, $nombresCodigo, $quien) {
    $c = $limpio($campo);
    $v = $limpio($v);
    if ($v === '') return '';
    if (isset($nombresCodigo[$c][(int) $v]) && ctype_digit($v)) return $nombresCodigo[$c][(int) $v];
    if ($c === 'servicio_verificado' && in_array($v, ['0', '1'], true)) return $v === '1' ? 'Sí' : 'No';
    if ($c === 'servicio_verificado_por' && ctype_digit($v)) return $quien((int) $v);
    if (in_array($c, ['estado_vital', 'servicio_origen', 'estado'], true)) return ucfirst(strtolower(str_replace('_', ' ', $v)));

    return $v;
};

// Las altas del paciente ("Alta desde Excel provincial", etc.) no son cambios de campo
$esAlta    = static fn ($h) => str_starts_with($limpio($h['campo_modificado']), 'Alta');
$cantAltas  = count(array_filter($historialPaciente, $esAlta));
$cantCampos = count($historialPaciente) - $cantAltas;

// Cambios agrupados por momento y usuario (una importación cambia muchos campos a la vez)
$grupos = [];
foreach ($historialPaciente as $h) {
    $clave = substr((string) $h['created_at'], 0, 16) . '|' . (int) $h['modificado_por'];
    $grupos[$clave] ??= ['fecha' => $h['created_at'], 'usuario' => $h['modificado_por'], 'filas' => []];
    $grupos[$clave]['filas'][] = $h;
}

// Resumen
$cuentaEq = ['alta' => 0, 'modificacion' => 0, 'baja' => 0];
foreach ($historialEquipos as $h) {
    $a = strtolower((string) $h['accion']);
    if (isset($cuentaEq[$a])) $cuentaEq[$a]++;
}
$fechas = array_filter(array_merge(array_column($historialPaciente, 'created_at'), array_column($historialEquipos, 'created_at')));
$ultimo = $fechas ? max($fechas) : null;

// Datos del paciente para el encabezado
$riesgo = strtoupper((string) $get($paciente, 'factor_riesgo'));
$riesgo = in_array($riesgo, ['ALTO', 'MEDIANO', 'BAJO'], true) ? $riesgo : 'PENDIENTE';
$estado = $get($paciente, 'estado_vital') === 'fallecido' ? 'Fallecido' : ucfirst((string) ($get($paciente, 'estado') ?: '—'));

// Volver al listado de pacientes: si se llegó desde el listado, conserva sus filtros y la página
$urlListado = base_url(route_to('electrodependiente_list'));
$anterior   = (string) previous_url();
$volver     = str_starts_with($anterior, $urlListado) && !str_contains($anterior, 'historial') ? $anterior : $urlListado;
$volverTxt  = 'Volver al listado de pacientes';
// Si se llegó desde EJESA (panel o detalle de un corte), volver ahí
$urlEjesa = base_url(route_to('ejesa_views'));
if (str_starts_with($anterior, $urlEjesa)) {
    $volver    = $anterior;
    $volverTxt = str_contains($anterior, '/corte/') ? 'Volver al corte' : 'Volver a EJESA';
}
?>

<div class="ej-breadcrumb">
    <a href="<?= base_url(route_to('home')); ?>" class="fas fa-home" aria-label="Inicio"></a>
    <a href="<?= base_url(route_to('inicio_views')); ?>">Inicio</a> ›
    <a href="<?= base_url(route_to('saludmental_views')); ?>">Salud mental</a> ›
    <a href="<?= esc($volver, 'attr') ?>">Electrodependientes</a> ›
    <strong>Historial</strong>
</div>

<div class="ej-panel">
    <div class="ej-panel-header">
        <div>
            <div class="hp-sub"><i class="fas fa-history"></i> Historial de cambios</div>
            <div class="hp-nombre"><?= esc($get($paciente, 'paciente') ?? '—') ?></div>
            <div class="hp-datos">
                <span class="ej-chip claro">DNI <?= esc($get($paciente, 'dni') ?: '—') ?></span>
                <?php if ($get($paciente, 'edad') !== null && $get($paciente, 'edad') !== ''): ?><span class="ej-chip claro"><?= (int) $get($paciente, 'edad') ?> años</span><?php endif; ?>
                <?php if ($get($paciente, 'tipo')): ?><span class="ej-chip claro"><?= esc($get($paciente, 'tipo')) ?></span><?php endif; ?>
                <span class="ej-chip claro">#<?= (int) $get($paciente, 'electrodependiente_id') ?></span>
                <span class="ej-chip <?= $riesgo ?>">Riesgo <?= $riesgo === 'PENDIENTE' ? 'sin dato' : $riesgo ?></span>
                <span class="ej-chip <?= $estado === 'Activo' ? 'ok' : 'neutro' ?>"><?= esc($estado) ?></span>
            </div>
        </div>
        <span class="ej-datos-al"><i class="fas fa-lock"></i> Solo consulta</span>
    </div>

    <div class="ej-panel-body">

        <div class="ej-toolbar">
            <a class="bl-btn ghost" href="<?= esc($volver, 'attr') ?>"><i class="fas fa-arrow-left"></i> <?= esc($volverTxt) ?></a>
        </div>

        <!-- ── Resumen ── -->
        <div class="ej-kpi-cards">
            <div class="ej-kpi-card navy">
                <div class="ej-kpi-icono"><i class="fas fa-user-edit"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Cambios en los datos</div>
                    <div class="ej-kpi-val"><?= $num($cantCampos) ?></div>
                    <div class="ej-kpi-sub">campos modificados<?= $cantAltas ? ' · más el alta del paciente' : '' ?></div>
                </div>
            </div>
            <div class="ej-kpi-card teal">
                <div class="ej-kpi-icono"><i class="fas fa-plug"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Movimientos de equipos</div>
                    <div class="ej-kpi-val"><?= $num(count($historialEquipos)) ?></div>
                    <div class="ej-kpi-sub"><?= $cuentaEq['alta'] ?> altas · <?= $cuentaEq['modificacion'] ?> modificaciones · <?= $cuentaEq['baja'] ?> bajas</div>
                </div>
            </div>
            <div class="ej-kpi-card azul">
                <div class="ej-kpi-icono"><i class="far fa-clock"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Último cambio</div>
                    <div class="ej-kpi-val" style="font-size:18px;"><?= $ultimo ? date('d/m/Y', strtotime($ultimo)) : '—' ?></div>
                    <div class="ej-kpi-sub"><?= $ultimo ? date('H:i', strtotime($ultimo)) . ' h' : 'sin cambios registrados' ?></div>
                </div>
            </div>
            <div class="ej-kpi-card gris">
                <div class="ej-kpi-icono"><i class="fas fa-users"></i></div>
                <div class="ej-kpi-contenido">
                    <div class="ej-kpi-lbl">Quiénes modificaron</div>
                    <div class="ej-kpi-val"><?= $num(count($idsUsuarios)) ?></div>
                    <div class="ej-kpi-sub"><?= $idsUsuarios ? esc(implode(', ', array_map($quien, $idsUsuarios))) : 'solo cargas del sistema' ?></div>
                </div>
            </div>
        </div>

        <div class="ej-grid-2" style="grid-template-columns: 1fr;">

            <!-- ══════════ Cambios en los datos del paciente ══════════ -->
            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-user-edit"></i> Cambios en los datos del paciente</span>
                    <span class="ej-chip neutro"><?= count($historialPaciente) ?> registro<?= count($historialPaciente) != 1 ? 's' : '' ?> · más nuevos primero</span>
                </div>
                <?php if (empty($historialPaciente)): ?>
                    <p class="ej-vacio">Sin cambios registrados en los datos del paciente.</p>
                <?php else: ?>
                <div class="ej-tabla-wrap">
                <table class="ej-tabla tarjetas hp-tabla">
                    <thead><tr><th style="width:240px;">Campo</th><th>Cambio</th></tr></thead>
                    <?php foreach ($grupos as $g): ?>
                    <tbody>
                        <tr class="hp-grupo">
                            <td colspan="2" class="ej-td-titulo">
                                <i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($g['fecha'])) ?> h
                                · <i class="fas <?= $g['usuario'] ? 'fa-user' : 'fa-file-import' ?>"></i> <?= esc($quien($g['usuario'])) ?>
                                <?php $nc = count(array_filter($g['filas'], static fn ($h) => !$esAlta($h))); ?>
                                <span class="ej-chip neutro"><?= $nc ? $nc . ' campo' . ($nc != 1 ? 's' : '') : 'alta del paciente' ?></span>
                            </td>
                        </tr>
                        <?php foreach ($g['filas'] as $h): $campo = $h['campo_modificado']; ?>
                        <?php if (str_starts_with($limpio($campo), 'Alta')): ?>
                        <?php
                        // Evento de alta del paciente (no es un cambio de campo): se explica de dónde salió
                        $detalle = $limpio($h['valor_nuevo']);
                        $origen  = $limpio($campo);
                        if (preg_match('/^(.+?)\s+fila\s+(\d+)$/iu', $detalle, $m)) {
                            $hoja    = str_replace(['BAJASFALLECIDOS', 'BAJAS FALLECIDOS'], 'BAJAS / FALLECIDOS', strtoupper($m[1]));
                            $origen  = 'Se cargó desde el Excel provincial';
                            $detalle = 'hoja ' . $hoja . ', fila ' . $m[2];
                        }
                        $esPrueba = stripos($campo, 'prueba') !== false;
                        ?>
                        <tr class="hp-alta">
                            <td data-label="Evento"><span class="ej-chip ALTA"><i class="fas fa-user-plus"></i> Paciente dado de alta</span></td>
                            <td data-label="Origen">
                                <div class="hp-cambio">
                                    <span><strong><?= esc($origen) ?></strong><?php if ($detalle !== ''): ?> <span style="color:var(--text-muted);">(<?= esc($detalle) ?>)</span><?php endif; ?></span>
                                    <?php if ($esPrueba): ?><span class="ej-chip VENCIDO">registro de prueba</span><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php continue; endif; ?>
                        <tr>
                            <td data-label="Campo"><span class="ej-chip hp-campo"><?= esc($nombreCampo($campo)) ?></span></td>
                            <td data-label="Cambio">
                                <div class="hp-cambio">
                                    <?= $valor($legible($campo, $h['valor_anterior']), 'hp-antes') ?>
                                    <i class="fas fa-arrow-right hp-flecha"></i>
                                    <?= $valor($legible($campo, $h['valor_nuevo']), 'hp-despues') ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php endforeach; ?>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══════════ Historial de equipamiento ══════════ -->
            <div class="ej-bloque">
                <div class="ej-bloque-titulo">
                    <span><i class="fas fa-plug"></i> Historial de equipamiento</span>
                    <span class="ej-chip neutro"><?= count($historialEquipos) ?> movimiento<?= count($historialEquipos) != 1 ? 's' : '' ?> · más nuevos primero</span>
                </div>
                <?php if (empty($historialEquipos)): ?>
                    <p class="ej-vacio">Sin movimientos de equipos registrados.</p>
                <?php else: ?>
                <div class="ej-bloque-body">
                    <ul class="hp-linea">
                    <?php foreach ($historialEquipos as $h):
                        $accion  = strtolower((string) $h['accion']); // alta / modificacion / baja
                        $ant     = is_array($h['datos_anteriores']) ? $h['datos_anteriores'] : (json_decode((string) $h['datos_anteriores'], true) ?: []);
                        $nvo     = is_array($h['datos_nuevos']) ? $h['datos_nuevos'] : (json_decode((string) $h['datos_nuevos'], true) ?: []);
                        $etiq    = ['alta' => 'ALTA', 'modificacion' => 'MODIFICACIÓN', 'baja' => 'BAJA'][$accion] ?? strtoupper($accion);
                        $nombre  = $limpio($nvo['equipamiento'] ?? ($ant['equipamiento'] ?? '')) ?: 'Equipo';
                        $datos   = $accion === 'baja' ? $ant : $nvo;
                    ?>
                        <li class="hp-mov <?= esc($accion, 'attr') ?>">
                            <div class="hp-mov-cab">
                                <span class="hp-mov-titulo"><span class="ej-chip <?= strtoupper(str_replace('ó', 'o', $accion)) ?>"><?= $etiq ?></span> <?= esc($nombre) ?></span>
                                <span class="hp-mov-cuando"><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($h['created_at'])) ?> · <?= esc($quien($h['modificado_por'])) ?></span>
                            </div>

                            <?php if ($accion === 'modificacion'): ?>
                                <?php
                                $cambios = [];
                                foreach ($camposEquipo as $campo => $nombreCampo) {
                                    $va = $limpio($ant[$campo] ?? '');
                                    $vn = $limpio($nvo[$campo] ?? '');
                                    if ($va !== $vn) $cambios[$nombreCampo] = [$ant[$campo] ?? '', $nvo[$campo] ?? ''];
                                }
                                ?>
                                <div class="hp-ficha">
                                    <?php foreach ($cambios as $nombreCampo => [$va, $vn]): ?>
                                        <div style="grid-column: span 2;"><span class="lbl"><?= esc($nombreCampo) ?></span>
                                            <div class="hp-cambio"><?= $valor($va, 'hp-antes') ?> <i class="fas fa-arrow-right hp-flecha"></i> <?= $valor($vn, 'hp-despues') ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (!$cambios): ?><div class="ej-sin-dato" style="grid-column: 1 / -1;">Se guardó sin diferencias en los datos.</div><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="hp-ficha <?= $accion === 'baja' ? 'baja' : '' ?>">
                                    <?php $hay = false; foreach ($camposEquipo as $campo => $nombreCampo):
                                        if ($campo === 'equipamiento') continue;
                                        $v = $limpio($datos[$campo] ?? '');
                                        if ($v === '') continue;
                                        $hay = true;
                                    ?>
                                        <div><span class="lbl"><?= esc($nombreCampo) ?></span><span class="hp-val"><?= esc($v) ?></span></div>
                                    <?php endforeach; ?>
                                    <?php if (!$hay): ?><div class="ej-sin-dato" style="grid-column: 1 / -1;">Sin más datos del equipo.</div><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <p class="ej-leyenda"><i class="fas fa-info-circle"></i> Registro de auditoría de solo lectura. "Sistema / importación" indica cambios hechos por una carga masiva (por ejemplo, el Excel provincial) y no por un usuario.</p>
    </div>
</div>

<?= $this->endSection() ?>
