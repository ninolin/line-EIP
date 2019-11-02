<?php

namespace App\Http\Controllers\View\WebPersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\LeaveTypeRepository;
use App\Repositories\UserRepository;
use DB;
use Log;

class applyleave extends Controller
{
    protected $leaveTypeRepo;
    protected $userRepo;

    public function __construct(
        LeaveTypeRepository $leaveTypeRepo,
        UserRepository $userRepo
    )
    {
        $this->leaveTypeRepo = $leaveTypeRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        //因為有同一種假但不同天數的假別，所以做distinct name，之後新增假時，再判斷是用那一種假別的id
        $leavetypes     = $this->leaveTypeRepo->findDistinctType();
        $users          = $this->userRepo->findAllUser();
        $user_no        = \Session::get('user_no') ?? null;
        $user_profile   = $this->userRepo->findUserByUserNo($user_no);
        $hours_select   = [ '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11',  
                            '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
        if($user_no == null){
            echo 'user no not found';
            exit;
        }  
        $startHour = date_format(date_create($user_profile[0]->work_start),"H");
        $startMin = date_format(date_create($user_profile[0]->work_start),"i");
        $endHour = date_format(date_create($user_profile[0]->work_end),"H");
        $endMin = date_format(date_create($user_profile[0]->work_end),"i");
        $default_agent_user_no = $user_profile[0]->default_agent_user_no;
        return view('contents.WebPersonalOperate.applyleave', [
            'leavetypes'            => $leavetypes,
            'users'                 => $users,
            'user_profile'          => $user_profile,
            'nowdate'               => date("Y-m-d"),
            'user_id'               => $user_no,
            'tab'                   => 'applyleave',
            'hours_select'          => $hours_select,
            'startHour'             => $startHour,
            'startMin'              => $startMin,
            'endHour'               => $endHour,
            'endMin'                => $endMin,
            'default_agent_user_no' => $default_agent_user_no
        ]);
    }
}
