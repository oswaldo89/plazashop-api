<?php

namespace App\Http\Controllers\Api;

use App\Mail\WelcomeMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $user = User::where('email', $request->get('email'))->first();

        if ($user) {
            return response()->json(['error' => 'el usuario ya existe'], 422);
        }

        $input = $request->all();
        $input['confirmation_code'] = str_random(30);
        $input['password'] = bcrypt($request->get('password'));
        $user = User::create($input);
        $token = $user->createToken('MyApp')->accessToken;

        //Mail::to($user['email'])->send(new WelcomeMail($user));

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ($user->confirmed == 1) {
                $token = $user->createToken('MyApp')->accessToken;
                return response()->json(['token' => $token, 'user' => $user], 200);
            } else {
                return response()->json(['error' => 'No ha verificado su correo electronico.'], 401);
            }

        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json(compact('user'), 200);
    }
}
