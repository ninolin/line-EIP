<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\LeaveProvider;
use App\Repositories\AnnualLeaveRepository;
use App\Repositories\LeaveApplyRepository;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\OverworkTypeRepository;
use App\Services\UserService;
use DB;
use Log;
use Exception;

class individuallog extends Controller
{
    protected $annualLeaveRepo;
    protected $leaveApplyRepo;
    protected $leaveTypeRepo;
    protected $overworkTypeRepo;
    protected $userService;

    public function __construct
    (
        AnnualLeaveRepository $annualLeaveRepo,
        LeaveApplyRepository $leaveApplyRepo,
        LeaveTypeRepository $leaveTypeRepo,
        OverworkTypeRepository $overworkTypeRepo,
        UserService $userService
    )
    {
        $this->annualLeaveRepo = $annualLeaveRepo;
        $this->leaveApplyRepo = $leaveApplyRepo;
        $this->leaveTypeRepo = $leaveTypeRepo;
        $this->overworkTypeRepo = $overworkTypeRepo;
        $this->userService = $userService;
    }

    /**
     * 顯示個人請假紀錄畫面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function get_leavetype()
    {
        try {
            $types = $this->leaveTypeRepo->findDistinctType();
            return response()->json([
                'status' => 'successful',
                'data' => $types
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ]);
        }
    }

    public function get_individual_log($type_name, $line_id)
    {
        try {
            $user = $this->userService->get_user_info($line_id, 'line');
            if($user['status'] == 'error') throw new Exception($user['message']);
            $user_no = $user['data']->NO;
            //透過假別的名稱去找到該假別全部的id
            $types_id = [];
            $is_annual = false;
            $is_compensatory = false;
            if($type_name == '加班') {
                $types = $this->overworkTypeRepo->findAllType();
                foreach ($types as $t) {
                    array_push($types_id, $t->id);
                }
                //取得今年加班全部的紀錄
                $leaves = $this->leaveApplyRepo->findPersonalOverworkLog($user_no, null, date("Y").'-01-01 00:00:00', date("Y").'-12-31 23:59:59');
            } else {
                $types = $this->leaveTypeRepo->findTypeByName($type_name);
                foreach ($types as $t) {
                    if($t->annual == true) $is_annual = true;
                    if($t->compensatory == true) $is_compensatory = true;
                    array_push($types_id, $t->id);
                }
                //取得今年某假別全部的紀錄
                $leaves = $this->leaveApplyRepo->findPersonalLeaveLog($user_no, null, date("Y").'-01-01 00:00:00', date("Y").'-12-31 23:59:59', $types_id);
            }
            

            $success_hours = 0;
            $process_hours = 0;
            foreach ($leaves as $l) {
                if($l->apply_status == 'Y' && $l->apply_type == 'L') $success_hours += $l->leave_hours;
                if($l->apply_status == 'P' && $l->apply_type == 'L') $process_hours += $l->leave_hours;
                if($l->apply_status == 'Y' && $l->apply_type == 'O') $success_hours += $l->leave_hours;
                if($l->apply_status == 'P' && $l->apply_type == 'O') $process_hours += $l->leave_hours;
            }
            $annual_hours = 0;
            if($is_annual) {
                $annual_hours = $this->annualLeaveRepo->findAnnualDays($user_no, date("Y"))*8;
            }
            return view('line.individuallog', [
                'leaves'            =>  $leaves,
                'today'             =>  date("Y-m-d H:i:s"),
                'success_hours'     =>  $success_hours,
                'process_hours'     =>  $process_hours,
                'is_annual'         =>  $is_annual,
                'is_compensatory'   =>  $is_compensatory,
                'annual_hours'      =>  $annual_hours
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e
            ]);
        }
    }
    /**
     * LINE上顯示個人工時資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($line_id)
    {
        try {
            $user = $this->userService->get_user_info($line_id, 'line');
            if($user['status'] == 'error') throw new Exception($user['message']);
            $user_no = $user['data']->NO;

            $leaves = $this->leaveApplyRepo->findPersonalLeaveLog($user_no);

            return response()->json([
                'status' => 'successful',
                'data' => $leaves
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ]);
        }
    }

    /**
     * 取消請假/加班
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        try {
            $apply_id = $id;   
            $sql  = 'select event_id from eip_leave_apply where apply_status =? and id =?';
            $leaves = DB::select($sql, ['Y', $apply_id]);
            if(count($leaves) > 0) {
                foreach($leaves as $l) {
                    LeaveProvider::delete_event_from_gcalendar($l->event_id);
                }
            }
            if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['C', $apply_id]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            } else {
                return response()->json([
                    'status' => 'successful'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
