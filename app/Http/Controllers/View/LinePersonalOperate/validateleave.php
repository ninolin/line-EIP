<?php

namespace App\Http\Controllers\View\LinePersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LeaveProvider;
use App\Providers\HelperServiceProvider;
use App\Services\UserService;
use App\Repositories\LeaveProcessRepository;
use App\Repositories\LeaveApplyRepository;
use App\Services\SendLineMessageService;
use DB;
use Log;
use Config;
use DateTime;

class validateleave extends Controller
{
    protected $userService;
    protected $leaveProcessRepo;
    protected $leaveApplyRepo;
    protected $sendLineMessageService;

    public function __construct(
        UserService $userService,
        LeaveProcessRepository $leaveProcessRepo,
        LeaveApplyRepository $leaveApplyRepo,
        SendLineMessageService $sendLineMessageService
    )
    {
        $this->userService = $userService;
        $this->leaveProcessRepo = $leaveProcessRepo;
        $this->leaveApplyRepo = $leaveApplyRepo;
        $this->sendLineMessageService = $sendLineMessageService;
    }
    /**
     * 顯示LIFF待審核清單資料
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function index($type_name, $line_id)
    {
        $leaves = [];
        $user = $this->userService->get_user_info($line_id, 'line');
        if($user['status'] == 'error') throw new Exception($user['message']);
        $user_no = $user['data']->NO;
        if($type_name == 'unvalidate') {
            $unvalidate_result = $this->leaveProcessRepo->findUnValidateApplyProcess($user_no);
            if($unvalidate_result["status"] == "successful") {
                $leaves = $unvalidate_result["data"];
            }
        } else {
            $validate_result = $this->leaveProcessRepo->findValidateApplyProcess($user_no);
            if($validate_result["status"] == "successful") {
                $leaves = $validate_result["data"];
            }
        }
        
        return view('contents.LinePersonalOperate.validateleave', [
            'leaves'        => $leaves,
            'login_user_no' => $user_no,
            'type'          => $type_name
        ]);
    }

    /**
     * 審核請假/加班
     * @author nino
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $apply_id       = $id;                              //申請單id
            //$line_id        = $request->get('userId');          //審核人line_id
            $is_validate    = $request->get('is_validate');     //審核結果(0=拒絕,1=同意)
            $apply_type     = $request->get('apply_type');      //申請單類型(L,O)
            $reject_reason  = $request->get('reject_reason');   //拒絕理由
            $process_id     = $request->get('process_id');      //eip_leave_apply_process的id
            
            //取得apply單的資料
            $apply_data = json_decode(LeaveProvider::getLeaveApply($apply_id));
            if($reject_reason == "null") {$reject_reason = null;}
            if(DB::update("update eip_leave_apply_process set is_validate =?, reject_reason =?, validate_time = now() where id =?", [$is_validate, $reject_reason, $process_id]) != 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'update error'
                ]);
            }

            if($is_validate == 0) {
                //拒絕審核
                if(DB::update("update eip_leave_apply set apply_status =? where id =?", ['N', $apply_id]) != 1) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'update error'
                    ]);
                } else {
                    if($apply_data->apply_type == 'L') {
                        if($apply_data->leave_compensatory == 1) {
                            //拒絕加班補休要把用那天加班來補的紀錄刪掉
                            DB::delete("delete from eip_compensatory_relationship where leave_apply_id = ?", [$apply_id]);
                        }
                        $this->sendLineMessageService->sendNotify($apply_id, 'reject_leave');
                    } else {
                        $this->sendLineMessageService->sendNotify($apply_id, 'reject_overwork');
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            } else {
                //同意審核
                $processes = DB::select('select id from eip_leave_apply_process where apply_id = ? order by id desc limit 1', [$apply_id]);
                $last_approved_id = 0;
                foreach ($processes as $v) {
                    $last_approved_id = $v->id;
                }

                if($last_approved_id == $process_id) {
                    //全部審核完了
                    $insert_event = LeaveProvider::insert_event2gcalendar($apply_data->start_date, $apply_data->end_date, $apply_data->apply_user_cname."的".$apply_data->leave_name);
                    log::info($insert_event);
                    //if($insert_event == 0){ throw new Exception('Insert to google calendar failed!');  }
                    if(DB::update("update eip_leave_apply set apply_status =?, event_id =? where id =?", ['Y', $insert_event, $apply_id]) == 1) {
                        if($apply_data->apply_type == 'L') {
                            //如果是加班補休，要從補休中扣
                            if($apply_data->leave_compensatory == 1) {
                                self::use_compensatory_leave($apply_data);
                            }
                            $this->sendLineMessageService->sendNotify($apply_id, 'pass_leave');
                        } else {
                            $this->sendLineMessageService->sendNotify($apply_id, 'pass_overwork');
                        }
                        return response()->json([
                            'status' => 'successful'
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'update error'
                        ]);
                    }
                } else {
                    //繼續給下一個人審核
                    // $next_process_sql  = "select upper_user_no from eip_leave_apply_process where apply_id = ? and id > ? ";
                    // $next_process_sql .= "order by id desc limit 1 ";
                    // $next_process = DB::select($next_process_sql, [$apply_id, $process_id]);
                    // $next_approved_user_no = 0;
                    // foreach ($next_process as $v) {
                    //     $next_approved_user_no = $v->upper_user_no;
                    // }

                    // $upper_users = DB::select('select line_id from user where NO = ?', [$next_approved_user_no]);
                    // $upper_user_line_id = "";   //審核人的下一個審核人的line_id
                    // foreach ($upper_users as $v) {
                    //     $upper_user_line_id = $v->line_id;
                    // }
                    if($apply_data->apply_type == 'L') {
                        $this->sendLineMessageService->sendNotify($apply_id, 'apply_leave', 'validate_user');
                    } else {
                        $this->sendLineMessageService->sendNotify($apply_id, 'apply_overwork', 'validate_user');
                    }
                    return response()->json([
                        'status' => 'successful'
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show_other_leaves($id)
    {
        $leaves = $this->leaveApplyRepo->findPersonalOtherLeave($id);
        return response()->json([
            'status' => 'successful',
            'data' => $leaves
        ]);
    }
    
}
