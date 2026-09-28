<?php
namespace App\Models;

use CodeIgniter\Model;

class EquipamientoHistorialModel extends Model
{
    protected $table      = 'equipamiento_historial';
    protected $primaryKey = 'historial_id';
    protected $returnType = \App\Entities\EquipamientoHistorial::class;

    protected $allowedFields = [
        'equipamiento_id',
        'electrodependiente_id',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'modificado_por',
        'created_at',
    ];

    protected $useTimestamps = false;
}