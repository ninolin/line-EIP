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

Route::get('/login', function () {return view('login');})->name('login');
Route::get('/', function () {return view('login');})->name('login');
// Route::get('/applyLeave', function () {return view('line/applyLeave');})->name('applyLeave');
Route::get('/applyleave', 'View\applyleave@create');
Route::get('/applyoverwork', 'View\applyoverwork@create');
Route::get('/validateleave', 'View\validateleave@create');
Route::get('/individuallog', 'View\individuallog@create');

//Route::middleware('auth')->get('/whmanage', 'View\whmanage@index')->name('whmanage');
//Route::get('/whmanage', 'View\whmanage@index')->name('whmanage');
Route::middleware('auth')->get('/userlist', 'View\userlist@create')->name('userlist');
Route::middleware('auth')->post('/userlist', 'View\userlist@create')->name('userlist');
Route::middleware('auth')->get('/titlelist', 'View\titlelist@create')->name('titlelist');
Route::middleware('auth')->get('/leavetypelist', 'View\leavetypelist@create')->name('leavetypelist');
Route::middleware('auth')->get('/overworktypelist', 'View\overworktypelist@create')->name('overworktypelist');
Route::middleware('auth')->get('/leavelog', 'View\leavelog@create')->name('leavelog');
Route::middleware('auth')->get('/messagelog', 'View\messagelog@create')->name('messagelog');
Route::middleware('auth')->post('/messagelog', 'View\messagelog@create')->name('messagelog');
Route::middleware('auth')->get('/work/setting/class', 'View\WorkSetting\workclass@create')->name('ws_class');
Route::middleware('auth')->get('/work/setting/title', 'View\WorkSetting\title@create')->name('ws_title');
Route::middleware('auth')->get('/work/setting/leavetype', 'View\WorkSetting\leavetype@create')->name('ws_leavetype');
Route::middleware('auth')->get('/work/setting/overworktype', 'View\WorkSetting\overworktype@create')->name('ws_overworktype');
Route::middleware('auth')->get('/leavelog/dashboard', 'View\LeaveLog\dashboard@dashboard')->name('ws_title');
Route::middleware('auth')->get('/leavelog/last', 'View\LeaveLog\last@last')->name('ws_leavetype');
Route::middleware('auth')->get('/leavelog/individual', 'View\LeaveLog\individual@individual')->name('ws_overworktype');
Route::get('/calendar', function () {return view('contents.calendar');})->name('calendar');
Route::get('/formmanage', function () {return view('contents.formmanage');})->name('formmanage');

Route::post('/login', 'Auth\AuthController@login')->name('doLogin');    //一般登入
Route::post('/logout', 'Auth\AuthController@logout')->name('doLogout'); //登出
Route::post('/glogin', 'Auth\AuthController@glogin')->name('doGLogin'); //google登入
Route::get('/glogin', 'Auth\AuthController@glogin')->name('getGLoginData'); //google登入
