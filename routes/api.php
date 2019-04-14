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
Route::get('titlelist', 'View\titlelist@index');
Route::post('titlelist', 'View\titlelist@store');
Route::put('titlelist/{id}', 'View\titlelist@update');
Route::delete('titlelist/{id}', 'View\titlelist@destroy');
Route::post('leavetypelist', 'View\leavetypelist@store');
Route::put('leavetypelist/{id}', 'View\leavetypelist@update');
Route::delete('leavetypelist/{id}', 'View\leavetypelist@destroy');
Route::post('applyleave', 'View\applyleave@store');
Route::post('receive', 'Line\Receive@store');
Route::get('validateleave/{id}', 'View\validateleave@index');
Route::post('validateleave/{id}', 'View\validateleave@edit');
Route::get('applyleave/{id}', 'View\applyleave@show');