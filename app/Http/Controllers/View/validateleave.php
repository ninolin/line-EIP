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
     * 顯示待審核清單資料
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
     * 顯示待審核清單頁面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('line.validateleave', []);
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
            $validate = $request->get('validate');
            $apply_type = $request->get('apply_type');
            $reject_reason = $request->get('reject_reason');
            $is_validate = 0; //rejeact
            if($validate == 'agree') {
                $is_validate = 1; //agree
            }
            $users = DB::select('select NO, title_id, upper_user_no from user where line_id = ?', [$line_id]);
            $NO = "";                   //審核人NO
            $title_id = "";             //審核人title_id
            $upper_user_no = "";        //審核人的下一個審核人的user_no
            foreach ($users as $v) {
                //echo "vvv".$v->NO;
                $NO = $v->NO;
                $title_id = $v->title_id;
                $upper_user_no = $v->upper_user_no;
            }

            $apply_user = json_decode(LeaveApplyProvider::getLeaveApply($apply_id));
            //echo $apply_user->id;
            if(is_null($apply_user)) { throw new Exception('The line_id is not exist in the EIP');  }
            if($reject_reason == "null") {$reject_reason = null;}
            if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =? where apply_id =? and upper_user_no =?", [$is_validate, $reject_reason, $apply_id, $NO]) != 1) {
                //echo "bbb";
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            }
            //echo "ccc";
            if($is_validate == 0) {
                //拒絕審核
                if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['N', $apply_id]) != 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'update error'
                    ]);
                } else {
                    if($apply_user->apply_type == 'L') {
                        LineServiceProvider::pushTextMsg($apply_user->apply_user_line_id, "請假不通過 原因:".$reject_reason." 假別:". $apply_user->leave_name. " 起:". $apply_user->start_date ." ".$apply_user->start_time. " 迄:". $apply_user->end_date ." ".$apply_user->end_time. " 備註:". $apply_user->comment);
                    } else {
                        LineServiceProvider::pushTextMsg($apply_user->apply_user_line_id, "加班不通過 原因:".$reject_reason." 加班日:". $apply_user->over_work_date ."(".$apply_user->over_work_hours. "小時):". $apply_user->end_date ." 備註:". $apply_user->comment);
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            } else {
                //同意審核
                $leave_types = DB::select('select approved_title_id from eip_leave_type where id in (select type_id from eip_leave_apply where id =?)', [$apply_id]);
                $approved_title_id = "";
                foreach ($leave_types as $v) {
                    $approved_title_id = $v->approved_title_id;
                }
            
                if($approved_title_id == $title_id) {
                    //全部審核完了
                    log::info("update eip_leave_apply set apply_status =? where id =?");
                    log::info($apply_id);
                    if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['Y', $apply_id]) != 1) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'update error'
                        ]);
                    } else {
                        if($apply_user->apply_type == 'L') {
                            LineServiceProvider::pushTextMsg($apply_user->apply_user_line_id, "請假已通過 假別:". $apply_user->leave_name. " 起:". $apply_user->start_date ." ".$apply_user->start_time. " 迄:". $apply_user->end_date ." ".$apply_user->end_time. " 備註:". $apply_user->comment);
                        } else {
                            LineServiceProvider::pushTextMsg($apply_user->apply_user_line_id, "加班已通過 加班日:". $apply_user->over_work_date ."(".$apply_user->over_work_hours. "小時):". $apply_user->end_date ." 備註:". $apply_user->comment);
                        }
                        return response()->json([
                            'status' => 'successful'
                        ]);
                    }
                } else {
                    //繼續給下一個人審核
                    if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$apply_id, $apply_type, $NO, $upper_user_no]) != 1) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'insert error'
                        ]);
                    } else {
                        $upper_users = DB::select('select line_id from user where NO = ?', [$upper_user_no]);
                        $upper_user_line_id = "";   //審核人的下一個審核人的line_id
                        foreach ($upper_users as $v) {
                            $upper_user_line_id = $v->line_id;
                        }

                        if($apply_user->apply_type == 'L') {
                            LineServiceProvider::pushTextMsg($upper_user_line_id, $apply_user->apply_user_cname. "送出假單，請審核 假別:". $apply_user->leave_name. " 起:". $apply_user->start_date ." ".$apply_user->start_time. " 迄:". $apply_user->end_date ." ".$apply_user->end_time. " 備註:". $apply_user->comment);
                        } else {
                            LineServiceProvider::pushTextMsg($upper_user_line_id, $apply_user->apply_user_cname. "送出加班，請審核 加班日:". $apply_user->over_work_date ."(".$apply_user->over_work_hours. "小時) 備註:". $apply_user->comment);
                        }

                        return response()->json([
                            'status' => 'successful'
                        ]);
                    }
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
