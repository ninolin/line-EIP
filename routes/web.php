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

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/', function () {
    return view('login');
})->name('login');

Route::middleware('auth')->get('/whmanage', 'View\whmanage@index')->name('whmanage');
Route::middleware('auth')->get('/userlist', 'View\userlist@index')->name('userlist');
Route::middleware('auth')->get('/formmanage', function () {
    return view('contents.formmanage');
})->name('formmanage');

Route::post('/login', 'Auth\LoginController@login')->name('ccc');//驗證密碼

