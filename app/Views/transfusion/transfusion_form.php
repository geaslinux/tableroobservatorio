<?= $this->extend('layout/main'); ?>
<?= $this->section('title') ?> <?= isset($registro) ? 'EDITAR' : 'NUEVA' ?> TRANSFUSION <?= $this->endSection() ?>
<?= $this->section('menu') ?> <?= $this->include('admin/menu'); ?> <?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="section">
<h2 class="subtitle has-text-centered has-text-weight-bold" style="color:#13304d;">
    <?= isset($registro) ? 'Editar Transfusión' : 'Nueva Transfusión' ?>
</h2>

<form action="<?= isset($registro) ? base_url(route_to('transfusion_update')) : base_url(route_to('transfusion_store')) ?>" method="POST">
    <?= csrf_field() ?>
    <?php if (isset($registro)): ?>
        <?= $registro->getCampoOculto() ?>
    <?php endif; ?>

    <div class="container is-max-widescreen">
    <div class="notification" style="background-color: #13304d;">

        <?php if (session()->has('msg')): $msg = session('msg'); ?>
            <div class="notification is-<?= $msg['type'] ?> is-light"><?= $msg['body'] ?></div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="notification is-danger is-light">
                <ul><?php foreach (session('errors') as $e): ?><li><?= $e ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <!-- EJERCICIO + EFECTOR -->
        <div class="columns">
            <div class="column is-one-quarter">
                <div class="field">
                    <label class="label" style="color:white;">Ejercicio *</label>
                    <div class="control has-icons-left">
                        <input class="input <?= session('errors.ejercicio') ? 'is-danger' : '' ?>"
                            type="number" name="ejercicio"
                            placeholder="<?= date('Y') ?>"
                            max="<?= date('Y') ?>"
                            value="<?= old('ejercicio', $registro->ejercicio ?? date('Y')) ?>">
                        <span class="icon is-left"><i class="fas fa-calendar"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">
                        Solo hasta el año actual (<?= date('Y') ?>).
                    </p>
                    <?php if (session('errors.ejercicio')): ?>
                        <p class="help is-danger"><?= session('errors.ejercicio') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;">Efector *</label>
                    <div class="control">
                        <div class="select is-fullwidth <?= session('errors.efector_id') ? 'is-danger' : '' ?>">
                            <select name="efector_id">
                                <option value="">Seleccionar efector...</option>
                                <?php
                                $selEf = old('efector_id', $registro->efector_id ?? '');
                                foreach ($efectores as $ef):
                                ?>
                                    <option value="<?= $ef->efector_id ?>" <?= $selEf == $ef->efector_id ? 'selected' : '' ?>>
                                        <?= esc($ef->nombre) ?> — <?= esc($ef->region ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php if (session('errors.efector_id')): ?>
                        <p class="help is-danger"><?= session('errors.efector_id') ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($registro)): ?>
            <div class="column is-narrow">
                <div class="field">
                    <label class="label" style="color:white;">Estado</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="estado">
                                <option value="activo"      <?= ($registro->estado ?? 'activo') == 'activo'      ? 'selected' : '' ?>>Activo</option>
                                <option value="desactivado" <?= ($registro->estado ?? '') == 'desactivado' ? 'selected' : '' ?>>Desactivado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="estado" value="activo">
            <?php endif; ?>
        </div>

        <hr>

        <!-- MESES EN GRILLA 4 columnas -->
        <?php
        $mesesLabel = [
            'enero'      => 'Enero',
            'febrero'    => 'Febrero',
            'marzo'      => 'Marzo',
            'abril'      => 'Abril',
            'mayo'       => 'Mayo',
            'junio'      => 'Junio',
            'julio'      => 'Julio',
            'agosto'     => 'Agosto',
            'septiembre' => 'Septiembre',
            'octubre'    => 'Octubre',
            'noviembre'  => 'Noviembre',
            'diciembre'  => 'Diciembre',
        ];
        $chunks = array_chunk($mesesLabel, 4, true);
        ?>

        <?php foreach ($chunks as $grupo): ?>
        <div class="columns">
            <?php foreach ($grupo as $campo => $label): ?>
            <div class="column">
                <div class="field">
                    <label class="label" style="color:white;"><?= $label ?></label>
                    <div class="control has-icons-left">
                        <input class="input"
                            type="number"
                            id="mes_<?= $campo ?>"
                            name="<?= $campo ?>"
                            placeholder="0"
                            min="0"
                            value="<?= old($campo, $registro->$campo ?? 0) ?>"
                            oninput="calcularTotal()">
                        <span class="icon is-left"><i class="fas fa-tint"></i></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>

        <!-- TOTAL -->
        <div class="columns">
            <div class="column is-one-quarter">
                <div class="field">
                    <label class="label" style="color:white;">
                        Total
                        <span style="font-size:0.8rem; font-weight:normal; color:#ffe08a;">(suma de los 12 meses)</span>
                    </label>
                    <div class="control has-icons-left">
                        <input class="input"
                            type="number"
                            id="total"
                            name="total"
                            value="<?= old('total', $registro->total ?? 0) ?>"
                            style="background:#e8f5e9; font-weight:bold; color:#1a1a1a;"
                            readonly>
                        <span class="icon is-left"><i class="fas fa-calculator"></i></span>
                    </div>
                    <p class="help" style="color:#ffe08a;">Se calcula automáticamente.</p>
                </div>
            </div>
        </div>

        <hr>
        <div class="field is-grouped is-grouped-centered">
            <div class="control">
                <button class="button is-warning" type="submit">
                    <span class="icon"><i class="fas fa-save"></i></span>
                    <span><?= isset($registro) ? 'Actualizar' : 'Guardar' ?></span>
                </button>
            </div>
            <div class="control">
                <a href="<?= base_url(route_to('transfusion_list')) ?>" class="button is-warning">
                    <span class="icon"><i class="fas fa-list"></i></span>
                    <span>Volver al Listado</span>
                </a>
            </div>
        </div>

    </div>
    </div>
</form>
</section>

<script>
var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

function calcularTotal() {
    var total = 0;
    meses.forEach(function(m) {
        total += parseInt(document.getElementById('mes_' + m).value) || 0;
    });
    document.getElementById('total').value = total;
}

document.addEventListener('DOMContentLoaded', calcularTotal);
</script>

<?= $this->endSection() ?>