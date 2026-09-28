<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Guardia extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'anio'     => 'integer',
        'cantidad' => 'integer',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('guardia_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->guardia_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('guardia_show', $this->guardia_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="guardia_id" value="' . $this->guardia_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}