<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\HelperServiceProvider;
use DB;
use Log;
use Exception;
use Config;

date_default_timezone_set('Asia/Taipei');

class applyleave extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * 顯示applyleave頁面
     */
    public function create()
    {
        
        //因為有同一種假但不同天數的假別，所以做distinct name，之後新增假時，再判斷是用那一種假別的id
        $sql  ='select distinct elt.name, et.name as title_name, et.id as title_id ';
        $sql .='from eip_leave_type elt, eip_title et ';
        $sql .='where elt.approved_title_id = et.id ';
        $leavetypes = DB::select($sql, []);
        //用戶只列出有加入line的而且是status是T(True)
        $users = DB::select("select * from user where status = 'T' and line_id != '' order by cname", []);
        return view('line.applyleave', [
            'leavetypes' => $leavetypes,
            'users' => $users,
            'nowdate' => date("Y-m-d")
        ]);
    }

    /**
     * 新增一筆請假紀錄
     * 4個step:
     * step1: 取得要寫入請假相關table的資料(假別id,申請人、代理人和第一簽核人資料)
     * step2: 計算請假小時
     * step3: 寫入請假和簽核流程table
     * step4: 通知申請人、代理人和第一簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    public function store(Request $request)
    {
        try {
            $apply_user_line_id = $request->get('userId');            //申請者的line_id
            $leave_agent_user_no = $request->get('leaveAgent');       //代理人的user_NO
            $leavename = $request->get('leaveType');                  //假別名稱
            $start_date = explode("T",$request->get('startDate'))[0]; //起日
            $start_time = explode("T",$request->get('startDate'))[1]; //起時
            $end_date = explode("T",$request->get('endDate'))[0];     //迄日
            $end_time = explode("T",$request->get('endDate'))[1];     //迄時
            $comment = $request->input('comment');                    //備註
            if($comment == "") $comment = "-";

            //取得假別的id
            $leave_days = count(self::dates2array($start_date, $end_date));
            $leave_type_arr = DB::select('select * from eip_leave_type where name =? order by day ASC', [$leavename]);
            $leave_type_id = "";
            $i = 0;
            foreach ($leave_type_arr as $v) {
                $i++;
                if($leave_days < $v->day) {
                    $leave_type_id = $v->id;
                    break;
                }
                if($leave_type_id == "" && count($leave_type_arr) == $i){
                    $leave_type_id = $v->id;
                    break;
                }
            }
            //取得申請人的基本資料
            $sql = 'select u.NO, u.cname, ewc.* from user as u LEFT JOIN eip_work_class as ewc on u.work_class_id = ewc.id WHERE u.line_id = ?';
            $users = DB::select($sql, [$apply_user_line_id]);
            $apply_user_NO = "";            //申請人NO
            $apply_user_cname = "";         //申請人別名
            $apply_user_work_start = "";    //申請人的上班開始時間
            $apply_user_work_end = "";      //申請人的上班結束時間
            $apply_user_lunch_start = "";   //申請人的午休開始時間
            $apply_user_lunch_end = "";     //申請人的午休結束時間
            if(count($users) != 1) throw new Exception('請假失敗:請先將您的line加入EIP中'); 
            foreach ($users as $v) {
                $apply_user_no = $v->NO;
                $apply_user_cname = $v->cname;
                $apply_user_work_start = $v->work_start ? $v->work_start : '08:00:00'; 
                $apply_user_work_end = $v->work_end ? $v->work_end : '17:00:00';
                $apply_user_lunch_start = $v->lunch_start ? $v->lunch_start : '12:00:00';
                $apply_user_lunch_end = $v->lunch_end ? $v->lunch_end : '13:00:00';     
            }
            //取得代理人的資料
            $agent_users = DB::select('select cname, line_id from user where NO =?', [$leave_agent_user_no]);
            $agent_cname = ""; //代理人
            $agent_line_id = ""; //代理人的line_id
            foreach ($agent_users as $v) {
                $agent_cname = $v->cname;
                $agent_line_id = $v->line_id;
            }
            if($agent_line_id == "") throw new Exception('請假失敗:代理人的line未加入EIP中'); 
            //取得第一簽核人的資料
            $upper_users = DB::select('select NO, line_id from user where NO in (select upper_user_no from user where line_id =?)', [$apply_user_line_id]);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
            }
            if($upper_line_id == "") throw new Exception('請假失敗:未設定簽核人或簽核人的line未加入EIP中');
            //計算請假小時(目前先預設工時是早8晚5,30m為1單位)
            $dates = self::dates2array($start_date, $end_date);
            $leave_hours = 0;
            if(count($dates) == 1) { 
                //只請一天
                if(self::is_offday_by_gcalendar($start_date) == 8) { //先確定當天是不是休息日，不是休息日的話再來算請假小時
                    $leave_hours += self::cal_timediff($start_time, $end_time, $apply_user_lunch_start, $apply_user_lunch_end);
                }
            } else {    
                //請超過一天(正常上班時間為08:00-17:00)
                foreach ($dates as $key=>$d) {
                    if($key == 0) {
                        if(self::is_offday_by_gcalendar($start_date) == 8) {
                            $leave_hours += self::cal_timediff($start_time, "17:00", $apply_user_lunch_start, $apply_user_lunch_end);
                        }
                    } else if($key == count($dates)-1) {
                        if(self::is_offday_by_gcalendar($end_date) == 8) {
                            $leave_hours += self::cal_timediff("08:00", $end_time, $apply_user_lunch_start, $apply_user_lunch_end);
                        }
                    } else {
                        $leave_hours += self::is_offday_by_gcalendar($d);
                    }
                }
            }
            
            //寫入請假紀錄
            $sql = "insert into eip_leave_apply ";
            $sql .= "(apply_user_no, apply_type, agent_user_no, leave_type, start_date, end_date, leave_hours, comment) ";
            $sql .= "value ";
            $sql .= "(?, ?, ?, ?, ?, ?, ?, ?) ";
            if(DB::insert($sql, [$apply_user_no, 'L', $leave_agent_user_no, $leave_type_id, $start_date."T".$start_time, $end_date."T".$end_time, $leave_hours, $comment]) != 1) {
                throw new Exception('請假失敗:insert eip_leave_apply error'); 
            }
            //取得剛剛寫入的請假紀錄id
            $last_appy_record = DB::select('select max(id) as last_id from eip_leave_apply');
            $last_appy_id = ""; //假單流水號
            foreach ($last_appy_record as $v) {
                $last_appy_id = $v->last_id;
            }
            $upper_users = self::find_upper($apply_user_no, [], $leave_type_id);
            foreach ($upper_users as $u) {
                //寫入簽核流程紀錄(該table沒有紀錄申請人和簽核人的line_id是因為可能會有換line帳號的情況發生)
                if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$last_appy_id, 'L', $apply_user_no, $u]) != 1) {
                    DB::delete("delete from eip_leave_apply where id = ?", [$last_appy_id]);
                    DB::delete("delete from eip_leave_apply_process where apply_id = ?", [$last_appy_id]);
                    throw new Exception('insert db error'); 
                }
            }

            //通知申請人、代理人、第一簽核人
            Log::info("agent_line_id:".$agent_line_id);
            Log::info("upper_line_id:".$upper_line_id);
            //echo $agent_line_id;
            $msg = ["假別::". $leavename,"代理人::".$agent_cname,"起日::".$start_date." ".$start_time,"迄日::". $end_date ." ".$end_time,"備住::". $comment];
            LineServiceProvider::sendNotifyFlexMeg($apply_user_line_id, array_merge(["假單送出，請等待簽核"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($upper_line_id, array_merge(["請審核".$apply_user_cname."送出的假單"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($agent_line_id, array_merge([$apply_user_cname."指定您為請假代理人"], $msg));

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id = ?) as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.no ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.type_id = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.apply_user_no = u2.NO ';
        debug($sql);
        $leaves = DB::select($sql, [$id]);
        debug($leaves);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }

    /**
     * 回傳下一個簽核人
     *
     * @param  int      $user_no
     * @param  array    $array
     * @param  int      $approved_title_id
     * @return array    
     */
    static protected function find_upper($user_no, $array, $approved_title_id) {
        $users = DB::select('select title_id, upper_user_no from user where NO =?', [$user_no]);
        if($users > 0) {
            foreach ($users as $u) {
                if($u-> upper_user_no != 0 && $u-> title_id != $approved_title_id) {
                    array_push($array, $u-> upper_user_no);
                    return self::find_upper($u-> upper_user_no, $array, $approved_title_id);
                } else {
                    return $array;
                }
            }
        }
    }

    /**
     * 回傳2個時間間的相差小時，中午自動休一小時
     *
     * @param  string  $time1
     * @param  string  $time2
     * @return float 
     */
    static protected function cal_timediff($time1, $time2, $lunch_start, $lunch_end) {
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
     * 回傳2個日期間的所有日期
     *
     * @param  string  $date1
     * @param  string  $date2
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

    /**
     * 檢查日期的上班小時(deprecated)
     *
     * @param  string  $check_date Y-m-d
     * @return int
     */
    static protected function is_offday_by_gcalendar_by_sdk($check_date) {
        $gcalendar_key = Config::get('eip.gcalendar_key');
        log::info("gcalendar_key");
        log::info($gcalendar_key);
        $timeMin = rawurlencode($check_date."T00:00:00Z");
        $timeMax = rawurlencode($check_date."T01:00:00Z");
        //$timeMax = rawurlencode(date('Y-m-d', strtotime('+1 days', strtotime($check_date)))."T01:00:00Z");
        $calevents_str = HelperServiceProvider::get_req("https://www.googleapis.com/calendar/v3/calendars/nino.dev.try%40gmail.com/events?key=".$gcalendar_key."&timeMin=".$timeMin."&timeMax=".$timeMax);
        log::info("https://www.googleapis.com/calendar/v3/calendars/nino.dev.try%40gmail.com/events?key=".$gcalendar_key."&timeMin=".$timeMin."&timeMax=".$timeMax);
        $calevents = json_decode($calevents_str) -> items;
        $offhours = 8;
        foreach ($calevents as $e) {
            if($e-> status == "confirmed") {
                //if(strpos($e-> summary,'休息日') !== false && $e-> start-> date == $check_date) {
                if(strpos($e-> summary,'休息日') !== false) {
                    $offhours = $offhours - 8;
                }
            }
            if($e-> status == "cancelled") {
                $offhours = $offhours + 8;
            }
        }
        log::info($offhours);
        return $offhours;
    }
}
