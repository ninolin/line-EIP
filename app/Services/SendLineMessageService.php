<?php

namespace App\Services;

use App\Repositories\LineDefaultMsgRepository;
use App\Repositories\LeaveApplyRepository;
use App\Repositories\LeaveProcessRepository;
use Exception;
use Config;
use Log;

class SendLineMessageService 
{
    protected $lineDefaultMsgRepo;
    protected $leaveApplyRepo;
    protected $leaveProcessRepo;

    public function __construct
    (
        LineDefaultMsgRepository $lineDefaultMsgRepo,
        LeaveApplyRepository $leaveApplyRepo,
        LeaveProcessRepository $leaveProcessRepo
    )
    {
        $this->lineDefaultMsgRepo = $lineDefaultMsgRepo;
        $this->leaveApplyRepo = $leaveApplyRepo;
        $this->leaveProcessRepo = $leaveProcessRepo;
    }

    /**
     * 透過範本發通知
     *
     * @param  int  $apply_id
     * @return \Illuminate\Http\Response
     */
    public function sendNotify($apply_id, $event = 'apply_leave', $hook_user_type = null) 
    {
        //取得休假資料相關資料(變數名稱是訊息中的%變數%)
        $apply_leave        = $this->leaveApplyRepo->findApplyLeave($apply_id);
        $leave_name         = $apply_leave[0]->leave_name;
        $start_datetime     = $apply_leave[0]->start_date_f1;
        $end_datetime       = $apply_leave[0]->end_date_f1;
        $overwork_datetime  = $apply_leave[0]->overwork_date_f1;
        $overwork_hour      = $apply_leave[0]->over_work_hours;
        $comment            = $apply_leave[0]->comment;
        $apply_line_id      = $apply_leave[0]->apply_line_id;
        $apply_cname        = $apply_leave[0]->cname;
        $agent_line_id      = $apply_leave[0]->agent_line_id;
        $agent_cname        = $apply_leave[0]->agent_cname;
        $upper_line_id      = "";
        $reject_reason      = "";

        if($event == 'apply_leave' || $event == 'apply_overwork') {
            $next_upper_user    = $this->leaveProcessRepo->findNextUpperUser($apply_id);
            $upper_line_id      = $next_upper_user[0]->line_id;
        }
        if($event == 'reject_leave' || $event == 'reject_overwork') {
            $reject_reason      = $this->leaveProcessRepo->findRejectReason($apply_id);
        }

        $flex_msg = [];
        $notifies = [];
        if (preg_match("/leave/", $event)) {
            $flex_msg = ["假別::". $leave_name,"代理人::".$agent_cname,"起日::".$start_datetime,"迄日::". $end_datetime,"備住::". $comment];
        } else if (preg_match("/overwork/", $event)) {
            $flex_msg = ["加班日::". $overwork_datetime,"加班小時::".$overwork_hour,"備住::". $comment];
        }
        //取得通知範本
        if(is_null($hook_user_type)) {
            $notifies = $this->lineDefaultMsgRepo->findEventMessage($event);
        } else {
            $notifies = $this->lineDefaultMsgRepo->findOneMessage($event, $hook_user_type);
        }
        
        //範本中的變數換成真正的值
        foreach ($notifies as $notify) {
            preg_match_all('/\%\w*%/', $notify->message, $matchs);
            $matchs = $matchs[0];
            foreach ($matchs as $match) {
                $replace_var = substr($match, 1, strlen($match)-2);
                $notify->message = str_replace($match, $$replace_var, $notify->message);
            }
            if($notify->hook_user_type == 'apply_user') {
                self::pushFlexMeg($apply_line_id, array_merge([$notify->message], $flex_msg));
            } else if($notify->hook_user_type == 'validate_user') {
                self::pushFlexMeg($upper_line_id, array_merge([$notify->message], $flex_msg));
            } else {
                self::pushFlexMeg($agent_line_id, array_merge([$notify->message], $flex_msg));
            }            
        }
    }

    public function sendGreeting($sender_replyToken) {
        $notifies = $this->lineDefaultMsgRepo->findEventMessage('greet');
        foreach ($notifies as $notify) {
            self::replyTextMeg($sender_replyToken, $notify->message);           
        }
    }

    public function sendUpdateNotify($apply_id, $event, $old_user_line_id = null) 
    {
        try {
            //取得休假資料相關資料(變數名稱是訊息中的%變數%)
            $apply_leave        = $this->leaveApplyRepo->findApplyLeave($apply_id);
            $leave_name         = $apply_leave[0]->leave_name;
            $start_datetime     = $apply_leave[0]->start_date_f1;
            $end_datetime       = $apply_leave[0]->end_date_f1;
            $overwork_datetime  = $apply_leave[0]->overwork_date_f1;
            $overwork_hour      = $apply_leave[0]->over_work_hours;
            $comment            = $apply_leave[0]->comment;
            $apply_line_id      = $apply_leave[0]->apply_line_id;
            $apply_cname        = $apply_leave[0]->cname;
            $agent_line_id      = $apply_leave[0]->agent_line_id;
            $agent_cname        = $apply_leave[0]->agent_cname;
            $apply_type         = $apply_leave[0]->apply_type;
            $upper_line_id      = "";
            $reject_reason      = "";

            $flex_msg = [];
            $notifies = [];
            if ($apply_type == 'L') {
                $flex_msg = ["申請人::". $apply_cname, "假別::". $leave_name,"代理人::".$agent_cname,"起日::".$start_datetime,"迄日::". $end_datetime,"備住::". $comment];
            } else {
                $flex_msg = ["申請人::". $apply_cname, "加班日::". $overwork_datetime,"加班小時::".$overwork_hour,"備住::". $comment];
            }
            //取得通知範本
            $notifies = $this->lineDefaultMsgRepo->findEventMessage($event);
            
            //範本中的變數換成真正的值
            foreach ($notifies as $notify) {
                preg_match_all('/\%\w*%/', $notify->message, $matchs);
                $matchs = $matchs[0];
                foreach ($matchs as $match) {
                    $replace_var = substr($match, 1, strlen($match)-2);
                    $notify->message = str_replace($match, $$replace_var, $notify->message);
                }
                if($notify->hook_user_type == 'apply_user') {
                    self::pushFlexMeg($apply_line_id, array_merge([$notify->message], $flex_msg));
                } else if($notify->hook_user_type == 'validate_user') {
                    //若更改的簽核人是下一個要簽核的人，就要通知新舊簽核人，反正不用，因為輪到他簽核時才會通知
                    $data = $this->leaveProcessRepo->findApplyValidateRecord($apply_id);
                    foreach ($data as $d) {
                        self::pushFlexMeg($d->line_id, array_merge([$notify->message], $flex_msg));
                    }
                    $next_upper_user = $this->leaveProcessRepo->findNextUpperUser($apply_id);
                    self::pushFlexMeg($next_upper_user[0]->line_id, array_merge([$notify->message], $flex_msg));
                } else if($notify->hook_user_type == 'agent_user'){
                    self::pushFlexMeg($agent_line_id, array_merge([$notify->message], $flex_msg));
                } else {
                    if(!is_null($old_user_line_id)) {
                        self::pushFlexMeg($old_user_line_id, array_merge([$notify->message], $flex_msg));
                    }
                }            
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function pushFlexMeg($line_id, $msg) 
    {
        try {
            $content = "";
            for ( $i=1 ; $i<count($msg) ; $i++ ) {
                $content .= '{
                    "type": "box",
                    "layout": "horizontal",
                    "contents": [
                    {
                        "type": "text",
                        "text": "'.explode("::",$msg[$i])[0]. '",
                        "size": "sm",
                        "color": "#555555",
                        "flex": 0
                    },
                    {
                        "type": "text",
                        "text": "'.explode("::",$msg[$i])[1].'",
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
            self::_sendMsgToLine($response);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function replyTextMeg($reply_token, $msg) 
    {
        try {
            $response = array (
                "replyToken" => $reply_token,
                "messages" => array (
                    array (
                        "type" => "text",
                        "text" => $msg
                    )
                )
            );
            self::_sendMsgToLine($response, 'reply');
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function _sendMsgToLine($content, $type = 'push') 
    {
        try {
            $types = array("push", "reply");
            if (!in_array($type, $types)) throw new Exception("send line msg type error");

            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: Bearer ".Config::get('line.channel_token');
            $ch = curl_init("https://api.line.me/v2/bot/message/".$type);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));                                                                  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);                                                                                                   
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            throw $e;
        }
    }

}