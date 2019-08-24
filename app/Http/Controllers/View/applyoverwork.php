<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use DB;
use Log;
use Exception;

class applyoverwork extends Controller
{

    /**
     * 顯示applyoverwork頁面
     */
    public function create()
    {
        return view('line.applyoverwork', [
            'nowdate' => date("Y-m-d")
        ]);
    }

    /**
     * 新增一筆加班紀錄
     * 3個step:
     * step1: 取得要寫入請假相關table的資料(假別id,申請人、代理人和第一簽核人資料)
     * step2: 寫入加班和簽核流程table
     * step3: 通知申請人和第一簽核人
     * 
     * @param \Illuminate\Http\Request
     */
    public function store(Request $request)
    {
        try {
            $apply_user_id = $request->get('userId');  //申請者的line_id
            $overworkDate = $request->get('overworkDate');  //加班日
            $overworkHour = $request->get('overworkHour');  //加班小時
            $comment = $request->get('comment');            //備註
            $use_mode = $request->get('use_mode');    
            if($comment == "") $comment = "-";
            
            //透過加班小時找到加班type_id
            $overwork_type_arr = DB::select('select * from eip_overwork_type', []);
            $overwork_type_id = "";
            $overwork_approved_title_id = "";
            foreach ($overwork_type_arr as $v) {
                if($overworkHour < $v->hour) {
                    $overwork_type_id = $v->id;
                    $overwork_approved_title_id = $v->approved_title_id;
                    break;
                }
            }
            //取得申請人的NO和別名
            $users = $this->_get_user_info($apply_user_id,$use_mode);

            $apply_user_NO = "";    //申請人NO
            $apply_user_cname = ""; //申請人別名
            if(count($users) != 1) throw new Exception('請加班失敗:請先將您的line加入EIP中'); 
            foreach ($users as $v) {
                $apply_user_no = $v->NO;
                $apply_user_cname = $v->cname;
            }
            //取得第一簽核人的資料
            $upper_users = $this->_get_upper_users_info($apply_user_id,$use_mode);
            $upper_line_id = "";    //第一簽核人的line_id
            $upper_user_no = "";    //第一簽核人的user_no
            foreach ($upper_users as $v) {
                $upper_line_id = $v->line_id; 
                $upper_user_no = $v->NO; 
            }
            if($upper_line_id == "" || $upper_user_no == "") throw new Exception('請加班:未設定簽核人或簽核人的line未加入EIP中');

            //寫入請假紀錄
            $sql = "insert into eip_leave_apply ";
            $sql .= "(apply_user_no, apply_type, leave_type, over_work_date, over_work_hours, comment) ";
            $sql .= "value ";
            $sql .= "(?, ?, ?, ?, ?, ?) ";
            if(DB::insert($sql, [$apply_user_no, 'O', $overwork_type_id, $overworkDate, $overworkHour, $comment]) != 1) {
                throw new Exception('insert eip_leave_apply error'); 
            }
            //取得剛剛寫入的請假紀錄id
            $last_appy_record = DB::select('select max(id) as last_id from eip_leave_apply');
            $last_appy_id = ""; //假單流水號
            foreach ($last_appy_record as $v) {
                $last_appy_id = $v->last_id;
            }
            
            //寫入簽核流程紀錄(該table沒有紀錄申請人和簽核人的line_id是因為可能會有換line帳號的情況發生)
            $upper_users = self::find_upper($apply_user_no, [], $overwork_approved_title_id);
            foreach ($upper_users as $u) {
                if(DB::insert("insert into eip_leave_apply_process (apply_id, apply_type, apply_user_no, upper_user_no) value (?, ?, ?, ?)", [$last_appy_id, 'O', $apply_user_no, $u]) != 1) {
                    DB::delete("delete from eip_leave_apply where id = ?", [$last_appy_id]);
                    DB::delete("delete from eip_leave_apply_process where apply_id = ?", [$last_appy_id]);
                    throw new Exception('insert db error'); 
                }
            }

            //通知申請人、代理人、第一簽核人
            Log::info("upper_line_id:".$upper_line_id);
            $msg = ["加班日::". $overworkDate,"加班小時::".$overworkHour,"備住::". $comment];
            LineServiceProvider::sendNotifyFlexMeg($apply_user_id, array_merge(["加班送出，請等待簽核"], $msg));
            LineServiceProvider::sendNotifyFlexMeg($upper_line_id, array_merge(["請審核".$apply_user_cname."送出的加班申請"], $msg));
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
     * 取得申請人的NO和別名
     *
     * @param  string   $apply_user_id
     * @param  string   $use_mode
     * @return array    
     */
    private function _get_user_info($apply_user_id,$use_mode){

        if($use_mode == 'web'){
            return DB::select('select NO, cname from user where NO =?', [$apply_user_id]);
        }
        return DB::select('select NO, cname from user where line_id =?', [$apply_user_id]);
    }


    /**
     * 取得第一簽核人的資料
     *
     * @param  string   $apply_user_id
     * @param  string   $use_mode
     * @return array    
     */
    private function _get_upper_users_info($apply_user_id,$use_mode){

        if($use_mode == 'web'){
            return  DB::select('select NO, line_id from user where NO in (select upper_user_no from user where NO =?)', [$apply_user_id]);
        }
        return DB::select('select NO, line_id from user where NO in (select upper_user_no from user where line_id =?)', [$apply_user_id]);
    }


    /**
     * 回傳下一個簽核人，因為有互相指定為下一個簽核人造成無窮迴圈的狀況，所以array最長為10
     *
     * @param  int      $user_no
     * @param  array    $array
     * @param  int      $approved_title_id
     * @return array    
     */
    static protected function find_upper($user_no, $array, $approved_title_id) {
        $users = DB::select('select title_id, upper_user_no from user where NO =?', [$user_no]);
        if($users > 0) {
            foreach ($users as $u) {
                if($u-> upper_user_no != 0 && $u-> title_id != $approved_title_id && count($array) < 10) {
                    array_push($array, $u-> upper_user_no);
                    return self::find_upper($u-> upper_user_no, $array, $approved_title_id);
                } else {
                    return $array;
                }
            }
        }
    }

}
