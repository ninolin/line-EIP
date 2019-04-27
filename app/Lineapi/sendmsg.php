<?php

namespace App\Lineapi;
use DB;
class sendmsg
{
    /**
     * 發送line message
     *
     * @param  User  $user
     * @return Collection
     */
    public function textmsg($line_id, $msg)
    {
        //尋找該用戶所屬line_channel的access_token
        $channel_array = Config::get('line.channel');
        $line_channel = "";
        $line_channel_access_token = "";
        $users = DB::select('select line_channel from user where line_id = ?', [$line_id]);
        foreach ($users as $value) {
            $line_channel = $value->line_channel; //這個用戶所屬的line_channel
            foreach ($channel_array as $c) {
                $channel_array_c_id = explode(":",$c)[0];
                $channel_array_c_token = explode(":",$c)[1];
                if($channel_array_c_id == $line_channel) {
                    $line_channel_access_token = $channel_array_c_token; //找到這個line_channel的access token
                }
            }
        }
        if($line_channel_access_token == "") return 0;

        $response = array (
            "to" => $line_id,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $msg
                )
            )
        );
        $header[] = "Content-Type: application/json";
        $header[] = "Authorization: Bearer ".$line_channel_access_token;
        $ch = curl_init("https://api.line.me/v2/bot/message/push");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
        $result = curl_exec($ch);
        curl_close($ch);
    }
}