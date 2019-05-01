<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use DB;
use Config;

class LineServiceProvider extends ServiceProvider
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

    private function findAccessToken($line_id) {
        //尋找該用戶所屬line_channel的access_token
        $channel_array = Config::get('line.channel');
        log::info($channel_array);
        $line_channel = "";
        $line_channel_access_token = "";
        $users = DB::select('select line_channel from user where line_id = ?', [$line_id]);
        log::info($users);
        foreach ($users as $value) {
            $line_channel = $value->line_channel; //這個用戶所屬的line_channel
            foreach ($channel_array as $c) {
                if(explode(":",$c)[0] == $line_channel) {
                    $line_channel_access_token = explode(":",$c)[1]; //找到這個line_channel的access token
                }
            }
        }
        if($line_channel_access_token == "") return 0;
    }

    private function sendPushMsg($line_channel_access_token, $response) {
        $header[] = "Content-Type: application/json";
        $header[] = "Authorization: Bearer ".$line_channel_access_token;
        $ch = curl_init("https://api.line.me/v2/bot/message/push");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public static function pushTextMsg($line_id, $msg)
    {
        
        $line_channel_access_token = $this->findAccessToken($line_id);
        // $channel_array = Config::get('line.channel');
        // log::info($channel_array);
        // $line_channel = "";
        // $line_channel_access_token = "";
        // $users = DB::select('select line_channel from user where line_id = ?', [$line_id]);
        // log::info($users);
        // foreach ($users as $value) {
        //     $line_channel = $value->line_channel; //這個用戶所屬的line_channel
        //     foreach ($channel_array as $c) {
        //         if(explode(":",$c)[0] == $line_channel) {
        //             $line_channel_access_token = explode(":",$c)[1]; //找到這個line_channel的access token
        //         }
        //     }
        // }
        // if($line_channel_access_token == "") return 0;

        $response = array (
            "to" => $line_id,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $msg
                )
            )
        );
        log::info($line_channel_access_token);
        log::info($response);
        $result = $this->sendPushMsg($line_channel_access_token, $response);
        // $header[] = "Content-Type: application/json";
        // $header[] = "Authorization: Bearer ".$line_channel_access_token;
        // $ch = curl_init("https://api.line.me/v2/bot/message/push");
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
        // $result = curl_exec($ch);
        // curl_close($ch);
    }

    public static function replyTextMsgWithChannel($line_id, $reply_token, $line_channel, $msg)
    {
        //尋找該用戶所屬line_channel的access_token
        $channel_array = Config::get('line.channel');
        $line_channel_access_token = "";
        foreach ($channel_array as $c) {
            if(explode(":",$c)[0] == $line_channel) {
                $line_channel_access_token = explode(":",$c)[1]; //找到這個line_channel的access token
            }
        }
        if($line_channel_access_token == "") return 0;

        $response = array (
            "replyToken" => $reply_token,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $msg
                )
            )
        );
        log::info($line_channel_access_token);
        log::info($response);
        $header[] = "Content-Type: application/json";
        $header[] = "Authorization: Bearer ".$line_channel_access_token;
        $ch = curl_init("https://api.line.me/v2/bot/message/reply");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
