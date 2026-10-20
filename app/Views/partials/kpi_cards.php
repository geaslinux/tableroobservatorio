<?php
/**
 * Tarjetas KPI con ícono (estilos en public/css/panel.css).
 *
 * Uso:
 *   <?= view('partials/kpi_cards', ['tarjetas' => [
 *       ['label' => 'Bases', 'valor' => $totalBases, 'icono' => 'fa-hospital', 'color' => 'teal'],
 *       ['label' => '% ocupacional', 'valor' => $promedio, 'decimales' => 2, 'sufijo' => '%', 'icono' => 'fa-bed', 'color' => 'amber'],
 *   ]]) ?>
 *
 * Claves de cada tarjeta:
 *   label      texto de la etiqueta (se muestra en mayúsculas)
 *   valor      número (se formatea 1.234,56) o texto
 *   icono      clase Font Awesome sin el prefijo "fas " (ej. fa-hospital)
 *   color      teal | blue | purple | navy | green | red | amber
 *   decimales  opcional, cantidad de decimales para valores numéricos (0 por defecto)
 *   sufijo     opcional, texto pegado al valor (ej. "%")
 *   sub        opcional, línea chica debajo del valor
 */
$colores = ['teal', 'blue', 'purple', 'navy', 'green', 'red', 'amber'];

// Tarjetas por fila en escritorio, para que las filas queden parejas (nunca 5 + 1):
// hasta 4 → fila de 4 · 5 → fila de 5 · 6 → 2 filas de 3 · 7 u 8 → filas de 4
$cantidad = count($tarjetas);
$porFila  = match (true) {
    $cantidad === 5 => 5,
    $cantidad === 6 => 3,
    default         => 4,
};
?>
<div class="ml-kpi-cards por-fila-<?= $porFila ?>" style="--kpi-por-fila: <?= $porFila ?>;">
    <?php foreach ($tarjetas as $t):
        $color = in_array($t['color'] ?? '', $colores, true) ? $t['color'] : 'teal';
        $valor = $t['valor'] ?? 0;
        $texto = is_numeric($valor)
            ? number_format((float) $valor, (int) ($t['decimales'] ?? 0), ',', '.')
            : esc((string) $valor);
    ?>
        <div class="ml-kpi-card kpi-<?= $color ?>">
            <div class="ml-kpi-icon"><i class="fas <?= esc($t['icono'] ?? 'fa-chart-bar', 'attr') ?>"></i></div>
            <div class="ml-kpi-content">
                <div class="ml-kpi-label"><?= esc($t['label']) ?></div>
                <div class="ml-kpi-value"><?= $texto ?><?= esc($t['sufijo'] ?? '') ?></div>
                <?php if (!empty($t['sub'])): ?>
                    <div class="ml-kpi-sub" title="<?= esc($t['sub'], 'attr') ?>"><?= esc($t['sub']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>