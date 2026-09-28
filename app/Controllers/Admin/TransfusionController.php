<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransfusionController extends BaseController
{
    private $meses = [
        'enero','febrero','marzo','abril','mayo','junio',
        'julio','agosto','septiembre','octubre','noviembre','diciembre'
    ];

    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_efector   = $this->request->getGet('efector')   ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        if ($filtro_ejercicio) $model->where('transfusion.ejercicio',  $filtro_ejercicio);
        if ($filtro_efector)   $model->where('transfusion.efector_id', $filtro_efector);
        if ($filtro_region)    $model->where('efector.region',         $filtro_region);

        if ($filtro_estado != '') {
            $model->where('transfusion.estado', $filtro_estado);
        } else {
            $model->whereIn('transfusion.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_efector', 'filtro_region', 'filtro_estado');
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $ejercicios = array_column(
            model('TransfusionModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $regiones  = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        // ── Auditoría PRIMERO ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'transfusion')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('TransfusionModel')
            ->whereIn('transfusion.estado', ['activo', 'desactivado'])
            ->where('transfusion.created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('TransfusionModel')
            ->whereIn('transfusion.estado', ['activo', 'desactivado'])
            ->where('transfusion.updated_at >', $ultimaVista)
            ->where('transfusion.updated_at != transfusion.created_at')
            ->countAllResults();

        // ── Filtros DESPUÉS de conteos ────────────────────────────────────
        $model   = model('TransfusionModel')->conRelaciones();
        $filtros = $this->aplicarFiltros($model);

        return view('transfusion/transfusion_list', array_merge($filtros, [
            'registros'   => $model->orderBy('transfusion.ejercicio', 'DESC')
                                   ->orderBy('efector.nombre', 'ASC')
                                   ->paginate($perPage),
            'pager'       => $model->pager,
            'ejercicios'  => $ejercicios,
            'efectores'   => $efectores,
            'regiones'    => $regiones,
            'nuevas'      => $nuevas,
            'modificadas' => $modificadas,
            'ultimaVista' => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user()->id;
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $registro   = $visitModel->where('user_id', $userId)
                                 ->where('modulo', 'transfusion')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'transfusion')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'transfusion',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('transfusion_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('transfusion/transfusion_form', [
            'efectores' => $efectores,
            'meses'     => $this->meses,
        ]);
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $existe = model('TransfusionModel')
            ->where('ejercicio',  $this->request->getPost('ejercicio'))
            ->where('efector_id', $this->request->getPost('efector_id'))
            ->whereIn('estado',   ['activo', 'desactivado'])
            ->first();

        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['efector_id' => 'Ya existe un registro para ese ejercicio y efector.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro para ese ejercicio y efector.']);
        }

        $data = [
            'ejercicio'  => $this->request->getPost('ejercicio'),
            'efector_id' => $this->request->getPost('efector_id'),
            'estado'     => 'activo',
        ];

        $total = 0;
        foreach ($this->meses as $mes) {
            $val        = (int) $this->request->getPost($mes);
            $data[$mes] = $val;
            $total     += $val;
        }
        $data['total'] = $total;

        model('TransfusionModel')->insert($data);

        return redirect()->to(route_to('transfusion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('TransfusionModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('transfusion_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        $registro->setCampoOculto();

        return view('transfusion/transfusion_form', [
            'registro'  => $registro,
            'efectores' => $efectores,
            'meses'     => $this->meses,
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id = $this->request->getPost('transfusion_id');

        $data = [
            'ejercicio'  => $this->request->getPost('ejercicio'),
            'efector_id' => $this->request->getPost('efector_id'),
            'estado'     => $this->request->getPost('estado') ?? 'activo',
        ];

        $total = 0;
        foreach ($this->meses as $mes) {
            $val        = (int) $this->request->getPost($mes);
            $data[$mes] = $val;
            $total     += $val;
        }
        $data['total'] = $total;

        model('TransfusionModel')->update($id, $data);

        return redirect()->to(route_to('transfusion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual = (int) date('Y');

        return $this->validate([
            'ejercicio'  => "required|integer|less_than_equal_to[{$anioActual}]",
            'efector_id' => 'required|integer',
        ], [
            'ejercicio' => [
                'less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}.",
            ],
            'efector_id' => [
                'required' => 'Debe seleccionar un efector.',
            ],
        ]);
    }

    // ─── Resumen ─────────────────────────────────────────────────────────
    public function resumen()
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';

        $ejercicios = array_column(
            model('TransfusionModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $regiones = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        $model = model('TransfusionModel')->paraResumen();

        if ($filtro_ejercicio) $model->where('transfusion.ejercicio', $filtro_ejercicio);
        if ($filtro_region)    $model->where('efector.region',        $filtro_region);

        $registros = $model->orderBy('efector.region', 'ASC')
                           ->orderBy('efector.nombre', 'ASC')
                           ->findAll();

        return view('transfusion/transfusion_resumen', [
            'registros'        => $registros,
            'ejercicios'       => $ejercicios,
            'regiones'         => $regiones,
            'filtro_ejercicio' => $filtro_ejercicio,
            'filtro_region'    => $filtro_region,
        ]);
    }

    // ─── Exportar resumen Excel ───────────────────────────────────────────
    public function exportResumen()
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_region    = $this->request->getGet('region')    ?? '';

        $model = model('TransfusionModel')->paraResumen();

        if ($filtro_ejercicio) $model->where('transfusion.ejercicio', $filtro_ejercicio);
        if ($filtro_region)    $model->where('efector.region',        $filtro_region);

        $registros = $model->orderBy('efector.region', 'ASC')
                           ->orderBy('efector.nombre', 'ASC')
                           ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen Transfusiones');

        $headers = ['A'=>'Ejercicio','B'=>'Hospital / Efector','C'=>'Nivel Complejidad','D'=>'Departamento','E'=>'Ubicación','F'=>'Región','G'=>'Total'];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $row          = 2;
        $regionActual = null;
        $subtotal     = 0;
        $granTotal    = 0;

        foreach ($registros as $r) {
            if ($r->efector_region !== $regionActual) {
                if ($regionActual !== null) {
                    $sheet->setCellValue('F' . $row, 'Subtotal ' . $regionActual);
                    $sheet->setCellValue('G' . $row, $subtotal);
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                          ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d0e4ff');
                    $row++;
                }
                $regionActual = $r->efector_region;
                $subtotal     = 0;
            }

            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->efector_nombre);
            $sheet->setCellValue('C' . $row, $r->efector_nivel ?? '');
            $sheet->setCellValue('D' . $row, $r->efector_depto ?? '');
            $sheet->setCellValue('E' . $row, $r->efector_ubicacion ?? '');
            $sheet->setCellValue('F' . $row, $r->efector_region ?? '');
            $sheet->setCellValue('G' . $row, $r->total);
            $subtotal  += $r->total;
            $granTotal += $r->total;
            $row++;
        }

        if ($regionActual !== null) {
            $sheet->setCellValue('F' . $row, 'Subtotal ' . $regionActual);
            $sheet->setCellValue('G' . $row, $subtotal);
            $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
                  ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('d0e4ff');
            $row++;
        }

        $sheet->setCellValue('F' . $row, 'TOTAL GENERAL');
        $sheet->setCellValue('G' . $row, $granTotal);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()
              ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('13304d');
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->getColor()->setRGB('FFFFFF');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'resumen_transfusiones_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Exportar Excel completo ──────────────────────────────────────────
    public function export()
    {
        $model = model('TransfusionModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('transfusion.ejercicio', 'DESC')
                           ->orderBy('efector.nombre', 'ASC')
                           ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Transfusiones');

        $headers = [
            'A'=>'Ejercicio','B'=>'Efector','C'=>'Nivel','D'=>'Region',
            'E'=>'Enero','F'=>'Febrero','G'=>'Marzo','H'=>'Abril',
            'I'=>'Mayo','J'=>'Junio','K'=>'Julio','L'=>'Agosto',
            'M'=>'Septiembre','N'=>'Octubre','O'=>'Noviembre','P'=>'Diciembre',
            'Q'=>'Total',
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
            $sheet->setCellValue('A' . $row, $r->ejercicio);
            $sheet->setCellValue('B' . $row, $r->efector_nombre);
            $sheet->setCellValue('C' . $row, $r->efector_nivel);
            $sheet->setCellValue('D' . $row, $r->efector_region);
            $sheet->setCellValue('E' . $row, $r->enero);
            $sheet->setCellValue('F' . $row, $r->febrero);
            $sheet->setCellValue('G' . $row, $r->marzo);
            $sheet->setCellValue('H' . $row, $r->abril);
            $sheet->setCellValue('I' . $row, $r->mayo);
            $sheet->setCellValue('J' . $row, $r->junio);
            $sheet->setCellValue('K' . $row, $r->julio);
            $sheet->setCellValue('L' . $row, $r->agosto);
            $sheet->setCellValue('M' . $row, $r->septiembre);
            $sheet->setCellValue('N' . $row, $r->octubre);
            $sheet->setCellValue('O' . $row, $r->noviembre);
            $sheet->setCellValue('P' . $row, $r->diciembre);
            $sheet->setCellValue('Q' . $row, $r->total);
        }

        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'transfusiones_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id    = $this->request->getVar('id');
        $model = model('TransfusionModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('transfusion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}