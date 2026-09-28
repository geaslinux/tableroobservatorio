<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProduccionQuirofanoController extends BaseController
{
    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_ejercicio = $this->request->getGet('ejercicio')  ?? '';
        $filtro_efector   = $this->request->getGet('efector_id') ?? '';
        $filtro_region    = $this->request->getGet('region')     ?? '';

        if ($filtro_ejercicio) $model->where('produccion_quirofano.ejercicio', $filtro_ejercicio);
        if ($filtro_efector)   $model->where('produccion_quirofano.efector_id', $filtro_efector);
        if ($filtro_region)    $model->where('efector.region', $filtro_region);

        return compact('filtro_ejercicio', 'filtro_efector', 'filtro_region');
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('ProduccionQuirofanoModel');

        // ── Listas para selects ─────────────────────────
        $ejercicios = array_column(
            $model->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        $regiones = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        // ── KPI CARDS ──
        $totalGeneral = (int) ($model->selectSum('produccion')->first()->produccion ?? 0);

        // Datos de los gráficos: respetan los filtros (instancia propia del modelo;
        // el join con efector es para el filtro de región)
        $modelGrafico = model('ProduccionQuirofanoModel', false)
            ->join('efector', 'efector.efector_id = produccion_quirofano.efector_id', 'left');
        $this->aplicarFiltros($modelGrafico);
        $totalPorEjercicio = $modelGrafico
            ->select('produccion_quirofano.ejercicio, SUM(produccion_quirofano.produccion) as total')
            ->groupBy('produccion_quirofano.ejercicio')
            ->orderBy('produccion_quirofano.ejercicio', 'DESC')
            ->asArray()
            ->findAll();

        // ── Filtros + listado con nombres ───────────────────────────────
        $modelFiltrado = $model->withNombres();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        $registros = $modelFiltrado
            ->orderBy('produccion_quirofano.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->paginate($perPage);

        $pager = $model->pager;

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'produccion_quirofano')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('produccion_quirofano/produccion_quirofano_list', array_merge($filtros, [
            'registros'         => $registros,
            'pager'             => $pager,
            'ejercicios'        => $ejercicios,
            'efectores'         => $efectores,
            'regiones'          => $regiones,
            'totalGeneral'      => $totalGeneral,
            'totalPorEjercicio' => $totalPorEjercicio,
            'ultimaVista'       => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'produccion_quirofano')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'produccion_quirofano')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'produccion_quirofano', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('produccion_quirofano_list'));
    }

    // ─── Create (alta masiva: un ejercicio, varios efectores) ──────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('produccion_quirofano/produccion_quirofano_form', [
            'efectores' => $efectores,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $model = model('ProduccionQuirofanoModel');

        $ejercicio  = $this->request->getPost('ejercicio');
        $efectorIds = $this->request->getPost('efector_id');
        $producciones = $this->request->getPost('produccion');

        $insertados = 0;
        $omitidos   = 0;

        foreach ($efectorIds as $i => $efectorId) {
            $produccion = intval($producciones[$i] ?? 0);

            if (empty($efectorId)) continue;

            $existe = $model
                ->where('efector_id', $efectorId)
                ->where('ejercicio', $ejercicio)
                ->first();

            if ($existe) {
                $omitidos++;
                continue;
            }

            $model->insert([
                'efector_id' => $efectorId,
                'ejercicio'  => $ejercicio,
                'produccion' => $produccion,
            ]);

            $insertados++;
        }

        $msg = "$insertados registros guardados.";
        if ($omitidos) $msg .= " $omitidos omitidos por ser duplicados (efector+ejercicio ya cargado).";

        return redirect()->to(route_to('produccion_quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => $msg]);
    }

    // ─── Edit (registro individual por PK) ─────────────────────────────────
    public function edit($id)
    {
        $model    = model('ProduccionQuirofanoModel');
        $registro = $model->find($id);

        if (!$registro) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();

        return view('produccion_quirofano/produccion_quirofano_form', [
            'registro'  => $registro,
            'efectores' => $efectores,
        ]);
    }

    // ─── Update ───────────────────────────────────────────────────────────
    public function update($id)
    {
        $model    = model('ProduccionQuirofanoModel');
        $registro = $model->find($id);

        if (!$registro) {
            return redirect()->to(route_to('produccion_quirofano_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $efectorId  = $this->request->getPost('efector_id');
        $ejercicio  = $this->request->getPost('ejercicio');
        $produccion = intval($this->request->getPost('produccion'));

        // chequeo de duplicado contra OTRO registro (no contra sí mismo)
        $duplicado = $model
            ->where('efector_id', $efectorId)
            ->where('ejercicio', $ejercicio)
            ->where('produccion_quirofano_id !=', $id)
            ->first();

        if ($duplicado) {
            return redirect()->back()->withInput()
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe producción cargada para ese efector en ese ejercicio.']);
        }

        $model->update($id, [
            'efector_id' => $efectorId,
            'ejercicio'  => $ejercicio,
            'produccion' => $produccion,
        ]);

        return redirect()->to(route_to('produccion_quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Producción actualizada correctamente']);
    }

    // ─── Destroy ────────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id');

        if (!$id) {
            return redirect()->to(route_to('produccion_quirofano_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        model('ProduccionQuirofanoModel')->delete($id);

        return redirect()->to(route_to('produccion_quirofano_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('ProduccionQuirofanoModel')->withNombres();
        $this->aplicarFiltros($model);

        $registros = $model
            ->orderBy('produccion_quirofano.ejercicio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Hospital',
            'C' => 'Nivel de Complejidad',
            'D' => 'Departamento',
            'E' => 'Ubicación',
            'F' => 'Región',
            'G' => 'Producción Quirúrgica',
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
            $sheet->setCellValue('C' . $row, $r->nivel_complejidad);
            $sheet->setCellValue('D' . $row, $r->departamento);
            $sheet->setCellValue('E' . $row, $r->ubicacion);
            $sheet->setCellValue('F' . $row, $r->region);
            $sheet->setCellValue('G' . $row, $r->produccion);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'produccion_quirofano_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}