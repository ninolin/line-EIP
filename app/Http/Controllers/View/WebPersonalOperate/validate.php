<?php

namespace App\Http\Controllers\View\WebPersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Providers\LeaveProvider;
use App\Providers\HelperServiceProvider;
use App\Repositories\LeaveProcessRepository;
use App\Repositories\UserRepository;
use App\Services\UserService;
use DB;
use Log;
use Config;
use DateTime;

class validate extends Controller
{
    
    protected $userService;
    protected $leaveProcessRepo;
    protected $userRepo;

    public function __construct(
        UserService $userService,
        LeaveProcessRepository $leaveProcessRepo,
        UserRepository $userRepo
    )
    {
        $this->userService = $userService;
        $this->leaveProcessRepo = $leaveProcessRepo;
        $this->userRepo = $userRepo;
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
        $search                     = Input::get('search', '');
        $user_no                    = \Session::get('user_no') ?? null;
        $key_users                  = $this->userRepo->findUserByKeyword($search, 1);
        $key_user_no                = null;
        if(count($key_users) == 1 && $search != '') {
            foreach ($key_users as $v) {
                $key_user_no = $v->NO;
            }
        }

        $validate_result = $this->leaveProcessRepo->findApplyProcess($user_no, $key_user_no, true, $validate_apply_page);
        if($validate_result["status"] == "successful") {
            $validate_apply = $validate_result["data"];
            $validate_apply_t_pages = $validate_result["total_pages"];
        }
        $unvalidate_result = $this->leaveProcessRepo->findUnValidateApplyProcess($user_no, $key_user_no, $unvalidate_apply_page);
        if($unvalidate_result["status"] == "successful") {
            $unvalidate_apply = $unvalidate_result["data"];
            $unvalidate_apply_t_pages = $unvalidate_result["total_pages"];
        }

        return view('contents.WebPersonalOperate.validate', [
            'tab'                       => 'validate',
            'search'                    => $search,
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
