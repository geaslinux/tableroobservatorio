<?= $this->extend('layout/home_layout') ?>

<?= $this->section('title') ?>Inicio · Ministerio de Salud<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $pob = $indicadores['poblacion'];
    $efe = $indicadores['efectores'];
    $aps = $indicadores['aps'];

    // Curva de población: se escala la serie al área del gráfico (128 × 48)
    $serie  = $pob['serie'];
    $min    = min($serie);
    $rango  = max(1, max($serie) - $min);
    $paso   = 124 / max(1, count($serie) - 1);
    $puntos = [];
    foreach ($serie as $i => $v) {
        $puntos[] = [round(2 + $i * $paso, 1), round(44 - (($v - $min) / $rango) * 34, 1)];
    }
    $linea = 'M' . implode(' L', array_map(static fn ($p) => $p[0] . ' ' . $p[1], $puntos));
    $area  = $linea . ' L' . end($puntos)[0] . ' 54 L2 54 Z';
    $ultimo = end($puntos);

    // Anillo de efectores: circunferencia de r=34 → 213.6
    $circ   = 2 * M_PI * 34;
    $offset = round($circ * (1 - min(100, max(0, $efe['porcentaje'])) / 100), 2);
?>
<div class="page-grid">
    <section class="hero hero--single">
        <div class="hero-stats hero-stats--top">

            <article class="stat-card blue">
                <div class="stat-icon"><i class="fas fa-people-group"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Población estimada de la Provincia de Jujuy</div>
                    <div class="stat-value"><?= esc($pob['valor']) ?></div>
                    <div class="trend up"><i class="fas fa-arrow-trend-up"></i> <?= esc($pob['tendencia']) ?></div>
                </div>
                <div class="stat-visual">
                    <svg class="mini-chart" viewBox="0 0 130 56" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <defs>
                            <linearGradient id="chartArea" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#3d8eff" stop-opacity="0.30"/>
                                <stop offset="1" stop-color="#3d8eff" stop-opacity="0"/>
                            </linearGradient>
                            <linearGradient id="chartLine" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0" stop-color="#94c8ff"/>
                                <stop offset="1" stop-color="#2b7de9"/>
                            </linearGradient>
                        </defs>
                        <path d="<?= $area ?>" fill="url(#chartArea)"/>
                        <path d="<?= $linea ?>" stroke="url(#chartLine)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="<?= $ultimo[0] ?>" cy="<?= $ultimo[1] ?>" r="7" fill="#3d8eff" opacity="0.18" class="punto-final"/>
                        <circle cx="<?= $ultimo[0] ?>" cy="<?= $ultimo[1] ?>" r="4" fill="#fff" stroke="#2b7de9" stroke-width="2.5"/>
                    </svg>
                </div>
            </article>

            <article class="stat-card teal">
                <div class="stat-icon"><i class="fas fa-hospital"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Efectores hospitalarios</div>
                    <div class="stat-value"><?= number_format($efe['porcentaje'], 1, ',', '.') ?>%</div>
                    <div class="trend up"><i class="fas fa-arrow-trend-up"></i> <?= esc($efe['tendencia']) ?></div>
                </div>
                <div class="stat-visual">
                    <div class="ring" role="img" aria-label="<?= number_format($efe['porcentaje'], 1, ',', '.') ?>% de efectores">
                        <svg viewBox="0 0 84 84" aria-hidden="true">
                            <defs>
                                <linearGradient id="ringTeal" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0" stop-color="#6fd7ca"/>
                                    <stop offset="1" stop-color="#2ab7a6"/>
                                </linearGradient>
                            </defs>
                            <circle class="ring-bg" cx="42" cy="42" r="34" fill="none" stroke-width="9"/>
                            <circle class="ring-fill" cx="42" cy="42" r="34" fill="none" stroke-width="9"
                                    stroke-dasharray="<?= round($circ, 2) ?>" stroke-dashoffset="<?= $offset ?>"/>
                        </svg>
                        <div class="ring-center"><i class="fas fa-house-medical"></i></div>
                    </div>
                </div>
            </article>

            <article class="stat-card orange">
                <div class="stat-icon"><i class="fas fa-clinic-medical"></i></div>
                <div class="stat-body">
                    <div class="stat-label">Establecimientos de APS</div>
                    <div class="stat-value"><?= esc($aps['valor']) ?></div>
                    <div class="trend neutral"><i class="fas fa-circle-info"></i> <?= esc($aps['detalle']) ?></div>
                </div>
                <div class="stat-visual">
                    <div class="soft-badge" aria-hidden="true"><i class="fas fa-hand-holding-medical"></i></div>
                </div>
            </article>
        </div>

        <div class="section-title section-title--tight">Tableros de mando</div>
        <div class="dashboard-grid dashboard-grid--gap">
            <article class="panel dashboard-card blue">
                <div>
                    <div class="dash-head">
                        <div class="dash-badge"><i class="fas fa-shield-heart"></i></div>
                        <div>
                            <div class="dash-title">Secretaría de Salud</div>
                            <div class="dash-subtitle">Indicadores de atención, cobertura y producción.</div>
                        </div>
                    </div>
                    <div class="dash-actions">
                        <a href="<?= base_url(route_to('inicio_views')); ?>" class="dash-btn">
                            Acceder al tablero <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="dash-art">
                    <img src="/imag/secretariaSalud.png" alt="Secretaría de Salud" class="dash-img-art">
                </div>
            </article>

            <article class="panel dashboard-card teal">
                <div>
                    <div class="dash-head">
                        <div class="dash-badge"><i class="fas fa-brain"></i></div>
                        <div>
                            <div class="dash-title">Secretaría de Salud Mental</div>
                            <div class="dash-subtitle">Indicadores de salud mental y adicciones.</div>
                        </div>
                    </div>
                    <div class="dash-actions">
                        <span class="dash-btn btn-disabled">
                            Acceder al tablero <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
                <div class="dash-art">
                    <img src="/imag/saludMental.png" alt="Secretaría de Salud Mental" class="dash-img-art">
                </div>
            </article>

            <article class="panel dashboard-card purple">
                <div>
                    <div class="dash-head">
                        <div class="dash-badge"><i class="fas fa-network-wired"></i></div>
                        <div>
                            <div class="dash-title">Coord. Gral. de Salud</div>
                            <div class="dash-subtitle">Indicadores de APS y epidemiología.</div>
                        </div>
                    </div>
                    <div class="dash-actions">
                        <span class="dash-btn btn-disabled">
                            Acceder al tablero <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
                <div class="dash-art">
                    <img src="/imag/coordinacionSalud.png" alt="Coordinación General de Salud" class="dash-img-art-coord">
                </div>
            </article>

            <article class="panel dashboard-card orange">
                <div>
                    <div class="dash-head">
                        <div class="dash-badge"><i class="fas fa-scale-balanced"></i></div>
                        <div>
                            <div class="dash-title">Legal y Técnica</div>
                            <div class="dash-subtitle">Apoyo legal y técnico en salud.</div>
                        </div>
                    </div>
                    <div class="dash-actions">
                        <span class="dash-btn btn-disabled">
                            Acceder al tablero <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
                <div class="dash-art">
                    <img src="/imag/legalTecnica.png" alt="Secretaría Legal y Técnica" class="dash-img-art-coord">
                </div>
            </article>
        </div>

        <div class="home-bottom">
            <div class="source">
                <i class="fas fa-circle-info"></i>
                Fuente: Sistema Integrado de Salud de Jujuy
            </div>
            <div class="government-mark">
                <small>Gobierno de</small>
                <div class="jujuy">JUJUY</div>
                <div class="tagline">Crece con la gente</div>
            </div>
        </div>
    </section>
    <div class="scroll-shadow"></div>
</div>
<?= $this->endSection() ?>