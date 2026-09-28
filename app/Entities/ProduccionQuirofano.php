<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProduccionQuirofano extends Entity
{
    protected $attributes = [
        'produccion_quirofano_id' => null,
        'efector_id'              => null,
        'ejercicio'               => null,
        'produccion'              => null,
    ];

    protected $casts = [
        'produccion_quirofano_id' => 'integer',
        'efector_id'              => 'integer',
        'ejercicio'               => 'integer',
        'produccion'              => 'integer',
    ];
}