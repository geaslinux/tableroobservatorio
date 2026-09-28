<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GuardiaController extends BaseController
{
    private $mesesValidos = [
        'ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO',
        'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'
    ];

    private $semestresValidos = ['PRIMER', 'SEGUNDO'];

    // ─── Filtros ──────────────────────────────────────────────────────────
    private function aplicarFiltros($model)
    {
        $filtro_anio      = $this->request->getGet('anio')        ?? '';
        $filtro_semestre  = $this->request->getGet('semestre')    ?? '';
        $filtro_mes       = $this->request->getGet('mes')         ?? '';
        $filtro_efector   = $this->request->getGet('efector_id')  ?? '';
        $filtro_servicio  = $this->request->getGet('servicio_id') ?? '';

        if ($filtro_anio)      $model->where('guardia.anio', $filtro_anio);
        if ($filtro_semestre)  $model->where('guardia.semestre', $filtro_semestre);
        if ($filtro_mes)       $model->where('guardia.mes', $filtro_mes);
        if ($filtro_efector)   $model->where('guardia.efector_id', $filtro_efector);
        if ($filtro_servicio)  $model->where('guardia.servicio_id', $filtro_servicio);

        return compact('filtro_anio', 'filtro_semestre', 'filtro_mes', 'filtro_efector', 'filtro_servicio');
    }

    // ─── Helpers de clave compuesta (identifica un grupo hospital+período) ─
    private function armarClave($efectorId, $anio, $semestre, $mes): string
    {
        return $efectorId . '-' . $anio . '-' . $semestre . '-' . ($mes ?: '0');
    }

    private function desarmarClave(string $clave): array
    {
        $partes = explode('-', $clave);

        return [
            'efector_id' => $partes[0] ?? null,
            'anio'       => $partes[1] ?? null,
            'semestre'   => $partes[2] ?? null,
            'mes'        => (isset($partes[3]) && $partes[3] !== '0') ? $partes[3] : null,
        ];
    }

    // ─── Arma el pivot: una fila por hospital+período, servicios en columnas
    private function agruparPivot(array $registros): array
    {
        $grupos = [];

        foreach ($registros as $r) {
            $clave = $this->armarClave($r->efector_id, $r->anio, $r->semestre, $r->mes);

            if (!isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'clave'      => $clave,
                    'efector_id' => $r->efector_id,
                    'hospital'   => $r->efector_nombre,
                    'mes'        => $r->mes,
                    'semestre'   => $r->semestre,
                    'anio'       => $r->anio,
                    'servicios'  => [], // [servicio_id => cantidad]
                    'total'      => 0,
                ];
            }

            $grupos[$clave]['servicios'][$r->servicio_id] =
                ($grupos[$clave]['servicios'][$r->servicio_id] ?? 0) + $r->cantidad;
            $grupos[$clave]['total'] += $r->cantidad;
        }

        return array_values($grupos);
    }

    // ─── Index ────────────────────────────────────────────────────────────
    public function index()
    {
        helper('auth');

        $config  = config('Obse');
        $perPage = $config->regPerPage ?? 15;

        $model = model('GuardiaModel');

        // ── Listas para selects ─────────────────────────
        $anios = array_column(
            $model->select('anio')->distinct()->orderBy('anio', 'DESC')->findAll(),
            'anio'
        );

        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $servicios = model('ServicioModel')->orderBy('orden', 'ASC')->findAll();

        // ── KPI CARDS ──
        $totalGeneral = (int) ($model->selectSum('cantidad')->first()->cantidad ?? 0);

        $totalPorSemestre = $model
            ->select('semestre, SUM(cantidad) as total')
            ->groupBy('semestre')
            ->asArray()
            ->findAll();

        // ── Filtros + traigo TODO lo filtrado para armar el pivot ──────────
        $modelFiltrado = $model->withNombres();
        $filtros       = $this->aplicarFiltros($modelFiltrado);

        $registros = $modelFiltrado
            ->orderBy('guardia.anio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $grupos = $this->agruparPivot($registros);

        // ── Paginación manual sobre el array ya agrupado ───────────────────
        $paginaActual = (int) ($this->request->getGet('page') ?? 1);
        $totalGrupos  = count($grupos);
        $offset       = ($paginaActual - 1) * $perPage;
        $grupoPagina  = array_slice($grupos, $offset, $perPage);

        // OJO: usamos route_to() en vez de escribir el path a mano.
        // Si la ruta 'guardia' vive dentro de un group() con prefijo,
        // setPath('guardia') genera links rotos (404). route_to() siempre
        // devuelve el path real de la ruta registrada.
        $pager = service('pager');
        $pager->setPath(route_to('guardia_list'));
        $pager->makeLinks($paginaActual, $perPage, $totalGrupos);

        // ── Auditoría ─────────────────────────────────────────────
        $userId     = user()->id;
        $visitModel = model('UserLastVisitModel');

        $registro    = $visitModel->where('user_id', $userId)->where('modulo', 'guardia')->first();
        $ultimaVista = $registro ? $registro->ultima_vista : '2000-01-01 00:00:00';

        return view('guardia/guardia_list', array_merge($filtros, [
            'grupos'           => $grupoPagina,
            'servicios'        => $servicios,
            'pager'            => $pager,
            'anios'            => $anios,
            'semestres'        => $this->semestresValidos,
            'meses'            => $this->mesesValidos,
            'efectores'        => $efectores,
            'totalGeneral'     => $totalGeneral,
            'totalPorSemestre' => $totalPorSemestre,
            'ultimaVista'      => $ultimaVista,
        ]));
    }

    // ─── Marcar visto ─────────────────────────────────────────────────────
    public function marcarVisto()
    {
        helper('auth');

        $userId = user_id();
        $ahora  = date('Y-m-d H:i:s');

        $visitModel = model('UserLastVisitModel');
        $existe = $visitModel->where('user_id', $userId)->where('modulo', 'guardia')->first();

        if ($existe) {
            $visitModel->where('user_id', $userId)->where('modulo', 'guardia')
                       ->set('ultima_vista', $ahora)->update();
        } else {
            $visitModel->insert(['user_id' => $userId, 'modulo' => 'guardia', 'ultima_vista' => $ahora]);
        }

        return redirect()->to(route_to('guardia_list'));
    }

    // ─── Create ───────────────────────────────────────────────────────────
    public function create()
    {
        $efectores = model('EfectorModel')->orderBy('nombre', 'ASC')->findAll();
        $servicios = model('ServicioModel')->orderBy('orden', 'ASC')->findAll();

        return view('guardia/guardia_form', [
            'efectores' => $efectores,
            'servicios' => $servicios,
            'meses'     => $this->mesesValidos,
            'semestres' => $this->semestresValidos,
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────
    public function store()
    {
        $model = model('GuardiaModel');

        $anio     = $this->request->getPost('anio');
        $semestre = $this->request->getPost('semestre');
        $mes      = $this->request->getPost('mes'); // opcional, puede venir vacío

        $efectorIds  = $this->request->getPost('efector_id');
        $servicioIds = $this->request->getPost('servicio_id');
        $cantidades  = $this->request->getPost('cantidad');

        $insertados = 0;
        $omitidos   = 0;

        foreach ($efectorIds as $i => $efectorId) {
            $servicioId = $servicioIds[$i];
            $cantidad   = intval($cantidades[$i]);

            if (empty($efectorId) || empty($servicioId)) continue;

            $existe = $model
                ->where('efector_id', $efectorId)
                ->where('servicio_id', $servicioId)
                ->where('anio', $anio)
                ->where('semestre', $semestre)
                ->where('mes', $mes ?: null)
                ->first();

            if ($existe) {
                $omitidos++;
                continue;
            }

            $model->insert([
                'efector_id'  => $efectorId,
                'servicio_id' => $servicioId,
                'anio'        => $anio,
                'semestre'    => $semestre,
                'mes'         => $mes ?: null,
                'cantidad'    => $cantidad,
            ]);

            $insertados++;
        }

        $msg = "$insertados registros guardados.";
        if ($omitidos) $msg .= " $omitidos omitidos por ser duplicados.";

        return redirect()->to(route_to('guardia_list'))
            ->with('msg', ['type' => 'success', 'body' => $msg]);
    }

    // ─── Show/Edit de un GRUPO (hospital + período) ────────────────────────
    // Reutiliza la MISMA vista de creación (guardia_form), pasándole
    // 'guardiaGrupo' => true para que la vista muestre la tabla fija
    // de servicios en vez de la tabla dinámica de alta.
    public function show($clave)
    {
        $datos = $this->desarmarClave($clave);
        $model = model('GuardiaModel');

        $registrosGrupo = $model
            ->where('efector_id', $datos['efector_id'])
            ->where('anio', $datos['anio'])
            ->where('semestre', $datos['semestre'])
            ->where('mes', $datos['mes'])
            ->findAll();

        if (!$registrosGrupo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $efectorActual = model('EfectorModel')->find($datos['efector_id']);
        $servicios     = model('ServicioModel')->orderBy('orden', 'ASC')->findAll();

        // Cantidades actuales por servicio_id, para precargar el formulario
        $cantidadesPorServicio = [];
        foreach ($registrosGrupo as $g) {
            $cantidadesPorServicio[$g->servicio_id] = $g->cantidad;
        }

        return view('guardia/guardia_form', [
            'guardiaGrupo'          => true,
            'clave'                 => $clave,
            'efectorActual'         => $efectorActual,
            'anio'                  => $datos['anio'],
            'semestre'              => $datos['semestre'],
            'mes'                   => $datos['mes'],
            'servicios'             => $servicios,
            'cantidadesPorServicio' => $cantidadesPorServicio,
            'formRoute'             => 'guardia_update',
        ]);
    }

    // ─── Update de un GRUPO completo ───────────────────────────────────────
    public function update()
    {
        $clave = $this->request->getPost('clave');

        if (!$clave) {
            return redirect()->to(route_to('guardia_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Grupo no encontrado']);
        }

        $datos = $this->desarmarClave($clave);
        $model = model('GuardiaModel');

        $servicioIds = $this->request->getPost('servicio_id');
        $cantidades  = $this->request->getPost('cantidad');

        if (!$servicioIds || !$cantidades) {
            return redirect()->back()
                ->with('msg', ['type' => 'danger', 'body' => 'No se recibieron datos de servicios']);
        }

        // Reemplaza todos los registros del grupo (borra y vuelve a insertar)
        $model->where('efector_id', $datos['efector_id'])
              ->where('anio', $datos['anio'])
              ->where('semestre', $datos['semestre'])
              ->where('mes', $datos['mes'])
              ->delete();

        foreach ($servicioIds as $i => $servicioId) {
            $cantidad = intval($cantidades[$i] ?? 0);
            if (empty($servicioId) || $cantidad <= 0) continue;

            $model->insert([
                'efector_id'  => $datos['efector_id'],
                'servicio_id' => $servicioId,
                'anio'        => $datos['anio'],
                'semestre'    => $datos['semestre'],
                'mes'         => $datos['mes'],
                'cantidad'    => $cantidad,
            ]);
        }

        return redirect()->to(route_to('guardia_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Guardia actualizada correctamente']);
    }

    // ─── Destroy de un GRUPO completo ──────────────────────────────────────
    public function destroy()
    {
        $clave = $this->request->getVar('id'); // seguimos usando 'id' porque así está la ruta

        if (!$clave) {
            return redirect()->to(route_to('guardia_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Grupo no encontrado']);
        }

        $datos = $this->desarmarClave($clave);
        $model = model('GuardiaModel');

        $model->where('efector_id', $datos['efector_id'])
              ->where('anio', $datos['anio'])
              ->where('semestre', $datos['semestre'])
              ->where('mes', $datos['mes'])
              ->delete();

        return redirect()->to(route_to('guardia_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Registro eliminado correctamente']);
    }

    // ─── Exportar Excel con filtros ─────────────────────────────────────────
    public function export()
    {
        $model = model('GuardiaModel')->withNombres();
        $this->aplicarFiltros($model);

        $guardias = $model
            ->orderBy('guardia.anio', 'DESC')
            ->orderBy('efector.nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = [
            'A' => 'Año',
            'B' => 'Semestre',
            'C' => 'Mes',
            'D' => 'Efector',
            'E' => 'Servicio',
            'F' => 'Cantidad',
        ];

        foreach ($headers as $col => $h) {
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('13304d');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach ($guardias as $i => $g) {
            $row = $i + 2;
            $sheet->setCellValue('A' . $row, $g->anio);
            $sheet->setCellValue('B' . $row, $g->semestre);
            $sheet->setCellValue('C' . $row, $g->mes);
            $sheet->setCellValue('D' . $row, $g->efector_nombre);
            $sheet->setCellValue('E' . $row, $g->servicio_nombre);
            $sheet->setCellValue('F' . $row, $g->cantidad);
        }

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new Xlsx($spreadsheet);
        $filename = 'guardias_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}