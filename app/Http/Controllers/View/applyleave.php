<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $leavetypes = DB::select('select elt.*, et.name as title_name, et.id as title_id from eip_leave_type elt, eip_title et where elt.approved_title_id = et.id ', []);
        $users = DB::select('select * from user', []);
        return view('line.applyleave', [
            'leavetypes' => $leavetypes,
            'users' => $users
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
            $leavetypes = DB::select('select name from eip_leave_type where id = ?', [$leave_type_id]);
            $users = DB::select('select cname from user where NO =?', [$leave_agent_user_no]);
            $response = array (
                "to" => $line_id,
                "messages" => array (
                    array (
                        "type" => "text",
                        "text" => "成功送出假單%0D%0A假別:". $leavetypes[0]. "%0D%0A起:". $start_date .$start_time
                    )
                )
            );
            //debug($response);

            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: Bearer g0E9K4fU54BZVITKc1w7C343NA8yb15YD76K+u472xg8ZCdFFeNGTk16hi97VjNxHQTBl3tRlMxEsoZ8x/nQZkvGY7EIDpWpHML6VB4zLqCdrdPUdlU6VBn6Lpzfjsi1WqRP+YQOhZlq87olqbR25VGUYhWQfeY8sLGRXgo3xvw=";
            $ch = curl_init("https://api.line.me/v2/bot/message/push");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
            $result = curl_exec($ch);
            curl_close($ch);

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
