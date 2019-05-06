<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use DB;
use Log;
use Exception;

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
        //因為有同一種假但不同天數的假別，所以做distinct name，之後新增假時，再判斷是用那一種假的id
        $sql = 'select distinct elt.name, et.name as title_name, et.id as title_id ';
        $sql .='from eip_leave_type elt, eip_title et ';
        $sql .='where elt.approved_title_id = et.id ';
        $leavetypes = DB::select($sql, []);
        $users = DB::select("select * from user where status = 'T' order by cname", []);
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
        try {
            /*
             * 2個step:
             * step1: 寫入請假紀錄
             * step2: 通知申請人、代理人、第一簽核人
            */
            $apply_user_line_id = $request->get('userId');      //申請者的line_id
            $leave_agent_user_no = $request->get('leaveAgent'); //代理人的user_NO
            $leavename = $request->get('leaveType');            //假別名稱
            $start_date = $request->get('startDate');           //起日
            $start_time = $request->get('startTime');           //起時
            $end_date = $request->get('endDate');               //迄日
            $end_time = $request->get('endTime');               //迄時
            $comment = $request->input('comment');              //備註
            if($comment == "") $comment = "-";

            //透過假別名稱、起日、迄日找到假別id
            $leave_days = round((strtotime($end_date)-strtotime($start_date))/3600/24); //請假天數
            //echo $leave_days;
            $leave_type_arr = DB::select('select * from eip_leave_type where name =? order by day ASC', [$leavename]);
            $leave_type_id = "";
            $i = 0;
            foreach ($leave_type_arr as $v) {
                $i++;
                if($leave_days < $v->day) {
                    $leave_type_id = $v->id;
                    break;
                }
                if($leave_type_id == "" && count($leave_type_arr) == $i){
                    $leave_type_id = $v->id;
                    break;
                }
            }
            //取得申請人的NO和別名
            $users = DB::select('select NO, cname from user where line_id =?', [$apply_user_line_id]);
            $apply_user_NO = "";    //申請人NO
            $apply_user_cname = ""; //申請人別名
            if(count($users) != 1) throw new Exception('請假失敗:請先將您的line加入EIP中'); 
            foreach ($users as $v) {
                $apply_user_no = $v->NO;
                $apply_user_cname = $v->cname;
            }
            //取得代理人的資料
            $agent_users = DB::select('select cname, line_id from user where NO =?', [$leave_agent_user_no]);
            $agent_cname = ""; //代理人
            $agent_line_id = ""; //代理人的line_id
            foreach ($agent_users as $v) {
                $agent_cname = $v->cname;
                $agent_line_id = $v->line_id;
            }
            if($agent_line_id == "") throw new Exception('請假失敗:代理人的line未加入EIP中'); 
            //取得第一簽核人的資料
            $upper_users = DB::select('select NO, line_id from user where NO in (select upper_user_no from user where line_id =?)', [$apply_user_line_id]);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
            }
            if($upper_line_id == "") throw new Exception('請假失敗:未設定簽核人或簽核人的line未加入EIP中');
            
            //寫入請假紀錄
            $sql = "insert into eip_leave_apply ";
            $sql .= "(apply_user_no, apply_type, agent_user_no, type_id, start_date, start_time, end_date, end_time, comment) ";
            $sql .= "value ";
            $sql .= "(?, ?, ?, ?, ?, ?, ?, ?, ?) ";
            if(DB::insert($sql, [$apply_user_no, 'L', $leave_agent_user_no, $leave_type_id, $start_date, $start_time, $end_date, $end_time, $comment]) != 1) {
                throw new Exception('請假失敗:insert eip_leave_apply error'); 
            }
            //取得剛剛寫入的請假紀錄id
            $last_appy_record = DB::select('select max(id) as last_id from eip_leave_apply');
            $last_appy_id = ""; //假單流水號
            foreach ($last_appy_record as $v) {
                $last_appy_id = $v->last_id;
            }
            //寫入簽核流程紀錄(該table沒有紀錄申請人和簽核人的line_id是因為可能會有換line帳號的情況發生)
            if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$last_appy_id, 'L', $apply_user_no, $upper_user_no]) != 1) {
                DB::delete("delete from eip_leave_apply where id = ?", [$last_appy_id]);
                throw new Exception('insert db error'); 
            }
            //通知申請人、代理人、第一簽核人
            Log::info("agent_line_id:".$agent_line_id);
            Log::info("upper_line_id:".$upper_line_id);
            //echo $agent_line_id;
            $msg = ["假別:". $leavename,"代理人:".$agent_cname,"起日:".$start_date." ".$start_time,"迄日:". $end_date ." ".$end_time,"備住:". $comment];
            LineServiceProvider::sendNotifyFlexMeg($apply_user_line_id, array_merge(["已送出假單，待簽核完成後即完成請假"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($upper_line_id, array_merge(["請審核".$apply_user_cname."送出的假單"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($agent_line_id, array_merge([$apply_user_cname."指定您為請假代理人"], $msg));

            return response()->json([
                'status' => 'successful'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
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
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id = ?) as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.no ';
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
