<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EquipamientoHistorial extends Entity
{
    protected $dates = ['created_at'];
    protected $casts = [
        'historial_id'          => 'integer',
        'equipamiento_id'       => '?integer',
        'electrodependiente_id' => 'integer',
        'modificado_por'        => '?integer',
        'datos_anteriores'      => 'json-array',
        'datos_nuevos'          => 'json-array',
    ];
}