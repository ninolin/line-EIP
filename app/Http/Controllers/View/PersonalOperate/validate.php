<?php

namespace App\Http\Controllers\View\PersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LineServiceProvider;
use App\Providers\LeaveProvider;
use App\Providers\HelperServiceProvider;
use App\Repositories\LeaveProcessRepository;
use App\Services\UserService;
use DB;
use Log;
use Config;
use DateTime;

class validate extends Controller
{
    
    protected $userService;
    protected $leaveProcessRepo;

    public function __construct(
        UserService $userService,
        LeaveProcessRepository $leaveProcessRepo
    )
    {
        $this->userService = $userService;
        $this->leaveProcessRepo = $leaveProcessRepo;
    }

    /**
     * web顯示簽核作業
     * @author nino
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        $validate_apply             = [];
        $unvalidate_apply           = [];
        $show_tab                   = Input::get('show_tab', 'unvalidate_apply');
        $validate_apply_page        = Input::get('validate_apply_page', 1);
        $unvalidate_apply_page      = Input::get('unvalidate_apply_page', 1);
        $validate_apply_t_pages     = 0;
        $unvalidate_apply_t_pages   = 0;

        $user_no = \Session::get('user_no') ?? null;
        $validate_result = $this->leaveProcessRepo->findApplyProcess($user_no, true, $validate_apply_page);
        if($validate_result["status"] == "successful") {
            $validate_apply = $validate_result["data"];
            $validate_apply_t_pages = $validate_result["total_pages"];
        }
        $unvalidate_result = $this->leaveProcessRepo->findUnValidateApplyProcess($user_no, $unvalidate_apply_page);
        if($unvalidate_result["status"] == "successful") {
            $unvalidate_apply = $unvalidate_result["data"];
            $unvalidate_apply_t_pages = $unvalidate_result["total_pages"];
        }

        return view('contents.PersonalOperate.validate', [
            'tab'                       => 'validate',
            'login_user_no'             => $user_no,
            'show_tab'                  => $show_tab,
            'validate_apply'            => $validate_apply,
            'validate_apply_page'       => $validate_apply_page,
            'validate_apply_t_pages'    => $validate_apply_t_pages,
            'unvalidate_apply'          => $unvalidate_apply,
            'unvalidate_apply_page'     => $unvalidate_apply_page,
            'unvalidate_apply_t_pages'  => $unvalidate_apply_t_pages
        ]);
    }

}
