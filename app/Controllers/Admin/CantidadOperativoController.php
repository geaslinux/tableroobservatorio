<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use App\Services\GoogleDriveService;
use PhpOffice\PhpSpreadsheet\IOFactory;


class CantidadOperativoController extends BaseController
{
    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio') ?? '';
        $filtro_operativo = $this->request->getGet('operativo') ?? '';
        $filtro_estado    = $this->request->getGet('estado')    ?? '';

        if ($filtro_ejercicio) $model->where('cantidad_operativo.ejercicio',    $filtro_ejercicio);
        if ($filtro_operativo) $model->where('cantidad_operativo.operativo_id', $filtro_operativo);

        if ($filtro_estado != '') {
            $model->where('cantidad_operativo.estado', $filtro_estado);
        } else {
            $model->whereIn('cantidad_operativo.estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_operativo', 'filtro_estado');
    }

    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $ejercicios = array_column(
            model('CantidadOperativoModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $operativos = model('OperativoModel')->orderBy('nombre', 'ASC')->findAll();

        // ── Auditoría PRIMERO ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'cantidad_operativo')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('CantidadOperativoModel')
            ->whereIn('cantidad_operativo.estado', ['activo', 'desactivado'])
            ->where('cantidad_operativo.created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('CantidadOperativoModel')
            ->whereIn('cantidad_operativo.estado', ['activo', 'desactivado'])
            ->where('cantidad_operativo.updated_at >', $ultimaVista)
            ->where('cantidad_operativo.updated_at != cantidad_operativo.created_at')
            ->countAllResults();

        // ── Filtros DESPUÉS de conteos ────────────────────────────────────
        $model   = model('CantidadOperativoModel')->conRelaciones();
        $filtros = $this->aplicarFiltros($model);

        return view('cantidad_operativo/cantidad_operativo_list', array_merge($filtros, [
            'registros'   => $model->orderBy('cantidad_operativo.ejercicio', 'DESC')
                                   ->orderBy('operativo.nombre', 'ASC')
                                   ->paginate($perPage),
            'pager'       => $model->pager,
            'ejercicios'  => $ejercicios,
            'operativos'  => $operativos,
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
                                 ->where('modulo', 'cantidad_operativo')
                                 ->first();

        if ($registro) {
            $visitModel->where('user_id', $userId)
                       ->where('modulo', 'cantidad_operativo')
                       ->set(['ultima_vista' => $ahora])
                       ->update();
        } else {
            $visitModel->insert([
                'user_id'      => $userId,
                'modulo'       => 'cantidad_operativo',
                'ultima_vista' => $ahora,
            ]);
        }

        return redirect()->to(route_to('cantidad_operativo_list'));
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');

        $operativos = model('OperativoModel')
            ->select('operativo.operativo_id, operativo.nombre, tipo_operativo.nombre AS tipo_nombre')
            ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left')
            ->orderBy('operativo.nombre', 'ASC')
            ->findAll();

        return view('cantidad_operativo/cantidad_operativo_form', [
            'operativos' => $operativos,
        ]);
    }
public function store()
{
    if (!$this->valida()) {
        return redirect()->back()->withInput()
            ->with('errors', $this->validator->getErrors())
            ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
    }

    $via_publica      = (int) $this->request->getPost('via_publica')      ?: 0;
    $via_hospitalaria = (int) $this->request->getPost('via_hospitalaria') ?: 0;
    $total            = $via_publica + $via_hospitalaria;
    $ejercicio        = $this->request->getPost('ejercicio');
    $operativo_id     = $this->request->getPost('operativo_id');

    // ── 1. Guardar en base de datos ───────────────────────────────────
    model('CantidadOperativoModel')->insert([
        'ejercicio'        => $ejercicio,
        'operativo_id'     => $operativo_id,
        'via_publica'      => $via_publica,
        'via_hospitalaria' => $via_hospitalaria,
        'total'            => $total,
        'estado'           => 'activo',
    ]);

    // ── 2. Buscar el operativo para tener nombre y tipo ───────────────
    $operativo = model('OperativoModel')
        ->select('operativo.nombre, tipo_operativo.nombre AS tipo_nombre')
        ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left')
        ->find($operativo_id);

    // ── 3. Sincronizar con Drive ──────────────────────────────────────
    try {
        $drive       = new GoogleDriveService();
        $mimeType    = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $nombreDrive = 'cantidad_operativos.xlsx';
        $rutaTemp    = WRITEPATH . 'uploads/' . $nombreDrive;

        $fileId = $drive->buscarArchivo($nombreDrive);

        if ($fileId) {
            // ── Archivo existe: descargar y agregar fila ──────────────
            $drive->descargarArchivo($fileId, $rutaTemp);
            $spreadsheet = IOFactory::load($rutaTemp);
            $sheet       = $spreadsheet->getActiveSheet();
            $nuevaFila   = $sheet->getHighestRow() + 1;
        } else {
            // ── No existe: crear con cabecera ─────────────────────────
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Cantidad Operativos');

            $headers = [
                'A' => 'Ejercicio',
                'B' => 'Operativo',
                'C' => 'Tipo',
                'D' => 'Vía Pública',
                'E' => 'Vía Hospitalaria',
                'F' => 'Total',
            ];

            foreach ($headers as $col => $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getStyle($col . '1')->getFont()->setBold(true);
                $sheet->getStyle($col . '1')->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setRGB('13304d');
                $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
            }

            $nuevaFila = 2;
        }

        // ── Escribir la nueva fila ────────────────────────────────────
        $sheet->setCellValue('A' . $nuevaFila, $ejercicio);
        $sheet->setCellValue('B' . $nuevaFila, $operativo->nombre      ?? '');
        $sheet->setCellValue('C' . $nuevaFila, $operativo->tipo_nombre ?? '');
        $sheet->setCellValue('D' . $nuevaFila, $via_publica);
        $sheet->setCellValue('E' . $nuevaFila, $via_hospitalaria);
        $sheet->setCellValue('F' . $nuevaFila, $total);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Guardar Excel temporal ────────────────────────────────────
        (new Xlsx($spreadsheet))->save($rutaTemp);

        // ── Subir o actualizar en Drive ───────────────────────────────
        if ($fileId) {
            $drive->actualizarArchivo($fileId, $rutaTemp, $mimeType);
        } else {
            $drive->subirArchivo($rutaTemp, $nombreDrive, $mimeType);
        }

        // ── Limpiar temporal ──────────────────────────────────────────
        if (file_exists($rutaTemp)) {
            unlink($rutaTemp);
        }

    } catch (\Exception $e) {
        log_message('error', 'Error Drive store: ' . $e->getMessage());
        // No interrumpe al usuario si Drive falla
    }

    return redirect()->to(route_to('cantidad_operativo_list'))
        ->with('msg', ['type' => 'success', 'body' => 'Registro guardado correctamente']);
}

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $registro = model('CantidadOperativoModel')->conRelaciones()->find($id);

        if (!$registro) {
            return redirect()->to(route_to('cantidad_operativo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $operativos = model('OperativoModel')
            ->select('operativo.operativo_id, operativo.nombre, tipo_operativo.nombre AS tipo_nombre')
            ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left')
            ->orderBy('operativo.nombre', 'ASC')
            ->findAll();

        $registro->setCampoOculto();

        return view('cantidad_operativo/cantidad_operativo_form', [
            'registro'   => $registro,
            'operativos' => $operativos,
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

        $id               = $this->request->getPost('cantidad_id');
        $via_publica      = (int) $this->request->getPost('via_publica')      ?: 0;
        $via_hospitalaria = (int) $this->request->getPost('via_hospitalaria') ?: 0;
        $total            = $via_publica + $via_hospitalaria;

        model('CantidadOperativoModel')->update($id, [
            'ejercicio'        => $this->request->getPost('ejercicio'),
            'operativo_id'     => $this->request->getPost('operativo_id'),
            'via_publica'      => $via_publica,
            'via_hospitalaria' => $via_hospitalaria,
            'total'            => $total,
            'estado'           => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('cantidad_operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro actualizado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual = (int) date('Y');

        return $this->validate([
            'ejercicio'    => "required|integer|less_than_equal_to[{$anioActual}]",
            'operativo_id' => 'required|integer',
        ], [
            'ejercicio' => [
                'less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}.",
            ],
            'operativo_id' => [
                'required' => 'Debe seleccionar un operativo.',
            ],
        ]);
    }

    // ─── Exportar Excel ──────────────────────────────────────────────────
    public function export()
    {
        $model = model('CantidadOperativoModel')->conRelaciones();
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('cantidad_operativo.ejercicio', 'DESC')
                           ->orderBy('operativo.nombre', 'ASC')
                           ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cantidad Operativos');

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Operativo',
            'C' => 'Tipo',
            'D' => 'Vía Pública',
            'E' => 'Vía Hospitalaria',
            'F' => 'Total',
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
            $sheet->setCellValue('B' . $row, $r->operativo_nombre);
            $sheet->setCellValue('C' . $row, $r->tipo_nombre ?? '');
            $sheet->setCellValue('D' . $row, $r->via_publica);
            $sheet->setCellValue('E' . $row, $r->via_hospitalaria);
            $sheet->setCellValue('F' . $row, $r->total);
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'cantidad_operativos_' . date('Ymd_His') . '.xlsx';

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
        $model = model('CantidadOperativoModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('cantidad_operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}