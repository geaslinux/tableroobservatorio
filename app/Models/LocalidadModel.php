<?php
namespace App\Models;
use CodeIgniter\Model;

class LocalidadModel extends Model
{
    protected $table            = 'localidad';
    protected $primaryKey       = 'localidad_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['nombre'];
    protected $useTimestamps    = false;
}