<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use Log;

class messagelog extends Controller
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
        $timelimit = time() - 86400*10;
        $sql = "select * from eip_line_message where time > ? ";
        if($search != '') {
            $sql .= 'and username like "%'.$search.'%" or message like "%'.$search.'%" ';
        }
        $sql .= "order by ".$order_col." ".$order_type." limit ?,10";
        $messages = DB::select($sql, [$timelimit, ($page-1)*10]);

        $page_sql = 'select * from eip_line_message where time > ? ';
        if($search != '') {
            $page_sql .= 'and username like "%'.$search.'%" or message like "%'.$search.'%" ';
        }

        $total_messages = DB::select($page_sql, [$timelimit]);
        $total_pages = ceil(count($total_messages)/10);
        return view('contents.messagelog', [
            'search' => $search,
            'order_col' => $order_col,
            'order_type' => $order_type,
            'messages' => $messages,
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
