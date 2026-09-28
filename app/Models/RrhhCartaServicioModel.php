<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RrhhCartaServicio;

class RrhhCartaServicioModel extends Model
{
    protected $DBGroup          = 'base2';
    protected $table            = 'rrhh_carta_servicio';
    protected $primaryKey       = 'rrhh_id';
    protected $useAutoIncrement = true;
    protected $returnType       = RrhhCartaServicio::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'hospital',
        'nivel_complejidad',
        'region',
        'dni',
        'nombre_apellido',
        'profesion',
        'especialidad',
        'revista',
        'consultorio',
        'guardia_cargo',
        'telemedicina',
        'prosane',
        'carnet_sanitario',
    ];

    protected $useTimestamps = false;

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}