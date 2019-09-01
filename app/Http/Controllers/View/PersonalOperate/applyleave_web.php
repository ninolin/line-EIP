<?php

namespace App\Http\Controllers\View\PersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\View\applyleave;
use DB;

class applyleave_web extends applyleave
{
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        //因為有同一種假但不同天數的假別，所以做distinct name，之後新增假時，再判斷是用那一種假別的id
        $sql  ='select distinct name from eip_leave_type';
        $leavetypes = DB::select($sql, []);
        //用戶只列出是status是T(True)
        $users = DB::select("select * from user where status = 'T' order by cname", []);
       
        $user_no = session()->all()['user_no'] || null;

        if($user_no == null){
            echo 'user no not found';
            exit;
        }

        $user_info = $this->get_user_by_line_id($user_no,'web');        

        return view('contents.PersonalOperate.applyleave', [
            'leavetypes' => $leavetypes,
            'users'      => $users,
            'nowdate'    => date("Y-m-d"),
            'user_id'    => $user_no,
            'user_info'  => $user_info,
        ]);
    }
}
