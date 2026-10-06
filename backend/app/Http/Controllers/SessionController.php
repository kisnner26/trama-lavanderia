<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $key = 'login:'.hash('sha256', $request->validated('email').'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'demasiados intentos. vuelve a probar en un minuto.']);
        }
        if (! Auth::attemptWhen($request->safe()->only(['email', 'password']), fn (User $user): bool => $user->memberships()->exists())) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'no pudimos abrir tu sesión. revisa tus credenciales.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
