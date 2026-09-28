<?php
namespace App\Models;

use CodeIgniter\Model;

class SubCategoriaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'sub_categoria';
    protected $primaryKey       = 'sub_categoria_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'App\Entities\SubCategoria';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre'];

    protected $useTimestamps = false;
}