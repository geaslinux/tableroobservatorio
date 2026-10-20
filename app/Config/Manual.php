<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Módulo Documentación (manual de usuario).
 *
 * La tabla `documentacion` es compartida con sistemabasec (misma base `rep`) y las capturas
 * se guardan en UNA sola carpeta: la de sistemabasec. En la base queda la ruta relativa
 * (`uploads/documentacion/archivo.png`), que se resuelve contra $raizImagenes.
 * Este sistema las muestra con la ruta `manual_imagen` (no están en su public/).
 */
class Manual extends BaseConfig
{
    /**
     * Rol cuyo manual muestra este sistema. sistemabase solo consulta información,
     * así que muestra el manual de "funcionario" (sistemabasec, que hace la carga, muestra "administrativo").
     * Las páginas marcadas "ambos" aparecen igual.
     */
    public string $rol = 'funcionario';

    /**
     * Carpeta contra la que se resuelven las rutas de las capturas: el public/ de sistemabasec.
     * Se puede cambiar en .env con `manual.raizImagenes = "C:/ruta/a/sistemabasec/public"`.
     */
    public string $raizImagenes = ROOTPATH . '../sistemabasec/public';

    /** Subcarpeta (dentro de $raizImagenes) donde se guardan las capturas nuevas. */
    public string $carpetaImagenes = 'uploads/documentacion';

    /** Carpeta absoluta de la raíz, normalizada y con barra final. */
    public function raiz(): string
    {
        $raiz = realpath($this->raizImagenes) ?: $this->raizImagenes;

        return rtrim(str_replace('\\', '/', $raiz), '/') . '/';
    }
}