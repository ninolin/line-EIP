<?php

namespace App\Http\Controllers\View\WorkSetting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Repositories\LineDefaultMsgRepository;

use Exception;

class LineDefaultMessage extends Controller
{
    protected $lineDefaultMsgRepo;

    public function __construct
    (
        LineDefaultMsgRepository $lineDefaultMsgRepo
    )
    {
        $this->lineDefaultMsgRepo = $lineDefaultMsgRepo;
    }

    /**
     * 顯示 "LINE預設訊息" 頁面
     *
     * @return \Illuminate\Http\Response
     */
    public function show_page()
    {
        try {
            $page = Input::get('page', 1);
            $data = $this->lineDefaultMsgRepo->findAllMessage($page);
            return view('contents.WorkSetting.line_default_msg', [
                'messages'      => $data["data"],
                'tab'           => 'linedefaultmsg',
                'total_pages'   => $data["total_pages"],
                'page'          => $page
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 取得指定的 "LINE預設訊息"
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * @author nino
     */
    public function get_one_message($id)
    {
        try {
            $message = $this->lineDefaultMsgRepo->findOneMessage($id);

            return response()->json([
                'status'    => 'successful',
                'data'      => $message
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_message(Request $request, $id)
    {
        try {
            $message    = Input::get('message');
            $validator  = Validator::make($request->all(), [
                'message'   => 'required|string|max:255'
            ]);
            if ($validator->fails()) throw $validator->errors()->all();

            $this->lineDefaultMsgRepo->updateMessage($id, $message);
            return response()->json([
                'status'    => 'successful'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => $e->getMessage()
            ], 500);
        }
    }

}
