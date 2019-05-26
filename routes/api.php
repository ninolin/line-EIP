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

Route::get('titlelist', 'View\titlelist@index');
Route::post('titlelist', 'View\titlelist@store');
Route::put('titlelist/{id}', 'View\titlelist@update');
Route::delete('titlelist/{id}', 'View\titlelist@destroy');

Route::post('leavetypelist', 'View\leavetypelist@store');
Route::put('leavetypelist/{id}', 'View\leavetypelist@update');
Route::delete('leavetypelist/{id}', 'View\leavetypelist@destroy');

Route::post('overworktypelist', 'View\overworktypelist@store');
Route::put('overworktypelist/{id}', 'View\overworktypelist@update');
Route::delete('overworktypelist/{id}', 'View\overworktypelist@destroy');

Route::post('applyleave', 'View\applyleave@store');
Route::get('applyleave/{id}', 'View\applyleave@show');

Route::post('applyoverwork', 'View\applyoverwork@store');
Route::get('applyoverwork/{id}', 'View\applyoverwork@show');

Route::post('receive', 'Line\Receive@receive');
Route::get('validateleave/{id}', 'View\validateleave@index');
Route::post('validateleave/{id}', 'View\validateleave@update');

Route::get('individuallog/{id}', 'View\individuallog@index');

Route::get('leavelog/{id}', 'View\leavelog@index');
