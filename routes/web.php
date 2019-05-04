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
Route::middleware('auth')->get('/titlelist', 'View\titlelist@create')->name('titlelist');
Route::middleware('auth')->get('/leavetypelist', 'View\leavetypelist@create')->name('leavetypelist');
Route::middleware('auth')->get('/overworktypelist', 'View\overworktypelist@create')->name('overworktypelist');
Route::middleware('auth')->get('/leavelog', 'View\leavelog@create')->name('leavelog');
Route::get('/formmanage', function () {return view('contents.formmanage');})->name('formmanage');
Route::post('/login', 'Auth\AuthController@login')->name('doLogin'); //驗證帳密
Route::post('/logout', 'Auth\AuthController@logout')->name('doLogout'); //驗證帳密

