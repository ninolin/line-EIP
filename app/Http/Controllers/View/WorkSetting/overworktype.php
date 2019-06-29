<?php

namespace App\Http\Controllers\View\WorkSetting;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use DB;

class overworktype extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * 顯示加班資料頁面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $types = DB::select('select eot.*, et.name as title_name, et.id as title_id from eip_overwork_type eot, eip_title et where eot.approved_title_id = et.id order by hour limit ?,10 ', [($page-1)*10]);
        $total_types = DB::select('select * from eip_overwork_type', []);
        $total_pages = ceil(count($total_types)/10);
        return view('contents.WorkSetting.overworktype', [
            'types'         => $types, 
            'page'          => $page,
            'total_pages'   => $total_pages,
            'tab'           => 'overworktype'
        ]);
    }

    /**
     * 新增加班資料
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $hour = $request->get('hour');
        $approved_title_id = $request->get('approved_title_id');
        //檢查參數格式是否正確
        $validator = Validator::make($request->all(), [
            'hour' => 'required|integer',
            'approved_title_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all()
            ], 400);
        }
        //檢查一樣小時的加班是否存在
        $types = DB::select('select hour from eip_overwork_type where hour =?', [$hour]);
        if(count($types) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'hour exists'
            ], 409);
        }

        if(DB::insert("insert into eip_overwork_type (hour, approved_title_id) values (?, ?)", [$hour, $approved_title_id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'insert error'
            ], 500);
        }
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
     * 修改加班資料
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $hour = $request->get('hour');
        $approved_title_id = $request->get('approved_title_id');
        //檢查參數格式是否正確
        $validator = Validator::make($request->all(), [
            'hour' => 'required|integer',
            'approved_title_id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all()
            ], 400);
        }
        //檢查一樣小時的加班是否存在
        $types = DB::select('select hour from eip_overwork_type where hour =? and id != ?', [$hour, $id]);
        if(count($types) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'hour exists'
            ], 409);
        }

        if(DB::update("update eip_overwork_type set hour =?, approved_title_id =? where id =?", [$hour, $approved_title_id, $id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ], 500);
        }
    }

    /**
     * 刪除加班資料
     * @author nino
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(DB::delete("delete from eip_overwork_type where id =?", [$id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'delete error'
            ], 500);
        }
    }
}
