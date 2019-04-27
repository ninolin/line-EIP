<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use DB;
class titlelist extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $titles = DB::select('select * from eip_title', []);
        return response()->json([
            'status' => 'successful',
            'data' => $titles
        ]);
        
    }

    /**
     * 顯示職等資料頁面
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $titles = DB::select('select * from eip_title limit ?,10', [($page-1)*10]);
        $total_titles = DB::select('select * from eip_title', []);
        $total_pages = ceil(count($total_titles)/10);
        return view('contents.titlelist', [
            'titles' => $titles, 
            'page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    /**
     * 新增職等資料
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //debug( $request->get('name'));
        //檢查參數格式是否正確
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:32'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all()
            ], 400);
        }
        $name = Input::get('name', '');
        //檢查一樣名稱的職等是否存在
        $titles = DB::select('select name from eip_title where name =?', [$name]);
        if(count($titles) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'name exists'
            ], 409);
        }

        if(DB::insert("insert into eip_title (name) values (?)", [$name]) == 1) {
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
     * 修改職等別資料
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        //檢查參數格式是否正確
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:32'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->all()
            ], 400);
        }
        $name = $request->get('name');
        //檢查一樣名稱的職等是否存在
        $titles = DB::select('select name from eip_title where name =? and id !=?', [$name, $id]);
        if(count($titles) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'name exists'
            ], 409);
        }

        if(DB::update("update eip_title set name =? where id =?", [$name, $id]) == 1) {
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
     * 刪除職等資料
     * @author nino
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(DB::delete("delete from eip_title where id =?", [$id]) == 1) {
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
