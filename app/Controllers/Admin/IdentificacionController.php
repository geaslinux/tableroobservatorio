<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Entities\Identificacion;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class IdentificacionController extends BaseController
{
    // ─── Filtros reutilizables ───────────────────────────────────────────
    private function aplicarFiltros($model, $ejercicios = [])
    {
        $ejercicioRaw    = $this->request->getGet('ejercicio');
        $filtro_departamento = $this->request->getGet('departamento') ?? '';
        $filtro_paciente = $this->request->getGet('paciente') ?? '';
        $filtro_estado       = $this->request->getGet('estado')       ?? '';

        // Si es la primera carga (sin query param), asigna el ejercicio más reciente
        if ($ejercicioRaw === null) {
            $filtro_ejercicio = $ejercicios[0] ?? '';
        } else {
            $filtro_ejercicio = $ejercicioRaw;
        }

        if ($filtro_ejercicio !== '') $model->where('ejercicio',    $filtro_ejercicio);
        if ($filtro_departamento)     $model->where('departamento', $filtro_departamento);

        if ($filtro_paciente) {
            if ($filtro_paciente === 'adulto') {
                $model->where('adulto >', 0);
            } elseif ($filtro_paciente === 'pediatrico') {
                $model->where('pediatrico >', 0);
            }
        }

        if ($filtro_estado != '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        return compact('filtro_ejercicio', 'filtro_departamento', 'filtro_paciente', 'filtro_estado');
    }

    // ─── Estadísticas (tabla detalle + gráficos) ─────────────────────────
    private function calcularEstadisticas($filtro_ejercicio, $filtro_departamento, $filtro_paciente, $filtro_estado)
    {
        $model = model('IdentificacionModel');

        if ($filtro_ejercicio !== '') $model->where('ejercicio',    $filtro_ejercicio);
        if ($filtro_departamento)     $model->where('departamento', $filtro_departamento);

        if ($filtro_paciente) {
            if ($filtro_paciente === 'adulto') {
                $model->where('adulto >', 0);
            } elseif ($filtro_paciente === 'pediatrico') {
                $model->where('pediatrico >', 0);
            }
        }

        if ($filtro_estado != '') {
            $model->where('estado', $filtro_estado);
        } else {
            $model->whereIn('estado', ['activo', 'desactivado']);
        }

        $registros = $model->orderBy('ejercicio', 'DESC')->orderBy('departamento', 'ASC')->findAll();

        // KPIs
        $totalAdulto     = 0;
        $totalPediatrico = 0;
        $totalGeneral    = 0;
        $departamentosSet = [];

        // Tabla detalle
        $statsFilas = [];

        foreach ($registros as $r) {
            $adultoVal     = (int) $r->adulto;
            $pediatricoVal = (int) $r->pediatrico;

            $totalAdulto     += $adultoVal;
            $totalPediatrico += $pediatricoVal;
            $departamentosSet[$r->departamento] = true;

            // Determinar la cantidad según el filtro de paciente activo
            if ($filtro_paciente === 'adulto') {
                $cantFila = $adultoVal;
            } elseif ($filtro_paciente === 'pediatrico') {
                $cantFila = $pediatricoVal;
            } else {
                $cantFila = (int) $r->total;
            }

            $totalGeneral += $cantFila;

            $statsFilas[] = [
                'departamento' => $r->departamento,
                'ejercicio'    => $r->ejercicio,
                'cantidad'     => $cantFila,
                'adulto'       => $adultoVal,
                'pediatrico'   => $pediatricoVal,
            ];
        }

        // Ejercicios más recientes presentes en el set filtrado
        $ejerciciosDisponibles = [];
        foreach ($registros as $r) {
            if (!in_array($r->ejercicio, $ejerciciosDisponibles)) {
                $ejerciciosDisponibles[] = $r->ejercicio;
            }
        }
        rsort($ejerciciosDisponibles);

        $statsEjercicios = ($filtro_ejercicio !== '')
            ? [$filtro_ejercicio]
            : $ejerciciosDisponibles;

        // Barras: departamento (eje X) vs ejercicio (series)
        $statsBarras = [];
        foreach ($registros as $r) {
            if (!in_array($r->ejercicio, $statsEjercicios)) continue;

            if ($filtro_paciente === 'adulto') {
                $cantBarra = (int) $r->adulto;
            } elseif ($filtro_paciente === 'pediatrico') {
                $cantBarra = (int) $r->pediatrico;
            } else {
                $cantBarra = (int) $r->total;
            }

            $statsBarras[] = [
                'departamento' => $r->departamento,
                'ejercicio'    => $r->ejercicio,
                'cantidad'     => $cantBarra,
            ];
        }

        // Torta: total por ejercicio
        $totalesPorEjercicio = [];
        foreach ($registros as $r) {
            if ($filtro_paciente === 'adulto') {
                $cantTorta = (int) $r->adulto;
            } elseif ($filtro_paciente === 'pediatrico') {
                $cantTorta = (int) $r->pediatrico;
            } else {
                $cantTorta = (int) $r->total;
            }

            $totalesPorEjercicio[$r->ejercicio] = ($totalesPorEjercicio[$r->ejercicio] ?? 0) + $cantTorta;
        }

        $statsTorta = [];
        foreach ($totalesPorEjercicio as $ej => $tot) {
            $statsTorta[] = ['ejercicio' => $ej, 'total' => $tot];
        }
        usort($statsTorta, fn($a, $b) => $b['ejercicio'] <=> $a['ejercicio']);

        return [
            'totalAdulto'        => $totalAdulto,
            'totalPediatrico'    => $totalPediatrico,
            'totalGeneral'       => $totalGeneral,
            'totalDepartamentos' => count($departamentosSet),
            'statsFilas'         => $statsFilas,
            'statsBarras'        => $statsBarras,
            'statsEjercicios'    => $statsEjercicios,
            'statsTorta'         => $statsTorta,
        ];
    }

    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        // ── Listas para selects (modelos limpios) ─────────────────────────
        $ejercicios = array_column(
            model('IdentificacionModel')->select('ejercicio')->distinct()->orderBy('ejercicio', 'DESC')->findAll(),
            'ejercicio'
        );

        $departamentos = array_column(
            model('IdentificacionModel')->select('departamento')->distinct()->orderBy('departamento', 'ASC')->whereIn('estado', ['activo', 'desactivado'])->findAll(),
            'departamento'
        );

        $todosLosEjercicios = array_column(
            model('IdentificacionModel')->select('ejercicio')->distinct()->whereIn('estado', ['activo', 'desactivado'])->orderBy('ejercicio', 'ASC')->findAll(),
            'ejercicio'
        );

        // ── Auditoría PRIMERO ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)
                                  ->where('modulo', 'identificacion')
                                  ->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        $nuevas = model('IdentificacionModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('created_at >', $ultimaVista)
            ->countAllResults();

        $modificadas = model('IdentificacionModel')
            ->whereIn('estado', ['activo', 'desactivado'])
            ->where('updated_at >', $ultimaVista)
            ->where('updated_at != created_at')
            ->countAllResults();

        // ── Filtros DESPUÉS ───────────────────────────────────────────────
        $model   = model('IdentificacionModel');
        $filtros = $this->aplicarFiltros($model, $ejercicios);

        // ── Estadísticas / KPIs (sobre el set filtrado completo, sin paginar) ─
        $stats = $this->calcularEstadisticas(
            $filtros['filtro_ejercicio'],
            $filtros['filtro_departamento'],
            $filtros['filtro_paciente'],
            $filtros['filtro_estado']
        );

        return view('identificacion/identificacion_list', array_merge($filtros, $stats, [
            'identificaciones'   => $model->orderBy('ejercicio', 'DESC')->orderBy('departamento', 'ASC')->paginate($perPage),
            'pager'              => $model->pager,
            'ejercicios'         => $ejercicios,
            'todosLosEjercicios' => $todosLosEjercicios,
            'departamentos'      => $departamentos,
            'nuevas'             => $nuevas,
            'modificadas'        => $modificadas,
            'ultimaVista'        => $ultimaVista,
        ]));
    }

    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $model  = model('UserLastVisitModel');

        $data = [
            'user_id'     => $userId,
            'modulo'      => 'identificacion',
            'ultima_vista'=> date('Y-m-d H:i:s'),
        ];

        $existe = $model
            ->where('user_id', $userId)
            ->where('modulo', 'identificacion')
            ->first();

        if ($existe) {
            $model->update($userId, $data);
        } else {
            $model->insert($data);
        }

        return redirect()->to(route_to('identificacion_list'));
    }

    // ─── Vista formulario nuevo ──────────────────────────────────────────
    public function create()
    {
        helper('form');
        return view('identificacion/identificacion_form');
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $departamento = strtoupper(trim($this->request->getPost('departamento')));

        $existe = model('IdentificacionModel')
            ->where('ejercicio',    $this->request->getPost('ejercicio'))
            ->where('departamento', $departamento)
            ->whereIn('estado',     ['activo', 'desactivado'])
            ->first();

        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['departamento' => 'Ya existe un registro para ese ejercicio y departamento.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un registro para ese ejercicio y departamento.']);
        }

        $adulto     = (int) $this->request->getPost('adulto');
        $pediatrico = (int) $this->request->getPost('pediatrico');
        $total      = $adulto + $pediatrico;

        model('IdentificacionModel')->insert([
            'ejercicio'    => $this->request->getPost('ejercicio'),
            'departamento' => $departamento,
            'adulto'       => $adulto,
            'pediatrico'   => $pediatrico,
            'total'        => $total,
            'estado'       => $this->request->getPost('estado') ?? 'activo',
        ]);

        return redirect()->to(route_to('identificacion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Identificación guardada correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        $anioActual = (int) date('Y');

        return $this->validate([
            'ejercicio'    => "required|integer|less_than_equal_to[{$anioActual}]",
            'departamento' => 'required',
            'adulto'       => 'required|integer',
            'pediatrico'   => 'required|integer',
        ], [
            'ejercicio' => [
                'less_than_equal_to' => "El ejercicio no puede ser mayor a {$anioActual}. Estamos en {$anioActual}.",
            ],
            'departamento' => [
                'required' => 'El departamento es obligatorio.',
            ],
        ]);
    }

    // ─── Vista importar ──────────────────────────────────────────────────
    public function import()
    {
        return view('identificacion/identificacion_import');
    }

    // ─── Procesar importación Excel ──────────────────────────────────────
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

            $model    = model('IdentificacionModel');
            $inserted = 0;
            $skipped  = 0;
            $errores  = [];

            foreach ($rows as $rowNum => $row) {
                if ($rowNum == 1) continue;

                $ejercicio    = trim($row['A'] ?? '');
                $departamento = strtoupper(trim($row['B'] ?? ''));
                $adulto       = intval(str_replace(['.', ','], '', $row['C'] ?? 0));
                $pediatrico   = intval(str_replace(['.', ','], '', $row['D'] ?? 0));

                if (empty($ejercicio) || empty($departamento)) continue;
                if (!is_numeric($ejercicio)) continue;

                $existe = $model->where('ejercicio', $ejercicio)->where('departamento', $departamento)->first();
                if ($existe) {
                    $skipped++;
                    continue;
                }

                $total = $adulto + $pediatrico;

                $model->insert([
                    'ejercicio'    => $ejercicio,
                    'departamento' => $departamento,
                    'adulto'       => $adulto,
                    'pediatrico'   => $pediatrico,
                    'total'        => $total,
                    'estado'       => 'activo',
                ]);
                $inserted++;
            }

            $msg = "Importación completada: $inserted registros importados.";
            if ($skipped)        $msg .= " $skipped duplicados omitidos.";
            if (!empty($errores)) $msg .= ' Errores: ' . implode(' | ', $errores);

            return redirect()->to(route_to('identificacion_list'))
                ->with('msg', ['type' => 'success', 'body' => $msg]);

        } catch (\Exception $e) {
            return redirect()->back()->with('msg', [
                'type' => 'danger',
                'body' => 'Error al procesar el archivo: ' . $e->getMessage(),
            ]);
        }
    }

    // ─── Template Excel ──────────────────────────────────────────────────
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Identificacion');

        $headers = [
            'A' => 'EJERCICIO',
            'B' => 'DEPARTAMENTO',
            'C' => 'ADULTO',
            'D' => 'PEDIATRICO',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $sheet->setCellValue('A2', date('Y'));
        $sheet->setCellValue('B2', 'CAPITAL');
        $sheet->setCellValue('C2', 0);
        $sheet->setCellValue('D2', 0);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'template_identificacion.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // ─── Exportar Excel con filtros ──────────────────────────────────────
    public function export()
    {
        $model = model('IdentificacionModel');
        $this->aplicarFiltros($model);
        $identificaciones = $model->orderBy('ejercicio', 'DESC')->orderBy('departamento', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Ejercicio',
            'B' => 'Departamento',
            'C' => 'Adulto',
            'D' => 'Pediátrico',
            'E' => 'Total',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($identificaciones as $i => $id) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $id->ejercicio);
            $sheet->setCellValue('B' . $row, $id->departamento);
            $sheet->setCellValue('C' . $row, $id->adulto);
            $sheet->setCellValue('D' . $row, $id->pediatrico);
            $sheet->setCellValue('E' . $row, $id->total);
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'identificacion_' . date('Ymd_His') . '.xlsx';

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
        $model = model('IdentificacionModel');
        if ($model->find($id)) {
            $model->update($id, ['estado' => 'eliminado']);
        }
        return redirect()->to(route_to('identificacion_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }
}