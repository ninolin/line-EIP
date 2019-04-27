<?php

namespace App\Http\Controllers\Line;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use DB;
use App\Lineapi\sendmsg;
use App\Providers\LineServiceProvider;

class Receive extends Controller
{
    public function receive(Request $request)
    {
        $line_channel = "1635106062";
        $bodyContent = $request->getContent(); //取得request的body內容
        $json_obj = json_decode($bodyContent); //轉成json格式
        $sender_replyToken = $json_obj->events[0]->replyToken; //取得訊息的replyToken
        $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
        $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
        log::info("sender_txt:".$sender_txt);
        $user = DB::select('select * from user where line_id =?', [$sender_userid]);
        if(count($user) == 0) {
            log::info("aaaa");
            //該line_id無在db中存在，判斷是不是一個md5字串，是的話就是要進行綁定，否的話就請輸入認證碼
            if (preg_match("/[a-z0-9]{32}/", $sender_txt)) {
                log::info("bbbb");
                $unlink_user = DB::select("select * from user where line_id = '' or line_id is null", []);
               
                foreach ($unlink_user as $v) {
                    log::info(md5($v->dd));
                    if(md5($v->dd) == $sender_txt){
                        log::info("zzzz");
                        log::info($sender_userid." ".$line_channel." ".$v->NO);
                        if(DB::update("update user set line_id =?, line_channel = ? where NO =?", [$sender_userid, $line_channel, $v->NO]) == 1) {
                            LineServiceProvider::pushTextMsg($sender_userid, "恭喜".$v->cname."成功加入，歡迎使用");
                        } else {
                            LineServiceProvider::replyTextMsgWithChannel($sender_userid, $sender_replyToken, $line_channel, "綁定失敗:更新db失敗");
                        }
                    }
                }
            } else {
                log::info("cccc");
                LineServiceProvider::replyTextMsgWithChannel($sender_userid, $sender_replyToken, $line_channel, "歡迎初次使用EIP系統，請輸入認證碼來讓我知道你是誰");
            }            
        } else {
            log::info("dddd");
            LineServiceProvider::replyTextMsgWithChannel($sender_userid, $sender_replyToken, $line_channel, $sender_txt);
        }
        return response()->json([
            'status' => 'successful',
            'message'=> 1
        ]);
    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        log::info("aaaa:");
        $bodyContent = $request->getContent(); //取得request的body內容
        $json_obj = json_decode($bodyContent); //轉成json格式
        $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
        $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
        log::info("sender_txt:".$sender_txt);
        $user = DB::select('select * from user where line_id =?', [$sender_userid]);
        if(count($user) == 0) {
            //該line_id無在db中存在，判斷是不是一個md5字串，是的話就是要進行綁定，否的話就請輸入認證碼
            if (preg_match("/[a-z0-9]{32}/", $sender_txt)) {
                $unlink_user = DB::select("select * from user where line_id = '' or line_is is null", []);
                foreach ($unlink_user as $v) {
                    if(md5($v->dd) == $sender_txt){
                        if(DB::update("update user set line_id =? where NO =?", [$sender_userid, $v->NO]) != 1) {
                            LineServiceProvider::sendTextMsg($sender_userid, "恭喜".$v->cname."成功加入，歡迎使用");
                        } else {
                            LineServiceProvider::sendTextMsg($sender_userid, "綁定失敗:更新db失敗");
                        }
                    } else {
                        LineServiceProvider::sendTextMsg($sender_userid, "綁定失敗:找不到符合的認證碼");
                    }
                }
            } else {
                LineServiceProvider::sendTextMsg($sender_userid, "歡迎初次使用EIP系統，請輸入認證碼來讓我知道你是誰");
            }            
        } else {
            LineServiceProvider::sendTextMsg($sender_userid, $sender_txt);
        }
        return response()->json([
            'status' => 'successful',
            'message'=> 1
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //$abc = LineServiceProvider::test();
        $bodyContent = $request->getContent();
        Log::info($bodyContent);
        $json_obj = json_decode($bodyContent); //轉成json格式
        $sender_userid = $json_obj->events[0]->source->userId; //取得訊息發送者的id
        $sender_txt = $json_obj->events[0]->message->text; //取得訊息內容
        LineServiceProvider::sendTextMsg($sender_userid, $sender_txt);
        return response()->json([
            'status' => 'successful',
            'message'=> 1
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
