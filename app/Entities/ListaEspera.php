<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ListaEspera extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'ejercicio'               => 'integer',
        'efector_id'              => 'integer',
        'especialidad_id'         => '?integer',
        'cantidad_pacientes'      => '?integer',
        'comp_quirurgica_alta'    => '?integer',
        'comp_quirurgica_mediana' => '?integer',
        'comp_quirurgica_baja'    => '?integer',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('lista_espera_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->lista_espera_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('lista_espera_show', $this->lista_espera_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="lista_espera_id" value="' . $this->lista_espera_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}