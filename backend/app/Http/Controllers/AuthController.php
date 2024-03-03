<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){

        //encrypt password before save to database
        $request->merge([
            'password' => bcrypt($request->password)
        ]);

        // create user
        $user = User::create($request->validated());

        $success['token'] = $user->createToken('ord')->plainTextToken;
        $success['user'] = new UserResource($user);

        return response()->json([
            'user' => $success['user'],
            'token' => $success['token']
        ], 201);
    }
    public function login(Request $request) {

        $validator = Validator::make($request->all, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        if(!auth()->attempt($validator->validated())){
            return response()->json([
                'message' => 'Invalid credentials'
            ], 422);
        }

        $user=auth()->user();

        $success['token'] = $user->createToken('ord')->plainTextToken;
        $success['user'] = new UserResource($user);

        return response()->json([
            'user' => $success['user'],
            'token' => $success['token']
        ], 201);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ], 200);
    }
}
