<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ConsultaTableroController extends BaseController
{
    private function consulta(): \App\Libraries\EstadisticaConsulta
    {
        return new \App\Libraries\EstadisticaConsulta((int) user_id(), $this->request->getGet());
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
        try {
            if ($pagina === false) throw new \InvalidArgumentException('Página inválida.');
            $consulta = $this->consulta();
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
        return view('admin/consulta_tablero', compact('datos', 'catalogos', 'filtros', 'error', 'pagina'));
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
            $archivo = fopen('php://temp/maxmemory:2097152', 'w+');
            fwrite($archivo, "\xEF\xBB\xBF");
            fputcsv($archivo, ['ID', 'Fecha', 'Hora', 'Formulario', 'Profesional', 'Diagnóstico', 'Procedimiento', 'Hospital de origen', 'Servicio de origen'], ';');
            $pagina = 1;
            do {
                $filas = $consulta->detalle($pagina++);
                foreach ($filas as $fila) {
                    $valores = [];
                    foreach (['atencion_id', 'fecha', 'hora', 'formulario', 'profesional', 'diagnostico', 'procedimiento', 'hospital', 'servicio'] as $campo) {
                        $texto = (string) ($fila[$campo] ?? '');
                        if (preg_match('/^[\s]*[=+@-]/u', $texto)) $texto = "'" . $texto;
                        $valores[] = $texto;
                    }
                    fputcsv($archivo, $valores, ';');
                }
            } while (count($filas) === 25);
            rewind($archivo);
            $contenido = stream_get_contents($archivo);
            fclose($archivo);
            return $this->response->setHeader('Cache-Control', 'private, no-store')->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                ->setHeader('Content-Disposition', 'attachment; filename="atenciones_consulta.csv"')->setBody($contenido);
        } catch (\InvalidArgumentException $e) {
            return $this->response->setStatusCode(422)->setBody(esc($e->getMessage()));
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(503)->setBody('No se pudo generar la exportación. Intente nuevamente.');
        }
    }
}
