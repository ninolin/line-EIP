<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use DB;
use Config;

class ReceiveServiceProvider extends ServiceProvider
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

    public static function receiveLeavelog($line_id, $msg) {
        $sql = "select * from eip_leave_apply where apply_user_no IN (select NO from user where line_id =?) order by apply_time DESC";
        $applies = DB::select($sql, [$line_id]);
        
        // $line_channel_access_token = self::findAccessToken($line_id);
        // $response = array (
        //     "to" => $line_id,
        //     "messages" => array (
        //         array (
        //             "type" => "text",
        //             "text" => $msg
        //         )
        //     )
        // );
        // $result = self::sendPushMsg($line_channel_access_token, $response);
    }
}
