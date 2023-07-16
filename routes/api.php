<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'v1'], function () {

    Route::group([
        'prefix' => 'auth',
        'namespace' => '\App\Http\Controllers\Api\v1\Auth'
    ], function () {

        Route::group(['middleware' => 'guest'], function () {
            Route::post('register', 'RegisterController@register');
            Route::post('login', 'LoginController@login');
            Route::post('phone/verify/{id}/{token}', 'LoginController@validatePhoneVerifyToken');
            Route::post('request-password-reset-token', 'ResetPasswordController@requestPasswordResetToken');
            Route::post('change-password', 'ResetPasswordController@changePassword');
            Route::post('email/verify/{id}/{hash}', 'RegisterController@verify');
        });

        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::post('logout', 'LoginController@logout');
            Route::post('logout-other-devices', 'LoginController@logoutOtherDevices');
        });
    });

    Route::group([
        'prefix' => 'adverts',
        'namespace' => '\App\Http\Controllers\Api\v1\Adverts'
    ], function () {
        Route::get('categories', 'CategoryController@index');
        Route::get('categories/{category}', 'CategoryController@show');
    });
});
