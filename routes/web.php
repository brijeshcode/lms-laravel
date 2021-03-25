<?php

use Illuminate\Support\Facades\Route;

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


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

	Route::get('admin/payment/gateway/stripe/test', 'App\Http\Controllers\Payments\Gateways\StripeController@test')->name('stripe.test');
	Route::get('admin/payment/gateway/stripe/test/single-pay/{paymentMethodId}', 'App\Http\Controllers\Payments\Gateways\StripeController@makeSinglePayment')->name('stripe.test.single-pay');