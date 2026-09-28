<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Servicio;

class ServicioModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'servicio';
    protected $primaryKey       = 'servicio_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Servicio::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre', 'orden'
    ];

    protected $useTimestamps = false;
}