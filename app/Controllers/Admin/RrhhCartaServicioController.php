<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\RrhhCartaServicio;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RrhhCartaServicioController extends BaseController
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

        $hospitales     = array_column((new \App\Models\RrhhCartaServicioModel())->select('hospital')->distinct()->orderBy('hospital', 'ASC')->findAll(), 'hospital');
        $regiones       = array_column((new \App\Models\RrhhCartaServicioModel())->select('region')->distinct()->orderBy('region', 'ASC')->findAll(), 'region');
        $niveles        = array_column((new \App\Models\RrhhCartaServicioModel())->select('nivel_complejidad')->distinct()->orderBy('nivel_complejidad', 'ASC')->findAll(), 'nivel_complejidad');
        $especialidades = array_column((new \App\Models\RrhhCartaServicioModel())->select('especialidad')->distinct()->orderBy('especialidad', 'ASC')->findAll(), 'especialidad');

        $totalRegistros    = (new \App\Models\RrhhCartaServicioModel())->countAllResults();
        $totalHospitales   = count(array_filter($hospitales));
        $totalProfesionales = (new \App\Models\RrhhCartaServicioModel())->select('dni')->distinct()->countAllResults();

        $model   = model('RrhhCartaServicioModel');
        $filtros = $this->aplicarFiltros($model);

        return view('rrhh_carta_servicio/rrhh_carta_servicio_list', array_merge($filtros, [
            'registros'          => $model->orderBy('hospital', 'ASC')->orderBy('nombre_apellido', 'ASC')->paginate($perPage),
            'pager'              => $model->pager,
            'hospitales'         => $hospitales,
            'regiones'           => $regiones,
            'niveles'            => $niveles,
            'especialidades'     => $especialidades,
            'totalRegistros'     => $totalRegistros,
            'totalHospitales'    => $totalHospitales,
            'totalProfesionales' => $totalProfesionales,
        ]));
    }

    // ─── Vista importar ──────────────────────────────────────────────────
    public function import()
    {
        return view('rrhh_carta_servicio/rrhh_carta_servicio_import');
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

            $model = new \App\Models\RrhhCartaServicioModel();
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
                    'dni'               => trim($row['D'] ?? ''),
                    'nombre_apellido'   => trim($row['E'] ?? ''),
                    'profesion'         => trim($row['F'] ?? ''),
                    'especialidad'      => trim($row['G'] ?? ''),
                    'revista'           => trim($row['H'] ?? ''),
                    'consultorio'       => trim($row['I'] ?? ''),
                    'guardia_cargo'     => trim($row['J'] ?? ''),
                    'telemedicina'      => trim($row['K'] ?? ''),
                    'prosane'           => trim($row['L'] ?? ''),
                    'carnet_sanitario'  => trim($row['M'] ?? ''),
                ];
                $inserted++;

                if (count($data) >= 500) {
                    $model->insertBatch($data);
                    $data = [];
                }
            }
            if (!empty($data)) $model->insertBatch($data);

            return redirect()->to(route_to('rrhh_carta_servicio_list'))
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
        $sheet->setTitle('RRHH');

        $headers = [
            'A' => 'HOSPITAL',
            'B' => 'NIVEL COMPLEJIDAD',
            'C' => 'REGION',
            'D' => 'DNI',
            'E' => 'NOMBRE Y APELLIDO',
            'F' => 'PROFESION',
            'G' => 'ESPECIALIDAD',
            'H' => 'REVISTA',
            'I' => 'CONSULTORIO',
            'J' => 'GUARDIA A CARGO',
            'K' => 'TELEMEDICINA',
            'L' => 'PROSANE',
            'M' => 'CARNET SANITARIO',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'template_rrhh_carta_servicio.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Exportar Excel con filtros ──────────────────────────────────────
    public function export()
    {
        $model = model('RrhhCartaServicioModel');
        $this->aplicarFiltros($model);

        $registros = $model->orderBy('hospital', 'ASC')->orderBy('nombre_apellido', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Hospital',
            'B' => 'Nivel Complejidad',
            'C' => 'Región',
            'D' => 'DNI',
            'E' => 'Nombre y Apellido',
            'F' => 'Profesión',
            'G' => 'Especialidad',
            'H' => 'Revista',
            'I' => 'Consultorio',
            'J' => 'Guardia a Cargo',
            'K' => 'Telemedicina',
            'L' => 'Prosane',
            'M' => 'Carnet Sanitario',
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
            $sheet->setCellValue('D' . $row, $r->dni);
            $sheet->setCellValue('E' . $row, $r->nombre_apellido);
            $sheet->setCellValue('F' . $row, $r->profesion);
            $sheet->setCellValue('G' . $row, $r->especialidad);
            $sheet->setCellValue('H' . $row, $r->revista);
            $sheet->setCellValue('I' . $row, $r->consultorio);
            $sheet->setCellValue('J' . $row, $r->guardia_cargo);
            $sheet->setCellValue('K' . $row, $r->telemedicina);
            $sheet->setCellValue('L' . $row, $r->prosane);
            $sheet->setCellValue('M' . $row, $r->carnet_sanitario);
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'rrhh_carta_servicio_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}