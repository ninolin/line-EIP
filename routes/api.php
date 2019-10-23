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

Route::post('applyleave', 'View\LinePersonalOperate\applyleave@store');
Route::get('applyleave/{id}', 'View\LinePersonalOperate\applyleave@show');
Route::get('applyleave/user/{line_id}', 'View\LinePersonalOperate\applyleave@get_user_by_line_id');
Route::post('applyoverwork', 'View\LinePersonalOperate\applyoverwork@store');
Route::get('applyoverwork/{id}', 'View\LinePersonalOperate\applyoverwork@show');
Route::get('validateleave/{id}', 'View\LinePersonalOperate\validateleave@index');
Route::put('validateleave/{id}', 'View\LinePersonalOperate\validateleave@update');
Route::get('validateleave/show_other_leaves/{id}', 'View\LinePersonalOperate\validateleave@show_other_leaves');
Route::put('individuallog/{id}', 'View\LinePersonalOperate\individuallog@cancel');
Route::get('individuallog/leavetype', 'View\LinePersonalOperate\individuallog@get_leavetype');

Route::post('receive', 'View\receive@receive');

Route::get('userlist', 'View\userlist@index');
Route::get('userlist/{id}', 'View\userlist@get_user_data');
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

Route::get('leavelog/process/{id}', 'View\LeaveWorkManageLog\leavelog@list_process_logs');
Route::get('leavelog/changelog/{id}', 'View\WorkManage\leavelog@list_change_logs');
Route::put('leavelog/change_upper_user', 'View\WorkManage\leavelog@change_upper_user');
Route::put('leavelog/change_agent_user', 'View\WorkManage\leavelog@change_agent_user');
Route::put('leavelog/change_leave_date', 'View\WorkManage\leavelog@change_leave_date');
Route::put('leavelog/change_overwork_date', 'View\WorkManage\leavelog@change_overwork_date');
Route::get('leavelog/export', 'View\WorkManage\leavelog@export')->name('exportExcel');
Route::get('leavelog/export_last_month', 'View\WorkManage\leavelog@exportLastMonth')->name('exportLastMonthExcel');
Route::get('test', 'Line\Test@show');

Route::get('workclass', 'View\WorkSetting\workclass@index');
Route::post('workclass', 'View\WorkSetting\workclass@store');
Route::put('workclass/{id}', 'View\WorkSetting\workclass@update');
Route::delete('workclass/{id}', 'View\WorkSetting\workclass@destroy');

Route::get('lineDefaultMsg/{id}', 'View\WorkSetting\LineDefaultMessage@get_one_message');
Route::put('lineDefaultMsg/{id}', 'View\WorkSetting\LineDefaultMessage@update_message');

