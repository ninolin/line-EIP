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
Route::get('/leavelog', 'View\leavelog@create')->name('leavelog');
//Route::middleware('auth')->get('/whmanage', 'View\whmanage@index')->name('whmanage');
Route::get('/whmanage', 'View\whmanage@index')->name('whmanage');
Route::get('/userlist', 'View\userlist@create')->name('userlist');
Route::get('/titlelist', 'View\titlelist@create')->name('titlelist');
Route::get('/leavetypelist', 'View\leavetypelist@create')->name('leavetypelist');
Route::get('/overworktypelist', 'View\overworktypelist@create')->name('overworktypelist');
Route::get('/formmanage', function () {return view('contents.formmanage');})->name('formmanage');
Route::post('/login', 'Auth\LoginController@login')->name('doLogin');//驗證密碼

