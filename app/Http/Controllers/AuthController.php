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
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('login', $request->username)->first();

        // Check if user exists and password matches SHA-512 hash (Case Insensitive)
        if ($user && strtoupper($user->senha) === strtoupper(hash('sha512', $request->password))) {
            // Update ultimo_log timestamp
            $user->ultimo_log = now();
            $user->save();
            
            \Illuminate\Support\Facades\Auth::login($user);
            $request->session()->regenerate();
            return redirect('/dashboard');
        }

        return back()->withErrors([
            'username' => 'As credenciais fornecidas estão incorretas.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
