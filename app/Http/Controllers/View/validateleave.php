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
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as levae_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id IN (  ';
        $sql .= '   select leave_apply_id ';
        $sql .= '   from eip_leave_apply_process ';
        $sql .= '   where upper_user_no IN ';
        $sql .= '   (select NO from user where line_id =?)) ';
        $sql .= ') as a ';
        $sql .= 'left join user as u1';
        $sql .= 'on a.leave_agent_user_no = u1.no ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type_id = eip_leave_type.id';
        $sql .= 'left join user as u2';
        $sql .= 'on a.line_id = u2.line_id';
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
        $line_id = $request->get('userId');
        $leave_agent_user_no = $request->get('leaveAgent');
        $leave_type_id = $request->get('leaveType');
        $start_date = $request->get('startDate');
        $start_time = $request->get('startTime');
        $end_date = $request->get('endDate');
        $end_time = $request->get('endTime');
        $comment = $request->get('comment');
        //debug($line_id);
        $sql = "insert into eip_leave_apply ";
        $sql .= "(line_id, leave_agent_user_no, leave_type_id, start_date, start_time, end_date, end_time, comment) ";
        $sql .= "value ";
        $sql .= "(?, ?, ?, ?, ?, ?, ?, ?) ";
        if(DB::insert($sql, [$line_id, $leave_agent_user_no, $leave_type_id, $start_date, $start_time, $end_date, $end_time, $comment]) == 1) {
            $last_appy_record = DB::select('select max(id) as last_id from eip_leave_apply');
            $last_appy_id = ""; //假單流水號
            foreach ($last_appy_record as $v) {
                $last_appy_id = $v->last_id;
            }

            $users = DB::select('select cname from user where line_id =?', [$line_id]);
            $cname = ""; //申請人
            foreach ($users as $v) {
                $cname = $v->cname;
            }

            $leavetypes = DB::select('select name from eip_leave_type where id = ?', [$leave_type_id]);
            $leavename = ""; //假別
            foreach ($leavetypes as $v) {
                $leavename = $v->name;
            }
            //debug($leavename);
            $agent_users = DB::select('select cname, line_id from user where NO =?', [$leave_agent_user_no]);
            $agent_cname = ""; //代理人
            $agent_line_id = ""; //代理人的line_id
            foreach ($agent_users as $v) {
                $agent_cname = $v->cname;
                $agent_line_id = $v->line_id;
            }

            $upper_users = DB::select('select NO, line_id from user where NO in (select upper_user_no from user where line_id =?)', [$line_id]);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
            }

            if(DB::insert("insert into eip_leave_apply_process (leave_apply_id, upper_user_no) value (?, ?)", [$last_appy_id, $upper_user_no]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'insert error'
                ]);
            }

            Log::info("agent_line_id:".$agent_line_id);
            Log::info("upper_line_id:".$upper_line_id);
            $response = array (
                "to" => $line_id,
                "messages" => array (
                    array (
                        "type" => "text",
                        "text" => "成功送出假單 假別:". $leavename. " 代理人: ".$agent_cname." 起:". $start_date ." ".$start_time. " 迄:". $end_date ." ".$end_time. " 備註:". $comment
                    )
                )
            );
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: Bearer g0E9K4fU54BZVITKc1w7C343NA8yb15YD76K+u472xg8ZCdFFeNGTk16hi97VjNxHQTBl3tRlMxEsoZ8x/nQZkvGY7EIDpWpHML6VB4zLqCdrdPUdlU6VBn6Lpzfjsi1WqRP+YQOhZlq87olqbR25VGUYhWQfeY8sLGRXgo3xvw=";
            $ch = curl_init("https://api.line.me/v2/bot/message/push");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
            $result = curl_exec($ch);
            curl_close($ch);

            if( $upper_line_id != "") {
                $response = array (
                    "to" => $upper_line_id,
                    "messages" => array (
                        array (
                            "type" => "text",
                            "text" => $cname. "送出假單，請審核 假別:". $leavename. " 代理人: ".$agent_cname." 起:". $start_date ." ".$start_time. " 迄:". $end_date ." ".$end_time. " 備註:". $comment
                        )
                    )
                );
                Log::info("upper_line_id_response:");
                Log::info("upper_line_id_response:".json_encode($response));

                $ch = curl_init("https://api.line.me/v2/bot/message/push");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
                $result = curl_exec($ch);
                curl_close($ch);
            }
            
            if( $agent_line_id != "") {
                $response = array (
                    "to" => $agent_line_id,
                    "messages" => array (
                        array (
                            "type" => "text",
                            "text" => $cname. "送出假單並指定您為代理人 假別:". $leavename. " 代理人: ".$agent_cname." 起:". $start_date ." ".$start_time. " 迄:". $end_date ." ".$end_time. " 備註:". $comment
                        )
                    )
                );
                
                $ch = curl_init("https://api.line.me/v2/bot/message/push");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
                $result = curl_exec($ch);
                curl_close($ch);
            }


            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'insert error'
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
        //
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
        //
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
