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
Route::get('/train/login','Train\TrainController@login');
Route::post('/train/content','Train\TrainController@content')->middleware('train1.login');
//pc
Route::get('/train/pc','Train\PcAddIosController@pc');
//android
Route::get('/train/android','Train\PcAddIosController@android');
//ios
Route::get('/train/ios','Train\PcAddIosController@ios');
//type处理
Route::post('/train/typelogin','Train\TrainController@typelogin');
