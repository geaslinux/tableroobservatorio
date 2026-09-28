<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SaludMentalCamasController extends BaseController
{
    private $tiposValidos     = ['PUBLICO', 'PRIVADO'];
    private $modalidadesValidas = ['RESIDENCIAL', 'INTERNACION'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_efector   = $this->request->getGet('efector_id') ?? '';
        $filtro_tipo      = $this->request->getGet('tipo')       ?? '';
        $filtro_modalidad = $this->request->getGet('modalidad')  ?? '';
        $filtro_region    = $this->request->getGet('region')     ?? '';

        if ($filtro_efector)   $model->where('salud_mental_camas.efector_id', $filtro_efector);
        if ($filtro_tipo)      $model->where('salud_mental_camas.tipo', $filtro_tipo);
        if ($filtro_modalidad) $model->where('salud_mental_camas.modalidad', $filtro_modalidad);
        if ($filtro_region)    $model->where('efector.region', $filtro_region);

        return compact('filtro_efector', 'filtro_tipo', 'filtro_modalidad', 'filtro_region');
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('SaludMentalCamasModel');

        $efectores  = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones   = ['CENTRO', 'VALLE', 'RAMAL I', 'RAMAL II', 'QUEBRADA', 'PUNA'];

        // ── KPI CARDS ──
        $totalCbAdultos     = (int) ($model->selectSum('cb_adultos')->first()->cb_adultos ?? 0);
        $totalCbPediatricos = (int) ($model->selectSum('cb_pediatricos')->first()->cb_pediatricos ?? 0);
        $totalBasicas       = (int) ($model->selectSum('total_basicas')->first()->total_basicas ?? 0);

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

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'salud_mental_camas')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('salud_mental_camas/salud_mental_camas_list', array_merge($filtros, [
            'registros'          => $registros,
            'pager'              => $pager,
            'efectores'          => $efectores,
            'regiones'           => $regiones,
            'tipos'              => $this->tiposValidos,
            'modalidades'        => $this->modalidadesValidas,
            'totalCbAdultos'     => $totalCbAdultos,
            'totalCbPediatricos' => $totalCbPediatricos,
            'totalBasicas'       => $totalBasicas,
            'ultimaVista'        => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'salud_mental_camas')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'salud_mental_camas')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'salud_mental_camas', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('salud_mental_camas_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('salud_mental_camas/salud_mental_camas_form', [
            'efectores'   => $efectores,
            'tipos'       => $this->tiposValidos,
            'modalidades' => $this->modalidadesValidas,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        model('SaludMentalCamasModel')->insert($this->datosDelPost());

        return redirect()->to(route_to('salud_mental_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente.']);
    }

    // ─── Show/Edit ────────────────────────────────────────────────────────
    public function show($id)
    {
        $model            = model('SaludMentalCamasModel');
        $saludMentalCamas = $model->find($id);

        if (!$saludMentalCamas) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $saludMentalCamas->setCampoOculto();

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('salud_mental_camas/salud_mental_camas_form', [
            'saludMentalCamas' => $saludMentalCamas,
            'efectores'        => $efectores,
            'tipos'            => $this->tiposValidos,
            'modalidades'      => $this->modalidadesValidas,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update()
    {
        $id = $this->request->getPost('salud_mental_camas_id');

        if (!$id) {
            return redirect()->to(route_to('salud_mental_camas_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('SaludMentalCamasModel')->update($id, $this->datosDelPost());

        return redirect()->to(route_to('salud_mental_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente.']);
    }

    // ─── Destroy ──────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id'); // seguimos usando 'id' porque así está la ruta

        if (!$id) {
            return redirect()->to(route_to('salud_mental_camas_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('SaludMentalCamasModel')->delete($id);

        return redirect()->to(route_to('salud_mental_camas_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Helper: arma el array de datos del form (create/update) ──────────
    private function datosDelPost(): array
    {
        return [
            'efector_id'     => $this->request->getPost('efector_id'),
            'modalidad'      => $this->request->getPost('modalidad'),
            'tipo'           => $this->request->getPost('tipo'),
            'cb_adultos'     => intval($this->request->getPost('cb_adultos')),
            'cb_pediatricos' => intval($this->request->getPost('cb_pediatricos')),
            'total_basicas'  => intval($this->request->getPost('total_basicas')),
        ];
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('SaludMentalCamasModel')->conEfector();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('efector.nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Efector',
            'B' => 'Modalidad',
            'C' => 'Tipo',
            'D' => 'Cama Básica Adultos',
            'E' => 'Cama Básica Pediátricos',
            'F' => 'Total Básicas',
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
            $sheet->setCellValue('B' . $row, $r->modalidad);
            $sheet->setCellValue('C' . $row, $r->tipo);
            $sheet->setCellValue('D' . $row, $r->cb_adultos);
            $sheet->setCellValue('E' . $row, $r->cb_pediatricos);
            $sheet->setCellValue('F' . $row, $r->total_basicas);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'salud_mental_camas_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}