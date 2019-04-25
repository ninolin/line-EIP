<?php

namespace App\Http\Controllers\Line;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;
use App\Lineapi\sendmsg;
use App\Providers\LineServiceProvider;

class Receive extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
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
