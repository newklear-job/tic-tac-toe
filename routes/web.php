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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/message', 'StepController@post');

Route::get('/getUserId', function(){
    return Auth::id();
});

Route::post('/chatMessage', 'LobbyController@chatMessage');
Route::post('/userMessage', 'LobbyController@userMessage');