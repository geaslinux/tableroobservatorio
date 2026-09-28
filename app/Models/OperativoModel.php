<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Operativo;

class OperativoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'operativo';
    protected $primaryKey       = 'operativo_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Operativo::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre', 'tipo_id'];

    protected $useTimestamps = false;

    public function conTipo()
    {
        return $this->select('operativo.operativo_id, operativo.nombre, operativo.tipo_id, tipo_operativo.nombre AS tipo_nombre')
                    ->join('tipo_operativo', 'tipo_operativo.tipo_op_id = operativo.tipo_id', 'left');
    }
}