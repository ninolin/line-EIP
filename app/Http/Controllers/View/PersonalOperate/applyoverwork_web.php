<?php

namespace App\Http\Controllers\View\PersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;

class applyoverwork_web extends Controller
{

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        $user_no = \Session::get('user_no') ?? null;

        if($user_no == null){
            echo 'user no not found';
            exit;
        }

        return view('contents.PersonalOperate.applyoverwork', [
            'tab'       => 'applyoverwork',
            'nowdate'   => date("Y-m-d"),
            'user_id'   => $user_no,
        ]);
    }
}
