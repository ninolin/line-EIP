<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
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
     * Show the form for creating a new resource.
     *
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //debug( $request->get('name'));
        $name = Input::get('name', '');
        $titles = DB::select('select name from eip_title where name =?', [$name]);
        if(count($titles) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'name exists'
            ]);
        }
        if(DB::insert("insert into eip_title (name) values (?)", [$name]) == 1) {
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

        //debug( $request->get('name'));
        //debug( $id);
        $name = $request->get('name');
        $titles = DB::select('select name from eip_title where name =? and id !=?', [$name, $id]);
        if(count($titles) > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'name exists'
            ]);
        }
        if(DB::update("update eip_title set name =? where id =?", [$name, $id]) == 1) {
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
        if(DB::delete("delete from eip_title where id =?", [$id]) == 1) {
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
