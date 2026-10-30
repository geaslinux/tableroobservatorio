<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelFecha;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ConsultaTableroController extends BaseController
{
    private function consulta(?array $entrada = null): \App\Libraries\EstadisticaConsulta
    {
        return new \App\Libraries\EstadisticaConsulta((int) user_id(), $entrada ?? $this->request->getGet());
    }

    private function autorizado(): bool
    {
        helper('auth');
        return user_id() && has_permission('LISTADO PERSONA');
    }

    public function index()
    {
        if (!$this->autorizado()) {
            return $this->response->setStatusCode(403)->setBody('No tiene permisos para acceder al tablero.');
        }
        $this->response->setHeader('Cache-Control', 'private, no-store');
        $error = null;
        $datos = null;
        $catalogos = ['formularios' => [], 'medico' => [], 'hospital' => [], 'servicio' => []];
        $filtros = array_fill_keys(\App\Libraries\EstadisticaConsulta::FILTROS, '');
        $pagina = filter_var($this->request->getGet('pagina') ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
        // Primera carga (sin ningún parámetro en la URL): solo el año actual.
        // Para ver todo el historial la vista envía "desde=" vacío, que ya no cuenta como primera carga.
        $inicial = $this->request->getGet() === [];
        try {
            if ($pagina === false) throw new \InvalidArgumentException('Página inválida.');
            $consulta = $this->consulta();
            $consulta = $this->consulta($inicial ? ['desde' => date('Y') . '-01-01'] : null);
            $filtros = $consulta->filtros;
            $catalogos = $consulta->catalogos();
            $datos = $consulta->resumen();
            $datos['distribuciones'] = $consulta->distribuciones();
            $pagina = min($pagina, max(1, (int) ceil((int) $datos['kpi']['atenciones'] / 25)));
            $datos['detalle'] = $consulta->detalle($pagina);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            $this->response->setStatusCode(422);
            foreach ($filtros as $key => $_) {
                $valor = $this->request->getGet($key);
                if (is_scalar($valor)) $filtros[$key] = (string) $valor;
            }
            $catalogos = (new \App\Libraries\EstadisticaConsulta((int) user_id(), []))->catalogos();
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $error = 'No se pudieron consultar los datos. Intente nuevamente o contacte al administrador.';
            $datos = null;
            $this->response->setStatusCode(503);
        }
        return view('admin/consulta_tablero', compact('datos', 'catalogos', 'filtros', 'error', 'pagina', 'inicial'));
    }

    public function detalle($id)
    {
        if (!$this->autorizado()) return $this->response->setStatusCode(403)->setBody('No tiene permisos para consultar registros.');
        try {
            $consulta = $this->consulta();
            $registro = $consulta->detalle(1, (int) $id)[0] ?? null;
            if (!$registro) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            $this->response->setHeader('Cache-Control', 'private, no-store');
            return view('admin/consulta_detalle', ['registro' => $registro, 'filtros' => $consulta->filtros]);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(422)->setBody(esc($e->getMessage()));
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(503)->setBody('No se pudo consultar la atención. Intente nuevamente.');
        }
    }

    public function exportar()
    {
        if (!$this->autorizado()) return $this->response->setStatusCode(403)->setBody('No tiene permisos para exportar.');
        try {
            $consulta = $this->consulta();
            $libro = new Spreadsheet();
            $hoja = $libro->getActiveSheet();
            $hoja->setTitle('Atenciones');

            $encabezados = ['ID', 'Fecha', 'Hora', 'Formulario', 'Profesional', 'Diagnóstico', 'Procedimiento', 'Hospital de origen', 'Servicio de origen'];
            $hoja->fromArray($encabezados, null, 'A1');
            $hoja->getStyle('A1:I1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A2B45']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Los textos se escriben como texto explícito: un valor que empiece con "=" no se toma como fórmula
            $textos = ['formulario' => 'D', 'profesional' => 'E', 'diagnostico' => 'F', 'procedimiento' => 'G', 'hospital' => 'H', 'servicio' => 'I'];
            $fila = 2;
            $pagina = 1;
            do {
                $registros = $consulta->detalle($pagina++);
                foreach ($registros as $r) {
                    $hoja->setCellValue("A{$fila}", (int) $r['atencion_id']);
                    $fecha = $r['fecha'] ? \DateTime::createFromFormat('!Y-m-d', $r['fecha']) : false;
                    if ($fecha) $hoja->setCellValue("B{$fila}", ExcelFecha::PHPToExcel($fecha));
                    if (!empty($r['hora']) && preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?/', $r['hora'], $h)) {
                        $hoja->setCellValue("C{$fila}", ((int) $h[1] * 3600 + (int) $h[2] * 60 + (int) ($h[3] ?? 0)) / 86400);
                    }
                    foreach ($textos as $campo => $col) {
                        $hoja->setCellValueExplicit("{$col}{$fila}", (string) ($r[$campo] ?? ''), DataType::TYPE_STRING);
                    }
                    $fila++;
                }
            } while (count($registros) === 25);

            $ultima = max(1, $fila - 1);
            $hoja->getStyle("B2:B{$ultima}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $hoja->getStyle("C2:C{$ultima}")->getNumberFormat()->setFormatCode('hh:mm');
            $hoja->getStyle("A2:C{$ultima}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $hoja->freezePane('A2');
            $hoja->setAutoFilter("A1:I{$ultima}");
            foreach (range('A', 'I') as $col) $hoja->getColumnDimension($col)->setAutoSize(true);
            // Textos largos: ancho tope y ajuste de línea para que la planilla siga siendo legible
            $hoja->calculateColumnWidths();
            foreach (['D', 'E', 'F', 'G', 'H', 'I'] as $col) {
                if ($hoja->getColumnDimension($col)->getWidth() > 60) {
                    $hoja->getColumnDimension($col)->setAutoSize(false)->setWidth(60);
                    $hoja->getStyle("{$col}2:{$col}{$ultima}")->getAlignment()->setWrapText(true);
                }
            }
            $hoja->getStyle("A2:I{$ultima}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

            ob_start();
            (new Xlsx($libro))->save('php://output');
            $contenido = ob_get_clean();
            $libro->disconnectWorksheets();

            return $this->response->setHeader('Cache-Control', 'private, no-store')
                ->download('atenciones_consulta_' . date('Ymd_His') . '.xlsx', $contenido);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(422)->setBody(esc($e->getMessage()));
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            log_message('error', 'Exportación de consultas: {clase}: {mensaje} en {archivo}:{linea}', [
                'clase' => get_class($e), 'mensaje' => $e->getMessage(), 'archivo' => $e->getFile(), 'linea' => $e->getLine(),
            ]);
            $detalle = ENVIRONMENT === 'development' ? ' (' . get_class($e) . ': ' . $e->getMessage() . ')' : '';
            return $this->response->setStatusCode(503)->setBody(esc('No se pudo generar la exportación. Intente nuevamente.' . $detalle));
        }
    }
}