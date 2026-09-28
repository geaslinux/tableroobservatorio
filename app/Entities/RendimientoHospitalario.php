<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class RendimientoHospitalario extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'rendimiento_id'         => 'integer',
        'efector_id'             => 'integer',
        'ejercicio'              => 'integer',
        'dias_func_servicio'     => '?integer',
        'altas'                  => '?integer',
        'defuncion'              => '?integer',
        'total_egresos'          => '?integer',
        'pases_a_sala'           => '?integer',
        'dias_estada'            => '?integer',
        'paciente_dia'           => '?integer',
        'cama_disponible'        => '?integer',
        'promedio_cama_disp'     => '?float',
        'promedio_pcte_dia'      => '?float',
        'promedio_permanencia'   => '?float',
        'porcentaje_ocupacional' => '?float',
        'promedio_dias_estada'   => '?float',
        'tasa_mortalidad'        => '?float',
        'giro_cama'              => '?float',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('rendimiento_hospitalario_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->rendimiento_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('rendimiento_hospitalario_show', $this->rendimiento_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="rendimiento_id" value="' . $this->rendimiento_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}