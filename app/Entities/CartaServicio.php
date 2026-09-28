<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class CartaServicio extends Entity
{
    protected $casts = [
        'cant_turnos' => 'integer',
    ];
}