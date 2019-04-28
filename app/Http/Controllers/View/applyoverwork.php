<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use DB;
use Log;

class applyoverwork extends Controller
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
        $users = DB::select("select * from user where status = 'T' and level != 'guest'", []);
        return view('line.applyoverwork', [
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
             * step2: 通知申請人、第一簽核人
            */
            $apply_user_line_id = $request->get('userId');  //申請者的line_id
            $overworkDate = $request->get('overworkDate');  //加班日
            $overworkHour = $request->get('overworkHour');  //加班小時
            $comment = $request->get('comment');            //備註
            //透過假別名稱、起日、迄日找到假別id
            $overwork_type_arr = DB::select('select * from eip_overwork_type', []);
            $overwork_type_id = "";
            foreach ($overwork_type_arr as $v) {
                if($overworkHour < $v->hour) {
                    $overwork_type_id = $v->id;
                    break;
                }
            }
            //取得申請人的NO和別名
            $users = DB::select('select NO, cname from user where line_id =?', [$line_id]);
            $apply_user_NO = "";    //申請人NO
            $apply_user_cname = ""; //申請人別名
            if(count($users) != 1) {
                throw new Exception('The line_id is not exist in the EIP'); 
            }
            foreach ($users as $v) {
                $apply_user_no = $v->NO;
                $apply_user_cname = $v->cname;
            }
            //debug($line_id);

            //寫入請假紀錄
            $sql = "insert into eip_leave_apply ";
            $sql .= "(apply_user_no, apply_type, type_id, over_work_date, over_work_hours, comment) ";
            $sql .= "value ";
            $sql .= "(?, ?, ?, ?, ?, ?) ";
            if(DB::insert($sql, [$apply_user_no, 'O', $overwork_type_id, $overworkDate, $overworkHour, $comment]) != 1) {
                throw new Exception('insert db error'); 
            }
            //取得剛剛寫入的請假紀錄id
            $last_appy_record = DB::select('select max(id) as last_id from eip_leave_apply');
            $last_appy_id = ""; //假單流水號
            foreach ($last_appy_record as $v) {
                $last_appy_id = $v->last_id;
            }
            //取得第一簽核人的資料
            $upper_users = DB::select('select NO, line_id from user where NO in (select upper_user_no from user where line_id =?)', [$apply_user_line_id]);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
            }
            //寫入簽核流程紀錄(該table沒有紀錄申請人和簽核人的line_id是因為可能會有換line帳號的情況發生)
            if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$last_appy_id, 'O', $apply_user_no, $upper_user_no]) != 1) {
                DB::delete("delete from eip_leave_apply where id = ?", [$last_appy_id]);
                throw new Exception('insert db error'); 
            }
            //通知申請人、代理人、第一簽核人
            Log::info("agent_line_id:".$agent_line_id);
            Log::info("upper_line_id:".$upper_line_id);
            LineServiceProvider::pushTextMsg($apply_user_line_id, "成功送出加班 加班日:". $overworkDate ." 加班小時".$overworkHour. " 備註:". $comment);
            LineServiceProvider::pushTextMsg($upper_line_id, $cname. "送出假單，請審核 加班日:". $overworkDate ." 加班小時".$overworkHour. " 備註:". $comment);

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
