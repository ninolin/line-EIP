<?php

namespace App\Repositories;

use DB;
use Exception;

class LeaveApplyRepository {

    public function __construct() 
    {

    }


    public function findPersonalLeaveLog(
        $user_no, 
        $apply_status = null, 
        $start_date = null, 
        $end_date = null, 
        $leave_type = null
    ) 
    {
        try {
            $query_data = [$user_no];
            $sql  = 'select 
                        a.*, 
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        apply_user_no =? and apply_type = "L"';

            if (!is_null($apply_status) and is_array($apply_status)) {
                $sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $query_data = array_merge($query_data, $apply_status);
            }

            if (!is_null($start_date)) {
                $sql .= ' and start_date >= ? ';
                array_push($query_data, $start_date);
            }

            if (!is_null($end_date)) {
                $sql .= ' and end_date <= ? ';
                array_push($query_data, $end_date);
            }

            if (!is_null($leave_type) and is_array($leave_type)) {
                $sql .= ' and leave_type IN ('. implode(",", array_fill(0, count($leave_type), '?')) . ') ';
                $query_data = array_merge($query_data, $leave_type);
            }

            $sql .=' order by id desc';
            $data = DB::select($sql, $query_data);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findPersonalOverworkLog(
        $user_no, 
        $apply_status = null, 
        $start_date = null, 
        $end_date = null
    ) 
    {
        try {
            $query_data = [$user_no];
            $sql  = 'select 
                        a.*, 
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        apply_user_no =? and apply_type = "O"';

            if (!is_null($apply_status) and is_array($apply_status)) {
                $sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $query_data = array_merge($query_data, $apply_status);
            }

            if (!is_null($start_date)) {
                $sql .= ' and over_work_date >= ? ';
                array_push($query_data, $start_date);
            }

            if (!is_null($end_date)) {
                $sql .= ' and over_work_date <= ? ';
                array_push($query_data, $end_date);
            }

            $sql .=' order by id desc';
            $data = DB::select($sql, $query_data);

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }
}