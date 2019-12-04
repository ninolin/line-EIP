<?php

namespace App\Repositories;

use DB;
use Exception;
use Log;

class LeaveProcessRepository {

    public function __construct() 
    {

    }

    public function findApplyProcess(
        $upper_user_no = null, 
        $apply_user_no = null, 
        $is_validate = null, 
        $page = null
    ) 
    {
        try {
            $count_data = [];
            $count_sql = 'select count(1) as count from eip_leave_apply_process where 1=1 ';
            
            $query_data = [];
            $where_sql = 'select a.id as process_id, b.* 
                    from eip_leave_apply_process a, eip_leave_apply b 
                    where a.apply_id = b.id';

            if (!is_null($apply_user_no)) {
                $where_sql .= ' and a.apply_user_no = ? ';
                $count_sql .= ' and apply_user_no = ? ';
                array_push($query_data, $apply_user_no);
                array_push($count_data, $apply_user_no);
            }

            if (!is_null($upper_user_no)) {
                $where_sql .= ' and a.upper_user_no = ? ';
                $count_sql .= ' and upper_user_no = ? ';
                array_push($query_data, $upper_user_no);
                array_push($count_data, $upper_user_no);
            }

            if (!is_null($is_validate) && $is_validate) {
                $where_sql .= ' and a.is_validate IS NOT NULL ';
                $count_sql .= ' and is_validate IS NOT NULL ';
            }

            if (!is_null($is_validate) && !$is_validate) {
                $where_sql .= ' and a.is_validate IS NULL ';
                $count_sql .= ' and is_validate IS NULL ';
            }

            $sql  = 'select 
                        a.*, 
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        eip_leave_type.name as leave_name
                    from ( '.$where_sql.' ) as a
                        left join user as u1 on a.agent_user_no = u1.NO
                        left join eip_leave_type on a.leave_type = eip_leave_type.id
                        left join user as u2 on a.apply_user_no = u2.NO
                    order by id desc';
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
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * 尋找用戶未簽核過的紀錄
     * 
     */
    public function findUnValidateApplyProcess(
        $upper_user_no = null,
        $apply_user_no = null, 
        $page = null
    ) 
    {
        try {

            $query_data = [];
            $where_sql = '  select a.id as process_id, b.* 
                            from eip_leave_apply_process a, eip_leave_apply b 
                            where 
                                a.apply_id = b.id and 
                                a.is_validate IS NULL and 
                                b.apply_status = "P" '; 

            if (!is_null($apply_user_no)) {
                $where_sql .= ' and a.apply_user_no = ? ';
                array_push($query_data, $apply_user_no);
            }

            if (!is_null($upper_user_no)) {
                $where_sql .= ' and a.upper_user_no = ? ';
                array_push($query_data, $upper_user_no);
            }

            $sql  = 'select 
                        a.*, 
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        eip_leave_type.name as leave_name
                    from 
                        ( '.$where_sql.' ) as a
                        left join user as u1 on a.agent_user_no = u1.NO
                        left join eip_leave_type on a.leave_type = eip_leave_type.id
                        left join user as u2 on a.apply_user_no = u2.NO
                    order by id desc';
            $data = DB::select($sql, $query_data);
            $new_data = [];
            foreach ($data as $d) {
                //去檢查這個no是不是這張假單的下一個簽核人
                $sql = 'select 
                            upper_user_no 
                        from 
                            eip_leave_apply_process 
                        where 
                            apply_id = ? and is_validate IS NULL 
                        order by id limit 1 ';
                $next_upper_users = DB::select($sql, [$d->id]);
                foreach ($next_upper_users as $n) {
                    $next_upper_user_no = $n->upper_user_no;
                    if($next_upper_user_no == $upper_user_no) {
                        array_push($new_data, $d);
                    }
                }
            }

            $total_pages = ceil(count($new_data)/10);
            if (!is_null($page)) {
                $new_data = array_slice($new_data, $page-1, 10);
            }
            return [
                'status'        => 'successful',
                'data'          => $new_data,
                'total_pages'   => $total_pages
            ];
        } catch (Exception $e) {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * 尋找用戶已簽核過的紀錄
     * 
     */
    public function findValidateApplyProcess(
        $upper_user_no = null, 
        $page = null
    ) 
    {
        try {

            $sql  = 'select 
                        a.*, 
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        eip_leave_type.name as leave_name
                    from 
                        (   select a.id as process_id, b.* 
                            from eip_leave_apply_process a, eip_leave_apply b 
                            where 
                                a.apply_id = b.id and 
                                a.is_validate IS NOT NULL and 
                                a.upper_user_no =? 
                        ) as a
                        left join user as u1 on a.agent_user_no = u1.NO
                        left join eip_leave_type on a.leave_type = eip_leave_type.id
                        left join user as u2 on a.apply_user_no = u2.NO
                    order by id desc';
            $data = DB::select($sql, [$upper_user_no]);
            if (!is_null($page)) {
                $data = array_slice($data, $page-1, 10);
            }
            $total_pages = floor(count($data)/10);
            
            return [
                'status'        => 'successful',
                'data'          => $data,
                'total_pages'   => $total_pages
            ];
        } catch (Exception $e) {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ];
        }
    }

    /**
     * 休假/加班申請的下一個簽核人資料
     * 
     */
    public function findNextUpperUser($apply_id) 
    {
        try {
            $sql = 'select 
                        u.* 
                    from 
                        eip_leave_apply_process e,
                        user u 
                    where 
                        e.apply_id = ? and 
                        e.is_validate IS NULL and 
                        e.upper_user_no = u.NO
                    order by id ASC limit 1 ';
            return DB::select($sql, [$apply_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 休假/加班申請已簽核過的紀錄
     * 
     */
    public function findApplyValidateRecord($apply_id) 
    {
        try {
            $sql = 'select 
                        e.upper_user_no, 
                        e.is_validate, 
                        u.line_id
                    from 
                        eip_leave_apply_process e left join 
                        user u
                    on 
                        e.upper_user_no = u.NO
                    where
                        e.apply_id = ? and 
                        e.is_validate IS NOT NULL';
            return DB::select($sql, [$apply_id]);
        } catch (Exception $e) {
            throw $e;
        }
    }
    /**
     * 休假/加班申請的拒絕原因
     * 
     */
    public function findRejectReason($apply_id)
    {
        try {
            
            $reject_reason = DB::table('eip_leave_apply_process')
                ->where('apply_id', $apply_id)
                ->where('is_validate', 0)
                ->value('reject_reason');
            return $reject_reason;

        } catch (Exception $e) {
            throw $e;
        }

        
    }

    
}