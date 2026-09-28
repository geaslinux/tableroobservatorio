<?php
namespace App\Models;

use CodeIgniter\Model;

class GestionPacienteHospitalModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'gestion_cama_hospitales';
    protected $primaryKey       = 'gestion_cama_hospitales_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\GestionPacienteHospital';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'departamento_id',
        'zona',
        'cuidados',
        'nivel_riesgo',
        'proceso',
        'tipo_cama',
        'tiempo_estancia',
        'observacion',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── JOINs con efector y departamento ─────────────────────────────────
    public function conRelaciones()
    {
        return $this->select('
                gestion_cama_hospitales.*,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                departamento.nombre       AS departamento_nombre
            ')
            ->join('efector',      'efector.efector_id = gestion_cama_hospitales.efector_id',               'left')
            ->join('departamento', 'departamento.departamento_id = gestion_cama_hospitales.departamento_id', 'left');
    }
}