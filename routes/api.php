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
            Route::post('register', 'RegisterController@register')->name('Auth.register');
            Route::post('login', 'LoginController@login')->name('Auth.login');
            Route::post('phone/verify/{id}/{token}', 'LoginController@validatePhoneVerifyToken')->name('Auth.validatePhoneVerifyToken');
            Route::post('request-password-reset-token', 'ResetPasswordController@requestPasswordResetToken')->name('Auth.requestPasswordResetToken');
            Route::post('change-password', 'ResetPasswordController@changePassword')->name('Auth.changePassword');
            Route::post('email/verify/{id}/{hash}', 'RegisterController@verify')->name('Auth.validateEmailVerifyToken');
        });

        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::post('logout', 'LoginController@logout')->name('Auth.logout');
            Route::post('logout-other-devices', 'LoginController@logoutOtherDevices')->name('Auth.logoutOtherDevices');
        });
    });

    Route::group([
        'prefix' => 'adverts',
        'namespace' => '\App\Http\Controllers\Api\v1\Adverts'
    ], function () {

        Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'me'], function () {
            Route::get('{category?}', 'MyAdvertController@index')->name('Adverts.me.list');
            Route::post('{category}', 'MyAdvertController@create')->name('Adverts.me.create');
            Route::put('{category}/{advert}', 'MyAdvertController@update')->name('Adverts.me.update');
            Route::put('{category}/{advert}/send-to-moderation', 'MyAdvertController@sendToModeration')->name('Adverts.me.sendToModeration');
            Route::put('{category}/{advert}/close', 'MyAdvertController@close')->name('Adverts.me.close');
            Route::delete('{category}/{advert}', 'MyAdvertController@destroy')->name('Adverts.me.destroy');
        });

        Route::get('user/{user}/{category?}', 'AdvertController@userAdverts')->name('Adverts.user.list');
        Route::get('categories', 'CategoryController@index')->name('Adverts.categories.list');
        Route::get('categories/{category}', 'CategoryController@show')->name('Adverts.categories.show');
        Route::get('show/{advert}', 'AdvertController@show')->name('Adverts.show');
        Route::get('{category?}', 'AdvertController@index')->name('Adverts.list');
    });

    Route::group(['prefix' => 'profiles', 'namespace' => '\App\Http\Controllers\Api\v1\Profiles'], function () {
        Route::group(['prefix' => 'me', 'middleware' => 'auth:sanctum'], function () {
            Route::post('avatar', 'AvatarController@create')->name('Profiles.me.avatar.create');
            Route::delete('avatar', 'AvatarController@destroy')->name('Profiles.me.avatar.delete');
            Route::put('request-сhange-phone', 'PhoneController@requestChangePhone')->name('Profiles.me.requestChangePhone');
            Route::put('verify-phone', 'PhoneController@verifyPhone')->name('Profiles.me.verifyPhone');
            Route::put('toggle-phone-auth', 'PhoneController@togglePhoneAuth')->name('Profiles.me.togglePhoneAuth');
            Route::put('request-сhange-email', 'EmailController@requestChangeEmail')->name('Profiles.me.requestChangeEmail');
            Route::put('verify-email', 'EmailController@verifyEmail')->name('Profiles.me.verifyEmail');
            Route::put('/', 'ProfileController@update')->name('Profiles.me.update');
        });
    });
});
