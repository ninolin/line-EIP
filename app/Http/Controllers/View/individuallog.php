<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Log;

class individuallog extends Controller
{
    /**
     * 顯示個人請假紀錄畫面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('line.individuallog', []);
    }

    /**
     * 顯示個人請假資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $sql  = 'select a.*, u2.cname as cname, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where apply_user_no IN (  ';
        $sql .= '   select NO from user where line_id =?';
        $sql .= ') and apply_status != "C") as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.apply_user_no = u2.NO order by id desc';
        $leaves = DB::select($sql, [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }

    /**
     * 取消請假/加班
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function cancel($id)
    {
        try {
            $apply_id = $id;   
            $sql  = 'select event_id from eip_leave_apply where apply_status =? and id =?';
            $leaves = DB::select($sql, ['Y', $apply_id]);
            if(count($leaves) > 0) {
                foreach($leaves as $l) {
                    self::delete_event2gcalendar($l->event_id);
                }
            }
            if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['C', $apply_id]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            } else {
                return response()->json([
                    'status' => 'successful'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * google calendar刪除
     *
     * @param  string  $event_id
     * @return int
     */
    static protected function delete_event2gcalendar($event_id) {
        $gcalendar_appscript_uri = Config::get('eip.gcalendar_appscript_uri');
        log::info($gcalendar_appscript_uri);
        $json_str = '{
            "type": "delete", 
            "eventId": "'.$event_id.'"
        }';
        log::info($json_str);
        $calevents_str = HelperServiceProvider::post_req($gcalendar_appscript_uri, $json_str);
        if(strpos($calevents_str,'Exception') !== false){ 
            return 0;
        }else{
            return 1;
        }
    }
}
