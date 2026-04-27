<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak valid.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showClaimForm(): View
    {
        return view('auth.register-claim');
    }

    /**
     * @throws ValidationException
     */
    public function checkNisn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nisn' => ['required', 'digits_between:8,20'],
        ]);

        $siswa = Siswa::query()
            ->with('user')
            ->where('nisn', $validated['nisn'])
            ->first();

        if (! $siswa || ! $siswa->user) {
            throw ValidationException::withMessages([
                'nisn' => 'NISN tidak ditemukan pada data siswa.',
            ]);
        }

        if (! Str::endsWith($siswa->user->email, '@claim.smkn7.local')) {
            throw ValidationException::withMessages([
                'nisn' => 'Akun siswa ini sudah pernah di-claim. Silakan login.',
            ]);
        }

        return redirect()
            ->route('register')
            ->with('claim_nisn', $siswa->nisn)
            ->with('claim_name', $siswa->user->name);
    }

    /**
     * @throws ValidationException
     */
    public function registerClaim(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nisn' => ['required', 'digits_between:8,20'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $siswa = Siswa::query()
            ->with('user')
            ->where('nisn', $validated['nisn'])
            ->first();

        if (! $siswa || ! $siswa->user) {
            throw ValidationException::withMessages([
                'nisn' => 'NISN tidak ditemukan pada data siswa.',
            ]);
        }

        $user = $siswa->user;

        if (! Str::endsWith($user->email, '@claim.smkn7.local')) {
            throw ValidationException::withMessages([
                'nisn' => 'Akun siswa ini sudah pernah di-claim. Silakan login.',
            ]);
        }

        $user->forceFill([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
