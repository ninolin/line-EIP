<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use DB;
class LoginController extends Controller
{
    private $auth;

    public function __construct(Authenticate $auth)
    {
        $this->auth = $auth;
    }

    public function login(Request $request)
    {   
        $account = $request->input('account');
        $password = $request->input('password');
        $users = DB::select('select * from users where name = ? and password = ?', [$account, $password]);
        if(sizeof($users) == 1 ) {
            if ($this->auth->setVerified()) {
                return $this->auth->redirect();
            }
        } else {
            return $this->auth->redirect();
        }
    }
}
