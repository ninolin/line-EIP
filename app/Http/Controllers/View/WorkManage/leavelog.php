<?php

namespace App\Http\Controllers\View\WorkManage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Console\commands\CalcLeaveDays;
use App\Providers\LeaveProvider;
use App\Repositories\LeaveApplyRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use App\Services\SendLineMessageService;
use App\Repositories\AnnualLeaveRepository;
use DB;
use Log;
use Exception;
use Config;
use Excel;
class leavelog extends Controller
{
    private $calcL;
    protected $leaveApplyRepo;
    protected $leaveTypeRepo;
    protected $userRepo;
    protected $sendLineMessageService;
    protected $annualLeaveRepo;

    public function __construct(
        CalcLeaveDays $calcL,
        LeaveApplyRepository $leaveApplyRepo,
        LeaveTypeRepository $leaveTypeRepo,
        UserRepository $userRepo,
        SendLineMessageService $sendLineMessageService,
        AnnualLeaveRepository $annualLeaveRepo
    )
    {
        $this->calcL = $calcL;
        $this->leaveApplyRepo = $leaveApplyRepo;
        $this->leaveTypeRepo = $leaveTypeRepo;
        $this->userRepo = $userRepo;
        $this->sendLineMessageService = $sendLineMessageService;
        $this->annualLeaveRepo = $annualLeaveRepo;
    }

    /**
     * 顯示某一筆休假/加班的簽核歷程
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function list_process_logs($id)
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
     * 顯示簽核中紀錄
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function show_last()
    {
        $search             = Input::get('search', '');
        $leaves_page        = Input::get('leaves_page', 1);
        $overworks_page     = Input::get('overworks_page', 1);
        $leaves             = [];
        $overworks          = [];
        $leaves_t_pages     = 0;
        $overworks_t_pages  = 0;
        $users              = $this->userRepo->findAllUser();
        $key_users_result   = $this->userRepo->findUserByKeyword($search);
        $key_users          = [];

        foreach ($key_users_result as $v) {
            array_push($key_users, $v->NO);
        }

        $overworks_result = $this->leaveApplyRepo->findPersonalOverworkLog($key_users, ["P"], null, null, $overworks_page);
        if($overworks_result["status"] == "successful") {
            $overworks = $overworks_result["data"];
            $overworks_t_pages = $overworks_result["total_pages"];
        }
        $leaves_result = $this->leaveApplyRepo->findPersonalLeaveLog($key_users, ["P"], null, null, null, $leaves_page);
        if($leaves_result["status"] == "successful") {
            $leaves = $leaves_result["data"];
            $leaves_t_pages = $leaves_result["total_pages"];
        } 

        return view('contents.WorkManage.leavelog', [
            'leaves'            => $leaves,
            'leaves_page'       => $leaves_page, 
            'leaves_t_pages'    => $leaves_t_pages, 
            'overworks'         => $overworks, 
            'overworks_page'    => $overworks_page, 
            'overworks_t_pages' => $overworks_t_pages, 
            'users'             => $users, 
            'search'            => $search,
            'tab'               => 'last',
            'login_user_no'     => session('user_no')
        ]);
    }

    /**
     * 顯示員工紀錄查詢頁面
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function show_individual() 
    {
        $search             = Input::get('search', '');
        $leave_year         = Input::get('leave_year', date('Y'));
        $leaves_page        = Input::get('leaves_page', 1);
        $overworks_page     = Input::get('overworks_page', 1);
        $agents_page        = Input::get('agents_page', 1);
        $show_tab           = Input::get('show_tab', 'leave');
        $leaves             = [];
        $overworks          = [];
        $agents             = [];
        $types              = [];
        $user_no            = 0;
        $leaves_t_pages     = 0;
        $overworks_t_pages  = 0;
        $agents_t_pages     = 0;
        $onboard_date       = "";
        $cname              = "";
        $users              = $this->userRepo->findAllUser();
        $key_users          = $this->userRepo->findUserByKeyword($search, 1);
        if(count($key_users) == 1 && $search != '') {
            foreach ($key_users as $v) {
                $user_no = $v->NO;
                $onboard_date = $v->onboard_date;
                $cname = $v->cname;
            }
            $overworks_result = $this->leaveApplyRepo->findPersonalOverworkLog([$user_no], null, $leave_year.'-01-01 00:00:00', $leave_year.'-12-31 23:59:59', $overworks_page);
            if($overworks_result["status"] == "successful") {
                $overworks = $overworks_result["data"];
                $overworks_t_pages = $overworks_result["total_pages"];
            }
            $leaves_result = $this->leaveApplyRepo->findPersonalLeaveLog([$user_no], null, $leave_year.'-01-01 00:00:00', $leave_year.'-12-31 23:59:59', null, $leaves_page);
            if($leaves_result["status"] == "successful") {
                $leaves = $leaves_result["data"];
                $leaves_t_pages = $leaves_result["total_pages"];
            }
  
            $agents_result = $this->leaveApplyRepo->findPersonalAgentLog($user_no, null, $leave_year.'-01-01 00:00:00', $leave_year.'-12-31 23:59:59', $agents_page);
            if($agents_result["status"] == "successful") {
                $agents = $agents_result["data"];
                $agents_t_pages = $agents_result["total_pages"];
            }
            $types = $this->leaveTypeRepo->findDistinctType();
            foreach ($types as $type) {
                $type->hours = 0;
            }
            foreach ($leaves as $key => $value) {
                if($value->apply_type == 'L' && $value->apply_status == 'Y') {
                    foreach($types as $tkey => $tvalue) {
                        if($tvalue->name == $value->leave_name) {
                            $types[$tkey]->hours = $types[$tkey]->hours + $value->leave_hours;
                        }
                    }
                }
            }
            $leave_day = $this->annualLeaveRepo->findAnnualDays($user_no, date('Y'));
            array_push($types, (object) array('name' => '可用休假', 'hours' => $leave_day*8));
        }

        return view('contents.WorkManage.individuallog', [
            'user_no'           => $user_no, 
            'cname'             => $cname,
            'onboard_date'      => $onboard_date,
            'leaves'            => $leaves,
            'leaves_page'       => $leaves_page, 
            'leaves_t_pages'    => $leaves_t_pages, 
            'overworks'         => $overworks, 
            'overworks_page'    => $overworks_page, 
            'overworks_t_pages' => $overworks_t_pages, 
            'agents'            => $agents, 
            'agents_page'       => $agents_page, 
            'agents_t_pages'    => $agents_t_pages, 
            'users'             => $users,
            'types'             => $types,
            'leave_year'        => $leave_year,
            'search'            => $search,
            'tab'               => 'individual',
            'show_tab'          => $show_tab,
            'login_user_no'     => session('user_no')
        ]);
    }

    /**
     * 更新簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_upper_user(Request $request)
    {
        try {
            $apply_id           = $request->get('apply_id');
            $apply_process_id   = $request->get('apply_process_id');
            $apply_type         = $request->get('apply_type');
            $new_upper_user_no  = $request->get('user_NO');
            $reason             = $request->get('reason');
            $login_user_no      = $request->get('login_user_no');

            $old_upper_user_no = "";
            $old_upper_user_cname = "";
            $old_upper_user_line_id = "";
            $data = DB::select("select u.NO, u.cname, u.line_id from eip_leave_apply_process elap, user u where elap.id =? and elap.upper_user_no = u.NO", [$apply_process_id]);
            if(count($data) == 1) { 
                foreach ($data as $v) {
                    $old_upper_user_no = $v->NO;
                    $old_upper_user_cname = $v->cname;
                    $old_upper_user_line_id = $v->line_id;
                }
            }
            $new_upper_user_cname = "";
            $new_upper_user_line_id = "";
            $data = DB::select("select cname, line_id from user where NO =?", [$new_upper_user_no]);
            if(count($data) == 1) { 
                foreach ($data as $v) {
                    $new_upper_user_cname = $v->cname;
                    $new_upper_user_line_id = $v->line_id;
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
            //發出line通知
            if($apply_type == 'L') {
                $this->sendLineMessageService->sendUpdateNotify($apply_id, 'change_leave_validater', $old_upper_user_line_id);
            } else {
                $this->sendLineMessageService->sendUpdateNotify($apply_id, 'change_overwork_validater', $old_upper_user_line_id);
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
            
            $this->sendLineMessageService->sendUpdateNotify($apply_id, 'change_agent', $old_agent_user_line_id);
            
            return response()->json([
                'status' => 'successful'
            ]);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新休假起迄日
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_leave_date(Request $request)
    {
        $apply_id               = $request->get('apply_id');
        $new_leave_start_date   = $request->get('new_leave_start_date');
        $new_leave_end_date     = $request->get('new_leave_end_date');
        $reason                 = $request->get('reason');
        $login_user_no          = $request->get('login_user_no');

        $apply_leave        = $this->leaveApplyRepo->findApplyLeave($apply_id);
        $work_class_id      = $apply_leave[0]->work_class_id;
        $event_id           = $apply_leave[0]->event_id;
        $apply_status       = $apply_leave[0]->apply_status;
        $leave_name         = $apply_leave[0]->leave_name;
        $apply_user_cname   = $apply_leave[0]->cname;
        $leave_hours        = 0;
        $insert_event       = null;

        //重算休假小時
        $r = json_decode(json_encode(LeaveProvider::getLeaveHours($new_leave_start_date, $new_leave_end_date, $work_class_id)));
        if($r->status == "successful") {
            $leave_hours = $r->leave_hours;
        } else {
            throw new Exception($r->message);
        }
        
        if($apply_status == 'Y') {
            LeaveProvider::delete_event_from_gcalendar($event_id);
            $insert_event = LeaveProvider::insert_event2gcalendar($new_leave_start_date, $new_leave_end_date, $apply_user_cname."的".$leave_name);
            
        }

        $update = $this->leaveApplyRepo->update_leave_date($apply_id, $new_leave_start_date, $new_leave_end_date, $reason, $login_user_no, $leave_hours, $insert_event);
        if($update["status"] == "successful") {
            $this->sendLineMessageService->sendUpdateNotify($apply_id, 'change_leave_date');
        } else {
            throw new Exception($update["message"]);
        }
        
        return response()->json([
            'status' => 'successful'
        ]);
    }

    /**
     * 更新加班日期
     * 
     * @param \Illuminate\Http\Request
     */
    public function change_overwork_date(Request $request)
    {
        $apply_id           = $request->get('apply_id');
        $new_overwork_date  = $request->get('new_overwork_date');
        $new_overwork_hours = $request->get('new_overwork_hours');
        $reason             = $request->get('reason');
        $login_user_no      = $request->get('login_user_no');
        $update = $this->leaveApplyRepo->update_overwork_date($apply_id, $new_overwork_date, $new_overwork_hours, $reason, $login_user_no);
        if($update["status"] == "successful") {
            $this->sendLineMessageService->sendUpdateNotify($apply_id, 'change_overwork_date');
        } else {
            throw new Exception($update["message"]);
        }
        return response()->json([
            'status' => 'successful'
        ]);
    }
    
    /**
     * 顯示某一筆休假/加班的修改紀錄
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

    public function export(Request $request)
    {
        $user_no = $request->input('user_no');
        $export_startdate = $request->input('export_startdate');
        $export_enddate = $request->input('export_enddate');
        $export_leaves = $request->input('export_leaves');

        $export_data = [['申請人','代理人','假別/加班','休假小時','加班小時','起','迄','備註','申請日']];
        $types = [];
        $alltypes = $this->leaveTypeRepo->findTypeByName(explode(",",$export_leaves));
        foreach ($alltypes as $type) {
            array_push($types, $type->id);
        }
        $leaves_result = $this->leaveApplyRepo->findPersonalLeaveLog([$user_no], ["Y"], $export_startdate, $export_enddate, $types);
        
        if($leaves_result["status"] == "successful") {
            $leaves = $leaves_result["data"];
            foreach ($leaves as $leave) {
                array_push($export_data, [
                    $leave->cname,
                    $leave->agent_cname,
                    $leave->leave_name,
                    $leave->leave_hours,
                    '-',
                    $leave->start_date_f1,
                    $leave->end_date_f1,
                    $leave->comment,
                    $leave->apply_time
                ]);
            }
        } 
        
        if (in_array("加班", explode(",",$export_leaves))) {
            $overworks_result = $this->leaveApplyRepo->findPersonalOverworkLog([$user_no], ["Y"], $export_startdate, $export_enddate);
            if($overworks_result["status"] == "successful") {
                $overworks = $overworks_result["data"];
                foreach ($overworks as $overwork) {
                    array_push($export_data, [
                        $overwork->cname,
                        $overwork->agent_cname,
                        '加班',
                        '-',
                        $overwork->over_work_hours,
                        $overwork->over_work_date_f1,
                        '-',
                        $overwork->comment,
                        $overwork->apply_time
                    ]);
                }
            }
        }

        Excel::create('工時紀錄',function ($excel) use ($export_data){
            $excel->sheet('score', function ($sheet) use ($export_data){
            $sheet->rows($export_data);
            });
        })->export('xls');
    }

    public function exportLastMonth()
    {
        $export_startdate = date("Y/m/01 00:00:00", strtotime('-1 month'));
        $export_enddate = date("Y/m/t 00:00:00", strtotime('-1 month'));
        $types = [];
        $export_data = [['申請人','代理人','假別/加班','休假小時','加班小時','起','迄','備註','申請日']];

        $alltypes = $this->leaveTypeRepo->findAllType();
        foreach ($alltypes as $type) {
            array_push($types, $type->id);
        }

        $leaves_result = $this->leaveApplyRepo->findPersonalLeaveLog(null, ["Y"], $export_startdate, $export_enddate, $types);
        
        if($leaves_result["status"] == "successful") {
            $leaves = $leaves_result["data"];
            foreach ($leaves as $leave) {
                array_push($export_data, [
                    $leave->cname,
                    $leave->agent_cname,
                    $leave->leave_name,
                    $leave->leave_hours,
                    '-',
                    $leave->start_date_f1,
                    $leave->end_date_f1,
                    $leave->comment,
                    $leave->apply_time
                ]);
            }
        } 

        $overworks_result = $this->leaveApplyRepo->findPersonalOverworkLog(null, ["Y"], $export_startdate, $export_enddate);
        if($overworks_result["status"] == "successful") {
            $overworks = $overworks_result["data"];
            foreach ($overworks as $overwork) {
                array_push($export_data, [
                    $overwork->cname,
                    $overwork->agent_cname,
                    '加班',
                    '-',
                    $overwork->over_work_hours,
                    $overwork->over_work_date_f1,
                    '-',
                    $overwork->comment,
                    $overwork->apply_time
                ]);
            }
        }

        Excel::create('工時紀錄',function ($excel) use ($export_data){
            $excel->sheet('score', function ($sheet) use ($export_data){
            $sheet->rows($export_data);
            });
        })->export('xls');
    }
}
