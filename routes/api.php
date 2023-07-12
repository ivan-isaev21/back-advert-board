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
        Route::post('register', 'RegisterController@register')->middleware('guest');
        Route::post('login', 'LoginController@login')->middleware('guest');

        Route::post('logout', 'LoginController@logout')->middleware('auth:sanctum');
        Route::post('logout-other-devices', 'LoginController@logoutOtherDevices')->middleware('auth:sanctum');

        Route::post('phone/verify/{id}/{token}', 'LoginController@validatePhoneVerifyToken');

        Route::post('request-password-reset-token', 'ResetPasswordController@requestPasswordResetToken');
        Route::post('change-password', 'ResetPasswordController@changePassword');

        Route::get('email/verify/{id}/{hash}', 'RegisterController@verifyEmail')->name('verification.verify');
    });
});
