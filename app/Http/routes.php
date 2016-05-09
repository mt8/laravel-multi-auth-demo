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


Route::auth();
Route::get('/', 'HomeController@index');
Route::get('/home', 'HomeController@index');

Route::get('/admin/login','AdminAuthController@showLoginForm');
Route::post('/admin/login','AdminAuthController@login');
Route::get('/admin','AdminHomeController@index');
Route::get('/admin/home','AdminHomeController@index');
Route::get('/admin/logout','AdminAuthController@logout');
