<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Log;

class validateleave extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id IN (  ';
        $sql .= '   select leave_apply_id ';
        $sql .= '   from eip_leave_apply_process ';
        $sql .= '   where is_validate IS NOT NULL and upper_user_no IN ';
        $sql .= '   (select NO from user where line_id =?)) ';
        $sql .= ') as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.leave_agent_user_no = u1.no ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type_id = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.line_id = u2.line_id ';
        debug($sql);
        $leaves = DB::select($sql, [$id]);
        debug($leaves);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
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
