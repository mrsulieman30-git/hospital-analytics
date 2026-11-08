<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLoggerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditLoggerService::log('LOGIN', 'User logged in', 'User', Auth::id());

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * 1-Click Fast Switch for testing different roles quickly.
     */
    public function quickSwitch(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            AuditLoggerService::log('LOGIN', "Quick-switched session to {$user->name}", 'User', $user->id);

            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$user->visible_role_badge})");
        }

        return redirect()->route('login')->with('error', 'Account not found.');
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLoggerService::log('LOGOUT', 'User logged out', 'User', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
