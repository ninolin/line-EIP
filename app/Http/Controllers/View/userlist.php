<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
class userlist extends Controller
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
        $sql =  'select u.*, et.name as title, u2.cname as upper_cname ';
        $sql .= 'from user u ';
        $sql .= 'left join eip_title et on u.title_id = et.id ';
        $sql .= 'left join user u2 on u.upper_user_no = u2.NO ';
        $sql .= 'limit ?,10 ';
        $users = DB::select($sql, [($page-1)*10]);
        //$users = DB::select('select u.*, et.name as title from user u left join eip_title et on u.title_id = et.id limit ?,10', [($page-1)*10]);
        $total_users = DB::select('select * from user', []);
        $total_pages = ceil(count($total_users)/10);
        debug($page);
        debug($users);
        return view('contents.userlist', [
            'users' => $users, 
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
        //
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
        $title_id = $request->get('title_id');
        $upper_user_no = $request->get('upper_user_no');
        if(DB::update("update user set title_id =?, upper_user_no =? where NO =?", [$title_id, $upper_user_no, $id]) == 1) {
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
        //
    }
}
