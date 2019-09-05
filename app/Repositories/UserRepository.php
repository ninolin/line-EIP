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
            $sql = "select u.NO, u.cname, u.onboard_date, u.work_class_id, u.line_id, ewc.work_start, ewc.work_end ";
            $sql.= "from user u ";
            $sql.= "left join eip_work_class ewc ";
            $sql.= "on u.work_class_id = ewc.id ";
            $sql.= "where u.status = 'T' and u.line_id =? ";
            return DB::select($sql, [$line_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findUserByUserNo($user_no) 
    {
        try {
            $sql = "select u.NO, u.cname, u.onboard_date, u.work_class_id, u.line_id, ewc.work_start, ewc.work_end ";
            $sql.= "from user u ";
            $sql.= "left join eip_work_class ewc ";
            $sql.= "on u.work_class_id = ewc.id ";
            $sql.= "where u.status = 'T' and u.NO =? ";
            return DB::select($sql, [$user_no]);
        } catch (Exception $e) {
            throw $e;
        }
    }


}