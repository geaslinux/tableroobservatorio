<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Persona extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];

    public function getEditLink(){        
        return "<a href=".base_url(route_to('persona_show',$this->persona_id)).">Editar</a>";
    }
	
	public function getDeleteLink(){
		return '<form style="display:inline;" action="'.base_url(route_to('persona_destroy')).'" method="post"><input type="hidden" name="_method" value="DELETE" /><input type="hidden" name="id" value="'.$this->id.'" />'.csrf_field().'<a  onclick="this.closest(\'form\').submit();return false;">Eliminar</a></form>';
	}

   
    
	public function setCampoOculto(){		
		$this->campoOculto =  '<input class="input" type="hidden" name="persona_id" value="'.$this->persona_id.'"><input type="hidden" name="_method" value="PUT" />';
	}
	
	public function getCampoOculto(){
		return $this->campoOculto; 
	}        
}