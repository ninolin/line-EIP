<?php

namespace App\Http\Controllers\View\WorkSetting;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use DB;
class workclass extends Controller
{
    /**
     * 回傳全部班表
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $titles = DB::select('select * from eip_work_class', []);
        return response()->json([
            'status' => 'successful',
            'data' => $titles
        ]);
    }

    /**
     * 顯示班表
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $sql  = 'select * from eip_work_class limit ?,10 ';
        $classes = DB::select($sql, [($page-1)*10]);
        $total_classes = DB::select('select * from eip_work_class', []);
        $total_pages = ceil(count($total_classes)/10);

        return view('contents.WorkSetting.workclass', [
            'classes'       => $classes, 
            'page'          => $page,
            'total_pages'   => $total_pages,
            'tab'           => 'workclass'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $name = Input::get('name', '');
        $work_start = Input::get('work_start', '');
        $work_end = Input::get('work_end', '');
        $lunch_start = Input::get('lunch_start', '');
        $lunch_end = Input::get('lunch_end', '');

        $sql = "insert into eip_work_class (name, work_start, work_end, lunch_start, lunch_end) values (?, ?, ?, ?, ?)";
        if(DB::insert($sql, [$name, $work_start, $work_end, $lunch_start, $lunch_end]) == 1) {
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $name = Input::get('name', '');
        $work_start = Input::get('work_start', '');
        $work_end = Input::get('work_end', '');
        $lunch_start = Input::get('lunch_start', '');
        $lunch_end = Input::get('lunch_end', '');

        $sql = "update eip_work_class set name =?, work_start =?, work_end =?, lunch_start =?, lunch_end =? where id =?";
        if(DB::update($sql, [$name, $work_start, $work_end, $lunch_start, $lunch_end, $id]) == 1) {
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(DB::delete("delete from eip_work_class where id =?", [$id]) == 1) {
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
