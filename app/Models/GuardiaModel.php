<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Guardia;

class GuardiaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'guardia';
    protected $primaryKey       = 'guardia_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Guardia::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id', 'servicio_id', 'anio', 'semestre', 'mes', 'cantidad'
    ];

    protected $useTimestamps = false;

    // Útil para traer nombre de efector/servicio en un join
    public function withNombres()
    {
        return $this->select('guardia.*, efector.nombre as efector_nombre, servicio.nombre as servicio_nombre')
                    ->join('efector', 'efector.efector_id = guardia.efector_id')
                    ->join('servicio', 'servicio.servicio_id = guardia.servicio_id');
    }
}