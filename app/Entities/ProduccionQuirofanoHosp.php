<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class ProduccionQuirofanoHosp extends Entity
{
    protected $datamap = [];
    protected $dates   = [];
    protected $casts   = [
        'ejercicio'                  => 'integer',
        'quirofanos_disponibles'     => '?integer',
        'quirofanos_urgencias'       => '?integer',
        'quirofanos_programadas'     => '?integer',
        'cirugias_urgencia'          => '?integer',
        'cirugias_prog_alta'         => '?integer',
        'cirugias_prog_mediana'      => '?integer',
        'cirugias_prog_baja'         => '?integer',
        'cirugias_prog_desconocido'  => '?integer',
        'total_cirugias_programadas' => '?integer',
        'sub_total'                  => '?integer',
        'porcentaje_provincia'       => '?float',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('quirofano_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->produccion_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('quirofano_show', $this->produccion_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="produccion_id" value="' . $this->produccion_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}