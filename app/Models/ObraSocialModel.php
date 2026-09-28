<?php
namespace App\Models;
use CodeIgniter\Model;

class ObraSocialModel extends Model
{
    protected $table            = 'obra_social';
    protected $primaryKey       = 'obra_social_id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['nombre'];
    protected $useTimestamps    = false;
}