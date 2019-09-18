<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\LeaveApplyRepository;
use App\Providers\LeaveProvider;
use Log;
use Exception;

class ApplyLeaveService 
{
    protected $userRepo;
    protected $leaveApplyRepo;

    public function __construct
    (
        UserRepository $userRepo,
        LeaveApplyRepository $leaveApplyRepo
    )
    {
        $this->userRepo = $userRepo;
        $this->leaveApplyRepo = $leaveApplyRepo;
    }

    /**
     * 檢查特休假夠不夠
     * 
     * @author nino
     * @param  int          $user_no        申請者的user_no
     * @param  datetime     $start_datetime 休假的開始日期時間(ex:2019-01-01T10:01:00)
     * @param  datetime     $end_datetime   休假的結束日期時間(ex:2019-01-05T10:01:00)
     * @param  int          $leave_hours    休假的總時數
     * @param  int          $work_class_id  申請者的班別id
     * @return json
     */
    public function check_annual_leave($user_no, $start_datetime, $end_datetime, $leave_hours, $work_class_id) 
    {
        try {

            if(
                strtotime($start_datetime)<=strtotime(date("Y").'-12-31 23:59:59') &&    //檢查跨年度的假
                strtotime($end_datetime)>strtotime(date("Y").'-12-31 23:59:59')
            ) {
                //檢查今年的假夠不夠
                $r = json_decode(json_encode(LeaveProvider::getLeaveHours($start_datetime, date("Y").'-12-31 23:59:59', $work_class_id)));
                if($r->status != "successful") throw new Exception($r->message);
                $leave_this_y_hours = $r->leave_hours;

                $r = $this->leaveApplyRepo->get_annual_use_hours(date("Y"), $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $this_y_annual_use_hours = $r["total_hours"];

                $r = $this->leaveApplyRepo->get_annual_hours(date("Y"), $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $this_y_annual_hours = $r["total_hours"];

                if(($leave_this_y_hours+$this_y_annual_use_hours) > $this_y_annual_hours) {
                    throw new Exception('請假失敗:今年已無足夠的休假可用'); 
                }
                //檢查明年的假夠不夠
                $r = json_decode(json_encode(LeaveProvider::getLeaveHours((date("Y")+1).'-01-01 00:00:00', $end_datetime, $work_class_id)));
                if($r->status != "successful") throw new Exception($r->message);
                $leave_next_y_hours = $r->leave_hours;
                
                $r = $this->leaveApplyRepo->get_annual_use_hours(date("Y")+1, $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $next_y_annual_use_hours = $r["total_hours"];

                $r = $this->leaveApplyRepo->get_annual_hours(date("Y")+1, $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $next_y_annual_hours = $r["total_hours"];

                if(($leave_next_y_hours+$next_y_annual_use_hours) > $next_y_annual_hours) {
                    throw new Exception('請假失敗:明年已無足夠的休假可用'); 
                }
            } else if(
                strtotime($start_datetime)>=strtotime(date("Y").'-01-01 00:00:00') &&    //檢查今年度的假
                strtotime($end_datetime)<=strtotime(date("Y").'-12-31 23:59:59')
            ) {
                $leave_this_y_hours = $leave_hours;

                $r = $this->leaveApplyRepo->get_annual_use_hours(date("Y"), $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $this_y_annual_use_hours = $r["total_hours"];

                $r = $this->leaveApplyRepo->get_annual_hours(date("Y"), $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $this_y_annual_hours = $r["total_hours"];

                if(($leave_this_y_hours+$this_y_annual_use_hours) > $this_y_annual_hours) {
                    throw new Exception('請假失敗:已無足夠的休假可用'); 
                }
            } else if(
                strtotime($start_datetime)>=strtotime((date("Y")+1).'-01-01 00:00:00') && //檢查明年度的假
                strtotime($end_datetime)<=strtotime((date("Y")+1).'-12-31 23:59:59')
            ) {
                //檢查明年的假夠不夠
                $leave_next_y_hours = $leave_hours;
                
                $r = $this->leaveApplyRepo->get_annual_use_hours(date("Y")+1, $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $next_y_annual_use_hours = $r["total_hours"];

                $r = $this->leaveApplyRepo->get_annual_hours(date("Y")+1, $user_no);
                if($r["status"] != "successful") throw new Exception($r->message);
                $next_y_annual_hours = $r["total_hours"];

                if(($leave_next_y_hours+$next_y_annual_use_hours) > $next_y_annual_hours) {
                    throw new Exception('請假失敗:已無足夠的休假可用'); 
                }
            } else {
                throw new Exception('請假失敗:日期錯誤'); 
            }

            return [
                'status'=> 'successful'
            ];
        } catch (Exception $e) {
            return [
                'status'    => 'error',
                'message'   => $e->getMessage()
            ];
        }
    }

}