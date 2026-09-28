<?php
namespace App\Models;

use CodeIgniter\Model;

class ElectrodependienteHistorialModel extends Model
{
    protected $table      = 'electrodependiente_historial';
    protected $primaryKey = 'historial_id';
    protected $returnType = \App\Entities\ElectrodependienteHistorial::class;

    protected $allowedFields = [
        'electrodependiente_id',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'modificado_por',
        'created_at',
    ];

    protected $useTimestamps = false;
}