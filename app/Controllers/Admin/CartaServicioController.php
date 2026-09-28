<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\CartaServicio;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CartaServicioController extends BaseController
{
    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_hospital     = $this->request->getGet('hospital')          ?? '';
        $filtro_region       = $this->request->getGet('region')            ?? '';
        $filtro_nivel        = $this->request->getGet('nivel_complejidad') ?? '';
        $filtro_especialidad = $this->request->getGet('especialidad')      ?? '';

        if ($filtro_hospital)     $model->where('hospital', $filtro_hospital);
        if ($filtro_region)       $model->where('region', $filtro_region);
        if ($filtro_nivel)        $model->where('nivel_complejidad', $filtro_nivel);
        if ($filtro_especialidad) $model->where('especialidad', $filtro_especialidad);

        return compact('filtro_hospital', 'filtro_region', 'filtro_nivel', 'filtro_especialidad');
    }

    // ─── Listado ───────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $hospitales     = array_column((new \App\Models\CartaServicioModel())->select('hospital')->distinct()->orderBy('hospital', 'ASC')->findAll(), 'hospital');
        $regiones       = array_column((new \App\Models\CartaServicioModel())->select('region')->distinct()->orderBy('region', 'ASC')->findAll(), 'region');
        $niveles        = array_column((new \App\Models\CartaServicioModel())->select('nivel_complejidad')->distinct()->orderBy('nivel_complejidad', 'ASC')->findAll(), 'nivel_complejidad');
        $especialidades = array_column((new \App\Models\CartaServicioModel())->select('especialidad')->distinct()->orderBy('especialidad', 'ASC')->findAll(), 'especialidad');

        // ── KPI CARDS (instancias frescas para evitar only_full_group_by) ──
        $totalRegistros  = (new \App\Models\CartaServicioModel())->countAllResults();
        $totalHospitales = count(array_filter($hospitales));
        $totalTurnos     = (int) ((new \App\Models\CartaServicioModel())->selectSum('cant_turnos')->first()->cant_turnos ?? 0);

        $model   = model('CartaServicioModel');
        $filtros = $this->aplicarFiltros($model);

        return view('carta_servicio/carta_servicio_list', array_merge($filtros, [
            'registros'       => $model->orderBy('hospital', 'ASC')->orderBy('profesional', 'ASC')->paginate($perPage),
            'pager'           => $model->pager,
            'hospitales'      => $hospitales,
            'regiones'        => $regiones,
            'niveles'         => $niveles,
            'especialidades'  => $especialidades,
            'totalRegistros'  => $totalRegistros,
            'totalHospitales' => $totalHospitales,
            'totalTurnos'     => $totalTurnos,
        ]));
    }

    // ─── Vista importar ──────────────────────────────────────────────────
    public function import()
    {
        return view('carta_servicio/carta_servicio_import');
    }

    // ─── Procesar importación Excel (reemplaza el listado completo) ──────
    public function processImport()
    {
        $file = $this->request->getFile('archivo');

        if (!$file->isValid()) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Archivo inválido']);
        }

        $ext = strtolower($file->getClientExtension());
        if (!in_array($ext, ['xlsx', 'xls'])) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Solo se permiten archivos Excel (.xlsx, .xls)']);
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, true);

            $model = new \App\Models\CartaServicioModel();
            $model->truncate();

            $data     = [];
            $inserted = 0;

            foreach ($rows as $rowNum => $row) {
                if ($rowNum == 1) continue;

                $hospital = trim($row['A'] ?? '');
                if (empty($hospital)) continue;

                $data[] = [
                    'hospital'          => $hospital,
                    'nivel_complejidad' => trim($row['B'] ?? ''),
                    'region'            => trim($row['C'] ?? ''),
                    'tipo'              => trim($row['D'] ?? ''),
                    'profesion'         => trim($row['E'] ?? ''),
                    'especialidad'      => trim($row['F'] ?? ''),
                    'tipo_profesional'  => trim($row['G'] ?? ''),
                    'profesional'       => trim($row['H'] ?? ''),
                    'dia_atencion'      => trim($row['I'] ?? ''),
                    'horario_atencion'  => trim($row['J'] ?? ''),
                    'turno'             => trim($row['K'] ?? ''),
                    'cant_turnos'       => intval(str_replace(['.', ','], '', $row['L'] ?? 0)),
                ];
                $inserted++;

                if (count($data) >= 500) {
                    $model->insertBatch($data);
                    $data = [];
                }
            }
            if (!empty($data)) $model->insertBatch($data);

            return redirect()->to(route_to('carta_servicio_list'))
                ->with('msg', ['type' => 'success', 'body' => "Importación completada: $inserted registros cargados. Se reemplazó el listado anterior."]);

        } catch (\Exception $e) {
            return redirect()->back()->with('msg', ['type' => 'danger', 'body' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    // ─── Descargar template Excel ─────────────────────────────────────────
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('CartaServicio');

        $headers = [
            'A' => 'HOSPITAL',
            'B' => 'NIVEL COMPLEJIDAD',
            'C' => 'REGION',
            'D' => 'TIPO',
            'E' => 'PROFESION',
            'F' => 'ESPECIALIDAD',
            'G' => 'TIPO PROFESIONAL',
            'H' => 'PROFESIONAL',
            'I' => 'DIA ATENCION',
            'J' => 'HORARIO ATENCION',
            'K' => 'TURNO',
            'L' => 'CANT TURNOS',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'template_carta_servicio.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Exportar Excel con filtros ──────────────────────────────────────
    public function export()
    {
        $model = model('CartaServicioModel');
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('hospital', 'ASC')->orderBy('profesional', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Hospital',
            'B' => 'Nivel Complejidad',
            'C' => 'Región',
            'D' => 'Tipo',
            'E' => 'Profesión',
            'F' => 'Especialidad',
            'G' => 'Tipo Profesional',
            'H' => 'Profesional',
            'I' => 'Día Atención',
            'J' => 'Horario Atención',
            'K' => 'Turno',
            'L' => 'Cant. Turnos',
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
            $sheet->setCellValue('A' . $row, $r->hospital);
            $sheet->setCellValue('B' . $row, $r->nivel_complejidad);
            $sheet->setCellValue('C' . $row, $r->region);
            $sheet->setCellValue('D' . $row, $r->tipo);
            $sheet->setCellValue('E' . $row, $r->profesion);
            $sheet->setCellValue('F' . $row, $r->especialidad);
            $sheet->setCellValue('G' . $row, $r->tipo_profesional);
            $sheet->setCellValue('H' . $row, $r->profesional);
            $sheet->setCellValue('I' . $row, $r->dia_atencion);
            $sheet->setCellValue('J' . $row, $r->horario_atencion);
            $sheet->setCellValue('K' . $row, $r->turno);
            $sheet->setCellValue('L' . $row, $r->cant_turnos);
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'carta_servicio_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}