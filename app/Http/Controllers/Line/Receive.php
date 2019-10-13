<?php

namespace App\Http\Controllers\Line;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use DB;
use App\Lineapi\sendmsg;
use App\Services\UserService;
use App\Services\SendLineMessageService;
use Exception;

class Receive extends Controller
{
    protected $userService;
    protected $sendLineMessageService;

    public function __construct(
        UserService $userService,
        SendLineMessageService $sendLineMessageService
    )
    {
        $this->userService = $userService;
        $this->sendLineMessageService = $sendLineMessageService;
    }

    public function receive(Request $request)
    {
        try {
            // if(empty($_GET['channel_id'])) {
            //     throw new Exception('url get channel_id failed');
            // }
            //line_channel       = $_GET['channel_id'];

            $bodyContent        = $request->getContent();               //取得request的body內容
            $json_obj           = json_decode($bodyContent);            //轉成json格式
            $sender_replyToken  = $json_obj->events[0]->replyToken;     //取得訊息的replyToken
            $sender_lineid      = $json_obj->events[0]->source->userId; //取得訊息發送者的id
            $sender_type        = $json_obj->events[0]->message->type;  //取得訊息的type
            if($sender_type == 'text') {
                $sender_txt     = $json_obj->events[0]->message->text;  //取得訊息內容
            } else {
                $sender_txt     = $json_obj->events[0]->message->id;    //取得訊息內容
            }
            $user = $this->userService->get_user_info($sender_lineid, 'line');
            $sql  = "insert into eip_line_message (
                        username, line_id, body, type, message, time
                    ) value (
                        ?, ?, ?, ?, ?, UNIX_TIMESTAMP(NOW())
                    ) ";
            if($user['status'] == 'error') {
                if(DB::insert($sql, [null, $sender_lineid, $bodyContent, $sender_type, $sender_txt]) != 1) {throw new Exception('insert eip_line_message failed(1)');}
                $this->sendLineMessageService->sendGreeting($sender_replyToken);
            } else {
                if(DB::insert($sql, [$user['data']->cname, $sender_lineid, $bodyContent, $sender_type, $sender_txt]) != 1) {throw new Exception('insert eip_line_message failed(1)');}
            }
            
            return response()->json([
                'status' => 'successful',
                'message'=> 1
            ]);
            
        } catch (Exception $e) {
            log::info('receive throw error: '.$e->getMessage());
        }
    }

}
