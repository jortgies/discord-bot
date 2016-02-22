<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/', function () {
        return view('home');
    });
    Route::get('/upload', function(){
        return view('upload');
    });
    Route::get('/list', 'FileController@listFiles');
    Route::post('upload', 'FileController@upload');
});
