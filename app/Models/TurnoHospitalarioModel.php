<?php
namespace App\Models;

use CodeIgniter\Model;

class TurnoHospitalarioModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'turno_hospitalario';
    protected $primaryKey       = 'turno_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\TurnoHospitalario';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'mes', 'efector_id',
        'turnos_atendidos', 'ausentes', 'cancelados', 'sin_codificar',
        'total_otorgados', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function conRelaciones()
    {
        return $this->select('
                turno_hospitalario.*,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                efector.region            AS efector_region
            ')
            ->join('efector', 'efector.efector_id = turno_hospitalario.efector_id', 'left');
    }
}