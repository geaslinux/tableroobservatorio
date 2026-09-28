<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\TipoOperativo;

class TipoOperativoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'tipo_operativo';
    protected $primaryKey       = 'tipo_op_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\TipoOperativo::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre'];

    protected $useTimestamps = false;
}