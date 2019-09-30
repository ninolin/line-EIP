<?php

namespace App\Repositories;

use DB;
use Exception;

class LeaveTypeRepository {

    public function __construct() 
    {

    }


    public function findAllType() 
    {
        try {
            $sql = 'select * from eip_leave_type order by name desc';
            return DB::select($sql, []);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findDistinctType() 
    {
        try {
            $sql = 'select distinct name from eip_leave_type order by name desc';
            return DB::select($sql, []);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findTypeByName($type_names) 
    {
        try {
            $sql = 'select * from eip_leave_type where name IN ('. implode(",", array_fill(0, count($type_names), '?')) . ')';
            return DB::select($sql, $type_names);
        } catch (Exception $e) {
            throw $e;
        }
    }
}