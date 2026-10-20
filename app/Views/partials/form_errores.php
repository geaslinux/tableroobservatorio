<?php
/**
 * Resumen de errores de validación (los manda el controller con ->with('errors', ...)).
 * Mismo estilo de aviso que base_form.
 */
$erroresForm = session('errors');
if (empty($erroresForm) || !is_array($erroresForm)) {
    return;
}
?>
<div class="bf-notice is-danger">
    <i class="fas fa-exclamation-circle"></i>
    <div>
        <strong>Revisá los siguientes datos:</strong>
        <ul>
            <?php foreach ($erroresForm as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>