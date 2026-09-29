<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Base;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BaseCController extends BaseController
{
    // ─── Filtros reutilizables ───────────────────────────────────────────
private function aplicarFiltros($model)
{
    $search           = strtoupper($this->request->getGet('search') ?? '');
    $filtro_tipo      = $this->request->getGet('tipo') ?? '';
    $filtro_region    = $this->request->getGet('region') ?? '';
    $filtro_provincia = $this->request->getGet('provincia') ?? '';
    $filtro_estado    = $this->request->getGet('estado') ?? '';
    $estado_modo      = $this->request->getGet('estado_modo') ?? 'ninguno';

    // ── Búsqueda ───────────────────────────────────────────────
    if ($search != '') {
        $model->groupStart()
              ->like('UPPER(nombre)', $search)
              ->orLike('UPPER(ubicacion)', $search)
              ->orLike('UPPER(region)', $search)
              ->orLike('UPPER(nro)', $search)
              ->groupEnd();
    }

    // ── Otros filtros ──────────────────────────────────────────
    if ($filtro_tipo) {
        $model->where('tipo', $filtro_tipo);
    }

    if ($filtro_region) {
        $model->where('region', $filtro_region);
    }

    if ($filtro_provincia) {
        $model->where('provincia', $filtro_provincia);
    }

    // ── FILTRO DE ESTADO ───────────────────────────────────────
    switch ($estado_modo) {

        case 'activo':

            // Solo bases activas
            $filtro_estado = 'activo';
            $model->where('estado', 'activo');

            break;

        case 'inactivo':

            // Solo bases desactivadas
            $filtro_estado = 'desactivado';
            $model->where('estado', 'desactivado');

            break;

        case 'ninguno':

            /*
             * Ningún botón seleccionado:
             * mostramos BASE + USE,
             * activas + desactivadas.
             */
            $filtro_estado = '';
            $model->whereIn('estado', ['activo', 'desactivado']);

            break;

        case 'todos':

        default:

            /*
             * Todos seleccionado:
             * mostramos BASE + USE,
             * activas + desactivadas.
             */
            $filtro_estado = '';
            $model->whereIn('estado', ['activo', 'desactivado']);

            break;
    }

    return compact(
        'search',
        'filtro_tipo',
        'filtro_region',
        'filtro_provincia',
        'filtro_estado',
        'estado_modo'
    );
}

 public function index()
{
    helper('auth');
    $config  = config('Obse');
    $perPage = $config->regPerPage ?? 10;

    // ── Auditoría PRIMERO (antes de aplicar filtros al modelo) ────────
    $userId      = user()->id;
    $visitModel  = model('UserLastVisitModel');
    $registro    = $visitModel->where('user_id', $userId)
                              ->where('modulo', 'bases')
                              ->first();
    $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

    $nuevas = model('BaseModel')
        ->where('created_at >', $ultimaVista)
        ->whereIn('estado', ['activo','desactivado'])
        ->countAllResults(); // resetea el builder

    $modificadas = model('BaseModel')
        ->where('updated_at >', $ultimaVista)
        ->where('updated_at != created_at')
        ->whereIn('estado', ['activo','desactivado'])
        ->countAllResults(); // resetea el builder

    $totalRedGlobal = model('BaseModel')
        ->whereIn('estado', ['activo', 'desactivado'])
        ->countAllResults();

        $totalActivasGlobal = model('BaseModel')
        ->where('estado', 'activo')
        ->countAllResults();

    $totalDesactivadasGlobal = model('BaseModel')
        ->where('estado', 'desactivado')
        ->countAllResults();

    // ── Filtros DESPUÉS de los conteos ────────────────────────────────
    $model   = model('BaseModel');
    $filtros = $this->aplicarFiltros($model); // ahora el builder está limpio

    // ── Conteo de activas/desactivadas (respetando los filtros actuales) ──
    $modelActivas = model('BaseModel');
    $this->aplicarFiltros($modelActivas);
    $totalActivas = $modelActivas->where('estado', 'activo')->countAllResults();

    $modelDesact = model('BaseModel');
    $this->aplicarFiltros($modelDesact);
    $totalDesactivadas = $modelDesact->where('estado', 'desactivado')->countAllResults();

    // ── Conteo por TIPO: BASE / USE (respetando filtros, sobre estado activo+desactivado) ──
    $modelBases = model('BaseModel');
    $this->aplicarFiltros($modelBases);
    $totalBases = $modelBases->where('tipo', 'BASE')->countAllResults();

    $modelUses = model('BaseModel');
    $this->aplicarFiltros($modelUses);
    $totalUses = $modelUses->where('tipo', 'USE')->countAllResults();

    // ── Datos para el mapa (sin paginar) ──
    $modelMap = model('BaseModel');
    $this->aplicarFiltros($modelMap);
    $basesMap = $modelMap->orderBy('tipo','ASC')->orderBy('nombre','ASC')->findAll();

    $totalGeneral = $totalBases + $totalUses;

    $regiones   = ['PUNA','QUEBRADA','CENTRO','VALLE','RAMAL I','RAMAL II'];
    $provincias = ['JUJUY'];

    return view('base/base_list', array_merge($filtros, [
        'bases'             => $model->orderBy('tipo','ASC')->orderBy('nombre','ASC')->paginate($perPage),
        'basesMap'          => $basesMap,
        'pager'             => $model->pager,
        'regiones'          => $regiones,
        'provincias'        => $provincias,
        'nuevas'            => $nuevas,
        'modificadas'       => $modificadas,
        'ultimaVista'       => $ultimaVista,
        'totalActivas'      => $totalActivas,
        'totalDesactivadas' => $totalDesactivadas,
        'totalBases'        => $totalBases,
        'totalUses'         => $totalUses,
        'totalGeneral'      => $totalGeneral,
        'totalRedGlobal'    => $totalRedGlobal,
        'totalActivasGlobal'      => $totalActivasGlobal,
        'totalDesactivadasGlobal' => $totalDesactivadasGlobal,
    ]));
}
// ─── Marcar Visto ────────────────────────────────────────────────────
public function marcarVisto()
{
    helper('auth');
    $userId = user()->id;
    $ahora  = date('Y-m-d H:i:s');
    $db     = \Config\Database::connect();

    $existe = $db->table('user_last_visit')
                 ->where('user_id', $userId)
                 ->where('modulo', 'bases')
                 ->get()->getRow();

    if ($existe) {
        $db->table('user_last_visit')
           ->where('user_id', $userId)
           ->where('modulo', 'bases')
           ->update(['ultima_vista' => $ahora]);
    } else {
        $db->table('user_last_visit')
           ->insert([
               'user_id'      => $userId,
               'modulo'       => 'bases',
               'ultima_vista' => $ahora,
           ]);
    }

    return redirect()->to(route_to('base_list'));
}
// ─── Convertir POST a mayúsculas ─────────────────────────────────────
private function postToUpper(): array
{
    $camposMayus = ['tipo','nro','nombre','coordenadas','ubicacion','region'];
    $data = [];
    foreach ($camposMayus as $campo) {
        $valor = $this->request->getPost($campo);
        $data[$campo] = $valor !== null ? strtoupper(trim($valor)) : null;
    }

    // Estos van tal cual, sin toUpperCase
    $data['provincia'] = 'JUJUY'; // siempre fijo
    $data['estado']    = $this->request->getPost('estado') ?? 'activo';
    $data['base_id']   = $this->request->getPost('base_id');

    return $data;
}


    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
{
    $model = model('BaseModel');
    $this->aplicarFiltros($model);
    $bases = $model->orderBy('tipo', 'ASC')->orderBy('nombre', 'ASC')->findAll();

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();

    // Encabezados
    $headers = ['A'=>'Tipo','B'=>'Nro','C'=>'Nombre','D'=>'Coordenadas',
                'E'=>'Ubicación','F'=>'Región','G'=>'Provincia','H'=>'Estado'];

    foreach ($headers as $col => $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->getFont()->setBold(true);
    }

    // Datos
    foreach ($bases as $i => $b) {
        $row = $i + 2;
        $sheet->setCellValue('A' . $row, $b->tipo);
        $sheet->setCellValue('B' . $row, $b->nro);
        $sheet->setCellValue('C' . $row, $b->nombre);
        $sheet->setCellValue('D' . $row, $b->coordenadas);
        $sheet->setCellValue('E' . $row, $b->ubicacion);
        $sheet->setCellValue('F' . $row, $b->region);
        $sheet->setCellValue('G' . $row, $b->provincia);
        $sheet->setCellValue('H' . $row, $b->estado);
    }

    // Autoajuste columnas
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer   = new Xlsx($spreadsheet);
    $filename = 'bases_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
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
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $base = new Base($this->postToUpper());
        model('BaseModel')->save($base);

        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base guardada correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($base_id)
    {
        $model = model('BaseModel');
        if (!$base = $model->find($base_id)) {
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

        if (!$model->find($id)) {
            return redirect()->to(route_to('base_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Base no encontrada']);
        }

        if (!$this->valida(true)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $base = new Base($this->postToUpper());
        $model->save($base);

        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base actualizada correctamente']);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('BaseModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('base_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Base eliminada correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
 public function valida($isEditing = false)
{
    $rules = [
        'nombre'    => 'required|regex_match[/^[A-Z0-9\s\.\-\,]+$/]',
        'tipo'      => 'required',
        'ubicacion' => 'required|regex_match[/^[A-Z0-9\s\.\-\,]+$/]',
        'region'    => 'required',
        'provincia' => 'required',
    ];
    if (!$isEditing) {
        $rules['nombre'] = 'required|is_unique[base.nombre]|regex_match[/^[A-Z0-9\s\.\-\,]+$/]';
    }

    $messages = [
        'nombre'    => ['regex_match' => 'El nombre debe estar en MAYÚSCULAS.'],
        'ubicacion' => ['regex_match' => 'La ubicación debe estar en MAYÚSCULAS.'],
    ];

    return $this->validate($rules, $messages);
}
}