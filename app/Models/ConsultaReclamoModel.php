<?php
namespace App\Models;

use CodeIgniter\Model;

class ConsultaReclamoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'consulta_reclamo';
    protected $primaryKey       = 'consulta_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\ConsultaReclamo::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'mes', 'tipo_llamado',
        'categoria_id', 'sub_categoria_id',
        'atencion', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function conRelaciones()
    {
        return $this->select('
                consulta_reclamo.*,
                categoria.nombre     AS categoria_nombre,
                sub_categoria.nombre AS sub_categoria_nombre
            ')
            ->join('categoria',     'categoria.categoria_id = consulta_reclamo.categoria_id',         'left')
            ->join('sub_categoria', 'sub_categoria.sub_categoria_id = consulta_reclamo.sub_categoria_id', 'left');
    }
}