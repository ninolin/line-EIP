<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use DB;
use Storage;
use Google_Client;
use Google_Service_Oauth2;
use Google_Service_Oauth2_Resource_Userinfo;

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

    public function glogin()
    {   
        $gclient = new Google_Client();
        $gclient->setAuthConfig(storage_path('credentials.json'));
        $gclient->setAccessType('offline'); // offline access
        $gclient->setIncludeGrantedScopes(true); // incremental auth
        $gclient->addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
        $gclient->setRedirectUri('https://sporzfy.com'); // 寫憑證設定：「已授權的重新導向 URI 」的網址

        $google_login_url = $gclient->createAuthUrl(); // 取得要點擊登入的網址
    }

    public function getGloginData() {
        // 登入後，導回來的網址會有 code 的參數
        if (isset($_GET['code']) && $gclient->authenticate($_GET['code'])) {
            $token = $gclient->getAccessToken(); // 取得 Token
            // $token data: [
            // 'access_token' => string
            // 'expires_in' => int 3600
            // 'scope' => string 'https://www.googleapis.com/auth/userinfo.email openid https://www.googleapis.com/auth/userinfo.profile' (length=102)
            // 'created' => int 1550000000
            // ];
            $gclient->setAccessToken($token); // 設定 Token

            $oauth = new Google_Service_Oauth2($gclient);
            $profile = $oauth->userinfo->get();

            $uid = $profile->id; // Primary key
            log::info($profile); // 自行取需要的內容來使用囉~
        } else {
            // 直接導向登入網址
            header('Location: ' . $google_login_url);
            exit;
        }
    } 
    // public function glogin(Request $request)
    // {   
    //     $gmail = $request->input('gmail');
    //     $token = $request->input('token');
    //     debug($gmail);
    //     // return response()->json([
    //     //     'status' => 'successful'
    //     // ]);
    //     //debug(md5($password));
    //     $users = DB::select('select * from user where gmail = ?', [$gmail]);
    //     if(sizeof($users) == 1 ) {
    //         if ($this->auth->setVerified()) {
    //             return response()->json([
    //                 'status' => 'successful'
    //             ]);
    //             //return $this->auth->redirect();
    //         }
    //     } else {
    //         return response()->json([
    //             'status' => 'error',
    //             'message'=> '帳號或密碼錯誤'
    //         ]);
    //         //return redirect('login')->with('login_status', '帳號或密碼錯誤');
    //     }
    // }
}
