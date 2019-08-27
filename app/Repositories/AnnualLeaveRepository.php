<?php

namespace App\Repositories;

use DB;
use Exception;

class AnnualLeaveRepository {

    public function __construct() 
    {

    }


    public function findAnnualDays($user_no, $year) 
    {
        try {
            $sql = 'select annual_leaves from eip_annual_leave where user_no =? and year =?';
            $data = DB::select($sql, [$user_no, $year]);
            if(count($data) > 0) {
                return $data[0]->annual_leaves;
            } else {
                return 0;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}