<?php

namespace App\Http\Controllers\View\WebPersonalOperate;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Log;

class applyoverwork extends Controller
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

        return view('contents.WebPersonalOperate.applyoverwork', [
            'tab'       => 'applyoverwork',
            'nowdate'   => date("Y-m-d"),
            'user_id'   => $user_no,
        ]);
    }
}
