<?php
/**
 * Manual de usuario en PDF (Dompdf). Sin layout: HTML autónomo.
 * Dompdf no soporta flex/grid: la maquetación usa bloques y tablas.
 *
 * @var string $rol, $rolNombre, $titulo, $fecha
 * @var array  $modulos   ['clave' => ['nombre' => ..., 'paginas' => [Documentacion, ...]], ...]
 * @var array  $imagenes  [documentacion_id => data URI o null]
 * @var string|null $logo data URI del logo
 * @var bool   $conPortada portada + índice (manual o módulo) o solo la página
 */
$totalPaginas = array_sum(array_map(static fn ($m) => count($m['paginas']), $modulos));
$primera      = true;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?= esc($titulo) ?></title>
<style>
    @page { margin: 48px 40px 56px 40px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1a2b45; line-height: 1.55; }

    /* Portada */
    .portada { text-align: center; padding-top: 110px; }
    .portada img.logo { max-width: 220px; max-height: 90px; margin-bottom: 50px; }
    .portada .etiqueta { font-size: 11px; letter-spacing: 3px; text-transform: uppercase; color: #00b4a0; font-weight: bold; }
    .portada h1 { font-size: 30px; margin: 14px 0 6px; color: #1a2b45; }
    .portada h2 { font-size: 16px; margin: 0 0 40px; color: #5a6a7e; font-weight: normal; }
    .portada .rol {
        display: inline-block; padding: 8px 26px; border-radius: 20px;
        background: #1a2b45; color: #fff; font-size: 13px; font-weight: bold;
    }
    .portada .datos { margin-top: 70px; font-size: 10px; color: #5a6a7e; }
    .portada .linea { width: 80px; height: 4px; background: #00b4a0; margin: 26px auto; }

    /* Índice */
    .indice h2 { font-size: 18px; color: #1a2b45; border-bottom: 3px solid #00b4a0; padding-bottom: 6px; margin: 0 0 14px; }
    .indice .modulo { margin: 14px 0 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #00b4a0; }
    .indice table { width: 100%; border-collapse: collapse; }
    .indice td { padding: 5px 4px; border-bottom: 1px dotted #d0d5de; font-size: 10.5px; }
    .indice td.vista { color: #5a6a7e; text-align: right; font-size: 9.5px; width: 40%; }

    .salto { page-break-before: always; }

    /* Página del manual */
    .ruta { font-size: 9px; color: #5a6a7e; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    h1.titulo { font-size: 20px; margin: 0 0 8px; color: #1a2b45; }
    .tag {
        display: inline-block; padding: 2px 9px; border-radius: 8px; font-size: 9px; font-weight: bold;
        margin-right: 4px; background: #eef0f3; color: #5a6a7e;
    }
    .tag.rol-administrativo { background: #d9f4f0; color: #008a7a; }
    .tag.rol-funcionario    { background: #dcebf8; color: #2980b9; }
    .tag.rol-ambos          { background: #e3e7ee; color: #1a2b45; }
    .resumen {
        margin: 12px 0 6px; padding: 6px 0 6px 12px; border-left: 4px solid #00b4a0;
        font-size: 11.5px; color: #5a6a7e;
    }
    h3.seccion {
        font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #1a2b45;
        border-bottom: 2px solid #d9f4f0; padding-bottom: 4px; margin: 18px 0 8px;
    }

    .captura { border: 1px solid #d0d5de; padding: 0; }
    .captura .barra { background: #eef0f3; border-bottom: 1px solid #d0d5de; padding: 5px 8px; font-size: 8.5px; color: #5a6a7e; }
    .captura img { width: 100%; }

    /* Descripción (HTML del editor) */
    .descripcion p { margin: 0 0 8px; }
    .descripcion h1, .descripcion h2, .descripcion h3, .descripcion h4 { color: #1a2b45; margin: 12px 0 5px; }
    .descripcion h1 { font-size: 15px; } .descripcion h2 { font-size: 13.5px; } .descripcion h3 { font-size: 12px; } .descripcion h4 { font-size: 11px; }
    .descripcion ul, .descripcion ol { margin: 0 0 8px 18px; padding: 0; }
    .descripcion li { margin-bottom: 3px; }
    .descripcion blockquote { margin: 0 0 8px; padding: 6px 10px; border-left: 4px solid #00b4a0; background: #eefaf8; }
    .descripcion table { width: 100%; border-collapse: collapse; margin: 0 0 8px; }
    .descripcion td, .descripcion th { border: 1px solid #d0d5de; padding: 4px 6px; }
    .descripcion img { max-width: 100%; }
    .descripcion pre, .descripcion code { font-family: 'DejaVu Sans Mono', monospace; background: #f4f6f9; font-size: 9.5px; }
    .descripcion a { color: #00b4a0; }

    /* Validaciones */
    table.validaciones { width: 100%; border-collapse: collapse; font-size: 9.5px; }
    table.validaciones th {
        background: #1a2b45; color: #fff; text-align: left; padding: 6px 7px;
        font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.5px;
    }
    table.validaciones td { padding: 6px 7px; border-bottom: 1px solid #eef0f3; vertical-align: top; }
    table.validaciones tr:nth-child(even) td { background: #f8f9fb; }
    table.validaciones td.campo { font-weight: bold; width: 22%; }
    table.validaciones td.mensaje { color: #c0392b; }

    .actualizado { margin-top: 14px; font-size: 8.5px; color: #5a6a7e; }
</style>
</head>
<body>

<?php if ($conPortada): ?>
    <!-- ── PORTADA ── -->
    <div class="portada">
        <?php if ($logo): ?><img class="logo" src="<?= $logo ?>" alt="Logo"><?php endif; ?>
        <div class="etiqueta">Manual de usuario</div>
        <h1><?= esc($titulo) ?></h1>
        <h2>Documentación de las pantallas del sistema</h2>
        <span class="rol">Rol: <?= esc($rolNombre) ?></span>
        <div class="linea"></div>
        <div class="datos">
            <?= $totalPaginas ?> página<?= $totalPaginas == 1 ? '' : 's' ?> documentada<?= $totalPaginas == 1 ? '' : 's' ?>
            · <?= count($modulos) ?> módulo<?= count($modulos) == 1 ? '' : 's' ?><br>
            Generado el <?= esc($fecha) ?>
        </div>
    </div>

    <!-- ── ÍNDICE ── -->
    <div class="indice salto">
        <h2>Índice</h2>
        <?php foreach ($modulos as $m): ?>
            <div class="modulo"><?= esc($m['nombre']) ?></div>
            <table>
                <?php foreach ($m['paginas'] as $p): ?>
                    <tr>
                        <td><?= esc($p->titulo) ?></td>
                        <td class="vista"><?= esc($p->vista_nombre) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ── PÁGINAS ── -->
<?php foreach ($modulos as $m): ?>
    <?php foreach ($m['paginas'] as $p):
        $validaciones = $p->getValidacionesLista();
        $imagen       = $imagenes[$p->documentacion_id] ?? null;
        $salto        = $conPortada || !$primera;
        $primera      = false;
    ?>
        <div class="<?= $salto ? 'salto' : '' ?>">
            <div class="ruta"><?= esc($p->modulo_nombre) ?> &rsaquo; <?= esc($p->vista_nombre) ?></div>
            <h1 class="titulo"><?= esc($p->titulo) ?></h1>
            <div>
                <span class="tag rol-<?= esc($p->rol, 'attr') ?>"><?= esc($p->getRolNombre()) ?></span>
                <span class="tag"><?= esc($p->getTipoVistaNombre()) ?></span>
                <?php if ($p->version): ?><span class="tag">Versión <?= esc($p->version) ?></span><?php endif; ?>
            </div>

            <?php if ($p->resumen): ?>
                <div class="resumen"><?= esc($p->resumen) ?></div>
            <?php endif; ?>

            <?php if ($imagen): ?>
                <h3 class="seccion">Vista de la pantalla</h3>
                <div class="captura">
                    <div class="barra"><?= esc($p->modulo_nombre) ?> &rsaquo; <?= esc($p->vista_nombre) ?></div>
                    <img src="<?= $imagen ?>" alt="Captura">
                </div>
            <?php endif; ?>

            <?php if ($p->descripcion): ?>
                <h3 class="seccion">Descripción</h3>
                <div class="descripcion"><?= $p->descripcion ?></div>
            <?php endif; ?>

            <?php if ($validaciones): ?>
                <h3 class="seccion">Validaciones (<?= count($validaciones) ?>)</h3>
                <table class="validaciones">
                    <thead>
                        <tr><th>#</th><th>Campo</th><th>Regla</th><th>Mensaje que ve el usuario</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($validaciones as $i => $v): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td class="campo"><?= esc($v['campo'] ?? '') ?></td>
                                <td><?= esc($v['regla'] ?? '') ?></td>
                                <td class="mensaje"><?= esc($v['mensaje'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="actualizado">Última actualización: <?= $p->updated_at ? $p->updated_at->format('d/m/Y H:i') : '—' ?></div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>

</body>
</html>
