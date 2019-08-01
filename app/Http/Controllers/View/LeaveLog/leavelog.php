<?php

namespace App\Http\Controllers\View\LeaveLog;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Log;
use App\Console\commands\CalcLeaveDays;

class leavelog extends Controller
{
    private $calcL;

    public function __construct(CalcLeaveDays $calcL)
    {
        $this->calcL = $calcL;
    }

    public function list_logs($id)
    {
        $sql  = "select elap.*, u.cname ";
        $sql .= "from eip_leave_apply_process elap, user u ";
        $sql .= "where elap.upper_user_no = u.NO and elap.apply_id = ?";
        $processes = DB::select($sql, [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $processes
        ]);
    }

    /**
     * 顯示最近工時紀錄
     * @param  string  search
     * @param  string  leave_year
     * @return \Illuminate\Http\Response
     */
    public function show_last()
    {
        $search = Input::get('search', '');
        $page = Input::get('page', 1);
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply) as a ';
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
            if($value->apply_type == 'L') {
                $start_date = str_replace("T", " ", $value->start_date);
                $end_date = str_replace("T", " ", $value->end_date);
                $logs[$key]->start_date = $start_date;
                $logs[$key]->end_date = $end_date;
            }
        }
        $total_logs = DB::select('select * from eip_leave_apply', []);
        $total_pages = ceil(count($total_logs)/10);

        return view('contents.LeaveLog.leavelog', [
            'logs'          => $logs, 
            'search'        => $search,
            'page'          => $page,
            'total_pages'   => $total_pages,
            'tab'           => 'last'
        ]);
    }

    /**
     * 顯示員工紀錄頁面
     * @param  string  search
     * @param  string  leave_year
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

    public function change_upper_user(Request $request)
    {
        $apply_process_id = $request->get('apply_process_id');
        $user_NO = $request->get('user_NO');
        if(DB::update("update eip_leave_apply_process set upper_user_no =? where id =?", [$user_NO, $apply_process_id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ], 500);
        }
    }
}
