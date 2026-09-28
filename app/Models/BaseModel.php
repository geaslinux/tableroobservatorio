<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Base;

class BaseModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'base';
    protected $primaryKey       = 'base_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Base::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tipo',
        'nro',
        'nombre',
        'coordenadas',
        'ubicacion',
        'region',
        'provincia',
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