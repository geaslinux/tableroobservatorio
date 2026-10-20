<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Base de los controllers del módulo Documentación (traído de sistemabasec).
 * Solo tiene los helpers que usan DocumentacionController y ManualController:
 * paginación, redirecciones con mensaje, baja lógica y armado/descarga de Excel.
 * Los demás módulos de este sistema siguen extendiendo BaseController.
 */
abstract class AdminController extends BaseController
{
    // Estados que se muestran en listados (la baja lógica deja 'eliminado')
    protected const ESTADOS_VISIBLES = ['activo', 'desactivado'];

    private const COLOR_ENCABEZADO = '13304d';

    // ─── Paginación ──────────────────────────────────────────────────────
    protected function porPagina(): int
    {
        return (int) (config('Obse')->regPerPage ?? 15);
    }

    // ─── Redirecciones con mensaje ───────────────────────────────────────
    protected function irALista(string $ruta, string $mensaje, string $tipo = 'success'): RedirectResponse
    {
        return redirect()->to(route_to($ruta))->with('msg', ['type' => $tipo, 'body' => $mensaje]);
    }

    protected function volverConErrores(): RedirectResponse
    {
        return redirect()->back()->withInput()
            ->with('errors', $this->validator->getErrors())
            ->with('msg', ['type' => 'error', 'body' => 'Corrija los errores']);
    }

    // ─── Baja lógica (estado = 'eliminado') ──────────────────────────────
    protected function bajaLogica(string $modelo, string $rutaLista): RedirectResponse
    {
        $id    = $this->request->getVar('id');
        $model = model($modelo, false);

        if (!$id || !$model->find($id)) {
            return $this->irALista($rutaLista, 'Registro no encontrado', 'error');
        }

        $model->update($id, ['estado' => 'eliminado']);

        return $this->irALista($rutaLista, 'Registro eliminado correctamente');
    }

    // Entero desde el POST (vacío = 0)
    protected function postEntero(string $campo): int
    {
        return (int) $this->request->getPost($campo);
    }

    // ─── Excel ───────────────────────────────────────────────────────────
    // Libro nuevo con la fila de encabezados ya escrita y con estilo
    protected function nuevaPlanilla(array $encabezados, string $titulo = ''): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $hoja        = $spreadsheet->getActiveSheet();

        if ($titulo !== '') $hoja->setTitle($titulo);

        $hoja->fromArray(array_values($encabezados), null, 'A1');
        $ultima = Coordinate::stringFromColumnIndex(count($encabezados));
        $hoja->getStyle("A1:{$ultima}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_ENCABEZADO]],
        ]);

        return $spreadsheet;
    }

    // Escribe filas (arrays de valores) desde la fila indicada
    protected function escribirFilas(Worksheet $hoja, array $filas, int $desde = 2): void
    {
        if ($filas) {
            $hoja->fromArray($filas, null, 'A' . $desde, true);
        }
    }

    // Ajusta anchos y devuelve el archivo como descarga (sin header()/exit)
    protected function descargarExcel(Spreadsheet $spreadsheet, string $nombre): ResponseInterface
    {
        foreach ($spreadsheet->getAllSheets() as $hoja) {
            foreach ($hoja->getColumnIterator() as $columna) {
                $hoja->getColumnDimension($columna->getColumnIndex())->setAutoSize(true);
            }
        }
        $spreadsheet->setActiveSheetIndex(0);

        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $contenido = ob_get_clean();
        $spreadsheet->disconnectWorksheets();

        return $this->response->download($nombre, $contenido);
    }
}