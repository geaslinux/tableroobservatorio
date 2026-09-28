<?php

namespace App\Models;

use CodeIgniter\Model;
//use Hashids\Hashids;


class PaisModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'pais';
    protected $primaryKey       = 'pais_id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'pais_nombre',
        'iso_code',
        'pais_createdOn',
        'pais_updatedOn',
        'pais_createdBy',
        'pais_updatedBy'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'pais_createdOn';
    protected $updatedField  = 'pais_updatedOn';
    protected $deletedField  = 'deleted_at';
 
    
    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

	/*
    protected function code($data){
		$hash = new Hashids();
		if(is_array($data['data'])){
			foreach($data['data'] as $d){

				$d->id = $hash->encode($d->id);
				//d($d->id);
			}
		}else{
			$data['data']->id = $hash->encode($data['data']->id);
		}		
		return $data;		
	}
    */	    
}
