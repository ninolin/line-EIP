<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\LeaveApplyProvider;
use DB;
use Log;

class validateleave extends Controller
{
    /**
     * 顯示LIFF待審核清單資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id IN (  ';
        $sql .= '   select apply_id ';
        $sql .= '   from eip_leave_apply_process ';
        $sql .= '   where is_validate IS NULL and upper_user_no IN ';
        $sql .= '   (select NO from user where line_id =?)) ';
        $sql .= ') as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
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
            $apply_id = $id;
            $line_id = $request->get('userId');
            $is_validate = $request->get('is_validate');
            $apply_type = $request->get('apply_type');
            $reject_reason = $request->get('reject_reason');
            $users = DB::select('select NO, title_id, upper_user_no from user where line_id = ?', [$line_id]);
            $NO = "";                   //審核人NO
            $title_id = "";             //審核人title_id
            $upper_user_no = "";        //審核人的下一個審核人的user_no
            foreach ($users as $v) {
                $NO = $v->NO;
                $title_id = $v->title_id;
                $upper_user_no = $v->upper_user_no;
            }

            $apply_user = json_decode(LeaveApplyProvider::getLeaveApply($apply_id));
            if(is_null($apply_user)) { throw new Exception('The line_id is not exist in the EIP');  }
            if($reject_reason == "null") {$reject_reason = null;}
            if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =? where apply_id =? and upper_user_no =?", [$is_validate, $reject_reason, $apply_id, $NO]) != 1) {
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
                    if($apply_user->apply_type == 'L') {
                        LineServiceProvider::sendNotifyFlexMeg($apply_user->apply_user_line_id, array_merge(["請假不通過","原因:".$reject_reason,"假別:".$apply_user->leave_name,"起日:".$apply_user->start_date,"迄日:".$apply_user->end_date,"備註:". $apply_user->comment]));
                    } else {
                        LineServiceProvider::sendNotifyFlexMeg($apply_user->apply_user_line_id, array_merge(["加班不通過","原因:".$reject_reason,"加班日:".$apply_user->over_work_date,"加班小時:".$apply_user->over_work_hours,"備註:". $apply_user->comment]));
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            } else {
                //同意審核
                $processes = DB::select('select upper_user_no from eip_leave_apply_process where apply_id = ? order by id desc limit 1', [$apply_id]);
                $last_approved_user_no = 0;
                foreach ($processes as $v) {
                    $last_approved_user_no = $v->upper_user_no;
                }
            
                if($last_approved_user_no == $NO) {
                    //全部審核完了
                    if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['Y', $apply_id]) != 1) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'update error'
                        ]);
                    } else {
                        if($apply_user->apply_type == 'L') {
                            LineServiceProvider::sendNotifyFlexMeg($apply_user->apply_user_line_id, array_merge(["請假已通過","假別:".$apply_user->leave_name,"起日:".$apply_user->start_date,"迄日:".$apply_user->end_date,"備註:". $apply_user->comment]));
                        } else {
                            LineServiceProvider::sendNotifyFlexMeg($apply_user->apply_user_line_id, array_merge(["加班已通過","加班日:".$apply_user->over_work_date,"加班小時:".$apply_user->over_work_hours,"備註:". $apply_user->comment]));
                        }
                        return response()->json([
                            'status' => 'successful'
                        ]);
                    }
                } else {
                    //繼續給下一個人審核
                    $next_process_sql  = "select upper_user_no from eip_leave_apply_process where apply_id = ? ";
                    $next_process_sql .= "and id > ( select id from eip_leave_apply_process where apply_id = ? and upper_user_no =?) ";
                    $next_process_sql .= "order by id desc limit 1 ";
                    $next_process = DB::select($next_process_sql, [$apply_id, $apply_id, $NO]);
                    $next_approved_user_no = 0;
                    foreach ($next_process as $v) {
                        $next_approved_user_no = $v->upper_user_no;
                    }

                    $upper_users = DB::select('select line_id from user where NO = ?', [$next_approved_user_no]);
                    $upper_user_line_id = "";   //審核人的下一個審核人的line_id
                    foreach ($upper_users as $v) {
                        $upper_user_line_id = $v->line_id;
                    }
                    if($apply_user->apply_type == 'L') {
                        LineServiceProvider::sendNotifyFlexMeg($upper_user_line_id, array_merge(["請審核".$apply_user->apply_user_cname."送出的假單","假別:".$apply_user->leave_name,"起日:".$apply_user->start_date,"迄日:".$apply_user->end_date,"備註:". $apply_user->comment]));
                    } else {
                        LineServiceProvider::sendNotifyFlexMeg($upper_user_line_id, ["請審核".$apply_user->apply_user_cname."送出的加班","加班日:".$apply_user->over_work_date,"加班小時:".$apply_user->over_work_hours,"備註:". $apply_user->comment]);
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

}
