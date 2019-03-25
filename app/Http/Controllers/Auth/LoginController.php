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
        debug($account);
        debug(md5($password));
        //$users = DB::select('select * from users where name = ? and password = ?', [$account, $password]);
        $users = DB::connection('mysql_erptools')->select('select * from user where email = ? and password = ?', [$account, md5($password)]);
        print_r($users);

        if(sizeof($users) == 1 ) {
            if ($this->auth->setVerified()) {
                return $this->auth->redirect();
            }
        } else {
            return $this->auth->redirect();
        }
    }
}
