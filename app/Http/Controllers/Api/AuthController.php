<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $user = User::where('email',$request->get('email'))->first();

        if($user){
            return response()->json(['error'=> 'el usuario ya existe'], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($request->get('password'));
        $user = User::create($input);
        $token =  $user->createToken('MyApp')->accessToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token =  $user->createToken('MyApp')->accessToken;
            return response()->json([
                'token' => $token,
                'user' => $user
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json(compact('user'), 200);
    }
}
