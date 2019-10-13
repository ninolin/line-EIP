<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', function () {return view('login');})->name('login');   //login page
Route::get('/', function () {return view('login');})->name('login');        //login page
Route::post('/login', 'Auth\AuthController@login')->name('doLogin');        //一般登入 
Route::post('/logout', 'Auth\AuthController@logout')->name('doLogout');     //登出
Route::post('/glogin', 'Auth\AuthController@glogin')->name('doGLogin');     //google登入
Route::get('/glogin', 'Auth\AuthController@glogin')->name('getGLoginData'); //登入後導回來取得google user profile

Route::get('/applyleave', 'View\LinePersonalOperate\applyleave@create');
Route::get('/applyoverwork', 'View\LinePersonalOperate\applyoverwork@create');
Route::get('/leavetype', function () {return view('contents.LinePersonalOperate.leavetype');});
Route::get('/individuallog/leavetype/{type_name}/{line_id}', 'View\LinePersonalOperate\individuallog@get_individual_log');
Route::get('/validatetype', function () {return view('contents.LinePersonalOperate.validatetype');});
Route::get('/validateleave/{type_name}/{line_id}', 'View\LinePersonalOperate\validateleave@index');

Route::middleware('auth')->get('/userlist', 'View\userlist@create')->name('userlist');
Route::middleware('auth')->post('/userlist', 'View\userlist@create')->name('userlist');
Route::middleware('auth')->get('/titlelist', 'View\titlelist@create')->name('titlelist');
Route::middleware('auth')->get('/leavetypelist', 'View\leavetypelist@create')->name('leavetypelist');
Route::middleware('auth')->get('/overworktypelist', 'View\overworktypelist@create')->name('overworktypelist');
Route::middleware('auth')->get('/messagelog', 'View\messagelog@create')->name('messagelog');
Route::middleware('auth')->post('/messagelog', 'View\messagelog@create')->name('messagelog');
Route::middleware('auth')->get('/work/setting/class', 'View\WorkSetting\workclass@create')->name('ws_class');
Route::middleware('auth')->get('/work/setting/title', 'View\WorkSetting\title@create')->name('ws_title');
Route::middleware('auth')->get('/work/setting/leavetype', 'View\WorkSetting\leavetype@create')->name('ws_leavetype');
Route::middleware('auth')->get('/work/setting/overworktype', 'View\WorkSetting\overworktype@create')->name('ws_overworktype');
Route::middleware('auth')->get('/work/setting/linedefaultmsg', 'View\WorkSetting\LineDefaultMessage@show_page')->name('ws_linedefaultmsg');
Route::middleware('auth')->get('/leavelog/last', 'View\LeaveLog\leavelog@show_last')->name('ll_last');
Route::middleware('auth')->get('/leavelog/individual', 'View\LeaveLog\leavelog@show_individual')->name('ll_individual');
Route::middleware('auth')->get('/webpo/applyleave', 'View\WebPersonalOperate\applyleave@show_view')->name('webpo_applyleave');
Route::middleware('auth')->get('/webpo/applyoverwork', 'View\WebPersonalOperate\applyoverwork@show_view')->name('webpo_applyoverwork');
Route::middleware('auth')->get('/webpo/validate', 'View\WebPersonalOperate\validate@show_view')->name('webpo_validate');
Route::middleware('auth')->get('/webpo/individual', 'View\WebPersonalOperate\individuallog@show_view')->name('webpo_individual');

Route::get('/calendar', function () {return view('contents.calendar');})->name('calendar');
Route::get('/formmanage', function () {return view('contents.formmanage');})->name('formmanage');


