<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\LeaveProvider;
use App\Providers\HelperServiceProvider;
use DB;
use Log;
use Config;
use DateTime;

class validateleave extends Controller
{
    /**
     * 顯示LIFF待審核清單資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $leaves = [];
        $applies = [];
        $NO = 0;
        $sql = 'select NO from user where line_id = ?';
        $users = DB::select($sql, [$id]);
        if(count($users) == 0) {
            return response()->json([
                'status' => 'successful',
                'data' => []
            ]);
        } else {
            foreach ($users as $u) {
                $NO = $u -> NO;
            }
        }

        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(  select a.id as process_id, b.* ';
        $sql .= '   from eip_leave_apply_process a, eip_leave_apply b ';
        $sql .= '   where b.apply_status = "P" and a.apply_id = b.id and a.is_validate IS NULL and a.upper_user_no IN ';
        $sql .= '   (select NO from user where line_id =?)';
        $sql .= ') as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 on a.apply_user_no = u2.NO';
        $leaves = DB::select($sql, [$id]);
        $new_leaves = [];
        foreach ($leaves as $l) {
            //去檢查這個no是不是這張假單的下一個簽核人
            $sql = 'select upper_user_no from eip_leave_apply_process ';
            $sql .='where apply_id = ? and is_validate IS NULL order by id limit 1 ';
            $next_upper_users = DB::select($sql, [$l->id]);
            foreach ($next_upper_users as $n) {
                $upper_user_no = $n -> upper_user_no;
                if($upper_user_no == $NO) {
                    array_push($new_leaves, $l);
                }
            }
        }
        return response()->json([
            'status' => 'successful',
            'data' => $new_leaves
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
            $apply_id       = $id;                              //申請單id
            //$line_id        = $request->get('userId');          //審核人line_id
            $is_validate    = $request->get('is_validate');     //審核結果(0=拒絕,1=同意)
            $apply_type     = $request->get('apply_type');      //申請單類型(L,O)
            $reject_reason  = $request->get('reject_reason');   //拒絕理由
            $process_id     = $request->get('process_id');      //eip_leave_apply_process的id
            
            //取得apply單的資料
            $apply_data = json_decode(LeaveProvider::getLeaveApply($apply_id));
            if($reject_reason == "null") {$reject_reason = null;}
            if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =?, validate_time = now() where id =?", [$is_validate, $reject_reason, $process_id]) != 1) {
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
                        if($apply_data->leave_compensatory == 1) {
                            //拒絕加班補休要把用那天加班來補的紀錄刪掉
                            DB::delete("delete from eip_compensatory_relationship where leave_apply_id = ?", [$apply_id]);
                        }
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
                    $insert_event = LeaveProvider::insert_event2gcalendar($apply_data->start_date, $apply_data->end_date, $apply_data->apply_user_cname."的".$apply_data->leave_name);
                    log::info($insert_event);
                    //if($insert_event == 0){ throw new Exception('Insert to google calendar failed!');  }
                    if(DB::update("update eip_leave_apply set apply_status =?, event_id =? where id =?", ['Y', $insert_event, $apply_id]) == 1) {
                        if($apply_data->apply_type == 'L') {
                            //如果是加班補休，要從補休中扣
                            if($apply_data->leave_compensatory == 1) {
                                self::use_compensatory_leave($apply_data);
                            }
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

    public function show_other_leaves($id)
    {
        $apply_user_no = $id;
        $sql  = "select ela.*, elt.name as leave_name ";
        $sql .= "from eip_leave_apply ela, eip_leave_type elt ";
        $sql .= "where ela.apply_user_no =? and ";
        $sql .= "ela.apply_type = 'L' and ela.apply_status IN ('Y','P') and ";
        $sql .= "ela.start_date >= now() and ela.leave_type = elt.id";
        $leaves = DB::select($sql, [$apply_user_no]);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }
    
}
