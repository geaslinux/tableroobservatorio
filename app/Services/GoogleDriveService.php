<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GoogleDriveService
{
    protected $service;
    protected $folderId;

    // Nombre fijo del archivo en Drive
    const ARCHIVO_DRIVE = 'cantidad_operativos.xlsx';

public function __construct()
{
    $client = new Client();
    
    // Deshabilitar logger para evitar conflicto con Monolog
    $client->setLogger(new \Psr\Log\NullLogger());
    
    $client->setAuthConfig('C:/laragon/www/sistemabase/app/Config/google-credentials.json');
    $client->addScope(Drive::DRIVE_FILE);
    $client->addScope(Drive::DRIVE);

    $this->service  = new Drive($client);
    $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID');
}

    /**
     * Busca el archivo maestro en Drive por nombre.
     * Retorna el ID si existe, null si no.
     */
    public function buscarArchivo(string $nombre): ?string
    {
        $q = "name='{$nombre}' and '{$this->folderId}' in parents and trashed=false";

        $resultado = $this->service->files->listFiles([
            'q'      => $q,
            'fields' => 'files(id, name)',
        ]);

        $files = $resultado->getFiles();
        return count($files) > 0 ? $files[0]->getId() : null;
    }

    /**
     * Descarga el archivo de Drive y lo guarda en una ruta local temporal.
     */
    public function descargarArchivo(string $fileId, string $rutaLocal): void
    {
        $response = $this->service->files->get($fileId, ['alt' => 'media']);
        file_put_contents($rutaLocal, $response->getBody()->getContents());
    }

    /**
     * Actualiza el contenido de un archivo existente en Drive.
     */
    public function actualizarArchivo(string $fileId, string $rutaLocal, string $mimeType): void
    {
        $emptyFile = new DriveFile();

        $this->service->files->update($fileId, $emptyFile, [
            'data'       => file_get_contents($rutaLocal),
            'mimeType'   => $mimeType,
            'uploadType' => 'multipart',
        ]);
    }

    /**
     * Sube un archivo nuevo a Drive.
     */
    public function subirArchivo(string $rutaLocal, string $nombre, string $mimeType): string
    {
        $metadata = new DriveFile([
            'name'    => $nombre,
            'parents' => [$this->folderId],
        ]);

        $archivo = $this->service->files->create($metadata, [
            'data'       => file_get_contents($rutaLocal),
            'mimeType'   => $mimeType,
            'uploadType' => 'multipart',
            'fields'     => 'id, name, webViewLink',
        ]);

        return $archivo->webViewLink;
    }
}