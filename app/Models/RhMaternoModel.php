<?php
namespace App\Models;

use CodeIgniter\Model;

class RhMaternoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'rh_materno';
    protected $primaryKey       = 'rh_materno_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\RhMaterno';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'ejercicio',
        'semestre',
        'servicio',
        'sector',
        'dias_funcionamiento_servicio',
        'ingresos',
        'pases_de',
        'altas',
        'defuncion',
        'total_egresos',
        'pases_a',
        'paciente_dia',
        'cama_disponible',
        'dias_estada',
        'promedio_cama_disponible',
        'promedio_paciente_dia',
        'promedio_dias_estada',
        'promedio_permanencia',
        'porcentaje_ocupacional',
        'estandares',
        'tasa_mortalidad',
        'giro_cama',
        'giro_sustitucion',
        'egresos_por_dia',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── JOIN con efector ────────────────────────────────────────────────
    public function conEfector()
    {
        return $this->select('
                rh_materno.*,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                efector.departamento      AS efector_departamento,
                efector.region            AS efector_region
            ')
            ->join('efector', 'efector.efector_id = rh_materno.efector_id', 'left');
    }

    // ── Evita duplicar el mismo servicio/sector para un efector-ejercicio-semestre ──
    public function existeRegistro($efectorId, $ejercicio, $semestre, $servicio, $sector, $excludeId = null)
    {
        $builder = $this->where('efector_id', $efectorId)
                         ->where('ejercicio', $ejercicio)
                         ->where('semestre', $semestre)
                         ->where('servicio', $servicio)
                         ->where('sector', $sector);

        if ($excludeId) {
            $builder->where('rh_materno_id !=', $excludeId);
        }

        return $builder->first() !== null;
    }
}