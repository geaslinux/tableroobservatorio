<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class RhMaterno extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];

    public function getEditLink()
    {
        return "<a href=" . base_url(route_to('rh_materno_show', $this->rh_materno_id)) . ">Editar</a>";
    }

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('rh_materno_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->rh_materno_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="rh_materno_id" value="' . $this->rh_materno_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}