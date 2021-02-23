<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PassportAuthController;
	use App\Models\Course;

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
/*
Personal access client created successfully.
Client ID: 1
Client secret: Av97tQeLd4YTydl3QetrqNpLtvqogj4EgeNkGsTK
Password grant client created successfully.
Client ID: 2
Client secret: UJaU1jTAIordtg7o4PpJxnYAULeUrRGqQOEYRyj4

*/

/*

"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiN2IzMjQ3NzUxMDcwOTllMWQwNjAwNzA0ZjllMjc4MDUzYTFlMTFkZThhMDg1NjIyY2M4YjVlZjA2NDYxMjY1ZDRiMDQ4ODQzNGFlYTczNjIiLCJpYXQiOiIxNjE0MDgxMjM1Ljg3NzEzMyIsIm5iZiI6IjE2MTQwODEyMzUuODc3MTQxIiwiZXhwIjoiMTY0NTYxNzIzNS44MTk4NzEiLCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.ZmH-L_UrD8v8xjOqxDG1VBxMkNdYRnW6tebjOvf_3LxbdIqcHI_vI5XHefy8ZyDVzSPeiJRRTeipU3-jCYCRbNrEQ6YxOIJ7ywYdN8N3pZdgheKGXqMw3UPBujGbUaBEwy6gXR9SvAFU3MXJzMC1_ngLMUCddQu2fjwWgCYpPcvG9VJ9wV4xplR8Co1fN01MtZMxBj06JCpYxwkyh5OYpCnS1iT45Em1KM6FPbF_XKUTm8UpSFDnWk6SUWe-ujR3QEEnz-WrkD7fI2k8UImLoM6pEAre0qDAeXPIgqlh1A-KqbnHChHDHOVoxy2tmNac60itI4bFdmcZv4e2lKIJAQogmuyMj25bajqEFFzISS3IYwKZqVFkRft8y-hTNIjeTV90nKrAeb7JgQ-mgVlRjyU5FY7ya2B6APo2qwHifW65-W0QwCHV-ANg5RS3fmJaMUrMpCWUNo69nGiSHpcE-QhSZyvGrnm53nsWc81-RIwRUdnMfV24Mczbv9l_eiK6Q7MrONSe5xZIQqlke6Bgj97mw2h2cnWGPG84If5PFPnK4lyewpr_x4y5u7-5vdZJbDVvdd-CL_aSTbn5G1qOsR8HOS4akh-f5afS7ZZoXlvlSv50cG_WqxVbMsL-RJL0mA6ghNts7RzNlap34uYf3x3tFa67SYAk6XZq7mE3CAY"

*/

/*
server keys
Personal access client created successfully.
Client ID: 1
Client secret: JCW5Qa1p9sn1VRWbGJMpRvzyG5Ou5JkQF221rbna
Password grant client created successfully.
Client ID: 2
Client secret: X7l5hDjJVjaxf6nPgTqMvSrLCjQ35Q0B7jvOQX7j

*/
Route::prefix('/v1')->group(function (){
	Route::get('/cources', function() {
	        return Course::get();
	});
	Route::post('register', [PassportAuthController::class, 'register']);
	Route::post('login', [PassportAuthController::class, 'login']);

	Route::post("user-login", "App\Http\Controllers\UserController@userLogin");

	Route::middleware('auth:api')->get('/user', function (Request $request) {
	    return $request->user();
	});

	Route::middleware('auth:api')->group(function () {
	    Route::get('get-user', [PassportAuthController::class, 'userInfo']);
	});

});
