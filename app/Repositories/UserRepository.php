<?php

namespace App\Repositories;

use DB;
use Exception;

class UserRepository {

    public function __construct() 
    {

    }


    public function findAllUser() 
    {
        try {
            $sql  = 'select * from user order by id desc';
            $data = DB::select($sql, []);
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByLineId($line_id) 
    {
        try {
            return DB::select('select NO, cname from user where line_id =?', [$line_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByUserNo($user_no) 
    {
        try {
            return DB::select('select NO, cname from user where NO =?', [$user_no]);
        } catch (Exception $e) {
            throw $e;
        }
    }
}