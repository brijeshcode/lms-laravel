<?php

use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CouponsController;
use App\Http\Controllers\API\PassportAuthController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonController;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('/v1')->group(function (){
	Route::post('register', [PassportAuthController::class, 'register']);
	Route::post('login', [PassportAuthController::class, 'login']);
	Route::get('logout', [PassportAuthController::class, 'logout']);


	Route::get('cources', [ CoursesController::class,'getCources'] );
	Route::get('cource/{courseId}', [ CoursesController::class,'getCource'] );

	/*Route::middleware('auth:api')->get('/user', function (Request $request) {
	    return $request->user();
	});*/

	// cart apis
	Route::post('cart/add', [CartController::class, 'add']);
	Route::get('cart/get/{key}', [CartController::class, 'get']);
	Route::post('cart/remove/product/{cartKey}/{productId}', [CartController::class, 'remove']);
	Route::post('cart/clear/{cartKey}', [CartController::class, 'clearCart']);

	// cart apis
	Route::get('coupons', [CouponsController::class, 'get']);
	Route::get('coupon/get/{couponCode}', [CouponsController::class, 'couponDetails']);
	Route::post('coupon/apply/{cartKey}/{couponCode}', [CartController::class, 'applyCoupon']);

	Route::get('lesson/{lessonId}/comments',[LessonController::class, 'getLessonCommentsApi']);

	Route::middleware('auth:api')->group(function () {
	    Route::get('get-user', [PassportAuthController::class, 'userInfo']);
		Route::post('lesson/{lessonId}/comment/add',[LessonController::class, 'addLessonCommentsApi']);
	});



});
