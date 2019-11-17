<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\LeaveApplyRepository;
use Config;
use Log;
use Exception;
use DB;

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
                $r = json_decode(json_encode(self::getLeaveHours($start_datetime, date("Y").'-12-31 23:59:59', $work_class_id)));
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
                $r = json_decode(json_encode(self::getLeaveHours((date("Y")+1).'-01-01 00:00:00', $end_datetime, $work_class_id)));
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

    /**
     * 找出簽核路徑，一直找第一簽核人下去，直到下列條件任一發生就停止
     * 1. 找不到第一簽核人
     * 2. 找到休假的簽核職等
     * 3. 回傳下一個簽核人，因為有互相指定為下一個簽核人造成無窮迴圈的狀況，所以array最長為10
     * 4. 第一簽核人重覆
     * 5. 第一簽核人等於休假申請人
     * 
     * @author nino
     * @param  int      $apply_user_no
     * @param  int      $user_no
     * @param  array    $array
     * @param  int      $approved_title_id
     * @return array    
     */
    public function find_upper($apply_user_no, $user_no, $array, $approved_title_id) {
        $users = $this->userRepo->findUserByUserNo($user_no);
        if($users > 0) {
            foreach ($users as $u) {
                if(
                    $u->upper_user_no != 0 &&               //找到第一簽核人
                    ($u->title_id != $approved_title_id || $u->NO == $apply_user_no) &&   //簽核職等不對
                    count($array) < 10 &&                   //簽核人數量<10
                    !in_array($u->upper_user_no, $array) && //簽核人不重覆
                    $u->upper_user_no != $apply_user_no     //簽核人不等於申請人
                ) {
                    array_push($array, $u->upper_user_no);
                    return self::find_upper($apply_user_no, $u->upper_user_no, $array, $approved_title_id);
                } else {
                    return $array;
                }
            }
        }
    }

    /**
     * 回傳2個時間的休假小時
     *
     * @param  datetime  $start_datetime Y-m-dTH:i
     * @param  datetime  $end_datetime  Y-m-dTH:i
     * @param  int  $work_class_id
     * @return float 
     */
    public static function getLeaveHours($start_datetime, $end_datetime, $work_class_id) {
        try {
            $start_date = date_format(date_create($start_datetime),"Y-m-d");
            $end_date = date_format(date_create($end_datetime),"Y-m-d");
            $start_time = date_format(date_create($start_datetime),"H:i:s");
            $end_time = date_format(date_create($end_datetime),"H:i:s");

            $work_start = "";
            $work_end = "";
            $lunch_start = "";
            $lunch_end = "";
            $sql  = 'select * from eip_work_class where id =?';
            $workclasses = DB::select($sql, [$work_class_id]);
            if(count($workclasses) == 1) {
                foreach ($workclasses as $w) {
                    $work_start = $w->work_start;
                    $work_end = $w->work_end;
                    $lunch_start = $w->lunch_start;
                    $lunch_end = $w->lunch_end;
                }
            } else {
                throw new Exception('work_class_id error');
            }

            $dates = self::dates2array($start_date, $end_date);
            $leave_hours = 0;
            if(count($dates) == 1) { 
                //只請一天
                if(self::is_offday_by_gcalendar($start_date) == 8) { //先確定當天是不是休息日，不是休息日的話再來算請假小時
                    $xx = self::cal_timediff($start_time, $end_time, $work_start, $work_end, $lunch_start, $lunch_end);
                    $leave_hours += self::cal_timediff($start_time, $end_time, $work_start, $work_end, $lunch_start, $lunch_end);
                }
            } else if(count($dates) > 1){    
                //請超過一天(正常上班時間為08:00-17:00)
                foreach ($dates as $key=>$d) {
                    if($key == 0) {
                        if(self::is_offday_by_gcalendar($start_date) == 8) {
                            $leave_hours += self::cal_timediff($start_time, $work_end, $work_start, $work_end, $lunch_start, $lunch_end);
                        }
                    } else if($key == count($dates)-1) {
                        if(self::is_offday_by_gcalendar($end_date) == 8) {
                            $leave_hours += self::cal_timediff($work_start, $end_time, $work_start, $work_end, $lunch_start, $lunch_end);
                        }
                    } else {
                        $leave_hours += self::is_offday_by_gcalendar($d);
                    }
                }
            } else {
                throw new Exception('日期區間不合理');
            }
            return array('status' => 'successful', 'leave_hours' => $leave_hours);
        } catch (Exception $e) {
            return array('status' => 'error', 'message' => $e->getMessage());
        }
    }

    /**
     * 回傳2個時間間的相差小時
     *
     * @param  time  $time1
     * @param  time  $time2
     * @param  date  $work_start
     * @param  date  $work_end
     * @param  date  $lunch_start
     * @param  date  $lunch_end
     * @return float 
     */
    static protected function cal_timediff($time1, $time2, $work_start, $work_end, $lunch_start, $lunch_end) {
        if($time1 <= $work_start) $time1 = $work_start;
        if($time2 >= $work_end) $time2 = $work_end;

        if(strtotime($time1) >= strtotime($lunch_end) || strtotime($time2) <= strtotime($lunch_start)) {
            return (strtotime($time2) - strtotime($time1))/(60*60);
        } else if(strtotime($time1) > strtotime($lunch_start) && strtotime($time1) < strtotime($lunch_end)) {
            return (strtotime($time2) - strtotime($lunch_end))/(60*60);
        } else if(strtotime($time2) > strtotime($lunch_start) && strtotime($time2) < strtotime($lunch_end)){
            return (strtotime($lunch_start) - strtotime($time1))/(60*60);
        } else {
            return ((strtotime($time2) - strtotime($time1)) - (strtotime($lunch_end) - strtotime($lunch_start)))/(60*60);
        }
    }

    /**
     * 回傳2個日期間的所有日期，日期不合理回傳空array，date1和date2同天會回傳當天的array
     *
     * @param  date  $date1 Y-m-d
     * @param  date  $date2 Y-m-d
     * @return array [date, date]
     */
    static protected function dates2array($date1, $date2) {
        $return= array();
        $diff_date = (strtotime($date2) - strtotime($date1))/ (60*60*24); //計算相差之天數
        for ($i=0; $i<=$diff_date; $i++) {
            array_push($return, date('Y-m-d', strtotime('+'.$i.' days', strtotime($date1))));
        }
        return $return;
    }

    /**
     * 檢查是否為上班日
     *
     * @param  date  $check_date Y-m-d
     * @return int  8:上班日,0:休息日
     */
    static protected function is_offday_by_gcalendar($check_date) {
        $gcalendar_appscript_uri = Config::get('eip.gcalendar_appscript_uri');
        $calevents_str = self::get_req($gcalendar_appscript_uri."?type=check&checkDate=".$check_date);
        $calevents = explode(",", $calevents_str);
        $offhours = 8;
        foreach ($calevents as $e) {
            if(strpos($e,'休息日') !== false) {
                $offhours = $offhours - 8;
            }
        }
        return $offhours;
    }

    static protected function get_req($_url) {
        $ch = curl_init(trim($_url));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}