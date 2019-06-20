<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use DB;
class AuthController extends Controller
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
        //debug(md5($password));
        $users = DB::select('select * from user where username = ? and password = ?', [$account, md5($password)]);
        if(sizeof($users) == 1 ) {
            if ($this->auth->setVerified()) {
                return $this->auth->redirect();
            }
        } else {
            return redirect('login')->with('login_status', '帳號或密碼錯誤');
        }
    }

    public function logout(Request $request)
    {   
        if ($this->auth->cleanVerified()) {
            return redirect('login');
        }
    }

    public function glogin(Request $request)
    {   
        $gmail = $request->input('gmail');
        $token = $request->input('token');
        debug($gmail);
        // return response()->json([
        //     'status' => 'successful'
        // ]);
        //debug(md5($password));
        $users = DB::select('select * from user where gmail = ?', [$gmail]);
        if(sizeof($users) == 1 ) {
            if ($this->auth->setVerified()) {
                return response()->json([
                    'status' => 'successful'
                ]);
                //return $this->auth->redirect();
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message'=> '帳號或密碼錯誤'
            ]);
            //return redirect('login')->with('login_status', '帳號或密碼錯誤');
        }
    }
}
