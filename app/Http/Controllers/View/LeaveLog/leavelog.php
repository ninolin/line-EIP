<?php

namespace App\Http\Controllers\View\LeaveLog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Console\commands\CalcLeaveDays;
use App\Providers\LeaveProvider;
use App\Providers\LineServiceProvider;
use DB;
use Log;
use Exception;
use Config;

class leavelog extends Controller
{
    private $calcL;

    public function __construct(CalcLeaveDays $calcL)
    {
        $this->calcL = $calcL;
    }

    /**
     * 顯示某一筆休假/加班的簽核歷程
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function list_logs($id)
    {
        $sql  = "select elap.*, u.cname from eip_leave_apply_process elap, user u ";
        $sql .= "where elap.upper_user_no = u.NO and elap.apply_id = ?";
        $processes = DB::select($sql, [$id]);
        return response()->json([
            'status'        => 'successful',
            'data'          => $processes
        ]);
    }

    /**
     * 顯示最近工時紀錄
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function show_last()
    {
        $search = Input::get('search', '');
        $page = Input::get('page', 1);
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where apply_status = "P") as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.apply_user_no = u2.NO ';
        $sql .= 'order by id DESC ';
        $sql .= 'limit ?,10 ';
        $logs = DB::select($sql, [($page-1)*10]);
        foreach ($logs as $key => $value) {
            $apply_time = strftime('%Y-%m-%d %H:%M', strtotime($value->apply_time));
            $logs[$key]->apply_time = $apply_time;
            if($value->apply_type == 'L') {
                $start_date = strftime('%Y-%m-%dT%H:%M', strtotime($value->start_date));
                $end_date = strftime('%Y-%m-%dT%H:%M', strtotime($value->end_date));
                
                $logs[$key]->start_date = $start_date;
                $logs[$key]->end_date = $end_date;
                
                //$start_date = str_replace("T", " ", $value->start_date);
                //$end_date = str_replace("T", " ", $value->end_date);
                //$logs[$key]->start_date = $start_date;
                //$logs[$key]->end_date = $end_date;
            }
        }
        $total_logs = DB::select('select * from eip_leave_apply', []);
        $total_pages = ceil(count($total_logs)/10);
        $agents = DB::select("select * from user where status = 'T' order by cname", []);
        return view('contents.LeaveLog.leavelog', [
            'logs'          => $logs, 
            'search'        => $search,
            'page'          => $page,
            'total_pages'   => $total_pages,
            'agents'        => $agents,
            'tab'           => 'last',
            'login_user_no' => session('user_no')
        ]);
    }

    /**
     * 顯示員工紀錄頁面
     * 
     * @return \Illuminate\Http\Response
     */
    public function show_individual() 
    {
        $search     = Input::get('search', '');
        $leave_year = Input::get('leave_year', date('Y'));
        $logs       = [];
        $types      = [];
        $NO = 0;
        $onboard_date = "";
        $cname = "";
        $sql = 'select NO, cname, onboard_date from user where username like "%'.$search.'%" or cname like "%'.$search.'%" or email like "%'.$search.'%" limit 1'; 
        $users = DB::select($sql, []);
        if(count($users) == 1 && $search != '') {
            foreach ($users as $v) {
                $NO = $v->NO;
                $onboard_date = $v->onboard_date;
                $cname = $v->cname;
            }
            $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
            $sql .= 'from ';
            $sql .= '(select * from eip_leave_apply) as a ';
            $sql .= 'left join user as u1 ';
            $sql .= 'on a.agent_user_no = u1.NO ';
            $sql .= 'left join eip_leave_type ';
            $sql .= 'on a.leave_type = eip_leave_type.id ';
            $sql .= 'left join user as u2 ';
            $sql .= 'on a.apply_user_no = u2.NO ';
            $sql .= 'where u2.NO = ? and a.start_date like "'.$leave_year.'%"';
            $logs = DB::select($sql, [$NO]);

            $sql = 'select name, 0 as hours from eip_leave_type group by name';
            $types = DB::select($sql, []);

            foreach ($logs as $key => $value) {
                if($value->apply_type == 'L') {
                    $start_date = str_replace("T", " ", $value->start_date);
                    $end_date = str_replace("T", " ", $value->end_date);
                    $logs[$key]->start_date = $start_date;
                    $logs[$key]->end_date = $end_date;
                }
                if($value->apply_type == 'L' && $value->apply_status == 'Y') {
                    foreach($types as $tkey => $tvalue) {
                        if($tvalue->name == $value->leave_name) {
                            $types[$tkey]->hours = $types[$tkey]->hours + $value->leave_hours;
                        }
                    }
                }
                
            }
            
            foreach($types as $v) {
                $v->days = round($v->hours/8, 1);
            }

            $leave_day = $this->calcL->calc_leavedays($onboard_date, $leave_year."-01-01");
            if($leave_day == 10000) {
                $leave_day = 0;
            }
            array_push($types, (object) array('name' => '可用休假', 'days' => $leave_day, 'hours' => $leave_day*8));
            debug($leave_day);
        }
        
        debug($types);
        return view('contents.LeaveLog.individuallog', [
            'NO'            => $NO, 
            'cname'         => $cname,
            'onboard_date'  => $onboard_date,
            'logs'          => $logs, 
            'types'         => $types,
            'leave_year'    => $leave_year,
            'search'        => $search,
            'tab'           => 'individual'
        ]);
    }

    /**
     * 更新簽核人
     * 更新eip_leave_apply, 寫入eip_leave_apply_change_log
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_upper_user(Request $request)
    {
        try {
            $apply_id           = $request->get('apply_id');
            $apply_process_id   = $request->get('apply_process_id');
            $new_upper_user_no  = $request->get('user_NO');
            $reason             = $request->get('reason');
            $login_user_no      = $request->get('login_user_no');

            $old_upper_user_cname = "";
            $data = DB::select("select u.cname from eip_leave_apply_process elap, user u where elap.id =? and elap.upper_user_no = u.NO", [$apply_process_id]);
            if(count($data) == 1) { 
                foreach ($data as $v) {
                    $old_upper_user_cname = $v->cname;
                }
            }
            $new_upper_user_cname = "";
            $data = DB::select("select cname from user where NO =?", [$new_upper_user_no]);
            if(count($data) == 1) { 
                foreach ($data as $v) {
                    $new_upper_user_cname = $v->cname;
                }
            }

            $sql = "insert into eip_leave_apply_change_log (apply_id, apply_process_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?, ?)";
            DB::beginTransaction(); 
            try {
                DB::update("update eip_leave_apply_process set upper_user_no =? where id =?", [$new_upper_user_no, $apply_process_id]);
                DB::insert($sql, [$apply_id, $apply_process_id, "簽核人從".$old_upper_user_cname."換成".$new_upper_user_cname, $reason, $login_user_no]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            return response()->json([
                'status' => 'successful'
            ]);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ], 500);
        }
    }
    
    /**
     * 更新代理人
     * 3個step:
     * step1: 檢查代理人是不是在代理期間內也有請假
     * step2: 更新eip_leave_apply, 寫入eip_leave_apply_change_log
     * step3: 通知申請人、新舊代理人、已簽核過的簽核人和下一個簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_agent_user(Request $request)
    {
        try {    
            $apply_id           = $request->get('apply_id');
            $new_agent_user_no  = $request->get('user_NO');
            $reason             = $request->get('reason');
            $login_user_no      = $request->get('login_user_no');

            //檢查新的代理人在該假單請假時間是否也正在請假
            $v = json_decode(LeaveProvider::getLeaveApply($apply_id));
            $old_agent_user_cname = $v->agent_cname;            //舊代理人的cname
            $old_agent_user_line_id = $v->agent_user_line_id;   //舊代理人的line_id
            $sql  = "select start_date from eip_leave_apply where apply_user_no = ? and start_date <= ? and end_date >= ? and apply_type = 'L' and apply_status IN ('P', 'Y')";
            $overlap = DB::select($sql, [$new_agent_user_no, $v->start_date, $v->start_date]);
            if(count($overlap) > 0) { 
                return response()->json([
                    'status' => 'error',
                    'message' => '失敗:代理人在該假單請假時間中也正在請假'
                ], 500);
            }
            $new_agent_user_cname = "";
            $data = DB::select("select cname from user where NO =?", [$new_agent_user_no]);
            if(count($data) == 1) { 
                foreach ($data as $v) {
                    $new_agent_user_cname = $v->cname;
                }
            }

            DB::beginTransaction(); 
            try {
                DB::update("update eip_leave_apply set agent_user_no =? where id =?", [$new_agent_user_no, $apply_id]);
                $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                DB::insert($sql, [$apply_id, "代理人從".$old_agent_user_cname."換成".$new_agent_user_cname, $reason, $login_user_no]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

            $v = json_decode(LeaveProvider::getLeaveApply($apply_id));
            $msg = ["申請人::". $v->apply_user_cname, "假別::". $v->leave_name, "代理人::".$v->agent_cname,"起日::".$v->start_date,"迄日::". $v->end_date,"備住::". $v->comment];
            LineServiceProvider::sendNotifyFlexMeg($v->apply_user_line_id, array_merge(["更換代理人"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($old_agent_user_line_id, array_merge(["代理人取消"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($v->agent_user_line_id, array_merge([$v->apply_user_cname."指定您為請假代理人"], $msg));
            $sql  = "select elap.upper_user_no, elap.is_validate, u.line_id ";
            $sql .= "from eip_leave_apply_process elap ";
            $sql .= "left join user u ";
            $sql .= "on elap.upper_user_no = u.NO ";
            $sql .= "where elap.apply_id = ?";
            $uppers = DB::select($sql, [$apply_id]);
            foreach ($uppers as $u) {
                //通知已簽核過的簽核人和下一個簽核人
                LineServiceProvider::sendNotifyFlexMeg($u->line_id, array_merge(["更換代理人"], $msg));
                if(is_null($u->is_validate)){
                    break;
                }
            }
            return response()->json([
                'status' => 'successful'
            ]);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ], 500);
        }
    }

    /**
     * 更新休假起日/休假迄日/加班日期/加班小時
     * 4個step:
     * step1: 判斷是要更新 休假起日/休假迄日/加班日期/加班小時
     * step2: 重算小時
     * step3: 更新eip_leave_apply
     * step3: 通知申請人、代理人、已簽核過的簽核人和下一個簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_date(Request $request)
    {
        try {    
            $apply_id       = $request->get('apply_id');
            $type           = $request->get('type');
            $new_date       = $request->get('new_date');
            $reason         = $request->get('reason');
            $login_user_no  = $request->get('login_user_no');

            $leave_hours = 0;
            $start_date = "";
            $end_date = "";
            $work_class_id = "";
            $column = "";
            $sql = "";
            $change_desc = "";
            if($type == 'leave_start_date') { //更新休假起日
                $change_desc = "休假起日更新為".$new_date;
                $column = "start_date";
                $sql  = "select ela.apply_user_no, ela.end_date, u.work_class_id ";
                $sql .= "from eip_leave_apply ela, user u where ela.id = ? and ela.apply_user_no = u.NO";
                $data = DB::select($sql, [$apply_id]);
                foreach ($data as $d) {
                    $start_date = $new_date;
                    $end_date = $d->end_date;
                    $work_class_id = $d->work_class_id;
                }
                
            } else if($type == 'leave_end_date') {  //更新休假迄日
                $change_desc = "休假迄日更新為".$new_date;
                $column = "end_date";
                $sql  = "select ela.apply_user_no, ela.start_date, u.work_class_id ";
                $sql .= "from eip_leave_apply ela, user u where ela.id = ? and ela.apply_user_no = u.NO";
                $data = DB::select($sql, [$apply_id]);
                foreach ($data as $d) {
                    $start_date = $d->start_date;
                    $end_date = $new_date;
                    $work_class_id = $d->work_class_id;
                }
            } else if($type == 'overwork_date') {   //更新加班日
                DB::beginTransaction(); 
                try {
                    DB::update("update eip_leave_apply set over_work_date =? where id =?", [$new_date, $apply_id]);
                    $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                    DB::insert($sql, [$apply_id, "加班日更新為".$new_date, $reason, $login_user_no]);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
                self::send_notify_after_change_date($apply_id, 'overwork');
                return response()->json([
                    'status' => 'successful'
                ]);
            } else if($type == 'overwork_hour') {   //更新加班小時

                DB::beginTransaction(); 
                try {
                    DB::update("update eip_leave_apply set over_work_hours =? where id =?", [$new_date, $apply_id]);
                    $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                    DB::insert($sql, [$apply_id, "加班小時更新為".$new_date, $reason, $login_user_no]);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
                self::send_notify_after_change_date($apply_id, 'overwork');
                return response()->json([
                    'status' => 'successful'
                ]);
            } else {
                throw new Exception('type error');
            }

            $r = json_decode(json_encode(LeaveProvider::getLeaveHours($start_date, $end_date, $work_class_id)));
            if($r->status == "successful") {
                $leave_hours = $r->leave_hours;
            } else {
                throw new Exception($r->message);
            }

            $apply_data = json_decode(LeaveProvider::getLeaveApply($apply_id));
            $insert_event = $apply_data->event_id;
            if($apply_data->apply_status == 'Y') {
                log::info("event_id: ".$apply_data->event_id);
                LeaveProvider::delete_event_from_gcalendar($apply_data->event_id);
                $insert_event = LeaveProvider::insert_event2gcalendar($start_date, $end_date, $apply_data->apply_user_cname."的".$apply_data->leave_name);
            }

            DB::beginTransaction(); 
            try {
                DB::update("update eip_leave_apply set ".$column." =?, leave_hours =?, event_id =? where id =?", [$new_date, $leave_hours, $insert_event, $apply_id]);
                $sql = "insert into eip_leave_apply_change_log (apply_id, change_desc, change_reason, change_user_no) value (?, ?, ?, ?)";
                DB::insert($sql, [$apply_id, $change_desc, $reason, $login_user_no]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            self::send_notify_after_change_date($apply_id, 'leave');

            return response()->json([
                'status' => 'successful'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 通知申請人、代理人、已簽核過的簽核人和下一個簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    static protected function send_notify_after_change_date($apply_id, $type) {
        if($type == "leave") {
            $v = json_decode(LeaveProvider::getLeaveApply($apply_id));
            $msg = ["申請人::". $v->apply_user_cname,"假別::". $v->leave_name,"代理人::".$v->agent_cname,"起日::".$v->start_date,"迄日::". $v->end_date,"備住::". $v->comment];
            LineServiceProvider::sendNotifyFlexMeg($v->apply_user_line_id, array_merge(["更換休假時間"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($v->agent_user_line_id, array_merge([$v->apply_user_cname."更換休假時間"], $msg));
            $sql  = "select elap.upper_user_no, elap.is_validate, u.line_id ";
            $sql .= "from eip_leave_apply_process elap ";
            $sql .= "left join user u ";
            $sql .= "on elap.upper_user_no = u.NO ";
            $sql .= "where elap.apply_id = ?";
            $uppers = DB::select($sql, [$apply_id]);
            foreach ($uppers as $u) {
                LineServiceProvider::sendNotifyFlexMeg($u->line_id, array_merge(["更換休假時間"], $msg));
                if(is_null($u->is_validate)){
                    break;
                }
            }
        } else {
            $v = json_decode(LeaveProvider::getLeaveApply($apply_id));
            $msg = ["申請人::". $v->apply_user_cname,"加班日::".$v->over_work_date,"加班小時::".$v->over_work_hours,"備住::". $v->comment];
            LineServiceProvider::sendNotifyFlexMeg($v->apply_user_line_id, array_merge(["更換加班時間"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($v->agent_user_line_id, array_merge([$v->apply_user_cname."更換加班時間"], $msg));
            $sql  = "select elap.upper_user_no, elap.is_validate, u.line_id ";
            $sql .= "from eip_leave_apply_process elap ";
            $sql .= "left join user u ";
            $sql .= "on elap.upper_user_no = u.NO ";
            $sql .= "where elap.apply_id = ?";
            $uppers = DB::select($sql, [$apply_id]);
            foreach ($uppers as $u) {
                LineServiceProvider::sendNotifyFlexMeg($u->line_id, array_merge(["更換加班時間"], $msg));
                if(is_null($u->is_validate)){
                    break;
                }
            }
        }
    }

    /**
     * 顯示某一筆休假/加班的簽核歷程
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function list_change_logs($id)
    {
        $sql  = "select elac.*, u.cname from eip_leave_apply_change_log elac, user u ";
        $sql .= "where elac.change_user_no = u.NO and elac.apply_id = ?";
        $logs = DB::select($sql, [$id]);
        return response()->json([
            'status'        => 'successful',
            'data'          => $logs
        ]);
    }
}
