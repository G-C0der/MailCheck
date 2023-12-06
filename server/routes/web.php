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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'web'
    ]
], function () {
    //Auth::routes();
    //Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Auth\LoginController@login');
    Route::post('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('/', 'App\DesktopController@index');
    Route::get('/home', 'App\DesktopController@index');

    Route::prefix("/request")->namespace("Requests")->name("requests")->group(function () {

        // Get all request emails
        Route::get("/getall", "RequestsController@getAllRequests")->name("index");

        // Forward the quarantine email to logged in user
        Route::post("/forward", "RequestsController@forwardRequest")->name("forward");

        // Release or stash the requested quarantine email
        Route::post("/handle", "RequestsController@handleRequest")->name("handle");
    });
});
