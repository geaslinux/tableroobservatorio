<?php

namespace App\Services;

use App\Models\RepositoriosModel;
use App\Models\ProcesoDepuracionModel;
use App\Models\DepuracionesModel;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use App\Services\ChunkReadFilter;

class DepuracionQueueService
{
    private $procesoModel;
    private $archivoModel;
    private $depuracionModel;
    private $db;
    
    public function __construct()
    {
        $this->procesoModel = new ProcesoDepuracionModel();
        $this->archivoModel = new RepositoriosModel();
        $this->depuracionModel = new DepuracionesModel();
        $this->db = \Config\Database::connect();
    }
    
    public function crearTrabajo($archivoId, $usuarioId, $configuracion)
    {
        $data = [
            'archivo_id' => $archivoId,
            'usuario_id' => $usuarioId,
            'estado' => 'pendiente',
            'configuracion' => json_encode($configuracion),
            'progreso' => 0,
            'filas_totales' => 0,
            'filas_procesadas' => 0
        ];
        
        $procesoId = $this->procesoModel->insert($data);
        
        if (!$procesoId) {
            throw new \Exception('No se pudo crear el proceso de depuración');
        }
        
        $this->ejecutarEnBackground($procesoId);
        
        return $procesoId;
    }
    
    private function ejecutarEnBackground($procesoId)
    {
        $command = "php " . FCPATH . "../spark depurador:procesar {$procesoId}";
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B {$command} > NUL 2>&1", "r"));
        } else {
            exec("{$command} > /dev/null 2>&1 &");
        }
    }
    
    /**
     * ⭐ ACTUALIZACIÓN DIRECTA CON SQL - MEJORADA CON MEJOR LOGGING
     */
    private function actualizarEstadoDirecto($procesoId, $data)
    {
        $sets = [];
        $binds = [];
        
        foreach ($data as $campo => $valor) {
            $sets[] = "{$campo} = ?";
            $binds[] = $valor;
        }
        
        $sets[] = "updated_at = NOW()";
        $binds[] = $procesoId;
        
        $sql = "UPDATE proceso_depuracion SET " . implode(', ', $sets) . " WHERE proceso_id = ?";
        
        log_message('info', "[Depurador] 📝 SQL: {$sql}");
        log_message('info', "[Depurador] 📝 Bindings: " . json_encode($binds));
        
        try {
            $result = $this->db->query($sql, $binds);
            
            if ($result) {
                $affected = $this->db->affectedRows();
                log_message('info', "[Depurador] ✅ SQL OK - Filas afectadas: {$affected}");
                
                if ($affected === 0) {
                    log_message('warning', "[Depurador] ⚠️ Ninguna fila actualizada. ¿Proceso #{$procesoId} existe?");
                }
                
                return true;
            } else {
                $error = $this->db->error();
                log_message('error', "[Depurador] ❌ SQL FALLÓ: " . json_encode($error));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "[Depurador] ❌ EXCEPCIÓN SQL: " . $e->getMessage());
            return false;
        }
    }
    
    private function procesarConStreaming($rutaArchivo, $archivo, $config, $procesoId, $proceso)
    {
        $resultado = [
            'archivo_depurado_id' => null,
            'filas_originales' => 0,
            'filas_duplicadas' => 0,
            'filas_vacias' => 0,
            'filas_nulas' => 0,
            'filas_otras' => 0,
            'filas_finales' => 0,
            'columnas_originales' => 0,
            'columnas_finales' => 0,
            'columnas_conservadas' => [],
            'acciones_aplicadas' => []
        ];
        
        $nombreTemp = 'temp_' . uniqid() . '.csv';
        $rutaTemp = FCPATH . 'archivos/' . $nombreTemp;
        
        $handleOutput = fopen($rutaTemp, 'w');
        if (!$handleOutput) {
            throw new \Exception('No se pudo crear archivo temporal');
        }
        
        $esCSV = strpos($archivo->tipo_archivo, 'csv') !== false;
        $filasVistas = [];
        $numeroLineaOutput = 0;
        
        $columnasSeleccionadas = isset($config['seleccionar_columnas']) && $config['seleccionar_columnas'] == '1'
            ? ($config['columnas_seleccionadas'] ?? null)
            : null;
            
        $columnasClave = isset($config['eliminar_nulas']) && $config['eliminar_nulas'] == '1'
            ? ($config['columnas_clave'] ?? [])
            : [];
        
        if ($esCSV) {
            $handle = fopen($rutaArchivo, 'r');
            if (!$handle) {
                throw new \Exception('No se pudo abrir CSV');
            }
            
            $totalLineas = 0;
            while (!feof($handle)) {
                $buffer = fread($handle, 8192);
                $totalLineas += substr_count($buffer, "\n");
            }
            rewind($handle);
            
            $this->actualizarProgreso($procesoId, $totalLineas, 0);
            $resultado['filas_originales'] = $totalLineas;
            
            $numeroLinea = 0;
            $tiempoInicio = microtime(true);
            
            while (($fila = fgetcsv($handle, 0, $config['delimitador'] ?? ',')) !== false) {
                $numeroLinea++;
                
                if ($numeroLinea == 1) {
                    $resultado['columnas_originales'] = count($fila);
                }
                
                $filaData = $this->procesarFila($fila, $config, $columnasClave);
                $incluir = $this->debeIncluirFila($filaData, $config, $filasVistas, $resultado, $numeroLinea == 1);
                
                if ($incluir) {
                    $filaFinal = $this->seleccionarColumnas($filaData['valores'], $columnasSeleccionadas);
                    $filaFinal = $this->agregarColumnasAdicionales($filaFinal, $config, $numeroLinea, $numeroLineaOutput);
                    
                    fputcsv($handleOutput, $filaFinal);
                    $numeroLineaOutput++;
                }
                
                if ($numeroLinea % 100 == 0) {
                    $progreso = ($numeroLinea / $totalLineas) * 100;
                    $tiempoTranscurrido = microtime(true) - $tiempoInicio;
                    $tiempoEstimado = $totalLineas > 0 
                        ? (int)(($tiempoTranscurrido / $numeroLinea) * ($totalLineas - $numeroLinea))
                        : 0;
                    
                    $this->actualizarProgreso($procesoId, $totalLineas, $numeroLinea, $tiempoEstimado);
                    
                    if ($numeroLinea % 1000 == 0) {
                        gc_collect_cycles();
                    }
                }
            }
            
            fclose($handle);
            
        } else {
            $reader = new XlsxReader();
            $reader->setReadDataOnly(true);
            
            $spreadsheetTemp = $reader->load($rutaArchivo);
            $hojasSeleccionadas = $config['hojas_seleccionadas'] ?? [0];
            $sheet = $spreadsheetTemp->getSheet($hojasSeleccionadas[0]);
            $totalRows = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            $resultado['filas_originales'] = $totalRows;
            $resultado['columnas_originales'] = $highestColumnIndex;
            
            $spreadsheetTemp->disconnectWorksheets();
            unset($spreadsheetTemp);
            gc_collect_cycles();
            
            $this->actualizarProgreso($procesoId, $totalRows, 0);
            
            $chunkSize = 50;
            $chunkFilter = new ChunkReadFilter();
            $reader->setReadFilter($chunkFilter);
            
            $numeroLinea = 0;
            $tiempoInicio = microtime(true);
            
            for ($startRow = 1; $startRow <= $totalRows; $startRow += $chunkSize) {
                $chunkFilter->setRows($startRow, $chunkSize);
                $spreadsheetChunk = $reader->load($rutaArchivo);
                $sheetChunk = $spreadsheetChunk->getSheet($hojasSeleccionadas[0]);
                
                $endRow = min($startRow + $chunkSize, $totalRows + 1);
                
                for ($row = $startRow; $row < $endRow; $row++) {
                    $numeroLinea++;
                    
                    $fila = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $fila[] = $sheetChunk->getCell($colLetter . $row)->getValue();
                    }
                    
                    $filaData = $this->procesarFila($fila, $config, $columnasClave);
                    $incluir = $this->debeIncluirFila($filaData, $config, $filasVistas, $resultado, $row == 1);
                    
                    if ($row == 1 && $startRow > 1) {
                        $incluir = false;
                    }
                    
                    if ($incluir) {
                        $filaFinal = $this->seleccionarColumnas($filaData['valores'], $columnasSeleccionadas);
                        $filaFinal = $this->agregarColumnasAdicionales($filaFinal, $config, $row, $numeroLineaOutput);
                        
                        fputcsv($handleOutput, $filaFinal);
                        $numeroLineaOutput++;
                    }
                }
                
                $progreso = ($numeroLinea / $totalRows) * 100;
                $tiempoTranscurrido = microtime(true) - $tiempoInicio;
                $tiempoEstimado = $totalRows > 0 
                    ? (int)(($tiempoTranscurrido / $numeroLinea) * ($totalRows - $numeroLinea))
                    : 0;
                
                $this->actualizarProgreso($procesoId, $totalRows, $numeroLinea, $tiempoEstimado);
                
                $spreadsheetChunk->disconnectWorksheets();
                unset($spreadsheetChunk);
                gc_collect_cycles();
            }
        }
        
        fclose($handleOutput);
        $resultado['filas_finales'] = $numeroLineaOutput;
        
        // ⭐ ACTUALIZAR A 100% ANTES DE CONVERTIR
        log_message('info', "[Depurador] 📊 Procesamiento completado. Actualizando a 100%...");
        $this->actualizarProgreso($procesoId, $resultado['filas_originales'], $resultado['filas_originales'], 0);
        
        // Convertir CSV a Excel
        $nombreLimpio = pathinfo($archivo->nombre_original, PATHINFO_FILENAME) . '_DEPURADO_' . date('YmdHis') . '.xlsx';
        $rutaLimpio = FCPATH . 'archivos/' . $nombreLimpio;
        
        log_message('info', "[Depurador] 📄 Convirtiendo a Excel: {$rutaLimpio}");
        $this->convertirCSVaExcel($rutaTemp, $rutaLimpio);
        log_message('info', "[Depurador] ✅ Conversión completada");
        
        @unlink($rutaTemp);
        
        // Calcular columnas finales
        if ($columnasSeleccionadas !== null) {
            $resultado['columnas_finales'] = count($columnasSeleccionadas);
            $resultado['columnas_conservadas'] = $columnasSeleccionadas;
        } else {
            $resultado['columnas_finales'] = $resultado['columnas_originales'];
        }
        
        if (isset($config['agregar_id']) && $config['agregar_id'] == '1') {
            $resultado['columnas_finales']++;
        }
        if (isset($config['agregar_fecha_proceso']) && $config['agregar_fecha_proceso'] == '1') {
            $resultado['columnas_finales']++;
        }
        
        // Registrar archivo depurado
        $archivoLimpioData = [
            'nombre_original' => $nombreLimpio,
            'nombre_sistema' => $nombreLimpio,
            'ruta_archivo' => 'archivos/' . $nombreLimpio,
            'tipo_archivo' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'tamano' => filesize($rutaLimpio),
            'categoria_id' => $archivo->categoria_id,
            'subcategoria_id' => $archivo->subcategoria_id,
            'reparticion_id' => $archivo->reparticion_id,
            'referencia' => 'DEPURADO: ' . $archivo->referencia,
            'descripcion' => 'Archivo depurado de: ' . $archivo->nombre_original,
            'estado' => 'activo',
            'created_by' => $proceso->usuario_id,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $resultado['archivo_depurado_id'] = $this->archivoModel->insert($archivoLimpioData);
        
        if (!$resultado['archivo_depurado_id']) {
            log_message('error', "[Depurador] ❌ No se pudo registrar el archivo depurado");
            throw new \Exception('No se pudo registrar el archivo depurado en la base de datos');
        }
        
        log_message('info', "[Depurador] ✅ Archivo registrado con ID: {$resultado['archivo_depurado_id']}");
        
        return $resultado;
    }
    
    private function procesarFila($fila, $config, $columnasClave)
    {
        $valores = [];
        $vacia = true;
        $tieneNulos = false;
        
        foreach ($fila as $index => $valor) {
            if (isset($config['eliminar_espacios']) && $config['eliminar_espacios'] == '1') {
                $valor = is_string($valor) ? trim($valor) : $valor;
            }
            
            if (isset($config['convertir_texto']) && $config['convertir_texto'] != '' && is_string($valor)) {
                switch ($config['convertir_texto']) {
                    case 'mayusculas': $valor = strtoupper($valor); break;
                    case 'minusculas': $valor = strtolower($valor); break;
                    case 'capitalizado': $valor = ucwords(strtolower($valor)); break;
                }
            }
            
            $valores[] = $valor;
            
            if (!empty($valor) && $valor !== null && $valor !== '') {
                $vacia = false;
            }
            
            if (!empty($columnasClave) && in_array($index, $columnasClave)) {
                if (empty($valor) || $valor === null || $valor === '') {
                    $tieneNulos = true;
                }
            }
        }
        
        return [
            'valores' => $valores,
            'vacia' => $vacia,
            'tieneNulos' => $tieneNulos
        ];
    }
    
    private function debeIncluirFila($filaData, $config, &$filasVistas, &$resultado, $esHeader)
    {
        if ($esHeader) return true;
        
        if (isset($config['eliminar_filas_vacias']) && $config['eliminar_filas_vacias'] == '1' && $filaData['vacia']) {
            $resultado['filas_vacias']++;
            return false;
        }
        
        if ($filaData['tieneNulos'] && isset($config['eliminar_nulas']) && $config['eliminar_nulas'] == '1') {
            $resultado['filas_nulas']++;
            return false;
        }
        
        if (isset($config['eliminar_duplicadas']) && $config['eliminar_duplicadas'] == '1') {
            $hash = md5(serialize($filaData['valores']));
            if (in_array($hash, $filasVistas)) {
                $resultado['filas_duplicadas']++;
                return false;
            }
            $filasVistas[] = $hash;
        }
        
        return true;
    }
    
    private function seleccionarColumnas($valores, $columnasSeleccionadas)
    {
        if ($columnasSeleccionadas === null) return $valores;
        
        $resultado = [];
        foreach ($valores as $index => $valor) {
            if (in_array($index, $columnasSeleccionadas)) {
                $resultado[] = $valor;
            }
        }
        return $resultado;
    }
    
    private function agregarColumnasAdicionales($fila, $config, $numeroLineaOriginal, $numeroLineaOutput)
    {
        if (isset($config['agregar_id']) && $config['agregar_id'] == '1') {
            $fila[] = $numeroLineaOriginal == 1 ? 'ID_AUTO' : $numeroLineaOutput;
        }
        
        if (isset($config['agregar_fecha_proceso']) && $config['agregar_fecha_proceso'] == '1') {
            $fila[] = $numeroLineaOriginal == 1 ? 'FECHA_PROCESO' : date('Y-m-d H:i:s');
        }
        
        return $fila;
    }
    
    private function convertirCSVaExcel($rutaCSV, $rutaExcel)
    {
        try {
            $reader = new Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(',');
            
            $spreadsheet = $reader->load($rutaCSV);
            
            $writer = new XlsxWriter($spreadsheet);
            $writer->save($rutaExcel);
            
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            gc_collect_cycles();
            
            if (!file_exists($rutaExcel)) {
                throw new \Exception("El archivo Excel no se generó");
            }
            
            $tamano = filesize($rutaExcel);
            log_message('info', "[Depurador] Excel generado: {$tamano} bytes");
            
        } catch (\Exception $e) {
            log_message('error', "[Depurador] Error convirtiendo: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function actualizarProgreso($procesoId, $total, $procesadas, $tiempoEstimado = null)
    {
        $progreso = $total > 0 ? ($procesadas / $total) * 100 : 0;
        
        $data = [
            'progreso' => round($progreso, 2),
            'filas_totales' => $total,
            'filas_procesadas' => $procesadas,
        ];
        
        if ($tiempoEstimado !== null) {
            $data['tiempo_estimado'] = $tiempoEstimado;
        }
        
        $this->actualizarEstadoDirecto($procesoId, $data);
    }
    
    private function registrarDepuracion($proceso, $resultado)
    {
        $config = json_decode($proceso->configuracion, true);
        
        $depuracionData = [
            'archivo_original_id' => $proceso->archivo_id,
            'archivo_depurado_id' => $resultado['archivo_depurado_id'],
            'hojas_procesadas' => json_encode($config['hojas_seleccionadas'] ?? []),
            'modo_procesamiento' => 'queue',
            'filas_originales' => $resultado['filas_originales'],
            'filas_duplicadas_eliminadas' => $resultado['filas_duplicadas'],
            'filas_vacias_eliminadas' => $resultado['filas_vacias'],
            'filas_nulas_eliminadas' => $resultado['filas_nulas'],
            'filas_otras_eliminadas' => $resultado['filas_otras'],
            'filas_finales' => $resultado['filas_finales'],
            'columnas_originales' => $resultado['columnas_originales'],
            'columnas_finales' => $resultado['columnas_finales'],
            'columnas_conservadas' => json_encode($resultado['columnas_conservadas']),
            'acciones_aplicadas' => json_encode($resultado['acciones_aplicadas'] ?? []),
            'configuracion_completa' => $proceso->configuracion,
            'estado' => 'completado',
            'tiempo_procesamiento' => strtotime($proceso->tiempo_fin) - strtotime($proceso->tiempo_inicio),
            'created_by' => $proceso->usuario_id,
        ];
        
        $this->depuracionModel->insert($depuracionData);
    }
    
    public function obtenerEstado($procesoId)
    {
        return $this->procesoModel->find($procesoId);
    }
    
    public function procesarDepuracion($procesoId)
    {
        $proceso = $this->procesoModel->find($procesoId);
        
        if (!$proceso) {
            throw new \Exception("Proceso #{$procesoId} no encontrado");
        }
        
        if ($proceso->estado !== 'pendiente') {
            throw new \Exception("Proceso #{$procesoId} ya está en estado: {$proceso->estado}");
        }
        
        log_message('info', "[Depurador] " . str_repeat("=", 50));
        log_message('info', "[Depurador] 🚀 INICIANDO PROCESO #{$procesoId}");
        log_message('info', "[Depurador] " . str_repeat("=", 50));
        
        // Actualizar a "procesando"
        $this->actualizarEstadoDirecto($procesoId, [
            'estado' => 'procesando',
            'tiempo_inicio' => date('Y-m-d H:i:s')
        ]);
        
        try {
            $archivo = $this->archivoModel->find($proceso->archivo_id);
            
            if (!$archivo) {
                throw new \Exception('Archivo no encontrado');
            }
            
            $rutaArchivo = FCPATH . $archivo->ruta_archivo;
            
            if (!file_exists($rutaArchivo)) {
                throw new \Exception("Archivo físico no encontrado");
            }
            
            log_message('info', "[Depurador] 📁 Archivo: {$archivo->nombre_original}");
            
            $config = json_decode($proceso->configuracion, true);
            
            if (!$config) {
                throw new \Exception('Configuración inválida');
            }
            
            log_message('info', "[Depurador] ⚙️ Iniciando procesamiento...");
            
            // ⭐ PROCESAR
            $resultado = $this->procesarConStreaming($rutaArchivo, $archivo, $config, $procesoId, $proceso);
            
            log_message('info', "[Depurador] ✅ Procesamiento completado");
            log_message('info', "[Depurador] 📦 Archivo depurado ID: {$resultado['archivo_depurado_id']}");
            
            // ⭐⭐⭐ ACTUALIZACIÓN CRÍTICA A "COMPLETADO" ⭐⭐⭐
            $tiempoFin = date('Y-m-d H:i:s');
            
            log_message('info', "[Depurador] " . str_repeat("=", 50));
            log_message('info', "[Depurador] 🎯 INICIANDO ACTUALIZACIÓN FINAL A COMPLETADO");
            log_message('info', "[Depurador] 🎯 Proceso ID: {$procesoId}");
            log_message('info', "[Depurador] 🎯 Archivo depurado ID: {$resultado['archivo_depurado_id']}");
            log_message('info', "[Depurador] " . str_repeat("=", 50));
            
            // ⭐ MÉTODO 1: Usando query directo (MÁS CONFIABLE)
            $resultadoJson = json_encode($resultado);
            
            log_message('info', "[Depurador] 📝 JSON resultado (primeros 200 chars): " . substr($resultadoJson, 0, 200));
            
            $sqlDirecto = "UPDATE proceso_depuracion 
                          SET estado = 'completado',
                              progreso = 100.00,
                              tiempo_fin = ?,
                              resultado_json = ?,
                              updated_at = NOW()
                          WHERE proceso_id = ?";
            
            log_message('info', "[Depurador] 🔧 Ejecutando SQL directo para cambiar a completado...");
            
            $resultadoUpdate = $this->db->query($sqlDirecto, [
                $tiempoFin,
                $resultadoJson,
                $procesoId
            ]);
            
            if (!$resultadoUpdate) {
                $error = $this->db->error();
                log_message('error', "[Depurador] ❌ SQL UPDATE FALLÓ: " . json_encode($error));
                throw new \Exception('Fallo al actualizar estado a completado: ' . json_encode($error));
            }
            
            $filasAfectadas = $this->db->affectedRows();
            log_message('info', "[Depurador] ✅ SQL ejecutado - Filas afectadas: {$filasAfectadas}");
            
            if ($filasAfectadas === 0) {
                log_message('error', "[Depurador] ⚠️ ADVERTENCIA: No se actualizó ninguna fila. ¿El proceso existe?");
                
                // Verificar que el proceso existe
                $existe = $this->db->query("SELECT proceso_id, estado FROM proceso_depuracion WHERE proceso_id = ?", [$procesoId])->getRow();
                log_message('info', "[Depurador] 🔍 Proceso en BD: " . json_encode($existe));
            }
            
            // ⭐ VERIFICACIÓN INMEDIATA (sin sleep)
            log_message('info', "[Depurador] 🔍 Verificando actualización...");
            
            // Limpiar cualquier caché del modelo
            $this->procesoModel->builder()->resetQuery();
            
            $procesoVerificado = $this->procesoModel->find($procesoId);
            
            if (!$procesoVerificado) {
                log_message('error', "[Depurador] ❌ CRÍTICO: No se pudo recuperar el proceso #{$procesoId}");
                throw new \Exception("No se pudo verificar el proceso #{$procesoId}");
            }
            
            log_message('info', "[Depurador] 📊 Estado después de actualización:");
            log_message('info', "[Depurador]   ├─ Estado: {$procesoVerificado->estado}");
            log_message('info', "[Depurador]   ├─ Progreso: {$procesoVerificado->progreso}%");
            log_message('info', "[Depurador]   ├─ Tiempo fin: {$procesoVerificado->tiempo_fin}");
            log_message('info', "[Depurador]   └─ Resultado JSON: " . (empty($procesoVerificado->resultado_json) ? '❌ VACÍO' : '✅ PRESENTE (' . strlen($procesoVerificado->resultado_json) . ' chars)'));
            
            // ⭐ SI FALLÓ, INTENTAR CON MÉTODO ALTERNATIVO
            if ($procesoVerificado->estado !== 'completado') {
                log_message('error', "[Depurador] ⚠️⚠️⚠️ CRÍTICO: Estado actual es '{$procesoVerificado->estado}' en lugar de 'completado'");
                log_message('info', "[Depurador] 🔄 Intentando método alternativo...");
                
                // Método 2: Sin resultado_json (quizás es muy grande)
                $sqlSimple = "UPDATE proceso_depuracion 
                             SET estado = 'completado',
                                 progreso = 100.00,
                                 tiempo_fin = NOW(),
                                 updated_at = NOW()
                             WHERE proceso_id = ?";
                
                $this->db->query($sqlSimple, [$procesoId]);
                
                // Esperar un momento
                usleep(500000); // 0.5 segundos
                
                // Verificar nuevamente
                $procesoVerificado = $this->procesoModel->find($procesoId);
                
                log_message('info', "[Depurador] 📊 Estado después de método alternativo: {$procesoVerificado->estado}");
                
                if ($procesoVerificado->estado !== 'completado') {
                    // Último intento: forzar con transacción
                    log_message('error', "[Depurador] ⚠️ Método alternativo falló. Último intento con transacción...");
                    
                    $this->db->transBegin();
                    $this->db->query("UPDATE proceso_depuracion SET estado = 'completado', progreso = 100.00 WHERE proceso_id = ?", [$procesoId]);
                    $this->db->transCommit();
                    
                    usleep(500000);
                    $procesoVerificado = $this->procesoModel->find($procesoId);
                    
                    if ($procesoVerificado->estado !== 'completado') {
                        log_message('error', "[Depurador] ❌❌❌ TODOS LOS MÉTODOS FALLARON");
                        log_message('error', "[Depurador] Estado final: {$procesoVerificado->estado}");
                        throw new \Exception("FALLO CRÍTICO: Imposible cambiar estado a 'completado'. Estado actual: {$procesoVerificado->estado}");
                    }
                }
                
                // Si llegamos aquí, el método alternativo funcionó
                // Ahora actualizar el resultado_json por separado
                log_message('info', "[Depurador] ✅ Estado cambiado a completado. Actualizando resultado_json...");
                $this->db->query("UPDATE proceso_depuracion SET resultado_json = ? WHERE proceso_id = ?", [$resultadoJson, $procesoId]);
            }
            
            log_message('info', "[Depurador] ✅✅✅ Estado COMPLETADO confirmado exitosamente");
            
            // Recargar proceso final
            $proceso = $this->procesoModel->find($procesoId);
            
            // Registrar en historial
            log_message('info', "[Depurador] 📝 Registrando en historial...");
            $this->registrarDepuracion($proceso, $resultado);
            
            log_message('info', "[Depurador] " . str_repeat("=", 50));
            log_message('info', "[Depurador] 🎉 PROCESO #{$procesoId} FINALIZADO EXITOSAMENTE");
            log_message('info', "[Depurador] " . str_repeat("=", 50));
            
            return $resultado;
            
        } catch (\Exception $e) {
            log_message('error', "[Depurador] ❌ ERROR: " . $e->getMessage());
            log_message('error', "[Depurador] Trace: " . $e->getTraceAsString());
            
            $this->actualizarEstadoDirecto($procesoId, [
                'estado' => 'error',
                'tiempo_fin' => date('Y-m-d H:i:s'),
                'mensaje_error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}