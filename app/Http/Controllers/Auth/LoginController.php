<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Campus;
use App\Support\CampusContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'campuses' => Campus::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        if (!Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'This account has been deactivated. Contact an administrator.',
            ]);
        }

        $user = Auth::user();
        $assigned = CampusContext::assignable($user);
        $submitted = $request->input('campus_id');
        $warning = null;

        if (CampusContext::managesData($user) && $submitted !== null && $submitted !== '' && $submitted !== CampusContext::ALL) {
            $campusId = (int) $submitted;

            if (in_array($campusId, $assigned, true)) {
                $request->session()->put(CampusContext::SESSION_KEY, $campusId);
            } elseif (CampusContext::isBounded($user)) {
                // The login form cannot know who is about to sign in, so a campus
                // outside this account's scope is dropped instead of rejecting the login.
                $request->session()->put(CampusContext::SESSION_KEY, $assigned[0]);
                $warning = 'That campus is not assigned to your account — opened '
                    . Campus::whereKey($assigned[0])->value('name') . ' instead.';
            }
        } elseif (CampusContext::isBounded($user)) {
            if ($assigned !== []) {
                $request->session()->put(CampusContext::SESSION_KEY, $assigned[0]);
            } else {
                $warning = 'Your account is not assigned to a campus yet, so no student, '
                    . 'class or finance data is visible. Contact the all-campus administrator.';
            }
        }

        AuditLog::record('login', 'user', $user->id, $user->name . ' logged in');

        $redirect = redirect()->intended(route('dashboard'));

        return $warning === null ? $redirect : $redirect->with('warning', $warning);
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            AuditLog::record('logout', 'user', Auth::id(), Auth::user()->name . ' logged out');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
