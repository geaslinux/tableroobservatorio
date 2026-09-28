<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\CapacidadCamas;

class CapacidadCamasModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'capacidad_camas';
    protected $primaryKey       = 'capacidad_camas_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\CapacidadCamas::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'tipo',
        'uti_adulto',
        'uti_coronario',
        'uti_pediatrico',
        'uti_neonatal',
        'total_uti',
        'total_uti_publicos',
        'utin_adulto',
        'utin_pediatrico',
        'utin_neonatal',
        'total_utin',
        'total_utin_publicos',
        'cb_adultos',
        'cb_pediatricos',
        'cb_neonatales',
        'total_basicas',
        'total_basicas_publicos',
        'camas_disponibles',
        'camas_disponibles_publicas',
        'camas_disponibles_publicas_salud_mental',
    ];

    protected $useTimestamps = false;

    /**
     * Trae la capacidad de camas con los datos del efector (nombre, region, nivel, etc.)
     * Uso: (new CapacidadCamasModel())->conEfector()->findAll();
     */
    public function conEfector()
    {
        return $this->select('capacidad_camas.*, efector.nombre, efector.nivel_complejidad, efector.departamento, efector.ubicacion, efector.region')
                    ->join('efector', 'efector.efector_id = capacidad_camas.efector_id');
    }

    /**
     * Trae la capacidad de camas de un efector puntual por su id.
     */
    public function porEfector(int $efectorId)
    {
        return $this->where('efector_id', $efectorId)->first();
    }

    /**
     * Filtra por region (CENTRO, VALLE, RAMAL I, RAMAL II, QUEBRADA, PUNA).
     * Uso: (new CapacidadCamasModel())->conEfector()->porRegion('CENTRO')->findAll();
     */
    public function porRegion(string $region)
    {
        return $this->where('efector.region', $region);
    }

    /**
     * Filtra por tipo de efector (PUBLICO / PRIVADO).
     */
    public function porTipo(string $tipo)
    {
        return $this->where('capacidad_camas.tipo', $tipo);
    }
}
