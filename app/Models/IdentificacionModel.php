<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Identificacion;

class IdentificacionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'identificacion';
    protected $primaryKey       = 'identificacion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Identificacion::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio',
        'departamento',
        'adulto',
        'pediatrico',
        'total',
        'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}