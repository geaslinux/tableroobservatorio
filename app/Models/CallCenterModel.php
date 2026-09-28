<?php
namespace App\Models;

use CodeIgniter\Model;

class CallCenterModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'call_center';
    protected $primaryKey       = 'call_center_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\CallCenter';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'mes', 'fecha',
        'atendidos', 'abandonadas', 'total',
        'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}