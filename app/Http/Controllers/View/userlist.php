<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Repositories\UserRepository;
use App\Console\commands\CalcLeaveDays;
use DB;
use Exception;
use Log;

class userlist extends Controller
{
    private $calcL;
    protected $userRepo;

    public function __construct(
        CalcLeaveDays $calcL, 
        UserRepository $userRepo
    )
    {
        $this->calcL = $calcL;
        $this->userRepo = $userRepo;
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
        $search = Input::get('search', null);
        $order_col = Input::get('order_col', 'cname');
        $order_type = Input::get('order_type', 'DESC');
        $total_pages = 0;
        $users = [];

        $user_result = $this->userRepo->findAllUserDetail($search, $order_col, $order_type, $page);
        if($user_result["status"] == "successful") {
            $users = $user_result["data"];
            $total_pages = $user_result["total_pages"];
        }

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
            $title_id               = $request->get('title_id');
            $default_agent_user_no  = $request->get('default_agent_user_no');
            $upper_user_no          = $request->get('upper_user_no');
            $work_class_id          = $request->get('work_class_id');
            $eip_level              = $request->get('eip_level');
            $sql = "update 
                        user 
                    set 
                        title_id =?, 
                        default_agent_user_no =?, 
                        upper_user_no =?, 
                        work_class_id =?,
                        eip_level =? 
                    where NO =?";
            if(DB::update($sql, [$title_id, $default_agent_user_no, $upper_user_no, $work_class_id, $eip_level, $id]) == 1) {
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

    /**
     * 取得用戶的個人資料
     * 
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function get_user_data(Request $request, $id)
    {
        try {    
            $user_profile = $this->userRepo->findUserByUserNo($id);
            if(count($user_profile) == 1) {
                return response()->json([
                    'status' => 'successful',
                    'data' => $user_profile[0]
                ]);
            } else {
                throw new Exception('user not found'); 
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
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
        $labor_annual_leaves = 0;
        $onboard_array = explode('-',$onboard_date);

        $onboard_y = $onboard_array[0];
        $onboard_m = $onboard_array[1];
        $onboard_d = $onboard_array[2];
        if($onboard_y == date("Y") && (date("m")-$onboard_m>=6)) {
            $labor_annual_leaves = $this->calcL->calc_leavedays($onboard_date, date("Y")."-".($onboard_m+6)."-".$onboard_d);
        } else {
            $labor_annual_leaves = $this->calcL->calc_leavedays($onboard_date, date("Y")."-01-01");
        }
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
