<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {return $request->user();});
Route::get('userlist', 'View\userlist@index');
Route::put('userlist/{id}', 'View\userlist@update');
Route::put('userlist/bindlineid/{id}', 'View\userlist@bindlineid');
Route::put('userlist/unbindlineid/{id}', 'View\userlist@unbindlineid');
Route::get('userlist/checklineid/{id}', 'View\userlist@checklineid');
Route::get('userlist/annualleave/{NO}', 'View\userlist@get_annualleave');
Route::put('userlist/annualleave/{NO}', 'View\userlist@update_annualleave');
Route::get('userlist/cal_laborannualleave/{onboard_date}', 'View\userlist@cal_laborannualleave');

Route::get('title', 'View\WorkSetting\title@index');
Route::post('title', 'View\WorkSetting\title@store');
Route::put('title/{id}', 'View\WorkSetting\title@update');
Route::delete('title/{id}', 'View\WorkSetting\title@destroy');

Route::post('leavetype', 'View\WorkSetting\leavetype@store');
Route::put('leavetype/{id}', 'View\WorkSetting\leavetype@update');
Route::delete('leavetype/{id}', 'View\WorkSetting\leavetype@destroy');

Route::post('overworktype', 'View\WorkSetting\overworktype@store');
Route::put('overworktype/{id}', 'View\WorkSetting\overworktype@update');
Route::delete('overworktype/{id}', 'View\WorkSetting\overworktype@destroy');

Route::post('applyleave', 'View\applyleave@store');
Route::get('applyleave/{id}', 'View\applyleave@show');

Route::post('applyoverwork', 'View\applyoverwork@store');
Route::get('applyoverwork/{id}', 'View\applyoverwork@show');

Route::post('receive', 'Line\Receive@receive');
Route::get('validateleave/{id}', 'View\validateleave@index');
Route::put('validateleave/{id}', 'View\validateleave@update');
Route::get('validateleave/show_other_leaves/{id}', 'View\validateleave@show_other_leaves');

Route::get('individuallog/{id}', 'View\individuallog@index');
Route::put('individuallog/{id}', 'View\individuallog@cancel');

Route::get('leavelog/{id}', 'View\LeaveLog\leavelog@list_logs');
Route::put('leavelog/change_upper_user', 'View\LeaveLog\leavelog@change_upper_user');
Route::put('leavelog/change_agent_user', 'View\LeaveLog\leavelog@change_agent_user');
Route::put('leavelog/change_date', 'View\LeaveLog\leavelog@change_date');
Route::get('test', 'Line\Test@show');

Route::get('workclass', 'View\WorkSetting\workclass@index');
Route::post('workclass', 'View\WorkSetting\workclass@store');
Route::put('workclass/{id}', 'View\WorkSetting\workclass@update');
Route::delete('workclass/{id}', 'View\WorkSetting\workclass@destroy');
//Route::post('/glogin', 'Auth\AuthController@glogin')->name('doGLogin'); //google登入
