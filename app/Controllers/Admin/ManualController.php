<?php
namespace App\Controllers\Admin;

use App\Models\DocumentacionModel;

/**
 * Documentación: consulta del manual de usuario.
 * Cada sistema muestra el manual de un solo rol (Config\Manual::$rol): sistemabase el de
 * funcionario y sistemabasec el de administrativo (sus páginas y las marcadas "ambos").
 * La carga está en DocumentacionController.
 */
class ManualController extends AdminController
{
    // ─── Portada del manual ──────────────────────────────────────────────
    // Cada sistema muestra solo la tarjeta de su rol (Config\Manual::$rol)
    public function inicio()
    {
        $rol = $this->rolDelSistema();

        return view('documentacion/manual_inicio', [
            'roles'      => DocumentacionModel::ROLES,
            'rolSistema' => $rol,
            'cantidades' => [$rol => model('DocumentacionModel', false)->publicadasParaRol($rol)->countAllResults()],
        ]);
    }

    // ─── Portada del manual de un rol (índice + búsqueda) ────────────────
    public function rol($rol)
    {
        if (!$this->rolValido($rol)) {
            return redirect()->to(route_to('manual_inicio'));
        }

        $buscar = trim((string) ($this->request->getGet('q') ?? ''));
        $indice = $this->indice($rol);

        // Búsqueda en título, resumen, pantalla y descripción
        $resultados = null;
        if ($buscar !== '') {
            $resultados = model('DocumentacionModel', false)
                ->publicadasParaRol($rol)
                ->groupStart()
                    ->like('titulo', $buscar)
                    ->orLike('resumen', $buscar)
                    ->orLike('vista_nombre', $buscar)
                    ->orLike('modulo_nombre', $buscar)
                    ->orLike('descripcion', $buscar)
                ->groupEnd()
                ->findAll();
        }

        return view('documentacion/manual_rol', [
            'rol'        => $rol,
            'rolNombre'  => DocumentacionModel::ROLES[$rol],
            'indice'     => $indice['modulos'],
            'total'      => count($indice['plano']),
            'buscar'     => $buscar,
            'resultados' => $resultados,
            'paginaId'   => null,
        ]);
    }

    // ─── Página del manual ───────────────────────────────────────────────
    public function pagina($rol, $id)
    {
        if (!$this->rolValido($rol)) {
            return redirect()->to(route_to('manual_inicio'));
        }

        $indice = $this->indice($rol);
        $plano  = $indice['plano'];
        $pos    = array_search((int) $id, array_map(fn ($p) => $p->documentacion_id, $plano), true);

        // Página inexistente, en borrador o de otro rol
        if ($pos === false) {
            return redirect()->to(route_to('manual_rol', $rol))
                ->with('msg', ['type' => 'warning', 'body' => 'La página no está disponible para este rol.']);
        }

        return view('documentacion/manual_pagina', [
            'rol'       => $rol,
            'rolNombre' => DocumentacionModel::ROLES[$rol],
            'indice'    => $indice['modulos'],
            'pagina'    => $plano[$pos],
            'anterior'  => $plano[$pos - 1] ?? null,
            'siguiente' => $plano[$pos + 1] ?? null,
            'paginaId'  => (int) $id,
            'buscar'    => '',
        ]);
    }

    // ─── PDF: manual completo del rol (o un solo módulo con ?modulo=…) ───
    public function pdf($rol)
    {
        if (!$this->rolValido($rol)) {
            return redirect()->to(route_to('manual_inicio'));
        }

        $modulos = $this->indice($rol)['modulos'];
        $modulo  = (string) ($this->request->getGet('modulo') ?? '');

        if ($modulo !== '') {
            if (!isset($modulos[$modulo])) {
                return redirect()->to(route_to('manual_rol', $rol))
                    ->with('msg', ['type' => 'warning', 'body' => 'Ese módulo no tiene páginas para este rol.']);
            }
            $modulos = [$modulo => $modulos[$modulo]];
        }

        if (!$modulos) {
            return redirect()->to(route_to('manual_rol', $rol))
                ->with('msg', ['type' => 'warning', 'body' => 'Todavía no hay páginas publicadas para descargar.']);
        }

        $titulo = $modulo !== '' ? 'Módulo ' . $modulos[$modulo]['nombre'] : 'Manual completo';
        $nombre = 'manual_' . $rol . ($modulo !== '' ? '_' . $modulo : '') . '_' . date('Ymd') . '.pdf';

        return $this->generarPdf($rol, $modulos, $titulo, $nombre, true);
    }

    // ─── PDF: una sola página del manual ─────────────────────────────────
    public function pdfPagina($rol, $id)
    {
        if (!$this->rolValido($rol)) {
            return redirect()->to(route_to('manual_inicio'));
        }

        $pagina = null;
        foreach ($this->indice($rol)['plano'] as $p) {
            if ($p->documentacion_id === (int) $id) {
                $pagina = $p;
                break;
            }
        }

        if ($pagina === null) {
            return redirect()->to(route_to('manual_rol', $rol))
                ->with('msg', ['type' => 'warning', 'body' => 'La página no está disponible para este rol.']);
        }

        $modulos = [$pagina->modulo => ['nombre' => $pagina->modulo_nombre, 'paginas' => [$pagina]]];
        $nombre  = 'manual_' . $rol . '_' . url_title($pagina->titulo, '_', true) . '.pdf';

        return $this->generarPdf($rol, $modulos, $pagina->titulo, $nombre, false);
    }

    // Arma el PDF con Dompdf y lo devuelve como descarga.
    // $conPortada: portada + índice (manual o módulo); sin ella, solo la página.
    private function generarPdf(string $rol, array $modulos, string $titulo, string $nombreArchivo, bool $conPortada)
    {
        set_time_limit(180);

        // Las capturas van incrustadas (data URI): Dompdf no necesita pedirlas por HTTP
        $imagenes = [];
        foreach ($modulos as $m) {
            foreach ($m['paginas'] as $p) {
                $imagenes[$p->documentacion_id] = $this->imagenIncrustada($p->imagen, config('Manual')->raiz());
            }
        }

        $html = view('documentacion/manual_pdf', [
            'rol'        => $rol,
            'rolNombre'  => DocumentacionModel::ROLES[$rol],
            'titulo'     => $titulo,
            'modulos'    => $modulos,
            'imagenes'   => $imagenes,
            'logo'       => $this->imagenIncrustada('imag/logo.png'),
            'conPortada' => $conPortada,
            'fecha'      => date('d/m/Y'),
        ]);

        $opciones = new \Dompdf\Options();
        $opciones->set('defaultFont', 'DejaVu Sans'); // tildes y ñ
        $opciones->set('isRemoteEnabled', false);
        $opciones->set('isHtml5ParserEnabled', true);
        $opciones->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($opciones);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Pie con número de página en todas las hojas
        $fuente = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $dompdf->getCanvas()->page_text(
            40, 815,
            mb_strimwidth('Manual de usuario · ' . DocumentacionModel::ROLES[$rol] . ' · ' . $titulo, 0, 90, '…'),
            $fuente, 7.5, [0.35, 0.42, 0.49]
        );
        $dompdf->getCanvas()->page_text(500, 815, 'Página {PAGE_NUM} de {PAGE_COUNT}', $fuente, 7.5, [0.35, 0.42, 0.49]);

        return $this->response
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($dompdf->output());
    }

    // ─── Captura de una página ───────────────────────────────────────────
    // Las capturas están en la carpeta de sistemabasec (fuera del public/ de este sistema),
    // así que se entregan desde acá.
    public function imagen($id)
    {
        $pagina  = model('DocumentacionModel', false)->find($id);
        $archivo = $pagina ? $this->archivoSeguro($pagina->imagen, config('Manual')->raiz()) : null;

        if ($archivo === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setContentType(mime_content_type($archivo) ?: 'image/png')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody(file_get_contents($archivo));
    }

    // Imagen como data URI (o null si no existe). $raiz: carpeta contra la que se resuelve la ruta
    // (FCPATH para archivos propios como el logo; Config\Manual para las capturas).
    private function imagenIncrustada(?string $ruta, ?string $raiz = null): ?string
    {
        $archivo = $this->archivoSeguro($ruta, $raiz ?? FCPATH);
        if ($archivo === null) {
            return null;
        }

        return 'data:' . mime_content_type($archivo) . ';base64,' . base64_encode(file_get_contents($archivo));
    }

    // Ruta absoluta de una imagen solo si existe y queda dentro de $raiz (evita ../)
    private function archivoSeguro(?string $ruta, string $raiz): ?string
    {
        if (!$ruta) {
            return null;
        }

        $base    = realpath($raiz);
        $archivo = realpath($raiz . $ruta);
        if ($base === false || $archivo === false || !str_starts_with($archivo, $base) || !is_file($archivo)) {
            return null;
        }

        $tipo = mime_content_type($archivo) ?: '';

        return str_starts_with($tipo, 'image/') ? $archivo : null;
    }

    // Páginas publicadas del rol agrupadas por módulo, y la lista plana en orden de lectura
    private function indice(string $rol): array
    {
        $paginas = model('DocumentacionModel', false)->publicadasParaRol($rol)->findAll();

        $modulos = [];
        foreach ($paginas as $p) {
            $modulos[$p->modulo]['nombre']    = $p->modulo_nombre;
            $modulos[$p->modulo]['paginas'][] = $p;
        }

        // La lista plana sigue el mismo orden que el índice (agrupado por módulo)
        $plano = [];
        foreach ($modulos as $m) {
            array_push($plano, ...$m['paginas']);
        }

        return ['modulos' => $modulos, 'plano' => $plano];
    }

    // Solo se puede ver el manual del rol de este sistema
    private function rolValido($rol): bool
    {
        return $rol === $this->rolDelSistema();
    }

    // Rol configurado en Config\Manual (funcionario si falta o no es válido)
    private function rolDelSistema(): string
    {
        $rol = config('Manual')->rol ?? 'funcionario';

        return in_array($rol, DocumentacionModel::ROLES_MANUAL, true) ? $rol : 'funcionario';
    }
}
