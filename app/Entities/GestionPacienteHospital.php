<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GestionPacienteHospital extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'gestion_cama_hospitales_id' => 'integer',
        'efector_id'                 => 'integer',
        'departamento_id'            => 'integer',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('gestion_paciente_hospital_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->gestion_cama_hospitales_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('gestion_paciente_hospital_show', $this->gestion_cama_hospitales_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="gestion_cama_hospitales_id" value="' . $this->gestion_cama_hospitales_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}