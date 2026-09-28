<?php
namespace App\Models;

use CodeIgniter\Model;

class EspecialidadModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'especialidad';
    protected $primaryKey       = 'especialidad_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre'];
    protected $useTimestamps    = false;
}