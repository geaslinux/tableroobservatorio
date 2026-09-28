<?php

namespace App\Validation;
use App\Models\UserModel;
use Config\Database;
//use Hashids\Hashids;

class UserRules
{
    function is_not_unique_has(string $str = null, string $field, array $data): bool
    {
        // Grab any data for exclusion of a single row.
        list($field, $whereField, $whereValue) = array_pad(explode(',', $field), 3, null);

        //$hash = new Hashids();
        //$str = $hash->decode($str)[0];
       
        $pos = strrpos($field,'.');
        $table = substr($field,0,$pos);
        $field = substr($field,$pos+1);
        
        // Break the table and field apart
        //sscanf($field, '%[^.].%[^.]', $table, $field);
        
        $db = Database::connect($data['DBGroup'] ?? null);

        $row = $db->table($table)
                ->select('1')
                ->where($field, $str)
                ->limit(1);

        if (! empty($whereField) && ! empty($whereValue))
        {
            if (! preg_match('/^\{(\w+)\}$/', $whereValue))
            {
                $row = $row->where($whereField, $whereValue);
            }
        }

        return (bool) ($row->get()->getRow() !== null);
    }
}