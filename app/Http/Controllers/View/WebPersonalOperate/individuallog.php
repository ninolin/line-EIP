<?php

namespace App\Http\Controllers\View\WebPersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Console\commands\CalcLeaveDays;
use App\Providers\LeaveProvider;
use App\Repositories\LeaveApplyRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use App\Repositories\AnnualLeaveRepository;
use DB;
use Log;
use Exception;
use Config;

class individuallog extends Controller
{
    private $calcL;
    protected $leaveApplyRepo;
    protected $leaveTypeRepo;
    protected $userRepo;
    protected $annualLeaveRepo;
    
    public function __construct(
        CalcLeaveDays $calcL,
        LeaveApplyRepository $leaveApplyRepo,
        LeaveTypeRepository $leaveTypeRepo,
        UserRepository $userRepo,
        AnnualLeaveRepository $annualLeaveRepo
    )
    {
        $this->calcL = $calcL;
        $this->leaveApplyRepo = $leaveApplyRepo;
        $this->leaveTypeRepo = $leaveTypeRepo;
        $this->userRepo = $userRepo;
        $this->annualLeaveRepo = $annualLeaveRepo;
    }

    /**
     * 顯示員工紀錄查詢頁面
     * 
     * @param  string  id
     * @return \Illuminate\Http\Response
     */
    public function show_view() 
    {
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
        $leave_day          = 0;
        $onboard_date       = "";
        $cname              = "";
        $user_no            = \Session::get('user_no') ?? null;
        $users              = $this->userRepo->findUserByUserNo($user_no);
        if(count($users) == 1) {
            foreach ($users as $v) {
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
            foreach ($types as $type) {
                $type->name = "已用".$type->name;
                $type->days = round($type->hours/8, 2);
            }
            array_push($types, (object) array('name' => '可用休假總數', 'hours' => $leave_day*8, 'days' => $leave_day));
        }

        return view('contents.WebPersonalOperate.individuallog', [
            'user_no'           => $user_no, 
            'show_tab'          => $show_tab,
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
            'tab'               => 'individual',
            'login_user_no'     => session('user_no')
        ]);
    }

}
