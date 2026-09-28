<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\RendimientoHospitalario;

class RendimientoHospitalarioModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'rendimiento_hospitalario';
    protected $primaryKey       = 'rendimiento_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\RendimientoHospitalario::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'ejercicio',
        'semestre',
        'dias_func_servicio',
        'altas',
        'defuncion',
        'total_egresos',
        'pases_a_sala',
        'dias_estada',
        'paciente_dia',
        'cama_disponible',
        'promedio_cama_disp',
        'promedio_pcte_dia',
        'promedio_permanencia',
        'porcentaje_ocupacional',
        'estandares',
        'promedio_dias_estada',
        'tasa_mortalidad',
        'giro_cama',
    ];

    protected $useTimestamps = false;

    /**
     * Trae rendimiento_hospitalario con los datos del efector (nombre, region, nivel, etc.)
     * Uso: (new RendimientoHospitalarioModel())->conEfector()->findAll();
     */
    public function conEfector()
    {
        return $this->select('rendimiento_hospitalario.*, efector.nombre, efector.nivel_complejidad, efector.departamento, efector.ubicacion, efector.region')
                    ->join('efector', 'efector.efector_id = rendimiento_hospitalario.efector_id');
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
     * Uso: (new RendimientoHospitalarioModel())->conEfector()->porRegion('CENTRO')->findAll();
     */
    public function porRegion(string $region)
    {
        return $this->where('efector.region', $region);
    }

    /**
     * Filtra por ejercicio (año).
     */
    public function porEjercicio($ejercicio)
    {
        return $this->where('rendimiento_hospitalario.ejercicio', $ejercicio);
    }

    /**
     * Filtra por semestre (PRIMER / SEGUNDO / PRIMER TRIM., etc.).
     */
    public function porSemestre(string $semestre)
    {
        return $this->where('rendimiento_hospitalario.semestre', $semestre);
    }

    /**
     * ¿Ya existe un registro cargado para ese efector + ejercicio + semestre?
     * Útil antes de insertar, ya que la tabla tiene UNIQUE KEY sobre esos 3 campos.
     */
    public function existeRegistro(int $efectorId, $ejercicio, string $semestre, ?int $excluirId = null)
    {
        $q = $this->where('efector_id', $efectorId)
                  ->where('ejercicio', $ejercicio)
                  ->where('semestre', $semestre);

        if ($excluirId) {
            $q->where('rendimiento_id !=', $excluirId);
        }

        return $q->first();
    }
}