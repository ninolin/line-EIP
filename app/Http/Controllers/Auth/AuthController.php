<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use DB;
use Storage;
use Google_Client;
use Google_Service_Oauth2;
use Log;

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
        deubg($account);
        $users = DB::select('select * from user where username = ? and password = ?', [$account, md5($password)]);
        deubg(sizeof($users));
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
        $gclient->setAuthConfig(storage_path('app/credentials.json'));
        $gclient->setAccessType('offline'); // offline access
        $gclient->setIncludeGrantedScopes(true); // incremental auth
        $gclient->addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
        $gclient->setRedirectUri('https://sporzfy.com/glogin'); // 寫憑證設定：「已授權的重新導向 URI 」的網址
        $google_login_url = $gclient->createAuthUrl(); // 取得要點擊登入的網址
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
            //$profile data: [
            //    [email] => a7872278722@gmail.com 
            //    [familyName] => 林 
            //    [gender] => 
            //    [givenName] => 佳誼 
            //    [hd] => 
            //    [id] => 113226465993829056826 
            //    [link] => 
            //    [locale] => zh-TW 
            //    [name] => 林佳誼 
            //    [picture] => https://lh6.googleusercontent.com/-UhiLwamxGBA/AAAAAAAAAAI/AAAAAAAAANc/Qzyb3O94tsA/photo.jpg [verifiedEmail] => 1 [modelData:protected] => Array ( [verified_email] => 1 [given_name] => 佳誼 [family_name] => 林 ) [processed:protected] => Array ( ) )
            //]
            $users = DB::select('select * from user where gmail = ?', [$profile->email]);
            if(sizeof($users) == 1 ) {
                if ($this->auth->setVerified()) {
                    return $this->auth->redirect();
                }
            } else {
                return redirect('login')->with('login_status', '該用戶不存在');
            }
        } else {
            // 直接導向登入網址
            header('Location: ' . $google_login_url);
            exit;
        }
    }
}
