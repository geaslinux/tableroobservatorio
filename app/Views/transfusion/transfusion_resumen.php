<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> RESUMEN TRANSFUSIONES <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container is-max-widescreen">
<div class="notification" style="background-color: #13304d;">

    <h1 class="title" style="color:white;">TRANSFUSIONES </h1>
    <hr/>

    <?php if (session()->has('msg')): $msg = session('msg'); ?>
        <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
    <?php endif; ?>

    <div class="columns is-vcentered" style="margin-bottom:0.5rem;">
        <div class="column is-narrow">
            <a class="button is-info" href="<?= base_url(route_to('transfusion_list')) ?>">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>Volver</span>
            </a>
        </div>
        <div class="column is-narrow">
            <a class="button is-success" href="<?= base_url(route_to('transfusion_resumen_export')) ?>?<?= http_build_query(['ejercicio' => $filtro_ejercicio, 'region' => $filtro_region]) ?>">
                <span class="icon"><i class="fas fa-file-excel"></i></span>
                <span>Descargar Excel</span>
            </a>
        </div>
    </div>

    <!-- FILTROS -->
    <form method="GET" action="<?= base_url(route_to('transfusion_resumen')) ?>">
        <div class="columns is-vcentered">

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Ejercicio</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="ejercicio">
                                <option value="">Todos</option>
                                <?php foreach ($ejercicios as $ej): ?>
                                    <option value="<?= $ej ?>" <?= $filtro_ejercicio == $ej ? 'selected' : '' ?>><?= $ej ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-2">
                <div class="field">
                    <label class="label" style="color:white;">Región</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="region">
                                <option value="">Todas</option>
                                <?php foreach ($regiones as $reg): ?>
                                    <option value="<?= $reg ?>" <?= $filtro_region == $reg ? 'selected' : '' ?>><?= $reg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white;">&nbsp;</label>
                    <div class="control">
                        <button class="button is-link" type="submit">
                            <span class="icon"><i class="fas fa-filter"></i></span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <!-- TABLA -->
    <div class="table-container">
    <table class="table is-fullwidth is-hoverable is-bordered">
        <thead>
            <tr style="background-color:#e8f0fe;">
                <th style="color:black;">Ejercicio</th>
                <th style="color:black;">Hospital / Efector</th>
                <th style="color:black;">Nivel de Complejidad</th>
                <th style="color:black;">Departamento</th>
                <th style="color:black;">Ubicación</th>
                <th style="color:black;">Región</th>
                <th style="color:black; text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $regionActual = null;
        $subtotal     = 0;
        $granTotal    = 0;
        foreach ($registros as $r):
            // Separador de región
            if ($r->efector_region !== $regionActual):
                if ($regionActual !== null): ?>
                    <tr style="background-color:#d0e4ff; font-weight:bold;">
                        <td colspan="6" style="text-align:right; color:#13304d;">
                            Subtotal <?= esc($regionActual) ?>
                        </td>
                        <td style="text-align:right; color:#13304d;">
                            <?= number_format($subtotal, 0, ',', '.') ?>
                        </td>
                    </tr>
                <?php endif;
                $regionActual = $r->efector_region;
                $subtotal     = 0;
            endif;
            $subtotal  += $r->total;
            $granTotal += $r->total;
        ?>
            <tr>
                <td><?= $r->ejercicio ?></td>
                <td><?= esc($r->efector_nombre) ?></td>
                <td><?= esc($r->efector_nivel ?? '—') ?></td>
                <td><?= esc($r->efector_depto ?? '—') ?></td>
                <td><?= esc($r->efector_ubicacion ?? '—') ?></td>
                <td>
                    <span class="tag
                        <?php
                        switch($r->efector_region) {
                            case 'CENTRO':   echo 'is-info';    break;
                            case 'VALLE':    echo 'is-success'; break;
                            case 'RAMAL I':
                            case 'RAMAL II': echo 'is-warning'; break;
                            case 'QUEBRADA': echo 'is-danger';  break;
                            case 'PUNA':     echo 'is-dark';    break;
                            default:         echo 'is-light';
                        }
                        ?>">
                        <?= esc($r->efector_region ?? '—') ?>
                    </span>
                </td>
                <td style="text-align:right;"><strong><?= number_format($r->total, 0, ',', '.') ?></strong></td>
            </tr>
        <?php endforeach; ?>

        <!-- Último subtotal de región -->
        <?php if ($regionActual !== null): ?>
            <tr style="background-color:#d0e4ff; font-weight:bold;">
                <td colspan="6" style="text-align:right; color:#13304d;">
                    Subtotal <?= esc($regionActual) ?>
                </td>
                <td style="text-align:right; color:#13304d;">
                    <?= number_format($subtotal, 0, ',', '.') ?>
                </td>
            </tr>
        <?php endif; ?>

        <!-- GRAN TOTAL -->
        <?php if (!empty($registros)): ?>
            <tr style="background-color:#13304d;">
                <td colspan="6" style="text-align:right; color:white; font-weight:bold; font-size:1.05rem;">
                    TOTAL GENERAL
                </td>
                <td style="text-align:right; color:white; font-weight:bold; font-size:1.05rem;">
                    <?= number_format($granTotal, 0, ',', '.') ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php if (empty($registros)): ?>
            <tr><td colspan="7" class="has-text-centered">No hay registros para mostrar.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>
</div>
<?= $this->endSection() ?>