<?php
namespace App\Models;

use CodeIgniter\Model;

class GestionCamaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'gestion_cama';
    protected $primaryKey       = 'gestion_cama_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\GestionCama';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'tipo_establecimiento',
        'tipo_gestion',
        'region',
        'cuidados_basicos_adultos',
        'cuidados_basicos_pediatricos',
        'observacion',
        'estado',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ── JOIN con efector ─────────────────────────────────────────────────
    public function conRelaciones()
    {
        return $this->select('
                gestion_cama.*,
                (gestion_cama.cuidados_basicos_adultos + gestion_cama.cuidados_basicos_pediatricos)
                    AS total_cuidados_basicos,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel
            ')
            ->join('efector', 'efector.efector_id = gestion_cama.efector_id', 'left');
    }
}