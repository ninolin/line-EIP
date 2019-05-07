<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Log;
use DB;
use Config;

class LineServiceProvider extends ServiceProvider
{
    
    // private static $msg_type1 = '{
    //     "type": "bubble",
    //     "styles": {"footer": {"separator": true}},
    //     "body": {"type": "box","layout": "vertical","contents": []}
    // }';

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

    private static function findAccessToken($line_id) {
        //尋找該用戶所屬line_channel的access_token
        $channel_array = Config::get('line.channel');
        $line_channel = "";
        $line_channel_access_token = "";
        $users = DB::select('select line_channel from user where line_id = ?', [$line_id]);
        foreach ($users as $value) {
            $line_channel = $value->line_channel; //這個用戶所屬的line_channel
            foreach ($channel_array as $c) {
                if(explode(":",$c)[0] == $line_channel) {
                    $line_channel_access_token = explode(":",$c)[1]; //找到這個line_channel的access token
                }
            }
        }
        return $line_channel_access_token;
    }

    private static function sendPushMsg($line_channel_access_token, $response) {
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
    //請假/加班/簽核通知訊息(通知申請人，簽核人，代理人)
    public static function sendNotifyFlexMeg($line_id, $msg) {
        //$msg = ["送出申請","假別:事假","代理人:阿泰","起日:2019","迄日:2019","備住:備住"];
        //print_r($msg);
        $content = "";
        $line_channel_access_token = self::findAccessToken($line_id);
        for ( $i=1 ; $i<count($msg) ; $i++ ) {
            $content .= '{
                "type": "box",
                "layout": "horizontal",
                "contents": [
                  {
                    "type": "text",
                    "text": "'.explode(":",$msg[$i])[0]. '",
                    "size": "sm",
                    "color": "#555555",
                    "flex": 0
                  },
                  {
                    "type": "text",
                    "text": "'.explode(":",$msg[$i])[1].'",
                    "size": "sm",
                    "color": "#111111",
                    "align": "end"
                  }
                ]
            },';
        }
        $content = substr($content,0,-1);
        $response =  '{
            "type": "bubble",
            "styles": {"footer": {"separator": true}},
            "body": {"type": "box","layout": "vertical","contents": [
                {
                    "type": "text",
                    "text": "'.$msg[0].'",
                    "weight": "bold",
                    "size": "md",
                    "margin": "md"
                },
                {
                    "type": "separator",
                    "margin": "xxl"
                },
                {
                    "type": "box",
                    "layout": "vertical",
                    "margin": "xxl",
                    "spacing": "sm",
                    "contents": ['.$content.']
                }
            ]}
        }';
        $response = json_decode($response, true);
        $response = array (
            "to" => $line_id,
            "messages" => array (
                array (
                    "type" => "flex",
                    "altText" => "This is a Flex Message",
                    "contents" => $response
                )
            )
        );
        log::info($line_channel_access_token);
        log::info($response);
        $result = self::sendPushMsg($line_channel_access_token, $response);
    }

    public static function sendIndividualLogFlexMeg($line_id) {
        $sql = 'select *, elt.name as leave_name ';
        $sql .='from eip_leave_apply elp left join eip_leave_type elt on elp.type_id = elt.id';
        $sql .='where elp.apply_user_no IN (select NO from user where line_id = ?)';
        $logs = DB::select($sql, [$line_id]);

        $content = "";
        $line_channel_access_token = self::findAccessToken($line_id);
        foreach ($logs as $v) {
            $start_date = $v->start_date;
            $leave_name = $v->leave_name;
            $apply_status = "成功";
            $apply_status_color = "#555555";
            if($v->apply_type == 'O') {
                $start_date = $v->over_work_date;
                $leave_name = "加班";
            }
            if($v->apply_status == 'P') {
                $apply_status = "簽核中";
                $apply_status_color = "#0073e6";
            } else if($v->apply_status == 'N') {
                $apply_status = "失敗";
                $apply_status_color = "#b32400";
            }
            $content .= '{
                "type": "box",
                "layout": "horizontal",
                "margin": "sm",
                "spacing": "sm",
                "contents": [
                    {
                        "type": "box",
                        "layout": "horizontal",
                        "contents": [
                          {
                            "type": "text",
                            "text": "'.$start_date.'",
                            "size": "sm",
                            "weight": "bold",
                            "color": "#555555",
                            "flex": 2
                          },
                          {
                            "type": "text",
                            "text": "'.$leave_name.'",
                            "size": "sm",
                            "color": "#555555"
                          },
                          {
                            "type": "text",
                            "text": "'.$apply_status.'",
                            "size": "sm",
                            "color": "'.$apply_status_color.'"
                          },
                          {
                            "type": "text",
                            "text": "更多..",
                            "size": "sm",
                            "weight": "bold",
                            "color": "#38488f",
                            "align": "end"
                          }
                        ]
                    }
                ]
            },
            {
                "type": "separator",
                "margin": "sm"
            },';
        }
        $content = substr($content,0,-1);
        $response =  '{
            "type": "bubble",
            "styles": {"footer": {"separator": true}},
            "body": {"type": "box","layout": "vertical","contents": [
                {
                    "type": "text",
                    "text": "工時紀錄",
                    "weight": "bold",
                    "size": "xl",
                    "margin": "md",
                    "color": "#1DB446"
                },
                '.$content.'
            ]}
        }';
        $response = json_decode($response, true);
        $response = array (
            "to" => $line_id,
            "messages" => array (
                array (
                    "type" => "flex",
                    "altText" => "This is a Flex Message",
                    "contents" => $response
                )
            )
        );
        log::info($line_channel_access_token);
        log::info($response);
        $result = self::sendPushMsg($line_channel_access_token, $response);
    }

    public static function pushTextMsg($line_id, $msg) {
        $line_channel_access_token = self::findAccessToken($line_id);
        $response = array (
            "to" => $line_id,
            "messages" => array (
                array (
                    "type" => "text",
                    "text" => $msg
                )
            )
        );
        $result = self::sendPushMsg($line_channel_access_token, $response);
    }
    
    //綁定失敗時用reply回給user
    public static function replyTextMsgWithChannel($line_id, $reply_token, $line_channel, $msg) {
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
