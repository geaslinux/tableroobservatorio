<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\CantidadOperativo;

class CantidadOperativoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'cantidad_operativo';
    protected $primaryKey       = 'cantidad_id';
    protected $useAutoIncrement = true;
    protected $returnType       = CantidadOperativo::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'operativo_id',
        'via_publica', 'via_hospitalaria',
        'total', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function conRelaciones()
    {
        return $this->select('
                cantidad_operativo.*,
                operativo.nombre        AS operativo_nombre,
                tipo_operativo.nombre   AS tipo_nombre
            ')
            ->join('operativo',      'operativo.operativo_id = cantidad_operativo.operativo_id', 'left')
            ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left');
    }
}