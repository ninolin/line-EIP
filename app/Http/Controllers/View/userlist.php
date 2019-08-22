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
     * 顯示員工設定頁面
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page = Input::get('page', 1);
        $search = Input::get('search', '');
        $order_col = Input::get('order_col', 'username');
        $order_type = Input::get('order_type', 'DESC');

        $sql =  'select u.*, et.name as title, u3.cname as default_agent_cname, u2.cname as upper_cname, ewc.name as work_class_name ';
        $sql .= 'from user u ';
        $sql .= 'left join eip_title et on u.title_id = et.id ';
        $sql .= 'left join user u2 on u.upper_user_no = u2.NO ';
        $sql .= 'left join user u3 on u.default_agent_user_no = u3.NO ';
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
     * 設定員工的職等、第一簽核人、班別、到職日，更新年休假
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $title_id = $request->get('title_id');
            $default_agent_user_no = $request->get('default_agent_user_no');
            $upper_user_no = $request->get('upper_user_no');
            $work_class_id = $request->get('work_class_id');
            if(DB::update("update user set title_id =?, default_agent_user_no =?, upper_user_no =?, work_class_id =? where NO =?", [$title_id, $default_agent_user_no, $upper_user_no, $work_class_id, $id]) == 1) {
                return response()->json([
                    'status' => 'successful'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * 員工綁定line_id
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
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

    /**
     * 員工解除綁定line_id
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
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

    /**
     * 檢查line_id是否有被綁定
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function checklineid(Request $request, $id)
    {
        $user = DB::select("select * from user where line_id =?", [$id]);
        return response()->json([
            'status' => 'successful',
            'data' => $user
        ]);
    }

    public function get_annualleave(Request $request, $id)
    {
        $annual_leaves = 0;
        $labor_annual_leaves = 0;
        $leaves = DB::select("select * from eip_annual_leave where user_no =? and year =?", [$id, date("Y")]);
        foreach ($leaves as $l) {
            $annual_leaves = $l->annual_leaves;
            $labor_annual_leaves = $l->labor_annual_leaves;
        }
        return response()->json([
            'status' => 'successful',
            'annual_leaves' => $annual_leaves,
            'labor_annual_leaves' => $labor_annual_leaves
        ]);
    }

    public function cal_laborannualleave(Request $request, $onboard_date)
    {
        $labor_annual_leaves = $this->calcL->calc_leavedays($onboard_date, date("Y")."-01-01");
        if($labor_annual_leaves == 10000) {
            $labor_annual_leaves = 0;
        }
        return response()->json([
            'status' => 'successful',
            'labor_annual_leaves' => $labor_annual_leaves
        ]);
    }

    public function update_annualleave(Request $request, $id)
    {
        try {
            $annual_leaves = $request->get('annual_leaves');
            $onboard_date = $request->get('onboard_date');
            $labor_annual_leaves = $this->calcL->calc_leavedays($onboard_date, date("Y")."-01-01");
            if($labor_annual_leaves == 10000) {
                $labor_annual_leaves = 0;
            }

            try {
                DB::update("update user set onboard_date =? where NO =?", [$onboard_date, $id]);
                DB::delete("delete from eip_annual_leave where user_no =? and year =?", [$id, date("Y")]);
                DB::insert("insert into eip_annual_leave (user_no, year, annual_leaves, labor_annual_leaves) value (?, ?, ?, ?)", [$id, date("Y"), $annual_leaves, $labor_annual_leaves]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
            return response()->json([
                'status' => 'successful'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

}
