<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Asistencia;

class AsistenciaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'asistencia';
    protected $primaryKey       = 'asistencia_id';
    protected $useAutoIncrement = true;
    protected $returnType       = Asistencia::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ejercicio', 'mes', 'operativa', 'nro', 'nombre',
        'emergencias_con_medico', 'emergencias_sin_medico',
        'urgencias', 'derivacion_publica', 'derivacion_privada',
        'internacion_domiciliaria', 'total', 'estado'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}