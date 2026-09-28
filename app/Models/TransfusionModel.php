<?php
namespace App\Models;

use CodeIgniter\Model;

class TransfusionModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'transfusion';
    protected $primaryKey       = 'transfusion_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Transfusion::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'efector_id',
        'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
        'total', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function conRelaciones()
    {
        return $this->select('
                transfusion.*,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                efector.departamento      AS efector_depto,
                efector.ubicacion         AS efector_ubicacion,
                efector.region            AS efector_region
            ')
            ->join('efector', 'efector.efector_id = transfusion.efector_id', 'left');
    }

    public function paraResumen()
    {
        return $this->select('
                transfusion.ejercicio,
                transfusion.total,
                efector.nombre            AS efector_nombre,
                efector.nivel_complejidad AS efector_nivel,
                efector.departamento      AS efector_depto,
                efector.ubicacion         AS efector_ubicacion,
                efector.region            AS efector_region
            ')
            ->join('efector', 'efector.efector_id = transfusion.efector_id', 'left')
            ->whereIn('transfusion.estado', ['activo', 'desactivado']);
    }
}