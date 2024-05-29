<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:3|max:100',
                    'phone' => 'required|string|min:3|max:50',
                    'password' => 'required|string|min:2|max:100|confirmed',
                    'role' => 'required|integer'
                ]

            );

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Validation Failed',
                        'errors' => $validator->errors()
                    ],
                    401
                );
            }
            $user = User::create(
                [
                    'u_name' => $request->name,
                    'u_email' => $request->email,
                    'password' => encrypt($request->password),
                    'u_role_id' => $request->name

                ]
            );

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Registration Successfull',
                    'data' => $user,
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ],
                200
            );
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'phone' => 'required|exists:users,phonestring|between:3,50',
                    'password' => 'required|string|min:2|max:100'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            //  $credentials = $request->only('email', 'password');
            $credentials = $validateUser->validated();


            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'invalid credentials',
                ], 403);
            }

            //check email
            // $user = User::where('email', $request->email)->first();

            $token = $request->user()->createToken("API-TOKEN")->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'user' => auth()->user(),
                'token' => $token
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // return $request->user();
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout successful']);
        } else {
            return response()->json(['message' => 'No user authenticated'], 401);
        }
    }

    // update user
    public function update(Request $request)
    {

        // return response($request);
        $validator = Validator::make($request->all(), [
            'name' => 'string|min:3|max:100',
            'phone' => 'string|min:3|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()->first()
            ], 401);
        }

        $user = User::find(auth()->user()->id);

        $user->update([
            'u_name' => $request->input('name'),
            'u_phone' => $request->input('phone'),
        ]);

        return response([
            'message' => 'User updated.',
            'user' => $user,
        ], 200);
    }
}
