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

    public function findUnValidateApplyProcess(
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
                                a.is_validate IS NULL and 
                                b.apply_status = "P" and 
                                a.upper_user_no =? 
                        ) as a
                        left join user as u1 on a.agent_user_no = u1.NO
                        left join eip_leave_type on a.leave_type = eip_leave_type.id
                        left join user as u2 on a.apply_user_no = u2.NO
                    order by id desc';
            $data = DB::select($sql, [$upper_user_no]);
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
            $total_pages = floor(count($new_data)/10);
            if($total_pages < 1) $total_pages = 1;
            $new_data = array_slice($new_data, $page-1, 10);
            return [
                'status' => 'successful',
                'data' => $new_data,
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