<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CapacidadCamasController extends BaseController
{
    private $tiposValidos = ['PUBLICO', 'PRIVADO'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector = $this->request->getGet('efector_id') ?? '';
        $filtro_tipo    = $this->request->getGet('tipo')       ?? '';
        $filtro_region  = $this->request->getGet('region')     ?? '';

        if ($filtro_efector) $model->where('capacidad_camas.efector_id', $filtro_efector);
        if ($filtro_tipo)    $model->where('capacidad_camas.tipo', $filtro_tipo);
        if ($filtro_region)  $model->where('efector.region', $filtro_region);

        return compact('filtro_efector', 'filtro_tipo', 'filtro_region');
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('CapacidadCamasModel');

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones  = ['CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA'];

        // ── KPI CARDS ──
        $totalCamasDisponibles = (int) ($model->selectSum('camas_disponibles')->first()->camas_disponibles ?? 0);
        $totalUti              = (int) ($model->selectSum('total_uti')->first()->total_uti ?? 0);
        $totalUtin             = (int) ($model->selectSum('total_utin')->first()->total_utin ?? 0);
        $totalBasicas          = (int) ($model->selectSum('total_basicas')->first()->total_basicas ?? 0);

        // ── Filtros + listado (paginado estándar) ──────────────────────────
        $modelFiltrado = $model->conEfector();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        $registros = $modelFiltrado
            ->orderBy('efector.nombre', 'ASC')
            ->paginate($perPage);

        $pager = $model->pager;

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'capacidad_camas')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('capacidad_camas/capacidad_camas_list', array_merge($filtros, [
            'registros'             => $registros,
            'pager'                 => $pager,
            'efectores'             => $efectores,
            'regiones'              => $regiones,
            'tipos'                 => $this->tiposValidos,
            'totalCamasDisponibles' => $totalCamasDisponibles,
            'totalUti'              => $totalUti,
            'totalUtin'             => $totalUtin,
            'totalBasicas'          => $totalBasicas,
            'ultimaVista'           => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'capacidad_camas')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'capacidad_camas')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'capacidad_camas', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('capacidad_camas_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('capacidad_camas/capacidad_camas_form', [
            'efectores' => $efectores,
            'tipos'     => $this->tiposValidos,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        model('CapacidadCamasModel')->insert($this->datosDelPost());

        return redirect()->to(route_to('capacidad_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente.']);
    }

    // ─── Show/Edit ────────────────────────────────────────────────────────
    public function show($id)
    {
        $model          = model('CapacidadCamasModel');
        $capacidadCamas = $model->find($id);

        if (!$capacidadCamas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $capacidadCamas->setCampoOculto();

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('capacidad_camas/capacidad_camas_form', [
            'capacidadCamas' => $capacidadCamas,
            'efectores'      => $efectores,
            'tipos'          => $this->tiposValidos,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update()
    {
        $id = $this->request->getPost('capacidad_camas_id');

        if (!$id) {
            return redirect()->to(route_to('capacidad_camas_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('CapacidadCamasModel')->update($id, $this->datosDelPost());

        return redirect()->to(route_to('capacidad_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente.']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id'); // seguimos usando 'id' porque así está la ruta

        if (!$id) {
            return redirect()->to(route_to('capacidad_camas_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('CapacidadCamasModel')->delete($id);

        return redirect()->to(route_to('capacidad_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Helper: arma el array de datos del form (create/update) ──────────
    private function datosDelPost(): array
    {
        return [
            'efector_id'                              => $this->request->getPost('efector_id'),
            'tipo'                                     => $this->request->getPost('tipo'),
            'uti_adulto'                               => intval($this->request->getPost('uti_adulto')),
            'uti_coronario'                            => intval($this->request->getPost('uti_coronario')),
            'uti_pediatrico'                           => intval($this->request->getPost('uti_pediatrico')),
            'uti_neonatal'                             => intval($this->request->getPost('uti_neonatal')),
            'total_uti'                                => intval($this->request->getPost('total_uti')),
            'total_uti_publicos'                       => intval($this->request->getPost('total_uti_publicos')),
            'utin_adulto'                              => intval($this->request->getPost('utin_adulto')),
            'utin_pediatrico'                          => intval($this->request->getPost('utin_pediatrico')),
            'utin_neonatal'                             => intval($this->request->getPost('utin_neonatal')),
            'total_utin'                               => intval($this->request->getPost('total_utin')),
            'total_utin_publicos'                      => intval($this->request->getPost('total_utin_publicos')),
            'cb_adultos'                               => intval($this->request->getPost('cb_adultos')),
            'cb_pediatricos'                           => intval($this->request->getPost('cb_pediatricos')),
            'cb_neonatales'                            => intval($this->request->getPost('cb_neonatales')),
            'total_basicas'                            => intval($this->request->getPost('total_basicas')),
            'total_basicas_publicos'                   => intval($this->request->getPost('total_basicas_publicos')),
            'camas_disponibles'                        => intval($this->request->getPost('camas_disponibles')),
            'camas_disponibles_publicas'                => intval($this->request->getPost('camas_disponibles_publicas')),
            'camas_disponibles_publicas_salud_mental'  => intval($this->request->getPost('camas_disponibles_publicas_salud_mental')),
        ];
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('CapacidadCamasModel')->conEfector();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Efector',            'B' => 'Tipo',
            'C' => 'UTI Adulto',         'D' => 'UTI Coronario',
            'E' => 'UTI Pediátrico',     'F' => 'UTI Neonatal',
            'G' => 'Total UTI',          'H' => 'Total UTI Públicos',
            'I' => 'UTIN Adulto',        'J' => 'UTIN Pediátrico',
            'K' => 'UTIN Neonatal',      'L' => 'Total UTIN',
            'M' => 'Total UTIN Públicos','N' => 'Cama Básica Adultos',
            'O' => 'Cama Básica Pediátricos', 'P' => 'Cama Básica Neonatales',
            'Q' => 'Total Básicas',      'R' => 'Total Básicas Públicos',
            'S' => 'Camas Disponibles',  'T' => 'Camas Disponibles Públicas',
            'U' => 'Camas Disp. Públicas Salud Mental',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($registros as $i => $r) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $r->nombre);
            $sheet->setCellValue('B' . $row, $r->tipo);
            $sheet->setCellValue('C' . $row, $r->uti_adulto);
            $sheet->setCellValue('D' . $row, $r->uti_coronario);
            $sheet->setCellValue('E' . $row, $r->uti_pediatrico);
            $sheet->setCellValue('F' . $row, $r->uti_neonatal);
            $sheet->setCellValue('G' . $row, $r->total_uti);
            $sheet->setCellValue('H' . $row, $r->total_uti_publicos);
            $sheet->setCellValue('I' . $row, $r->utin_adulto);
            $sheet->setCellValue('J' . $row, $r->utin_pediatrico);
            $sheet->setCellValue('K' . $row, $r->utin_neonatal);
            $sheet->setCellValue('L' . $row, $r->total_utin);
            $sheet->setCellValue('M' . $row, $r->total_utin_publicos);
            $sheet->setCellValue('N' . $row, $r->cb_adultos);
            $sheet->setCellValue('O' . $row, $r->cb_pediatricos);
            $sheet->setCellValue('P' . $row, $r->cb_neonatales);
            $sheet->setCellValue('Q' . $row, $r->total_basicas);
            $sheet->setCellValue('R' . $row, $r->total_basicas_publicos);
            $sheet->setCellValue('S' . $row, $r->camas_disponibles);
            $sheet->setCellValue('T' . $row, $r->camas_disponibles_publicas);
            $sheet->setCellValue('U' . $row, $r->camas_disponibles_publicas_salud_mental);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'capacidad_camas_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}