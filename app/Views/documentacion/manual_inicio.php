<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> DOCUMENTACIÓN <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>
<?= $this->section('css') ?><link rel="stylesheet" href="/css/panel.css"><link rel="stylesheet" href="/css/manual.css?v=9"><?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
// Datos de cada rol: color (clase), ícono, para quién es y qué temas cubre su manual.
// Cada sistema muestra solo el suyo (Config\Manual::$rol): sistemabase → funcionario, sistemabasec → administrativo.
$datosRol = [
    'administrativo' => [
        'clase' => 'mn-rol-admin',
        'icono' => 'fa-user-tie',
        'texto' => 'Para quienes cargan y administran los datos del sistema.',
        'temas' => [
            ['fa-pen-to-square', 'Formularios de carga', 'Cómo dar de alta y editar los registros de cada módulo.'],
            ['fa-file-import',   'Importar Excel',       'Carga masiva desde planillas y cómo resolver los errores.'],
            ['fa-list-check',    'Catálogos',            'Efectores, operativos y categorías que usan los formularios.'],
            ['fa-users-gear',    'Usuarios y grupos',    'Cuentas, roles y permisos de acceso.'],
        ],
    ],
    'funcionario' => [
        'clase' => 'mn-rol-func',
        'icono' => 'fa-user',
        'texto' => 'Para quienes consultan la información del sistema.',
        'temas' => [
            ['fa-table-columns',    'Paneles',            'Qué muestra cada indicador y cómo leer los gráficos.'],
            ['fa-filter',           'Listados y filtros', 'Cómo buscar, filtrar y ordenar los registros.'],
            ['fa-file-excel',       'Exportar Excel',     'Descargar en una planilla los datos que estás viendo.'],
            ['fa-map-location-dot', 'Mapas',              'Ubicaciones y distribución territorial.'],
        ],
    ],
];
$rol = $datosRol[$rolSistema];
$n   = $cantidades[$rolSistema] ?? 0;
?>

<div class="base-list-wrap">

    <?= view('partials/breadcrumb', ['nodo' => 'manual_inicio']) ?>

    <div class="bl-panel">
        <div class="bl-panel-header">
            <span class="bl-panel-title"><i class="fas fa-book-open"></i> MANUAL DE USUARIO</span>
            <div class="bl-kpi-group">
                <span class="bl-tag teal">
                    <i class="fas fa-file-lines" style="color:#fff; font-size:13px;"></i>
                    <?= $n ?> página<?= $n == 1 ? '' : 's' ?> publicada<?= $n == 1 ? '' : 's' ?>
                </span>
            </div>
        </div>

        <div class="bl-panel-body">

            <div class="bl-toolbar">
                <a href="<?= base_url(route_to('inicio_views')) ?>" class="bl-btn ghost">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <?php if (has_permission('GUARDAR PERSONA')): ?>
                    <a href="<?= base_url(route_to('documentacion_list')) ?>" class="bl-btn navy">
                        <i class="fas fa-pen-to-square"></i> Cargar páginas
                    </a>
                <?php endif; ?>
            </div>

            <div class="mn-portada <?= $rol['clase'] ?>">

                <!-- Tarjeta del rol: presentación y acceso al manual -->
                <section class="mn-portada-rol">
                    <div class="mn-portada-icono"><i class="fas <?= $rol['icono'] ?>"></i></div>
                    <span class="mn-portada-eyebrow">Manual de usuario</span>
                    <h2 class="mn-portada-titulo"><?= esc($roles[$rolSistema]) ?></h2>
                    <p class="mn-portada-texto"><?= esc($rol['texto']) ?></p>
                    <p class="mn-portada-texto">
                        Cada página explica una pantalla: para qué sirve, cómo se completa
                        y qué validaciones tiene, con una captura de referencia.
                    </p>

                    <div class="mn-portada-cant">
                        <i class="fas fa-file-lines"></i>
                        <strong><?= $n ?></strong> página<?= $n == 1 ? '' : 's' ?> publicada<?= $n == 1 ? '' : 's' ?>
                    </div>

                    <div class="mn-portada-acciones">
                        <a href="<?= base_url(route_to('manual_rol', $rolSistema)) ?>" class="mn-portada-btn principal">
                            Entrar al manual <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php if ($n > 0): ?>
                            <a href="<?= base_url(route_to('manual_pdf', $rolSistema)) ?>" class="mn-portada-btn secundario">
                                <i class="fas fa-file-pdf"></i> Descargar PDF
                            </a>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Qué cubre el manual de este rol -->
                <section class="mn-portada-temas">
                    <h3 class="mn-portada-subtitulo">Qué vas a encontrar</h3>
                    <ul class="mn-temas-lista">
                        <?php foreach ($rol['temas'] as [$icono, $titulo, $detalle]): ?>
                            <li class="mn-tema">
                                <span class="mn-tema-icono"><i class="fas <?= $icono ?>"></i></span>
                                <span>
                                    <strong class="mn-tema-titulo"><?= esc($titulo) ?></strong>
                                    <span class="mn-tema-detalle"><?= esc($detalle) ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

            </div>

            <div class="mn-ayudas mn-ayudas--tarjetas">
                <div><i class="fas fa-magnifying-glass"></i> <span><strong>Buscá</strong> por palabra desde el índice</span></div>
                <div><i class="fas fa-file-pdf"></i> <span><strong>Descargá</strong> el manual o una página en PDF</span></div>
                <div><i class="fas fa-print"></i> <span><strong>Imprimí</strong> la página que estés leyendo</span></div>
            </div>

        </div>
    </div>

</div>

<?= $this->endSection() ?>
