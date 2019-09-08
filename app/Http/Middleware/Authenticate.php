<?php

namespace App\Http\Middleware;

use Closure;
use Log;
class Authenticate
{
    const SESSION = 'code';//存放在Session的Key值
    const REDIRECT_TO = 'po_applyleave';//轉跳的主頁
    const EXPIRED_MINS = '+ 15min'; //輸入Session過期時間

    public function handle($request, Closure $next) {
        $timestamp = session(self::SESSION); //取得在session中的時間
        $user_no = session('user_no');
        $eip_level = session('eip_level');
        if($user_no and $timestamp and $timestamp >= time()){
            $this->setVerified($user_no, $eip_level, null);
            return $next($request);
        } else {
            return redirect(route('login'));
        }
    }

    public function setVerified($user_no, $eip_level, $min) {
        if (!$min) {
            $min = self::EXPIRED_MINS;
        }
        session([self::SESSION => strtotime($min)]);
        session(['user_no' => $user_no]);
        session(['eip_level' => $eip_level]);
        //log::info(session(self::SESSION));
        //log::info(session('user_no'));
        return true;
    }
    
    public function cleanVerified() {
        session([self::SESSION => 0]);
        return true;
    }

    public function redirect() {
        return redirect(route(self::REDIRECT_TO));
    }
}
