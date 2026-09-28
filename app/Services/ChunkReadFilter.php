<?php
// ============================================
// ARCHIVO: app/Services/ChunkReadFilter.php
// ============================================

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Filtro para leer archivos Excel en chunks (trozos)
 * Esto permite procesar archivos grandes sin consumir toda la RAM
 */
class ChunkReadFilter implements IReadFilter
{
    private $startRow = 0;
    private $endRow = 0;
    
    /**
     * Establecer el rango de filas a leer
     *
     * @param int $startRow Fila inicial
     * @param int $chunkSize Cantidad de filas a leer
     */
    public function setRows($startRow, $chunkSize)
    {
        $this->startRow = $startRow;
        $this->endRow = $startRow + $chunkSize;
    }
    
    /**
     * Determinar si una celda debe ser leída
     *
     * @param string $columnAddress Dirección de columna (A, B, C...)
     * @param int $row Número de fila
     * @param string $worksheetName Nombre de la hoja
     * @return bool True si debe leer la celda, False si no
     */
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Leer solo las filas dentro del rango establecido
        return ($row >= $this->startRow && $row < $this->endRow);
    }
}