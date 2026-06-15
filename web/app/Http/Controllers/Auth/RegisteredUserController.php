<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Helpers\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use carbon\carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'address' => ['required'],
            'email' => ['required', 'email'],
            'gender' => ['required', 'in:male,female'],
        ]);

        // create user (role = patient)
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'patient',
        ]);

        // hitung umur otomatis
        $age = Carbon::parse($request->birth_date)->age;

        // create patient detail
        Patient::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'birth_date' => $request->birth_date,
            'age' => $age,
            'address' => $request->address,
            'email' => $request->email,
            'gender' => $request->gender,
        ]);

        event(new Registered($user));
        
        // Log user registration activity
        ActivityLogger::logUserRegistered($user, 'patient');

        Auth::login($user);

        return redirect()->route('patient.dashboard');
    }
}
