<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ProduccionQuirofanoHosp;

class ProduccionQuirofanoHospModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'produccion_quirofano_hosp';
    protected $primaryKey       = 'produccion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\ProduccionQuirofanoHosp::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'ejercicio',
        'quirofanos_disponibles',
        'quirofanos_urgencias',
        'quirofanos_programadas',
        'cirugias_urgencia',
        'cirugias_prog_alta',
        'cirugias_prog_mediana',
        'cirugias_prog_baja',
        'cirugias_prog_desconocido',
        'total_cirugias_programadas',
        'sub_total',
        'porcentaje_provincia',
        'observacion',
    ];

    protected $useTimestamps = false;

    // Útil para traer nombre de efector en un join
    public function withNombres()
    {
        return $this->select('produccion_quirofano_hosp.*, efector.nombre as efector_nombre')
                    ->join('efector', 'efector.efector_id = produccion_quirofano_hosp.efector_id');
    }
}