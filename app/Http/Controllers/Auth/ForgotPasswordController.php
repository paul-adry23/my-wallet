<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
     public function showLinkRequestForm()
    {
        return view('forgot');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validation des champs
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2. Définition d'une clé unique pour le rate limiting (email + IP)
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 3. Protection contre le spam (limite : 5 requêtes en 5 minutes)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans " . ceil($seconds / 60) . " minute(s).",
            ]);
        }

        // 4. Rechercher l'utilisateur par email avec Eloquent
        $user = User::where('email', $request->email)->first();

        // 5. Protection contre l'énumération d'utilisateurs (ne pas indiquer si l'email existe ou pas)
        if (!$user || !$user->hasVerifiedEmail()) {
            // Toujours incrémenter le compteur de tentative, même si l'utilisateur est inconnu
            RateLimiter::hit($throttleKey, 300); // Blocage de 5 min
            return back()->with('status', 'Si un compte est associé à cette adresse email, un lien de réinitialisation a été envoyé.');
        }

        // 6. Vérifier si le compte est actif (si applicable)
        if (isset($user->active) && !$user->active) {
            return back()->withErrors(['email' => 'Ce compte est désactivé.']);
        }

        // 7. Envoyer le lien de réinitialisation
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // 8. Réponse selon le statut
        if ($status === Password::RESET_LINK_SENT) {
            RateLimiter::clear($throttleKey); // Réinitialiser le compteur si succès
            return back()->with('status', __($status));
        }

        // 9. Si l'envoi échoue pour une autre raison (rare)
        RateLimiter::hit($throttleKey, 300); // Incrémente quand même
        return back()->withErrors(['email' => __($status)]);
    }
}
