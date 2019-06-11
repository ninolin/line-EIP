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
        $sql .= ')) as a ';
        $sql .= 'left join user as u1 ';
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.leave_type = eip_leave_type.id ';
        $sql .= 'left join user as u2 ';
        $sql .= 'on a.apply_user_no = u2.NO ';
        $leaves = DB::select($sql, [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }
}
