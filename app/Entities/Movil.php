<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Movil extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];

    public function getEditLink()
    {
        return "<a href=" . base_url(route_to('movil_show', $this->movil_id)) . ">Editar</a>";
    }

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('movil_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->movil_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="movil_id" value="' . $this->movil_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}