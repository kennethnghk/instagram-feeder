<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// tools (eg. import media)
Route::group(['prefix' => 'tool'], function () {
   	Route::controller('media', 'Tool\MediaController');
});

// ajax apis 
Route::group(['prefix' => 'api'], function () {

   	Route::get('feed/media/{mode?}/{pagesize?}/{offset?}', 'Api\FeedController@media');
   	Route::controller('user', 'Api\UserController');  
   	
}); 

// index
Route::get('/', function () {
    return view('index');
});

// testing 
Route::get('/get_token', function () {
    return csrf_token();
});

