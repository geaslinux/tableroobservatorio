<?php
namespace App\Models;
use CodeIgniter\Model;
use App\Entities\UserLastVisit;

class UserLastVisitModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_last_visit';
    protected $primaryKey       = 'user_id';  // ← la PK real
    protected $useAutoIncrement = false;       // ← no es autoincrement
    protected $returnType       = UserLastVisit::class;
    protected $allowedFields    = ['user_id', 'modulo', 'ultima_vista'];
    protected $useTimestamps    = false;
}