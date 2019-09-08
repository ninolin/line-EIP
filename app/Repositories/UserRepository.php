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

    public function findAllUserDetail(
        $search = null, 
        $order_col = 'NO', 
        $order_type = 'DESC', 
        $page = null
    ) 
    {
        try {
            $count_data = [];
            $count_sql = 'select count(1) as count from user where status = "T" ';

            $query_data = [];
            $sql =  'select 
                        u.*, 
                        et.name as title, 
                        u3.cname as default_agent_cname, 
                        u2.cname as upper_cname, 
                        ewc.name as work_class_name
                    from user u 
                        left join eip_title et on u.title_id = et.id
                        left join user u2 on u.upper_user_no = u2.NO
                        left join user u3 on u.default_agent_user_no = u3.NO
                        left join eip_work_class ewc on u.work_class_id = ewc.id
                    where 
                        u.status = "T" ';
                        
            if (!is_null($search)) {
                $search = '%'.$search.'%';
                $sql .= 'and (u.username like ? or u.cname like ? or u.email like ?) ';
                $count_sql .= 'and (username like ? or cname like ? or email like ?) ';
                array_push($query_data, $search, $search, $search);
                array_push($count_data, $search, $search, $search);
            }

            $sql .= ' order by '.$order_col.' '.$order_type;
            if (!is_null($page)) {
                $sql .= ' limit '.(($page-1)*10).',10 ';
            }

            $total_logs = DB::select($count_sql, $count_data);
            $total_pages = ceil($total_logs[0]->count/10);
            $data = DB::select($sql, $query_data);

            return [
                'status' => 'successful',
                'data' => $data,
                'total_pages' => $total_pages
            ];
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
            $sql = "select 
                        u.*,
                        ewc.work_start, 
                        ewc.work_end 
                    from 
                        user u 
                        left join eip_work_class ewc on u.work_class_id = ewc.id 
                    where 
                        u.status = 'T' and 
                        u.NO =? ";
            return DB::select($sql, [$user_no]);
        } catch (Exception $e) {
            throw $e;
        }
    }


}