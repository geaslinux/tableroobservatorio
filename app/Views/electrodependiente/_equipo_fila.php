<?php
/**
 * Partial: _equipo_fila.php
 *
 * Variables esperadas:
 *   $idx        — índice numérico (o '__IDX__' para el template JS)
 *   $eq         — array con datos del equipo (puede ser vacío para fila nueva)
 *   $tiemposUso — array ['parcial', 'permanente']
 */
$eq         = $eq ?? [];
$tiemposUso = $tiemposUso ?? ['parcial', 'permanente'];
$isNew      = empty($eq);  // true = fila nueva desde JS
?>

<div class="equipo-card">

    <!-- ID oculto (para actualizar el existente; vacío en equipos nuevos) -->
    <input type="hidden"
           name="equipos[<?= $idx ?>][equipamiento_id]"
           value="<?= esc($eq['equipamiento_id'] ?? '') ?>">

    <!-- Número de equipo (se actualiza con JS) -->
    <p class="equipo-num">EQUIPO <?= is_numeric($idx) ? $idx + 1 : '?' ?></p>

    <!-- Botón quitar -->
    <button type="button" class="button is-danger is-small is-outlined btn-quitar-equipo" title="Quitar este equipo">
        <span class="icon"><i class="fas fa-times"></i></span>
    </button>

    <!-- Fila 1: Equipamiento · Marca · Serie · Modelo · Tiempo de uso -->
    <div class="columns is-multiline">

        <div class="column is-12-mobile is-3-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Equipamiento *</label>
                <div class="control has-icons-left">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][equipamiento]"
                           maxlength="100"
                           placeholder="Ej: bipap, concentrador de oxígeno"
                           value="<?= esc($eq['equipamiento'] ?? '') ?>"
                           required>
                    <span class="icon is-left is-small"><i class="fas fa-plug"></i></span>
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Marca</label>
                <div class="control">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][marca]"
                           maxlength="100"
                           placeholder="Ej: covidien"
                           value="<?= esc($eq['marca'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Serie</label>
                <div class="control">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][serie]"
                           maxlength="100"
                           placeholder="Nº de serie"
                           value="<?= esc($eq['serie'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Modelo</label>
                <div class="control">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][modelo]"
                           maxlength="100"
                           placeholder="Ej: Air Sep"
                           value="<?= esc($eq['modelo'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Tiempo de Uso</label>
                <div class="control">
                    <div class="select is-fullwidth is-small">
                        <select name="equipos[<?= $idx ?>][tiempo_uso]">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($tiemposUso as $tu): ?>
                                <option value="<?= $tu ?>"
                                    <?= ($eq['tiempo_uso'] ?? '') == $tu ? 'selected' : '' ?>>
                                    <?= ucfirst($tu) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Fila 2: F. Entrega · Médico Tratante · Titular del Servicio · Nº Servicio -->
    <div class="columns is-multiline">

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">F. Entrega</label>
                <div class="control">
                    <input class="input is-small"
                           type="date"
                           name="equipos[<?= $idx ?>][fecha_entrega]"
                           value="<?= esc($eq['fecha_entrega'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="column is-12-mobile is-3-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Médico Tratante</label>
                <div class="control has-icons-left">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][medico_tratante]"
                           maxlength="200"
                           placeholder="Apellido y nombre del médico"
                           value="<?= esc($eq['medico_tratante'] ?? '') ?>">
                    <span class="icon is-left is-small"><i class="fas fa-user-md"></i></span>
                </div>
            </div>
        </div>

        <div class="column is-12-mobile is-3-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Titular del Servicio</label>
                <div class="control has-icons-left">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][titular_servicio]"
                           maxlength="200"
                           placeholder="Apellido y nombre del titular"
                           value="<?= esc($eq['titular_servicio'] ?? '') ?>">
                    <span class="icon is-left is-small"><i class="fas fa-user"></i></span>
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Nº Servicio</label>
                <div class="control">
                    <input class="input is-small"
                           type="text"
                           name="equipos[<?= $idx ?>][nro_servicio]"
                           maxlength="50"
                           placeholder="Ej: 109265"
                           value="<?= esc($eq['nro_servicio'] ?? '') ?>">
                </div>
            </div>
        </div>

    </div>

    <!-- Fila 3: F. Ingreso RECS · Última Evaluación -->
    <div class="columns is-multiline">

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">F. Ingreso RECS</label>
                <div class="control">
                    <input class="input is-small"
                           type="date"
                           name="equipos[<?= $idx ?>][fecha_ingreso_recs]"
                           value="<?= esc($eq['fecha_ingreso_recs'] ?? '') ?>">
                </div>
            </div>
        </div>

        <div class="column is-6-mobile is-2-tablet">
            <div class="field">
                <label class="label" style="color:white; font-size:0.83rem;">Última Evaluación</label>
                <div class="control">
                    <input class="input is-small"
                           type="date"
                           name="equipos[<?= $idx ?>][ultima_evaluacion]"
                           value="<?= esc($eq['ultima_evaluacion'] ?? '') ?>">
                </div>
            </div>
        </div>

    </div>

</div><!-- /equipo-card -->