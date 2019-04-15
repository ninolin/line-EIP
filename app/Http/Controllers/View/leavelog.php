<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Log;

class leavelog extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $leavetypes = DB::select('select approved_title_id from eip_leave_type where id in (select leave_type_id from eip_leave_apply where id = ?)', [$id]);
        $approved_title_id = ""; //該假別最後審核人的職等
        foreach ($leavetypes as $v) {
            $approved_title_id = $v->approved_title_id;
        }
        $sql = "select NO from user where line_id in (select line_id from eip_leave_apply where id =?)";
        $users = DB::select($sql, [$id]);
        $user_no = ""; //申請人no
        foreach ($users as $v) {
            $user_no = $v->NO;
        }
        $result = [];
        for ( $i=0 ; $i<10 ; $i++ ) {
            $users = DB::select("select NO, cname, title_id from user where NO IN (select upper_user_no from user where NO =?)", [$user_no]);
            $user_no = "";
            $user_cname = "";
            $user_title_id = "";
            foreach ($users as $v) {
                $user_no = $v->NO;
                $user_cname = $v->cname;
                $user_title_id = $v->title_id;
            }

            $process = DB::select("select * from eip_leave_apply_process where leave_apply_id =? and upper_user_no =?", [$id, $user_no]);
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $sql =  'select ela.*, u1.cname, u2.cname as agent_cname, elt.name as leave_type_name ';
        $sql .= 'from eip_leave_apply ela, user as u1, user as u2, eip_leave_type elt ';
        $sql .= 'where ela.line_id = u1.line_id and ela.leave_agent_user_no = u2.NO and ela.leave_type_id = elt.id ';
        $sql .= 'limit ?,10 ';
        $logs = DB::select($sql, [($page-1)*10]);
        $total_logs = DB::select('select * from eip_leave_apply', []);
        $total_pages = ceil(count($total_logs)/10);

        return view('contents.leavelog', [
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
            $leave_types = DB::select('select approved_title_id from eip_leave_type where id in (select leave_type_id from eip_leave_apply where id =?)', [$leave_apply_id]);
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
