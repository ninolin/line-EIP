<?php

namespace App\Providers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\ServiceProvider;
use Log;
use DB;
use Config;
use Exception;
use App\Providers\LeaveProvider;

class LeaveProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    //取得apply單的資料
    public static function getLeaveApply($apply_id)
    {
        $sql  = 'select a.*, u2.cname as apply_user_cname, u2.line_id as apply_user_line_id, u1.cname as agent_cname, u1.line_id as agent_user_line_id, eip_leave_type.name as leave_name, eip_leave_type.compensatory as leave_compensatory ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id = ?) as a ';
        $sql .= 'left join user as u1 '; //u1是agent user
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 '; //u2是apply user
        $sql .= 'on a.apply_user_no = u2.NO ';
        $apply = DB::select($sql, [$apply_id]);
        if(count($apply) == 1) {
            return json_encode($apply[0]);
        } else {
            return 0;
        }
    }

    public static function getLeaveHours($start_date, $end_date, $work_class_id) {
        try {
            log::info($start_date);
            log::info($end_date);
            log::info($work_class_id);
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
                    $leave_hours += self::cal_timediff(date_format(date_create($start_date),"H:i:s"), date_format(date_create($end_date),"H:i:s"), $work_start, $work_end, $lunch_start, $lunch_end);
                }
            } else if(count($dates) > 1){    
                //請超過一天(正常上班時間為08:00-17:00)
                foreach ($dates as $key=>$d) {
                    if($key == 0) {
                        if(self::is_offday_by_gcalendar($start_date) == 8) {
                            $leave_hours += self::cal_timediff(date_format(date_create($start_date),"H:i:s"), $work_end, $work_start, $work_end, $lunch_start, $lunch_end);
                        }
                    } else if($key == count($dates)-1) {
                        if(self::is_offday_by_gcalendar($end_date) == 8) {
                            $leave_hours += self::cal_timediff($work_start, date_format(date_create($end_date),"H:i:s"), $work_start, $work_end, $lunch_start, $lunch_end);
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
     * @param  date  $date1
     * @param  date  $date2
     * @return array 
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
     * 檢查日期的上班小時
     *
     * @param  string  $check_date Y-m-d
     * @return int
     */
    static protected function is_offday_by_gcalendar($check_date) {
        $gcalendar_appscript_uri = Config::get('eip.gcalendar_appscript_uri');
        $calevents_str = HelperServiceProvider::get_req($gcalendar_appscript_uri."?checkDate=".$check_date);
        $calevents = explode(",", $calevents_str);
        $offhours = 8;
        foreach ($calevents as $e) {
            if(strpos($e,'休息日') !== false) {
                $offhours = $offhours - 8;
            }
        }
        return $offhours;
    }
}
