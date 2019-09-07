<?php

namespace App\Repositories;

use DB;
use Exception;
use Log;

class LeaveApplyRepository {

    public function __construct() 
    {

    }

    public function findApplyLeave($apply_id) 
    {
        try {
            $sql  = 'select 
                        a.*, 
                        DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%m") as start_date_f1,
                        DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%m") as end_date_f1,
                        DATE_FORMAT(a.start_date, "%Y-%m-%dT%H:%m") as start_date_f2,
                        DATE_FORMAT(a.end_date, "%Y-%m-%dT%H:%m") as end_date_f2,
                        u2.cname as cname, 
                        u2.work_class_id, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        a.id =?';
            $data = DB::select($sql, [$apply_id]);
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function findPersonalLeaveLog(
        $user_no = null, 
        $apply_status = null, 
        $start_date = null, 
        $end_date = null, 
        $leave_type = null,
        $page = null
    ) 
    {
        try {
            $count_data = [];
            $count_sql = 'select count(1) as count from eip_leave_apply where apply_type = "L" ';
            
            $query_data = [];
            $sql  = 'select 
                        a.*, 
                        DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%m") as start_date_f1,
                        DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%m") as end_date_f1,
                        DATE_FORMAT(a.start_date, "%Y-%m-%dT%H:%m") as start_date_f2,
                        DATE_FORMAT(a.end_date, "%Y-%m-%dT%H:%m") as end_date_f2,
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        apply_type = "L" ';
            
            if (!is_null($user_no) and is_array($user_no)) {
                $sql .= ' and apply_user_no IN ('. implode(",", array_fill(0, count($user_no), '?')) . ') ';
                $count_sql .= ' and apply_user_no IN ('. implode(",", array_fill(0, count($user_no), '?')) . ') ';
                $query_data = array_merge($query_data, $user_no);
                $count_data = array_merge($count_data, $user_no);
            }
            

            if (!is_null($apply_status) and is_array($apply_status)) {
                $sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $count_sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $query_data = array_merge($query_data, $apply_status);
                $count_data = array_merge($count_data, $apply_status);
            }

            if (!is_null($start_date)) {
                $sql .= ' and start_date >= ? ';
                $count_sql .= ' and start_date >= ? ';
                array_push($query_data, $start_date);
                array_push($count_data, $start_date);
            }

            if (!is_null($end_date)) {
                $sql .= ' and end_date <= ? ';
                $count_sql .= ' and end_date <= ? ';
                array_push($query_data, $end_date);
                array_push($count_data, $end_date);
            }

            if (!is_null($leave_type) and is_array($leave_type)) {
                $sql .= ' and leave_type IN ('. implode(",", array_fill(0, count($leave_type), '?')) . ') ';
                $count_sql .= ' and leave_type IN ('. implode(",", array_fill(0, count($leave_type), '?')) . ') ';
                $query_data = array_merge($query_data, $leave_type);
                $count_data = array_merge($count_data, $leave_type);
            }

            $sql .=' order by id desc';

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

    public function findPersonalOverworkLog(
        $user_no = null, 
        $apply_status = null, 
        $start_date = null, 
        $end_date = null,
        $page = null
    ) 
    {
        try {
            $count_data = [];
            $count_sql = 'select count(1) as count from eip_leave_apply where apply_type = "O" ';
            
            $query_data = [];
            $sql  = 'select 
                        a.*, 
                        DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%m") as start_date_f1,
                        DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%m") as end_date_f1,
                        DATE_FORMAT(a.start_date, "%Y-%m-%dT%H:%m") as start_date_f2,
                        DATE_FORMAT(a.end_date, "%Y-%m-%dT%H:%m") as end_date_f2,
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        apply_type = "O" ';

            if (!is_null($user_no) and is_array($user_no)) {
                $sql .= ' and apply_user_no IN ('. implode(",", array_fill(0, count($user_no), '?')) . ') ';
                $count_sql .= ' and apply_user_no IN ('. implode(",", array_fill(0, count($user_no), '?')) . ') ';
                $query_data = array_merge($query_data, $user_no);
                $count_data = array_merge($count_data, $user_no);
            }
            if (!is_null($apply_status) and is_array($apply_status)) {
                $sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $count_sql .= ' and apply_status IN ('. implode(",", array_fill(0, count($apply_status), '?')) . ') ';
                $query_data = array_merge($query_data, $apply_status);
                $count_data = array_merge($count_data, $apply_status);
            }

            if (!is_null($start_date)) {
                $sql .= ' and over_work_date >= ? ';
                $count_sql .= ' and over_work_date >= ? ';
                array_push($query_data, $start_date);
                array_push($count_data, $start_date);
            }

            if (!is_null($end_date)) {
                $sql .= ' and over_work_date <= ? ';
                $count_sql .= ' and over_work_date <= ? ';
                array_push($query_data, $end_date);
                array_push($count_data, $end_date);
            }

            $sql .=' order by id desc';

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

    public function findPersonalAgentLog(
        $user_no, 
        $apply_status = null, 
        $start_date = null, 
        $end_date = null, 
        $leave_type = null,
        $page = null
    ) 
    {
        try {
            $total_logs = DB::select('select count(1) as count from eip_leave_apply where agent_user_no =? and apply_type = "L"', [$user_no]);
            $total_pages = ceil($total_logs[0]->count/10);

            $query_data = [$user_no];
            $sql  = 'select 
                        a.*, 
                        DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%m") as start_date_f1,
                        DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%m") as end_date_f1,
                        DATE_FORMAT(a.start_date, "%Y-%m-%dT%H:%m") as start_date_f2,
                        DATE_FORMAT(a.end_date, "%Y-%m-%dT%H:%m") as end_date_f2,
                        u2.cname as cname, 
                        u1.cname as agent_cname, 
                        e.name as leave_name 
                    from eip_leave_apply as a
                        left join user as u1 on a.agent_user_no = u1.NO 
                        left join eip_leave_type as e on a.leave_type = e.id 
                        left join user as u2 on a.apply_user_no = u2.NO 
                    where 
                        agent_user_no =? and apply_type = "L"';

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

            if (!is_null($page)) {
                $sql .= ' limit '.(($page-1)*10).',10 ';
            }

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

    public function update_leave_date(
        $apply_id, 
        $start_date, 
        $end_date, 
        $reason,
        $login_user_no, 
        $leave_hours, 
        $event_id = null
    ) {
        try { 
            $change_desc = "休假起迄更新為".$start_date."到".$end_date;
            $sql = "update eip_leave_apply set start_date =?, end_date =?, leave_hours =? ";
            $sql_data = [$start_date, $end_date, $leave_hours];

            if (!is_null($event_id)) {
                $sql .= ' ,event_id = ? ';
                array_push($sql_data, $event_id);
            }

            $sql .= ' where id =? ';
            array_push($sql_data, $apply_id);

            DB::beginTransaction(); 
            try {
                DB::update($sql, $sql_data);
                $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                DB::insert($sql, [$apply_id, $change_desc, $reason, $login_user_no]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            return ['status' => 'successful'];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function update_overwork_date(
        $apply_id, 
        $overwork_date, 
        $overwork_hours, 
        $reason,
        $login_user_no
    ) {
        try { 
            $change_desc = "加班時間更新為".$overwork_date." ".$overwork_hours."小時";
            DB::beginTransaction(); 
            try {
                DB::update("update eip_leave_apply set over_work_date =?, over_work_hours =? where id =?", [$overwork_date, $overwork_hours, $apply_id]);
                $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                DB::insert($sql, [$apply_id, $change_desc, $reason, $login_user_no]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            return ['status' => 'successful'];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}