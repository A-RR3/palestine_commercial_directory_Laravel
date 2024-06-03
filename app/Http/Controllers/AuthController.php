<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'u_phone' => 'required|exists:users,u_phone|string|between:9,50',
                    'password' => 'required|string|min:2|max:100'
                ]
            );

            if ($validateUser->fails()) {
          
                return response()->json([
                    'status' => false,
                    'message' => __('auth.invalid_credentials'),
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if (!Auth::attempt($validateUser->validate())) {
                return response()->json([
                    'status' => false,
                    'message' => __('auth.incorrect_password'),
                ], 403);
            }

            $token = $request->user()->createToken("API-TOKEN")->plainTextToken;
            // return $request->user()->tokens();

            return response()->json([
                'status' => true,
                'message' => __('auth.user_logged_in_successfully'),
                'user' => auth()->user(),
                'token' => $token
            ], 200);

        } catch (\Throwable $th) {
           
            return response()->json([
                'status' => false,
                'message' => [
                    __('auth.internal_server_error'),
                    $th->getMessage()
                ]
            ], 500);
        }
    }

   
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout successful']);
        } else {
            return response()->json(['message' => 'No user authenticated'], 401);
        }
    }

}
