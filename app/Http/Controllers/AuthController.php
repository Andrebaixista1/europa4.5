<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $user = env('ADMIN_USER');
        $pass = env('ADMIN_PASSWORD');

        if ($request->input('username') === $user && $request->input('password') === $pass) {
            session(['is_admin' => true]);
            return redirect('/dashboard');
        }

        return back()->withErrors(['message' => 'Credenciais inválidas']);
    }

    public function logout()
    {
        session()->forget('is_admin');
        return redirect('/');
    }
}
