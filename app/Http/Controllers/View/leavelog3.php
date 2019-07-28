<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Log;

class leavelog3 extends Controller
{
    public function list_logs($id)
    {
        $sql  = "select elap.*, u.cname ";
        $sql .= "from eip_leave_apply_process elap, user u ";
        $sql .= "where elap.upper_user_no = u.NO and elap.apply_id = ?";
        $processes = DB::select($sql, [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $processes
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $sql =  "select approved_title_id from eip_leave_type where id in (select leave_type from eip_leave_apply where id = ? and apply_type = 'L')";
        $sql .= "union ";
        $sql .= "select approved_title_id from eip_overwork_type where id in (select leave_type from eip_leave_apply where id = ? and apply_type = 'O')";
        $leavetypes = DB::select($sql, [$id, $id]);
        $approved_title_id = ""; //該假別最後審核人的職等
        foreach ($leavetypes as $v) {
            $approved_title_id = $v->approved_title_id;
        }

        $users = DB::select("select apply_user_no from eip_leave_apply where id =?", [$id]);
        $apply_user_no = ""; //申請人no
        foreach ($users as $v) {
            $apply_user_no = $v->apply_user_no;
        }
        //因為db的設計機制是類似linklist的方式把員工的簽核人串起來的，所以會產生無窮迴圈的可能，所以最多跑10個簽核人
        $result = [];
        for ( $i=0 ; $i<10 ; $i++ ) {
            $users = DB::select("select NO, cname, title_id from user where NO IN (select upper_user_no from user where NO =?)", [$apply_user_no]);
            $user_no = "";
            $user_cname = "";
            $user_title_id = "";
            foreach ($users as $v) {
                $user_no = $v->NO;
                $apply_user_no = $v->NO;
                $user_cname = $v->cname;
                $user_title_id = $v->title_id;
            }

            $process = DB::select("select * from eip_leave_apply_process where apply_id =? and upper_user_no =?", [$id, $user_no]);
            if(count($process) > 0) {
                $is_validate = "";
                $reject_reason = "";
                $validate_time = "";
                foreach ($process as $v) {
                    $is_validate = $v->is_validate;
                    $reject_reason = $v->reject_reason;
                    $validate_time = $v->validate_time;
                }
                $arr = array(
                    'user_cname' => $user_cname, 
                    'is_validate' => $is_validate,
                    'reject_reason' => $reject_reason,
                    'validate_time' => $validate_time
                );
                array_push($result, $arr);
            } else {
                $arr = array(
                    'user_cname' => $user_cname, 
                    'is_validate' => '',
                    'reject_reason' => '',
                    'validate_time' => ''
                );
                array_push($result, $arr);
            }
            if($user_title_id == $approved_title_id){
                break;
            }
        }
        return response()->json([
            'status' => 'successful',
            'data' => $result
        ]);
    }

    /**
     * 顯示後台工時紀錄
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply) as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.apply_user_no = u2.NO ';
        $sql .= 'order by id DESC ';
        $sql .= 'limit ?,10 ';
        $logs = DB::select($sql, [($page-1)*10]);
        foreach ($logs as $key => $value) {
            if($value->apply_type == 'L') {
                $start_date = str_replace("T", " ", $value->start_date);
                $end_date = str_replace("T", " ", $value->end_date);
                $logs[$key]->start_date = $start_date;
                $logs[$key]->end_date = $end_date;
            }
        }
        $total_logs = DB::select('select * from eip_leave_apply', []);
        $total_pages = ceil(count($total_logs)/10);

        return view('contents.LeaveLog.leavelog', [
            'logs' => $logs, 
            'page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $leave_apply_id = $id;
        $line_id = $request->get('userId');
        $validate = $request->get('validate');
        $reject_reason = $request->get('reject_reason');
        $is_validate = 0; //rejeact
        if($validate == 'agree') {
            $is_validate = 1; //agree
        }
        $users = DB::select('select NO, title_id, upper_user_no from user where line_id =?', [$line_id]);
        $NO = ""; //審核人NO
        $title_id = ""; //審核人title_id
        $upper_user_no = ""; //審核人的下一個審核人
        foreach ($users as $v) {
            $NO = $v->NO;
            $title_id =  $v->title_id;
            $upper_user_no =  $v->upper_user_no;
        }

        if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =? where leave_apply_id =? and upper_user_no =?", [$is_validate, $reject_reason, $leave_apply_id, $NO]) != 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ]);
        }
        
        if($is_validate == 0) {
            //拒絕審核
            if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['N', $leave_apply_id]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            } else {
                return response()->json([
                    'status' => 'successful'
                ]);
            }
        } else {
            //同意審核
            $leave_types = DB::select('select approved_title_id from eip_leave_type where id in (select leave_type from eip_leave_apply where id =?)', [$leave_apply_id]);
            $approved_title_id = "";
            foreach ($leave_types as $v) {
                $approved_title_id = $v->approved_title_id;
            }
            if($approved_title_id == $title_id) {
                //全部審核完了
                log::info("update eip_leave_apply set apply_status =? where id =?");
                log::info($leave_apply_id);
                if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['Y', $leave_apply_id]) != 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'update error'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            } else {
                //繼續給下一個人審核
                if(DB::insert("insert into eip_leave_apply_process (leave_apply_id, upper_user_no) value (?, ?)", [$leave_apply_id, $upper_user_no]) != 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'insert error'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
