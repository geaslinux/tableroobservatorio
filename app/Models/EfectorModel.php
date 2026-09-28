<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Efector;

class EfectorModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'efector';
    protected $primaryKey       = 'efector_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Efector::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre', 'nivel_complejidad', 'departamento', 'ubicacion', 'region'
    ];

    protected $useTimestamps = false;
}