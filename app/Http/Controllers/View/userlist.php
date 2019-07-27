<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use DB;
use App\Console\commands\CalcLeaveDays;

class userlist extends Controller
{
    private $calcL;

    public function __construct(CalcLeaveDays $calcL)
    {
        $this->calcL = $calcL;
    }
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

        $sql =  'select u.*, et.name as title, u2.cname as upper_cname, ewc.name as work_class_name ';
        $sql .= 'from user u ';
        $sql .= 'left join eip_title et on u.title_id = et.id ';
        $sql .= 'left join user u2 on u.upper_user_no = u2.NO ';
        $sql .= 'left join eip_work_class ewc on u.work_class_id = ewc.id ';
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
        $work_class_id = $request->get('work_class_id');
        $onboard_date = $request->get('onboard_date');

        if(DB::update("update user set title_id =?, upper_user_no =?, work_class_id =?, onboard_date =? where NO =?", [$title_id, $upper_user_no, $work_class_id, $onboard_date, $id]) == 1) {
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

    public function calcleaveday(Request $request, $NO)
    {
        $leave_day = 0;
        $year_useleave = 0;
        $year_totalleave = 0;
        $users = DB::select("select * from user where NO =?", [$NO]);
        foreach ($users as $u) {
            $onboard_date = $u->onboard_date;
            if(!is_null($u->year_useleave)) {
                $year_useleave = $u->year_useleave;
            }
            if(!is_null($u->year_totalleave)) {
                $year_totalleave = $u->year_totalleave;
            }
            if(!is_null($onboard_date)) {
                $leave_day = $this->calcL->calc_leavedays($onboard_date, date("Y")."-01-01");
                if($leave_day == 10000) {
                    $leave_day = 0;
                }
            }
        }
        return response()->json([
            'status' => 'successful',
            'leave_day' => $leave_day,
            'year_useleave' => $year_useleave,
            'year_totalleave' => $year_totalleave
        ]);
    }

    public function updateleaveday(Request $request, $NO)
    {
        $leave_day = $request->get('leave_day');
        if(!is_null($leave_day)) {
            if(DB::update("update user set year_totalleave=? where NO =?", [$leave_day, $NO]) == 1) {
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
