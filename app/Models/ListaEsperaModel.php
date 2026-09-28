<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\ListaEspera;

class ListaEsperaModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'lista_espera';
    protected $primaryKey       = 'lista_espera_id';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\ListaEspera::class;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'efector_id',
        'ejercicio',
        'especialidad_id',
        'cantidad_pacientes',
        'comp_quirurgica_alta',
        'comp_quirurgica_mediana',
        'comp_quirurgica_baja',
    ];

    protected $useTimestamps = false;

    // Útil para traer nombre de efector y especialidad en un join
    public function withNombres()
    {
        return $this->select('lista_espera.*, efector.nombre as efector_nombre, especialidad.nombre as especialidad_nombre')
                    ->join('efector', 'efector.efector_id = lista_espera.efector_id')
                    ->join('especialidad', 'especialidad.especialidad_id = lista_espera.especialidad_id', 'left');
    }
}