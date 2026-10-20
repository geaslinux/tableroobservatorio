<?php
namespace App\Controllers\Admin;

use App\Models\DocumentacionModel;

/**
 * Administración → Manual.
 * ABM de las páginas del manual de usuario. La consulta está en ManualController (módulo Documentación).
 */
class DocumentacionController extends AdminController
{
    // Las capturas se guardan en la carpeta de sistemabasec (ver Config\Manual):
    // en la base queda la ruta relativa, así las dos instalaciones leen el mismo archivo.

    // Etiquetas HTML que puede tener la descripción (lo que genera el editor)
    private const HTML_PERMITIDO = '<p><br><b><strong><i><em><u><s><strike><sub><sup><span><font><h1><h2><h3><h4><h5><h6>'
        . '<ul><ol><li><blockquote><pre><code><a><table><thead><tbody><tr><th><td><hr><div><img>';

    public function __construct()
    {
        helper('navegacion');
    }

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model): array
    {
        $filtro_modulo = $this->request->getGet('modulo')     ?? '';
        $filtro_rol    = $this->request->getGet('rol')        ?? '';
        if (!in_array($filtro_rol, $this->rolesDelSistema(), true)) $filtro_rol = '';
        $filtro_tipo   = $this->request->getGet('tipo_vista') ?? '';
        $filtro_estado = $this->request->getGet('estado')     ?? '';
        $search        = trim($this->request->getGet('search') ?? '');

        // Solo las páginas del manual de este sistema (su rol y "ambos")
        $model->whereIn('rol', $this->rolesDelSistema());
        if ($filtro_modulo) $model->where('modulo', $filtro_modulo);
        if ($filtro_rol)    $model->where('rol', $filtro_rol);
        if ($filtro_tipo)   $model->where('tipo_vista', $filtro_tipo);
        if ($search !== '') {
            $model->groupStart()
                  ->like('titulo', $search)
                  ->orLike('resumen', $search)
                  ->orLike('vista_nombre', $search)
                  ->groupEnd();
        }

        if ($filtro_estado !== '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', self::ESTADOS_VISIBLES);
        }

        return compact('filtro_modulo', 'filtro_rol', 'filtro_tipo', 'filtro_estado', 'search');
    }

    // Catálogos que usan el listado y el formulario
    private function catalogos(): array
    {
        return [
            'modulos' => navegacion_documentables(),
            'tipos'   => DocumentacionModel::TIPOS_VISTA,
            'roles'      => array_intersect_key(DocumentacionModel::ROLES, array_flip($this->rolesDelSistema())),
            'rolSistema' => $this->rolesDelSistema()[0],
        ];
    }

    // Pantallas del sistema que todavía no tienen ninguna página del manual
    private function pendientes(array $modulos): array
    {
        $documentadas = array_flip(
            model('DocumentacionModel', false)
                ->whereIn('estado', self::ESTADOS_VISIBLES)
                ->whereIn('rol', $this->rolesDelSistema())
                ->findColumn('vista') ?? []
        );

        $pendientes = [];
        foreach ($modulos as $clave => $modulo) {
            foreach ($modulo['vistas'] as $vista => $nombre) {
                if (!isset($documentadas[$vista])) {
                    $pendientes[] = ['modulo' => $clave, 'modulo_nombre' => $modulo['nombre'], 'vista' => $vista, 'vista_nombre' => $nombre];
                }
            }
        }

        return $pendientes;
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        $catalogos = $this->catalogos();

        // KPI sobre todo lo vigente de este sistema (sin filtros)
        $vigentes = model('DocumentacionModel', false)
            ->whereIn('estado', self::ESTADOS_VISIBLES)
            ->whereIn('rol', $this->rolesDelSistema())
            ->asArray()->findAll();
        $porRol   = array_count_values(array_column($vigentes, 'rol'));
        $pendientes = $this->pendientes($catalogos['modulos']);

        $model   = model('DocumentacionModel');
        $filtros = $this->aplicarFiltros($model);

        return view('documentacion/documentacion_list', array_merge($filtros, $catalogos, [
            'registros'  => $model->orderBy('modulo_nombre', 'ASC')->orderBy('orden', 'ASC')->orderBy('titulo', 'ASC')->paginate($this->porPagina()),
            'pager'      => $model->pager,
            'pendientes' => $pendientes,
            'kpi'        => [
                'paginas'        => count($vigentes),
                'publicadas'     => count(array_filter($vigentes, fn ($d) => $d['estado'] === 'activo')),
                'delRol'         => $porRol[$catalogos['rolSistema']] ?? 0,
                'ambos'          => $porRol['ambos'] ?? 0,
                'pendientes'     => count($pendientes),
            ],
        ]));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    // Desde "pantallas sin documentar" llega con ?modulo=…&vista=… para precargarlas
    public function create()
    {
        return view('documentacion/documentacion_form', $this->catalogos() + [
            'prefill' => [
                'modulo' => $this->request->getGet('modulo') ?? '',
                'vista'  => $this->request->getGet('vista')  ?? '',
            ],
        ]);
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return $this->volverConErrores();
        }

        $datos = $this->datosPost() + [
            'imagen'     => $this->guardarImagen(null),
            'created_by' => user_id(),
            'updated_by' => user_id(),
        ];

        $id = model('DocumentacionModel')->insert($datos);

        if ($this->request->getPost('ver')) {
            return redirect()->to(route_to('manual_pagina', $this->rolParaVer($datos['rol']), $id));
        }

        return $this->irALista('documentacion_list', 'Página del manual guardada correctamente');
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        $registro = model('DocumentacionModel')->find($id);

        if (!$registro || $registro->estado === 'eliminado' || !$this->esDelSistema($registro)) {
            return $this->irALista('documentacion_list', 'Registro no encontrado', 'error');
        }

        $registro->setCampoOculto();

        return view('documentacion/documentacion_form', $this->catalogos() + [
            'registro' => $registro,
            'prefill'  => [],
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        $id       = (int) $this->request->getPost('documentacion_id');
        $model    = model('DocumentacionModel');
        $registro = $id ? $model->find($id) : null;

        if (!$registro || $registro->estado === 'eliminado' || !$this->esDelSistema($registro)) {
            return $this->irALista('documentacion_list', 'Registro no encontrado', 'error');
        }

        if (!$this->valida()) {
            return $this->volverConErrores();
        }

        $datos = $this->datosPost() + [
            'imagen'     => $this->guardarImagen($registro->imagen),
            'estado'     => in_array($this->request->getPost('estado'), self::ESTADOS_VISIBLES, true) ? $this->request->getPost('estado') : 'activo',
            'updated_by' => user_id(),
        ];

        $model->update($id, $datos);

        if ($this->request->getPost('ver')) {
            return redirect()->to(route_to('manual_pagina', $this->rolParaVer($datos['rol']), $id));
        }

        return $this->irALista('documentacion_list', 'Página del manual actualizada correctamente');
    }

    // ─── Destroy (baja lógica: la captura se conserva) ───────────────────
    public function destroy()
    {
        $registro = model('DocumentacionModel', false)->find($this->request->getVar('id'));
        if ($registro && !$this->esDelSistema($registro)) {
            return $this->irALista('documentacion_list', 'Registro no encontrado', 'error');
        }

        return $this->bajaLogica('DocumentacionModel', 'documentacion_list');
    }

    // ─── Exportar índice del manual a Excel ──────────────────────────────
    public function export()
    {
        $model = model('DocumentacionModel');
        $this->aplicarFiltros($model);
        $registros = $model->orderBy('modulo_nombre', 'ASC')->orderBy('orden', 'ASC')->findAll();

        $spreadsheet = $this->nuevaPlanilla([
            'Módulo', 'Pantalla', 'Tipo de vista', 'Rol', 'Título', 'Resumen',
            'Validaciones', 'Captura', 'Orden', 'Versión', 'Estado', 'Actualizado',
        ], 'Manual');

        $this->escribirFilas($spreadsheet->getActiveSheet(), array_map(fn ($r) => [
            $r->modulo_nombre,
            $r->vista_nombre,
            $r->getTipoVistaNombre(),
            $r->getRolNombre(),
            $r->titulo,
            $r->resumen,
            count($r->getValidacionesLista()),
            $r->imagen ? 'Sí' : 'No',
            $r->orden,
            $r->version,
            $r->estado === 'activo' ? 'Publicada' : 'Borrador',
            $r->updated_at ? $r->updated_at->format('d/m/Y H:i') : '',
        ], $registros));

        return $this->descargarExcel($spreadsheet, 'manual_' . date('Ymd_His') . '.xlsx');
    }

    // ─── Datos del formulario ────────────────────────────────────────────
    private function datosPost(): array
    {
        $modulos = navegacion_documentables();
        $modulo  = (string) $this->request->getPost('modulo');
        $vista   = (string) $this->request->getPost('vista');
        $version = trim((string) $this->request->getPost('version'));
        $resumen = trim((string) $this->request->getPost('resumen'));

        return [
            'modulo'        => $modulo,
            'modulo_nombre' => $modulos[$modulo]['nombre'],
            'vista'         => $vista,
            'vista_nombre'  => $modulos[$modulo]['vistas'][$vista],
            'tipo_vista'    => $this->request->getPost('tipo_vista'),
            'rol'           => $this->request->getPost('rol'),
            'titulo'        => trim((string) $this->request->getPost('titulo')),
            'resumen'       => $resumen !== '' ? $resumen : null,
            'descripcion'   => $this->limpiarHtml($this->request->getPost('descripcion')),
            'validaciones'  => $this->validacionesPost(),
            'orden'         => max(0, $this->postEntero('orden')),
            'version'       => $version !== '' ? $version : null,
        ];
    }

    // Filas de la tabla de validaciones → JSON (se descartan las filas vacías)
    private function validacionesPost(): ?string
    {
        $campos   = (array) ($this->request->getPost('validacion_campo') ?? []);
        $reglas   = (array) ($this->request->getPost('validacion_regla') ?? []);
        $mensajes = (array) ($this->request->getPost('validacion_mensaje') ?? []);

        $lista = [];
        foreach ($campos as $i => $campo) {
            $fila = [
                'campo'   => mb_substr(trim((string) $campo), 0, 100),
                'regla'   => mb_substr(trim((string) ($reglas[$i] ?? '')), 0, 300),
                'mensaje' => mb_substr(trim((string) ($mensajes[$i] ?? '')), 0, 300),
            ];
            if ($fila['campo'] !== '' || $fila['regla'] !== '' || $fila['mensaje'] !== '') {
                $lista[] = $fila;
            }
        }

        return $lista ? json_encode($lista, JSON_UNESCAPED_UNICODE) : null;
    }

    // HTML del editor: solo etiquetas de formato, sin scripts ni eventos
    private function limpiarHtml(?string $html): ?string
    {
        $html = trim((string) $html);

        // El editor deja "<p><br></p>" cuando está vacío
        if (trim(strip_tags($html, '<img><table><hr>')) === '') {
            return null;
        }

        $html = strip_tags($html, self::HTML_PERMITIDO);
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/\b(href|src)\s*=\s*(["\']?)\s*(javascript|vbscript):[^"\'>\s]*/i', '$1=$2#', $html);

        return $html;
    }

    // Guarda la captura subida y devuelve la ruta a guardar en la base.
    // Sin archivo nuevo: conserva la anterior (o la quita si se marcó "quitar captura").
    private function guardarImagen(?string $anterior): ?string
    {
        $archivo = $this->request->getFile('imagen');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $config  = config('Manual');
            $carpeta = $config->raiz() . $config->carpetaImagenes;
            if (!is_dir($carpeta)) {
                mkdir($carpeta, 0775, true);
            }

            $nombre = $archivo->getRandomName();
            $archivo->move($carpeta, $nombre);
            $this->borrarImagen($anterior);

            return $config->carpetaImagenes . '/' . $nombre;
        }

        if ($this->request->getPost('quitar_imagen')) {
            $this->borrarImagen($anterior);
            return null;
        }

        return $anterior;
    }

    private function borrarImagen(?string $ruta): void
    {
        // Solo archivos de la carpeta del manual
        $config = config('Manual');
        if ($ruta && str_starts_with($ruta, $config->carpetaImagenes . '/') && is_file($config->raiz() . $ruta)) {
            unlink($config->raiz() . $ruta);
        }
    }

    // Rol con el que se abre la vista previa: el manual de este sistema ("ambos" también se ve ahí)
    private function rolParaVer(string $rol): string
    {
        return $this->rolesDelSistema()[0];
    }

    // Cada sistema carga solo su manual (Config\Manual::$rol) y las páginas de "ambos" roles.
    // El primer elemento es siempre el rol del sistema.
    private function rolesDelSistema(): array
    {
        $rol = config('Manual')->rol ?? 'funcionario';

        return [in_array($rol, DocumentacionModel::ROLES_MANUAL, true) ? $rol : 'funcionario', 'ambos'];
    }

    private function esDelSistema(object $registro): bool
    {
        return in_array($registro->rol, $this->rolesDelSistema(), true);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida(): bool
    {
        $modulos = navegacion_documentables();

        $ok = $this->validate([
            'modulo'     => [
                'rules'  => 'required|in_list[' . implode(',', array_keys($modulos)) . ']',
                'errors' => ['required' => 'Seleccione el módulo.', 'in_list' => 'El módulo no existe.'],
            ],
            'vista'      => ['rules' => 'required', 'errors' => ['required' => 'Seleccione la pantalla que se documenta.']],
            'tipo_vista' => [
                'rules'  => 'required|in_list[' . implode(',', array_keys(DocumentacionModel::TIPOS_VISTA)) . ']',
                'errors' => ['required' => 'Seleccione el tipo de vista.', 'in_list' => 'Tipo de vista inválido.'],
            ],
            'rol'        => [
                'rules'  => 'required|in_list[' . implode(',', $this->rolesDelSistema()) . ']',
                'errors' => ['required' => 'Seleccione el rol.', 'in_list' => 'Rol inválido para el manual de este sistema.'],
            ],
            'titulo'     => [
                'rules'  => 'required|max_length[150]',
                'errors' => ['required' => 'El título es obligatorio.', 'max_length' => 'El título no puede superar 150 caracteres.'],
            ],
            'resumen'    => ['rules' => 'permit_empty|max_length[300]', 'errors' => ['max_length' => 'El resumen no puede superar 300 caracteres.']],
            'orden'      => ['rules' => 'permit_empty|integer|greater_than_equal_to[0]', 'errors' => ['integer' => 'El orden debe ser un número entero.']],
            'version'    => ['rules' => 'permit_empty|max_length[20]'],
            'imagen'     => [
                'rules'  => 'is_image[imagen]|mime_in[imagen,image/png,image/jpeg,image/webp,image/gif]|max_size[imagen,5120]',
                'errors' => [
                    'is_image' => 'La captura debe ser una imagen.',
                    'mime_in'  => 'La captura debe ser PNG, JPG, WEBP o GIF.',
                    'max_size' => 'La captura no puede superar 5 MB.',
                ],
            ],
        ]);

        // La pantalla tiene que pertenecer al módulo elegido
        $modulo = (string) $this->request->getPost('modulo');
        $vista  = (string) $this->request->getPost('vista');
        if ($ok && !isset($modulos[$modulo]['vistas'][$vista])) {
            $this->validator->setError('vista', 'La pantalla no pertenece al módulo elegido.');
            $ok = false;
        }

        return $ok;
    }
}
