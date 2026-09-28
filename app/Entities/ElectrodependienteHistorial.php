<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ElectrodependienteHistorial extends Entity
{
    protected $dates = ['created_at'];
    protected $casts = [
        'historial_id'          => 'integer',
        'electrodependiente_id' => 'integer',
        'modificado_por'        => '?integer',
    ];
}