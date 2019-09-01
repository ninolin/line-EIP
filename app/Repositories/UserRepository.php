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
            $sql = 'select NO, cname, onboard_date from user where status = "T"'; 
            $data = DB::select($sql, []);
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByKeyword($keyword, $limit = null) 
    {
        try {
            $keyword = '%'.$keyword.'%';
            $sql = 'select NO, cname, onboard_date from user where username like ? or cname like ? or email like ? '; 
            
            if (!is_null($limit)) {
                $sql .= ' limit '.$limit;
            }
            
            $data = DB::select($sql, [$keyword, $keyword, $keyword]);
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByLineId($line_id) 
    {
        try {
            return DB::select('select NO, cname, onboard_date from user where line_id =?', [$line_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByUserNo($user_no) 
    {
        try {
            return DB::select('select NO, cname, onboard_date from user where NO =?', [$user_no]);
        } catch (Exception $e) {
            throw $e;
        }
    }


}