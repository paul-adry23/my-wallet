<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{
    // Affiche la page du login
   

    public function showLoginForm() {
        return view('login');
    }

    //Récupérer le login depuis le formulaire
    public function login(Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        // Génère une clé unique par combinaison d'email et IP
    $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

    // Vérifie si la limite est atteinte
    if (RateLimiter::tooManyAttempts($throttleKey, 7)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        throw ValidationException::withMessages([
            'email' => "Trop de tentatives. Réessayez dans " . ceil($seconds / 60) . " minutes.",
        ]);
    }

    $remember = $request->has('remember');

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
    ], $remember)) {
        RateLimiter::clear($throttleKey); // Reset du compteur si la connexion réussit
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    // Incrémente le nombre d’échecs
    RateLimiter::hit($throttleKey, 900); // 900 secondes = 15 minutes

    return back()->withErrors([
        'email' => 'Mauvais identifiants.',
    ])->withInput();
    }

    //Se  déconnecter
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

