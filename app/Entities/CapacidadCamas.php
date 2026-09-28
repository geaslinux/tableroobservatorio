<?php
namespace App\Entities;
use CodeIgniter\Entity\Entity;
class CapacidadCamas extends Entity
{
    protected $datamap = [];
    protected $dates   = [];

    protected $attributes = [
        'capacidad_camas_id'                     => null,
        'efector_id'                              => null,
        'tipo'                                    => null,
        'uti_adulto'                              => null,
        'uti_coronario'                           => null,
        'uti_pediatrico'                          => null,
        'uti_neonatal'                             => null,
        'total_uti'                               => null,
        'total_uti_publicos'                      => null,
        'utin_adulto'                             => null,
        'utin_pediatrico'                         => null,
        'utin_neonatal'                            => null,
        'total_utin'                               => null,
        'total_utin_publicos'                     => null,
        'cb_adultos'                               => null,
        'cb_pediatricos'                          => null,
        'cb_neonatales'                           => null,
        'total_basicas'                           => null,
        'total_basicas_publicos'                  => null,
        'camas_disponibles'                       => null,
        'camas_disponibles_publicas'               => null,
        'camas_disponibles_publicas_salud_mental' => null,
    ];

    protected $casts = [
        'capacidad_camas_id'                      => 'integer',
        'efector_id'                               => 'integer',
        'uti_adulto'                               => 'integer',
        'uti_coronario'                            => 'integer',
        'uti_pediatrico'                           => 'integer',
        'uti_neonatal'                             => 'integer',
        'total_uti'                                => 'integer',
        'total_uti_publicos'                       => 'integer',
        'utin_adulto'                              => 'integer',
        'utin_pediatrico'                          => 'integer',
        'utin_neonatal'                            => 'integer',
        'total_utin'                               => 'integer',
        'total_utin_publicos'                      => 'integer',
        'cb_adultos'                               => 'integer',
        'cb_pediatricos'                           => 'integer',
        'cb_neonatales'                            => 'integer',
        'total_basicas'                            => 'integer',
        'total_basicas_publicos'                   => 'integer',
        'camas_disponibles'                        => 'integer',
        'camas_disponibles_publicas'                => 'integer',
        'camas_disponibles_publicas_salud_mental'  => 'integer',
    ];

    public function getDeleteLink()
    {
        return '<form style="display:inline;" action="' . base_url(route_to('capacidad_camas_destroy')) . '" method="post">
                    <input type="hidden" name="_method" value="DELETE" />
                    <input type="hidden" name="id" value="' . $this->capacidad_camas_id . '" />'
                    . csrf_field() .
                    '<a onclick="this.closest(\'form\').submit();return false;">Eliminar</a>
                </form>';
    }

    public function getEditLink()
    {
        return '<a href="' . base_url(route_to('capacidad_camas_show', $this->capacidad_camas_id)) . '">Editar</a>';
    }

    public function setCampoOculto()
    {
        $this->campoOculto = '<input type="hidden" name="capacidad_camas_id" value="' . $this->capacidad_camas_id . '">
                              <input type="hidden" name="_method" value="PUT" />';
    }

    public function getCampoOculto()
    {
        return $this->campoOculto;
    }
}