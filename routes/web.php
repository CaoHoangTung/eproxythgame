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

use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/api/getDices','GameController@getDices');
Route::get('/api/phase','GameController@viewPhase');

Route::get('/machine',function(){
    return view('machine');
});

Route::get('/game','GameController@index')->name('game');
Route::post('/game/bet', 'GameController@bet');

Route::get('/profile','ProfileController@index')->name('profile');

Route::get('/message',function(){
    return view('test',['messages'=>[]]);
});
Route::post('/message','MessageController@store');

Route::get('/test',function(){
    $email="cht@gmail.com";
    $gameId = "123";
    $amount = 1;
});