<?php

namespace App\Http\Controllers\View\WorkManage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Log;

class applyoverwork extends Controller
{
    protected $userRepo;

    public function __construct(
        UserRepository $userRepo
    )
    {
        $this->userRepo = $userRepo;
    }
    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        $users = $this->userRepo->findAllUser();
        return view('contents.WorkManage.applyoverwork', [
            'tab'       => 'applyoverwork',
            'users'     => $users,
            'nowdate'   => date("Y-m-d")
        ]);
    }
}
