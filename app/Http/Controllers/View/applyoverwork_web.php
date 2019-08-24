<?php

namespace App\Http\Controllers\View;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\View\applyoverleave;

class applyoverwork_web extends applyoverleave
{

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show_view()
    {
        $user_no = session()->all()['user_no'] || null;

        if($user_no == null){
            echo 'user no not found';
            exit;
        }

        return view('contents/PersonalOperate.applyoverwork', [
            'nowdate' => date("Y-m-d"),
            'user_id' => $user_no,
        ]);
    }
}
