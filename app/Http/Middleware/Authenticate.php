<?php

namespace App\Http\Middleware;

use Closure;
use Log;
class Authenticate
{
    const SESSION = 'code';//存放在Session的Key值
    const REDIRECT_TO = 'userlist';//轉跳的主頁
    const EXPIRED_MINS = '+ 15min'; //輸入Session過期時間

    public function handle($request, Closure $next) {
        $timestamp = session(self::SESSION); //取得在session中的時間
        if($timestamp and $timestamp >= time()){
            $this->setVerified();
            return $next($request);
        } else {
            return redirect(route('login'));
        }
    }

    public function setVerified($min = null) {
        if (!$min) {
            $min = self::EXPIRED_MINS;
        }
        session([self::SESSION => strtotime($min)]);
        log::info(session(self::SESSION));
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
