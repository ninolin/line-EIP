<?php

namespace App\Repositories;

use DB;
use Exception;

class OverworkTypeRepository {

    public function __construct() 
    {

    }


    public function findAllType() 
    {
        try {
            $sql = 'select * from eip_overwork_type order by hour desc';
            return DB::select($sql, []);
        } catch (Exception $e) {
            throw $e;
        }
    }
}