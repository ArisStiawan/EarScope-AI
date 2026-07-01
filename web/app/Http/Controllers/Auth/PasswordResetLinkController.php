<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Step 1: Validate username + email (no password field)
        if (!$request->filled('password')) {
            $request->validate([
                'username' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email'],
            ]);

            // Find user by username
            $user = User::where('username', $request->username)->first();

            if (! $user) {
                return back()->withInput($request->only('username', 'email'))
                    ->withErrors(['username' => __('Username tidak ditemukan.')]);
            }

            // Check if user's email matches
            if ($user->email !== $request->email) {
                return back()->withInput($request->only('username', 'email'))
                    ->withErrors(['email' => __('Username dan email tidak cocok.')]);
            }

            // Generate password reset token
            $token = Str::random(64);
            
            // Delete any existing tokens for this email
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            
            // Create new password reset token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // Redirect back with session flag to show password fields
            return back()
                ->with('show_password_form', true)
                ->with('reset_token', $token)
                ->withInput($request->only('username', 'email'));
        }

        // Step 2: Validate and update password
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user) {
            return back()->withInput($request->only('username', 'email'))
                ->withErrors(['username' => __('Username tidak ditemukan.')]);
        }

        if ($user->email !== $request->email) {
            return back()->withInput($request->only('username', 'email'))
                ->withErrors(['email' => __('Email tidak sesuai.')]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('status', __('Password berhasil diperbarui. Silakan login dengan password baru Anda.'));
    }
}
