<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\CartaServicio;

class CartaServicioModel extends Model
{
    protected $DBGroup          = 'base2';
    protected $table            = 'carta_servicio';
    protected $primaryKey       = 'carta_servicio_id';
    protected $useAutoIncrement = true;
    protected $returnType       = CartaServicio::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'hospital',
        'nivel_complejidad',
        'region',
        'tipo',
        'profesion',
        'especialidad',
        'tipo_profesional',
        'profesional',
        'dia_atencion',
        'horario_atencion',
        'turno',
        'cant_turnos',
    ];

    protected $useTimestamps = false;

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}