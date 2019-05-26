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
        $users = DB::select('select * from user where status = "T"', []);
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
        $search = Input::get('search', '');
        $order_col = Input::get('order_col', 'username');
        $order_type = Input::get('order_type', 'DESC');

        $sql =  'select u.*, et.name as title, u2.cname as upper_cname ';
        $sql .= 'from user u ';
        $sql .= 'left join eip_title et on u.title_id = et.id ';
        $sql .= 'left join user u2 on u.upper_user_no = u2.NO ';
        $sql .= 'where u.status = "T" ';
        if($search != '') {
            $sql .= 'and (u.username like "%'.$search.'%" or u.cname like "%'.$search.'%" or u.email like "%'.$search.'%") ';
        }
        $sql .= ' order by u.'.$order_col.' '.$order_type.' limit ?,10 ';
        $users = DB::select($sql, [($page-1)*10]);
        
        $page_sql = 'select * from user where status = "T"';
        if($search != '') {
            $page_sql .= 'and (username like "%'.$search.'%" or cname like "%'.$search.'%" or email like "%'.$search.'%") ';
        }
        $total_users = DB::select($page_sql, []);
        $total_pages = ceil(count($total_users)/10);
        debug($page);
        debug($users);
        return view('contents.userlist', [
            'search' => $search,
            'order_col' => $order_col,
            'order_type' => $order_type,
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

    public function bindlineid(Request $request, $id)
    {
        $line_id = $request->get('line_id');
        if(DB::update("update user set line_id =? where NO =?", [$line_id, $id]) == 1) {
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

    public function unbindlineid(Request $request, $id)
    {
        if(DB::update("update user set line_id ='' where NO =?", [$id]) == 1) {
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

    public function checklineid(Request $request, $id)
    {
        $user = DB::select("select * from user where line_id =?", [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $user
        ]);
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
