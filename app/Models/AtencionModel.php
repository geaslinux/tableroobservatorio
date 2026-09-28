<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Atencion;

class AtencionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'atencion';
    protected $primaryKey       = 'atencion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Atencion::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio',
        'mes',
        'atenciones_base',
        'asistidos_coberturas',
        'cantidad_coberturas',
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