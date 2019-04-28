<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use DB;
use Config;

class LeaveApplyProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    //取得apply單的資料
    public static function getLeaveApply($apply_id)
    {
        $sql  = 'select a.*, u2.cname as apply_user_cname, u2.line_id as apply_user_line_id, u1.cname as agent_cname, eip_leave_type.name as leave_name ';
        $sql .= 'from ';
        $sql .= '(select * from eip_leave_apply where id = ?) as a ';
        $sql .= 'left join user as u1 '; //u1是agent user
        $sql .= 'on a.agent_user_no = u1.NO ';
        $sql .= 'left join eip_leave_type ';
        $sql .= 'on a.type_id = eip_leave_type.id ';
        $sql .= 'left join user as u2 '; //u2是apply user
        $sql .= 'on a.apply_user_no = u2.NO ';
        $apply = DB::select($sql, [$apply_id]);
        if(count($apply == 1)) {
            return json_encode($apply[0]);
        } else {
            return 0;
        }
    }

}
