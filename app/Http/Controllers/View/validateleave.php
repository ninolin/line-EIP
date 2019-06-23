<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\LeaveApplyProvider;
use App\Providers\HelperServiceProvider;
use DB;
use Log;
use Config;

class validateleave extends Controller
{
    /**
     * 顯示LIFF待審核清單資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        // $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        // $sql .= 'from ';
        // $sql .= '(select * from eip_leave_apply where id IN (  ';
        // $sql .= '   select apply_id ';
        // $sql .= '   from eip_leave_apply_process ';
        // $sql .= '   where is_validate IS NULL and upper_user_no IN ';
        // $sql .= '   (select NO from user where line_id =?)) ';
        // $sql .= ') as a ';
        // $sql .= 'left join user as u1 ';
        // $sql .= 'on a.agent_user_no = u1.NO ';
        // $sql .= 'left join eip_leave_type ';
        // $sql .= 'on a.leave_type = eip_leave_type.id ';
        // $sql .= 'left join user as u2 ';
        // $sql .= 'on a.apply_user_no = u2.NO ';
        
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(  select a.id as process_id, b.* ';
        $sql .= '   from eip_leave_apply_process a, eip_leave_apply b ';
        $sql .= '   where a.apply_id = b.id and a.is_validate IS NULL and a.upper_user_no IN ';
        $sql .= '   (select NO from user where line_id =?)';
        $sql .= ') as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 on a.apply_user_no = u2.NO';
        debug($sql);
        $leaves = DB::select($sql, [$id]);
        debug($leaves);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }

    /**
     * 顯示LIFF待審核清單頁面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('line.validateleave', []);
    }

    /**
     * 審核請假/加班
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $apply_id = $id;                                //申請單id
            $line_id = $request->get('userId');             //審核人line_id
            $is_validate = $request->get('is_validate');    //審核結果(0=拒絕,1=同意)
            $apply_type = $request->get('apply_type');      //申請單類型(L,O)
            $reject_reason = $request->get('reject_reason');//拒絕理由
            $process_id = $request->get('process_id');      //eip_leave_apply_process的id
            // $users = DB::select('select NO, title_id, upper_user_no from user where line_id = ?', [$line_id]);
            // $NO = "";                   //審核人NO
            // $title_id = "";             //審核人title_id
            // $upper_user_no = "";        //審核人的下一個審核人的user_no
            // foreach ($users as $v) {
            //     $NO = $v->NO;
            //     $title_id = $v->title_id;
            //     $upper_user_no = $v->upper_user_no;
            // }
            
            //取得apply單的資料
            $apply_data = json_decode(LeaveApplyProvider::getLeaveApply($apply_id));
            if(is_null($apply_data)) { throw new Exception('The line_id is not exist in the EIP');  }
            if($reject_reason == "null") {$reject_reason = null;}
            if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =? where id =?", [$is_validate, $reject_reason, $process_id]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            }

            if($is_validate == 0) {
                //拒絕審核
                if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['N', $apply_id]) != 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'update error'
                    ]);
                } else {
                    if($apply_data->apply_type == 'L') {
                        LineServiceProvider::sendNotifyFlexMeg($apply_data->apply_user_line_id, array_merge(["請假不通過","原因::".$reject_reason,"假別::".$apply_data->leave_name,"起日::".$apply_data->start_date,"迄日::".$apply_data->end_date,"備註::". $apply_data->comment]));
                    } else {
                        LineServiceProvider::sendNotifyFlexMeg($apply_data->apply_user_line_id, array_merge(["加班不通過","原因::".$reject_reason,"加班日::".$apply_data->over_work_date,"加班小時::".$apply_data->over_work_hours,"備註::". $apply_data->comment]));
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            } else {
                //同意審核
                $processes = DB::select('select id from eip_leave_apply_process where apply_id = ? order by id desc limit 1', [$apply_id]);
                $last_approved_id = 0;
                foreach ($processes as $v) {
                    $last_approved_id = $v->id;
                }
                log::info($last_approved_id);
                log::info($process_id);
                if($last_approved_id == $process_id) {
                    //全部審核完了
                    if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['Y', $apply_id]) == 1) {
                        self::insert_event2gcalendar($apply_data->start_date, $apply_data->end_date, $apply_data->apply_user_cname."的".$apply_data->leave_name);
                        if($apply_data->apply_type == 'L') {
                            LineServiceProvider::sendNotifyFlexMeg($apply_data->apply_user_line_id, array_merge(["請假已通過","假別::".$apply_data->leave_name,"起日::".$apply_data->start_date,"迄日::".$apply_data->end_date,"備註::". $apply_data->comment]));
                        } else {
                            LineServiceProvider::sendNotifyFlexMeg($apply_data->apply_user_line_id, array_merge(["加班已通過","加班日::".$apply_data->over_work_date,"加班小時::".$apply_data->over_work_hours,"備註::". $apply_data->comment]));
                        }
                        return response()->json([
                            'status' => 'successful'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'update error'
                        ]);
                    }
                } else {
                    //繼續給下一個人審核
                    $next_process_sql  = "select upper_user_no from eip_leave_apply_process where apply_id = ? and id > ? ";
                    $next_process_sql .= "order by id desc limit 1 ";
                    $next_process = DB::select($next_process_sql, [$apply_id, $process_id]);
                    $next_approved_user_no = 0;
                    foreach ($next_process as $v) {
                        $next_approved_user_no = $v->upper_user_no;
                    }

                    $upper_users = DB::select('select line_id from user where NO = ?', [$next_approved_user_no]);
                    $upper_user_line_id = "";   //審核人的下一個審核人的line_id
                    foreach ($upper_users as $v) {
                        $upper_user_line_id = $v->line_id;
                    }
                    if($apply_data->apply_type == 'L') {
                        LineServiceProvider::sendNotifyFlexMeg($upper_user_line_id, array_merge(["請審核".$apply_data->apply_user_cname."送出的假單","假別::".$apply_data->leave_name,"起日::".$apply_data->start_date,"迄日::".$apply_data->end_date,"備註::". $apply_data->comment]));
                    } else {
                        LineServiceProvider::sendNotifyFlexMeg($upper_user_line_id, ["請審核".$apply_data->apply_user_cname."送出的加班","加班日::".$apply_data->over_work_date,"加班小時::".$apply_data->over_work_hours,"備註::". $apply_data->comment]);
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 請假寫入google calendar
     *
     * @param  string  $start_date
     * @param  string  $end_date
     * @param  string  $title
     * @return int
     */
    static protected function insert_event2gcalendar($start_date, $end_date, $title) {
        $gcalendar_appscript_uri = Config::get('eip.gcalendar_appscript_uri');
        log::info($gcalendar_appscript_uri);
        $json_str = '{
            "title": "'.$title.'", 
            "start": "'.$start_date.':00",
            "end": "'.$end_date.':00"
        }';
        log::info($json_str);
        $calevents_str = HelperServiceProvider::post_req($gcalendar_appscript_uri, $json_str);
        if(strpos($calevents_str,'Exception') !== false){ 
            return 0;
        }else{
            return 1;
        }
    }
}
