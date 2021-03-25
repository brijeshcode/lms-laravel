<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;


class PassportAuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $validated = \Validator::make($request->all(), [
            'name' => 'required|min:4|max:51|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);


        if ($validated->fails()) {
           return response()->json(['error' =>$validated->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('Laravel8PassportAuth')->accessToken;
        return response()->json(['token' => $token], 200);
    }


    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($data)) {

            $token = auth()->user()->createToken('Laravel8PassportAuth')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function logout()
	{
	    if (Auth::check()) {
	    	Auth::user()->AauthAcessToken()->delete();
	    }
 		return response()->json(['logout' => true], 200);
	}

    public function userInfo()
    {
		$user = auth()->user();
		$user = new UserResource(User::with('role')->findOrFail($user->id));
        return response()->json(['user' => $user], 200);
        // return response()->json(['data' => $user], 200);
		// return  $user;

    }
}
