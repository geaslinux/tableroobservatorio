<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\SaludMentalCamas;

class SaludMentalCamasModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'salud_mental_camas';
    protected $primaryKey       = 'salud_mental_camas_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\SaludMentalCamas::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'modalidad',
        'tipo',
        'cb_adultos',
        'cb_pediatricos',
        'total_basicas',
    ];

    protected $useTimestamps = false;

    /**
     * Trae salud_mental_camas con los datos del efector (nombre, region, nivel, etc.)
     * Uso: (new SaludMentalCamasModel())->conEfector()->findAll();
     */
    public function conEfector()
    {
        return $this->select('salud_mental_camas.*, efector.nombre, efector.nivel_complejidad, efector.departamento, efector.ubicacion, efector.region')
                    ->join('efector', 'efector.efector_id = salud_mental_camas.efector_id');
    }

    /**
     * Trae los registros de un efector puntual por su id.
     */
    public function porEfector(int $efectorId)
    {
        return $this->where('efector_id', $efectorId);
    }

    /**
     * Filtra por region (CENTRO, VALLE, RAMAL I, RAMAL II, QUEBRADA, PUNA).
     * Uso: (new SaludMentalCamasModel())->conEfector()->porRegion('CENTRO')->findAll();
     */
    public function porRegion(string $region)
    {
        return $this->where('efector.region', $region);
    }

    /**
     * Filtra por tipo (PUBLICO / PRIVADO).
     */
    public function porTipo(string $tipo)
    {
        return $this->where('salud_mental_camas.tipo', $tipo);
    }

    /**
     * Filtra por modalidad (RESIDENCIAL / INTERNACION).
     */
    public function porModalidad(string $modalidad)
    {
        return $this->where('salud_mental_camas.modalidad', $modalidad);
    }
}