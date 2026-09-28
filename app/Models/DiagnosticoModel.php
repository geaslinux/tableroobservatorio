<?php
namespace App\Models;
use CodeIgniter\Model;

class DiagnosticoModel extends Model
{
    protected $table            = 'diagnostico';
    protected $primaryKey       = 'diagnostico_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['nombre'];
    protected $useTimestamps    = false;
}