<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ProduccionQuirofano;

class ProduccionQuirofanoModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'produccion_quirofano';
    protected $primaryKey       = 'produccion_quirofano_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\ProduccionQuirofano::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id', 'ejercicio', 'produccion'
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'efector_id' => 'required|is_natural_no_zero',
        'ejercicio'  => 'required|is_natural_no_zero',
        'produccion' => 'permit_empty|is_natural',
    ];

    // ── Join con efector, para traer nombre/nivel/departamento/etc ────────
    public function withNombres()
    {
        return $this
            ->select('produccion_quirofano.*, efector.nombre as efector_nombre, efector.nivel_complejidad, efector.departamento, efector.ubicacion, efector.region')
            ->join('efector', 'efector.efector_id = produccion_quirofano.efector_id', 'left');
    }
}