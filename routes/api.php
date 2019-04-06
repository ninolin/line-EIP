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
Route::middleware('auth:api')->get('userlist', 'View\userlist@index');
Route::middleware('auth:api')->put('userlist/{id}', 'View\userlist@update');
Route::middleware('auth:api')->get('titlelist', 'View\titlelist@index');
Route::middleware('auth:api')->post('titlelist', 'View\titlelist@store');
Route::middleware('auth:api')->put('titlelist/{id}', 'View\titlelist@update');
Route::middleware('auth:api')->delete('titlelist/{id}', 'View\titlelist@destroy');
Route::post('receive', 'Line\Receive@index');