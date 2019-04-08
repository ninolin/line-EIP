<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
class leavetypelist extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = DB::select('select * from user', []);
        return response()->json([
            'status' => 'successful',
            'data' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $types = DB::select('select elt.*, et.name as title_name, et.id as title_id from eip_leave_type elt, eip_title et where elt.approved_title_id = et.id limit ?,10 ', [($page-1)*10]);
        $total_types = DB::select('select * from eip_leave_type', []);
        $total_pages = ceil(count($total_types)/10);
        return view('contents.leavetypelist', [
            'types' => $types, 
            'page' => $page,
            'total_pages' => $total_pages
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
        //debug( $request->get('name'));
        $name = $request->get('name');
        $approved_title_id = $request->get('approved_title_id');
        $types = DB::select('select name from eip_leave_type where name =?', [$name]);
        if(count($types) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'name exists'
            ]);
        }
        if(DB::insert("insert into eip_leave_type (name, approved_title_id) values (?, ?)", [$name, $approved_title_id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'insert error'
            ]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $name = $request->get('name');
        $approved_title_id = $request->get('approved_title_id');
        if(DB::update("update eip_leave_type set name =?, approved_title_id =? where id =?", [$name, $approved_title_id, $id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'update error'
            ]);
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
        if(DB::delete("delete from eip_leave_type where id =?", [$id]) == 1) {
            return response()->json([
                'status' => 'successful'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'delete error'
            ]);
        }
    }
}
