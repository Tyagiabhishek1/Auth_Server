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

Route::post('login', 'AuthController@login');
Route::post('upload', 'UploadController@upload');
Route::post('register', 'AuthController@register');
Route::group(['middleware' => 'auth:api'], function(){
Route::get('getUser', 'AuthController@getUser');
Route::post('logout', 'AuthController@logout');
Route::post('savedetails', 'AuthController@savedetails');
Route::post('reset-pass', 'AuthController@resetpassword');
});