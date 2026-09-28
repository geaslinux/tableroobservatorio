<?php
namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'categoria';
    protected $primaryKey       = 'categoria_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Categoria::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nombre'];

    protected $useTimestamps = false;
}