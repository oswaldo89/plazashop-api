<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function show($confirmation_code)
    {
        $response['status'] = null;
        $response['message'] = null;
        $response['user'] = null;
        $user = User::where("confirmation_code",$confirmation_code)->first();
        if (!$user)
        {
            $response['message'] = "Lo sentimos pero no se pudo validar su cuenta.";
            return view('pages.confirmation_code', compact('response'));
        }else{
            $user->confirmed = 1;
            $user->confirmation_code = null;
            $user->save();
            $response['status'] = true;
            $response['user'] = $user->ceoName;
            $response['message'] = "Su cuenta se ha validado correctamente, ahora puede iniciar sesión.";
            return view('pages.confirmation_code', compact('response'));
        }
    }
}
