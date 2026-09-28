<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class SaludMentalCamas extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'salud_mental_camas_id' => 'integer',
        'efector_id'            => 'integer',
        'cb_adultos'            => 'integer',
        'cb_pediatricos'        => 'integer',
        'total_basicas'         => 'integer',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('salud_mental_camas_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->salud_mental_camas_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('salud_mental_camas_show', $this->salud_mental_camas_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="salud_mental_camas_id" value="' . $this->salud_mental_camas_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}