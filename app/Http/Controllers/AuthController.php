<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tunjukkan borang log masuk.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Proses log masuk.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Sila masukkan alamat e-mel.',
            'email.email' => 'Format e-mel tidak sah.',
            'password.required' => 'Sila masukkan kata laluan.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log aktiviti log masuk
            \App\Models\LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => 'Pengguna berjaya log masuk ke dalam sistem.',
            ]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Maklumat log masuk yang diberikan tidak sepadan dengan rekod kami.',
        ])->onlyInput('email');
    }

    /**
     * Log keluar dari sistem.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\LogAktiviti::create([
                'user_id' => Auth::id(),
                'aktiviti' => 'Pengguna berjaya log keluar dari sistem.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
