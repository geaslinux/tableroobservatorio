<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BaseCController extends BaseController
{
    private const MODULO           = 'bases';
    private const REGIONES         = ['PUNA', 'QUEBRADA', 'CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II'];
    private const PROVINCIAS       = ['JUJUY'];
    private const ESTADOS_VISIBLES = ['activo', 'desactivado']; // la baja lógica deja 'eliminado'
    private const PRIMERA_VISITA   = '2000-01-01 00:00:00';

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model): array
    {
        $search           = strtoupper($this->request->getGet('search') ?? '');
        $filtro_tipo      = $this->request->getGet('tipo')      ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';
        $filtro_provincia = $this->request->getGet('provincia') ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        // ── Búsqueda ───────────────────────────────────────────────
        if ($search !== '') {
            $model->groupStart()
                  ->like('UPPER(nombre)', $search)
                  ->orLike('UPPER(ubicacion)', $search)
                  ->orLike('UPPER(region)', $search)
                  ->orLike('UPPER(nro)', $search)
                  ->groupEnd();
        }

        // ── Otros filtros ──────────────────────────────────────────
        if ($filtro_tipo)      $model->where('tipo', $filtro_tipo);
        if ($filtro_region)    $model->where('region', $filtro_region);
        if ($filtro_provincia) $model->where('provincia', $filtro_provincia);

        // ── Estado: sin filtro se ven activas y desactivadas (nunca eliminadas) ──
        if (in_array($filtro_estado, self::ESTADOS_VISIBLES, true)) {
            $model->where('estado', $filtro_estado);
        } else {
            $filtro_estado = '';
            $model->whereIn('estado', self::ESTADOS_VISIBLES);
        }

        /*
         * Botón de estado marcado en el listado: todos / activo / inactivo / ninguno.
         * Solo cambia cómo se ve (color de los pines y tarjetas); el filtro real es `estado`.
         * Si no viene (por ejemplo, desde el link de exportar), se deduce del estado.
         */
        $estado_modo = $this->request->getGet('estado_modo') ?? '';
        if (!in_array($estado_modo, ['todos', 'activo', 'inactivo', 'ninguno'], true)) {
            $estado_modo = match ($filtro_estado) {
                'activo'      => 'activo',
                'desactivado' => 'inactivo',
                default       => 'ninguno',
            };
        }

        return compact('search', 'filtro_tipo', 'filtro_region', 'filtro_provincia', 'filtro_estado', 'estado_modo');
    }

    // Conteo respetando los filtros actuales + una condición extra
    private function contarFiltrado(string $campo, string $valor): int
    {
        $model = model('BaseModel', false);
        $this->aplicarFiltros($model);

        return $model->where($campo, $valor)->countAllResults();
    }

    // ─── Última visita del usuario al módulo ─────────────────────────────
    private function ultimaVista(): string
    {
        $registro = model('UserLastVisitModel', false)
            ->where('user_id', user_id())
            ->where('modulo', self::MODULO)
            ->first();

        return $registro ? $registro->ultima_vista : self::PRIMERA_VISITA;
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');
        $perPage = config('Obse')->regPerPage ?? 10;

        // ── Novedades desde la última visita ──────────────────────
        $ultimaVista = $this->ultimaVista();

        $nuevas = model('BaseModel', false)
            ->whereIn('estado', self::ESTADOS_VISIBLES)
            ->where('created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('BaseModel', false)
            ->whereIn('estado', self::ESTADOS_VISIBLES)
            ->where('updated_at >', $ultimaVista)
            ->where('updated_at != created_at')
            ->countAllResults();

        // ── Totales de toda la red (sin filtros) ──────────────────
        $totalRedGlobal          = model('BaseModel', false)->whereIn('estado', self::ESTADOS_VISIBLES)->countAllResults();
        $totalActivasGlobal      = model('BaseModel', false)->where('estado', 'activo')->countAllResults();
        $totalDesactivadasGlobal = model('BaseModel', false)->where('estado', 'desactivado')->countAllResults();

        // ── Conteos respetando los filtros actuales ───────────────
        $totalBases = $this->contarFiltrado('tipo', 'BASE');
        $totalUses  = $this->contarFiltrado('tipo', 'USE');

        // ── Listado lateral y mapa: todas las bases del filtro, sin paginar ──
        $modelMapa = model('BaseModel', false);
        $this->aplicarFiltros($modelMapa);
        $basesMap = $modelMapa->orderBy('tipo', 'ASC')->orderBy('nombre', 'ASC')->findAll();

        // ── Tabla paginada ────────────────────────────────────────
        $model   = model('BaseModel', false);
        $filtros = $this->aplicarFiltros($model);
        $bases   = $model->orderBy('tipo', 'ASC')->orderBy('nombre', 'ASC')->paginate($perPage);

        return view('base/base_list', array_merge($filtros, [
            'bases'                   => $bases,
            'basesMap'                => $basesMap,
            'pager'                   => $model->pager,
            'regiones'                => self::REGIONES,
            'provincias'              => self::PROVINCIAS,
            'nuevas'                  => $nuevas,
            'modificadas'             => $modificadas,
            'ultimaVista'             => $ultimaVista,
            'totalActivas'            => $this->contarFiltrado('estado', 'activo'),
            'totalDesactivadas'       => $this->contarFiltrado('estado', 'desactivado'),
            'totalBases'              => $totalBases,
            'totalUses'               => $totalUses,
            'totalGeneral'            => $totalBases + $totalUses,
            'totalRedGlobal'          => $totalRedGlobal,
            'totalActivasGlobal'      => $totalActivasGlobal,
            'totalDesactivadasGlobal' => $totalDesactivadasGlobal,
        ]));
    }

    // ─── Marcar Visto ────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');
        $visitas = model('UserLastVisitModel', false);
        $clave   = ['user_id' => user_id(), 'modulo' => self::MODULO];
        $ahora   = date('Y-m-d H:i:s');

        // Una fila por usuario y módulo: siempre filtrar por los dos
        if ($visitas->where($clave)->first()) {
            $visitas->builder()->where($clave)->update(['ultima_vista' => $ahora]);
        } else {
            $visitas->insert($clave + ['ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('base_list'));
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('BaseModel', false);
        $this->aplicarFiltros($model);
        $bases = $model->orderBy('tipo', 'ASC')->orderBy('nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->fromArray(['Tipo', 'Nro', 'Nombre', 'Coordenadas', 'Ubicación', 'Región', 'Provincia', 'Estado'], null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        // Datos
        $filas = array_map(fn ($b) => [
            $b->tipo, $b->nro, $b->nombre, $b->coordenadas,
            $b->ubicacion, $b->region, $b->provincia, $b->estado,
        ], $bases);
        if ($filas) {
            $sheet->fromArray($filas, null, 'A2', true);
        }

        // Autoajuste columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $contenido = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $this->response->download('bases_' . date('Ymd_His') . '.xlsx', $contenido);
    }

    // ─── Create ──────────────────────────────────────────────────────────
    public function create()
    {
        helper('form');
        return view('base/base_form', ['formRoute' => 'base_store']);
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return $this->volverConErrores();
        }

        model('BaseModel')->insert($this->datosPost());

        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base guardada correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($base_id)
    {
        if (!$base = model('BaseModel')->find($base_id)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $base->setCampoOculto();

        return view('base/base_form', ['base' => $base, 'formRoute' => 'base_update']);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        $id    = $this->request->getPost('base_id');
        $model = model('BaseModel');

        if (!$id || !$model->find($id)) {
            return redirect()->to(route_to('base_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Base no encontrada']);
        }

        if (!$this->valida((int) $id)) {
            return $this->volverConErrores();
        }

        $model->update($id, $this->datosPost());

        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base actualizada correctamente']);
    }

    // ─── Destroy (baja lógica) ───────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('BaseModel');

        if (!$id || !$model->find($id)) {
            return redirect()->to(route_to('base_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Base no encontrada']);
        }

        $model->update($id, ['estado' => 'eliminado']);

        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base eliminada correctamente']);
    }

    // ─── Campos del POST en mayúsculas; la provincia es siempre JUJUY ────
    private function datosPost(): array
    {
        $data = [];
        foreach (['tipo', 'nro', 'nombre', 'coordenadas', 'ubicacion', 'region'] as $campo) {
            $valor        = $this->request->getPost($campo);
            $data[$campo] = $valor !== null ? strtoupper(trim($valor)) : null;
        }

        $data['provincia'] = self::PROVINCIAS[0];
        $estado            = $this->request->getPost('estado');
        $data['estado']    = in_array($estado, self::ESTADOS_VISIBLES, true) ? $estado : 'activo';

        return $data;
    }

    private function volverConErrores()
    {
        return redirect()->back()->withInput()
            ->with('errors', $this->validator->getErrors())
            ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    // En edición, el nombre único excluye al propio registro
    private function valida(?int $id = null): bool
    {
        $mayusculas = 'regex_match[/^[A-Z0-9\s\.\-\,]+$/]';
        $unico      = $id ? "is_unique[base.nombre,base_id,{$id}]" : 'is_unique[base.nombre]';

        return $this->validate([
            'nombre'    => "required|{$unico}|{$mayusculas}",
            'tipo'      => 'required|in_list[BASE,USE]',
            'ubicacion' => "required|{$mayusculas}",
            'region'    => 'required|in_list[' . implode(',', self::REGIONES) . ']',
        ], [
            'nombre'    => [
                'regex_match' => 'El nombre debe estar en MAYÚSCULAS.',
                'is_unique'   => 'Ya existe una base con ese nombre.',
            ],
            'ubicacion' => ['regex_match' => 'La ubicación debe estar en MAYÚSCULAS.'],
        ]);
    }
}