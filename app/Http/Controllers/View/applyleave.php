<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\HelperServiceProvider;
use App\Providers\LeaveProvider;
use App\Services\UserService;
use App\Repositories\LeaveApplyRepository;
use App\Services\ApplyLeaveService;
use DB;
use Log;
use Exception;
use Config;

date_default_timezone_set('Asia/Taipei');

class applyleave extends Controller
{

    protected $userService;
    protected $applyLeaveService;
    protected $leaveApplyRepo;

    public function __construct(
        UserService $userService,
        ApplyLeaveService $applyLeaveService,
        LeaveApplyRepository $leaveApplyRepo
    )
    {
        $this->userService = $userService;
        $this->applyLeaveService = $applyLeaveService;
        $this->leaveApplyRepo = $leaveApplyRepo;
    }

    /**
     * 透過lineid取得user的資料
     */
    public function get_user_by_line_id($user_id,$use_mode=null)
    {
        $sql = "select u.*, ewc.work_start, ewc.work_end ";
        $sql.= "from user u left join eip_work_class ewc on u.work_class_id = ewc.id ";

        if($use_mode == 'web'){
            $sql.= "where u.status = 'T' and u.NO =?";

            $users = DB::select($sql, [$user_id]);
            return [
                'status' => 'successful',
                'data' => $users
            ];
        }else{
            $sql.= "where u.status = 'T' and u.line_id =?";
            $users = DB::select($sql, [$user_id]);
            return response()->json([
                'status' => 'successful',
                'data' => $users
            ]);
        }
    }

    /**
     * 顯示applyleave頁面
     */
    public function create()
    {
        //因為有同一種假但不同天數的假別，所以做distinct name，之後新增假時，再判斷是用那一種假別的id
        $sql  ='select distinct name from eip_leave_type';
        $leavetypes = DB::select($sql, []);
        //用戶只列出有加入line的而且是status是T(True)
        $users = DB::select("select * from user where status = 'T' order by cname", []);
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
            $apply_user_line_id = $request->get('userId');                      //申請者的line_id
            $leave_agent_user_no= $request->get('leaveAgent');                  //代理人的user_NO
            $leavename          = $request->get('leaveType');                   //假別名稱
            $start_datetime     = $request->get('startDate');                   //起日時
            $start_date         = explode("T",$request->get('startDate'))[0];   //起日
            $start_time         = explode("T",$request->get('startDate'))[1];   //起時
            $end_datetime       = $request->get('endDate');                     //迄日時
            $end_date           = explode("T",$request->get('endDate'))[0];     //迄日
            $end_time           = explode("T",$request->get('endDate'))[1];     //迄時
            $comment            = $request->input('comment');                   //備註
            $use_mode           = $request->input('use_mode');         
            if($comment == "") $comment = "-";
            
            //檢查不能申請上個月之前的休假(上個月也不行)
            $start_m = date_format(date_create($start_date),"Ym");
            $now_m = date("Ym");
            if($start_m < $now_m) throw new Exception('只能申請這個月以後的休假'); 

            //取得申請人的基本資料
            $user = $this->userService->get_user_info($apply_user_line_id, $use_mode);
            if($user['status'] == 'error') throw new Exception($user['message']);
            $apply_user_no = $user['data']->NO;
            $apply_user_cname = $user['data']->cname;
            $apply_work_class_id = $user['data']->work_class_id; 
            $apply_user_line_id = $user['data']->line_id; 

            //檢查請假是否有其它休假
            if($this->leaveApplyRepo->check_leave_is_overlap($apply_user_no, $start_datetime)) {
                throw new Exception('請假期間內已有其它休假'); 
            }
            //檢查請假合理性-檢查代理人在該假單請假時間中是否也正在請假
            $sql  = "select start_date from eip_leave_apply where ";
            $sql .= "apply_user_no = ? and start_date <= ? and end_date >= ? and apply_type = 'L' and apply_status IN ('P', 'Y')";
            $overlap = DB::select($sql, [$leave_agent_user_no, $start_datetime, $end_datetime]);
            if(count($overlap) > 0) throw new Exception('代理人在該假單請假時間中也正在請假'); 

            //檢查請假合理性-檢查請假時間中有沒有正在擔任代理人
            $sql  = "select start_date from eip_leave_apply where agent_user_no =? ";
            $sql .= "and start_date <= ? and end_date >= ? and apply_type = 'L' and apply_status IN ('P', 'Y')";
            $overlap = DB::select($sql, [$apply_user_no, $start_datetime, $end_datetime]);
            if(count($overlap) > 0) throw new Exception('請假時間中正在擔任代理人'); 

            //取得假別的id
            $leave_days = count(self::dates2array($start_date, $end_date));
            $leave_type_arr = DB::select('select * from eip_leave_type where name =? order by day ASC', [$leavename]);
            $leave_type_id = "";
            $leave_min_time = 30;
            $leave_compensatory = 0;
            $leave_annual = 0;
            $leave_approved_title_id = "";
            $i = 0;
            foreach ($leave_type_arr as $v) {
                $i++;
                if($leave_days < $v->day || count($leave_type_arr) == $i) {
                    $leave_type_id = $v->id;
                    $leave_approved_title_id = $v->approved_title_id;
                    $leave_min_time = $v->min_time;
                    $leave_compensatory = $v->compensatory;
                    $leave_annual = $v->annual;
                    break;
                }
            }
            //檢查休假時間是否大於最小請假時間
            $diff_min = floor((strtotime($end_date." ".$end_time)-strtotime($start_date." ".$start_time))%86400/60); //請假分鐘
            if((int)$diff_min <= (int)$leave_min_time) throw new Exception('最小請假時間為'.$leave_min_time.'分鐘'); 

            //計算請假小時
            $leave_hours = 0;
            $r = json_decode(json_encode(LeaveProvider::getLeaveHours($start_datetime, $end_datetime, $apply_work_class_id)));
            if($r->status == "successful") {
                $leave_hours = $r->leave_hours;
            } else {
                throw new Exception($r->message);
            }

            //檢查特休假合理性-檢查有沒有足夠的休假可用
            if($leave_annual == 1) {
                $check = $this->applyLeaveService->check_annual_leave($apply_user_no, $start_datetime, $end_datetime, $leave_hours, $apply_work_class_id);
                if($check['status'] != 'successful') throw new Exception($check['message']);
            }

            //檢查補休假合理性-檢查有沒有足夠的加班可以補休
            if($leave_compensatory == 1) {
                $sql  = "select sum(over_work_hours) as total_over_work_hours from eip_leave_apply where apply_user_no =? and apply_type = 'O' and DATEDIFF(now(), over_work_date) <= 180 and apply_status = 'Y'";
                $data = DB::select($sql, [$apply_user_no]);
                $total_over_work_hours = 0;
                foreach ($data as $d) {
                    $total_over_work_hours = $d->total_over_work_hours; //180天內加班總時數
                }
                $sql  = "select sum(leave_hours) as total_leave_hours from eip_leave_apply where id IN ( ";
                $sql .= "   select distinct leave_apply_id from eip_compensatory_relationship ";
                $sql .= "   where over_work_apply_id IN ( ";
                $sql .= "       select id from eip_leave_apply where apply_user_no =? and apply_type = 'O' and DATEDIFF(now(), over_work_date) <= 180 and apply_status = 'Y' ";
                $sql .= "   ) ";
                $sql .= ") ";
                $data = DB::select($sql, [$apply_user_no]);
                $total_over_work_used_leave_hours = 0;
                foreach ($data as $d) {
                    $total_over_work_used_leave_hours = $d->total_leave_hours; //180天內加班已補休的總時數
                }
                $remain_over_work_can_leave_hours = $total_over_work_hours - $total_over_work_used_leave_hours;
                if($leave_hours > $remain_over_work_can_leave_hours) throw new Exception('請假失敗:已無足夠的補休假可用'); 
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
            $upper_users = self::find_upper($apply_user_no, [], $leave_approved_title_id);
            foreach ($upper_users as $u) {
                //寫入簽核流程紀錄(該table沒有紀錄申請人和簽核人的line_id是因為可能會有換line帳號的情況發生)
                if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$last_appy_id, 'L', $apply_user_no, $u]) != 1) {
                    DB::delete("delete from eip_leave_apply where id = ?", [$last_appy_id]);
                    DB::delete("delete from eip_leave_apply_process where apply_id = ?", [$last_appy_id]);
                    throw new Exception('insert db error'); 
                }
            }
            //加班補休要寫入eip_compensatory_relationship記錄補休是用那天加班來補
            if($leave_compensatory == 1) {
                $sql  = "select id, over_work_hours from eip_leave_apply where apply_user_no =? and apply_type = 'O' and DATEDIFF(now(), over_work_date) <= 180 and apply_status = 'Y' order by id ASC";
                $over_work_leaves = DB::select($sql, [$apply_user_no]); //180天內加班紀錄
                foreach ($over_work_leaves as $o) {
                    $over_work_use = DB::select("select sum(leave_hours) as leave_hours from eip_leave_apply where id IN (select leave_apply_id from eip_compensatory_relationship where over_work_apply_id =?)", [$o->id]);
                    $over_work_remain_hours = 0;
                    foreach ($over_work_use as $ow) {
                        $over_work_remain_hours = $o->over_work_hours - $ow->leave_hours; //這筆加班剩幾小時的補休可用
                    }
                    if($over_work_remain_hours > 0) {
                        DB::insert("insert into eip_compensatory_relationship (leave_apply_id, over_work_apply_id) value (?, ?)", [$last_appy_id, $o->id]);
                        $leave_hours = $leave_hours - $over_work_remain_hours;
                        if($leave_hours <= 0) break;
                    }
                }
            }
            //取得代理人的資料
            $agent_users = DB::select('select cname, line_id from user where NO =?', [$leave_agent_user_no]);
            $agent_cname = ""; //代理人
            $agent_line_id = ""; //代理人的line_id
            foreach ($agent_users as $v) {
                $agent_cname = $v->cname;
                $agent_line_id = $v->line_id;
            }

            //取得第一簽核人的資料
            $upper_users = DB::select('select NO, line_id from user where NO in (select upper_user_no from user where NO =?)', [$apply_user_no]);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
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
                'message' => $e->getMessage()
            ], 500);
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
        $apply_leave    = $this->leaveApplyRepo->findApplyLeave($id);
        return response()->json([
            'status'    => 'successful',
            'data'      => $apply_leave
        ]);
        // $sql  = 'select a.*, ';
        // $sql .= 'DATE_FORMAT(a.start_date, "%Y-%m-%d %H:%m") as start_date_f1,
        // DATE_FORMAT(a.end_date, "%Y-%m-%d %H:%m") as end_date_f1,
        // DATE_FORMAT(a.start_date, "%Y-%m-%dT%H:%m") as start_date_f2,
        // DATE_FORMAT(a.end_date, "%Y-%m-%dT%H:%m") as end_date_f2,
        // u2.cname as cname, u2.work_class_id, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        // $sql .= 'from ';
        // $sql .= '(select * from eip_leave_apply where id = ?) as a ';
        // $sql .= 'left join user as u1 ';
        // $sql .= 'on a.agent_user_no = u1.no ';
        // $sql .= 'left join eip_leave_type ';
        // $sql .= 'on a.leave_type = eip_leave_type.id ';
        // $sql .= 'left join user as u2 ';
        // $sql .= 'on a.apply_user_no = u2.NO ';
        // debug($sql);
        // $leaves = DB::select($sql, [$id]);
        // debug($leaves);
        // return response()->json([
        //     'status' => 'successful',
        //     'data' => $leaves
        // ]);
    }

    /**
     * 回傳下一個簽核人，因為有互相指定為下一個簽核人造成無窮迴圈的狀況，所以array最長為10
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
                if(
                    $u->upper_user_no != 0 && 
                    $u->title_id != $approved_title_id && 
                    count($array) < 10 && 
                    !in_array($u->upper_user_no, $array)
                ) {
                    array_push($array, $u->upper_user_no);
                    return self::find_upper($u-> upper_user_no, $array, $approved_title_id);
                } else {
                    return $array;
                }
            }
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
