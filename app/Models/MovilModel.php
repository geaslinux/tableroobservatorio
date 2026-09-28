<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Movil;

class MovilModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'movil';
    protected $primaryKey       = 'movil_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Movil::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        
        'ejercicio',
        'tipo',
        'cantidad',
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