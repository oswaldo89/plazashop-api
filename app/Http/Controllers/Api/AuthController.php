<?php

namespace App\Http\Controllers\Api;

use App\Mail\WelcomeMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Validator;
use App\Http\Controllers\Controller;
use Slack;

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
        $input['confirmed'] = 1;
        $input['firebase_token'] = "";
        $user = User::create($input);
        $token = $user->createToken('MyApp')->accessToken;

        //Mail::to($user['email'])->send(new WelcomeMail($user));
        Slack::send("Usuario nuevo: " . "\n\n" . "*Nombre:* " . $request->get('email') );

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

            $token = $user->createToken('MyApp')->accessToken;

            Slack::send("Usuario ingreso a la app: " . "\n\n" . "*Nombre:* " . $user->email );
            return response()->json(['token' => $token, 'user' => $user], 200);

        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json(compact('user'), 200);
    }

    public function saveFirebaseToken(Request $request)
    {
        $firebase_token = $request->get('firebase_token');
        $user_id = $request->get('user_id');

        $user = User::find($user_id);
        $user->firebase_token = $firebase_token;
        if ($user->update()) {
            return response()->json(['message' => "ok"], 200);
        } else {
            return response()->json(['message' => "error"], 401);
        }
    }
}
