<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EarScope') }}</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-slate-100 via-slate-50 to-teal-50/30 text-slate-800 flex items-center justify-center min-h-screen p-3 overflow-hidden relative">
        <!-- Floating Decorative Blobs -->
        <div class="absolute top-10 left-10 w-72 h-72 bg-teal-200/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-200/20 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>

        <div class="w-full max-w-md relative z-10 animate-fade-in-up">
            <!-- Card Container -->
            <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-teal-100/40 overflow-hidden">
                <!-- Header with Medical Gradient -->
                <div class="bg-gradient-to-br from-teal-600 via-teal-600 to-emerald-600 px-6 py-5 text-center relative overflow-hidden">
                    <!-- Techy grid background overlay -->
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:16px_16px]"></div>
                    
                    <div class="flex items-center justify-center mb-3 relative z-10">
                        <!-- Custom Animated Medical Logo -->
                        <div class="p-2.5 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-inner">
                            <x-application-logo class="w-12 h-12 text-white animate-heartbeat" />
                        </div>
                    </div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight mb-1 relative z-10">{{ config('app.name', 'EarScope') }}</h1>
                    <p class="text-teal-100 text-xs font-medium relative z-10">Platform Konsultasi Kesehatan Telinga Terintegrasi</p>
                </div>

                <!-- Form Content -->
                <div class="px-6 py-5">
                    @auth
                        <!-- Authenticated View -->
                        <div class="text-center py-3">
                            <div class="inline-flex p-3 bg-teal-50 rounded-full text-teal-600 mb-4 animate-bounce">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-slate-500 text-sm">Selamat datang kembali,</p>
                            <p class="text-lg font-bold text-slate-800 mt-1 mb-4">{{ auth()->user()->username }}</p>
                            
                            @php
                                $role = auth()->user()->role;
                                $dashboardUrl = '/dashboard';
                                if ($role === 'doctor') $dashboardUrl = route('doctor.dashboard');
                                elseif ($role === 'patient') $dashboardUrl = route('patient.dashboard');
                                elseif ($role === 'admin') $dashboardUrl = route('admin.dashboard');
                            @endphp
                            
                                    <a href="{{ $dashboardUrl }}" class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-md shadow-teal-500/20 hover:shadow-teal-500/30 transform hover:-translate-y-0.5">
                                Ke Dashboard Utama
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    @else
                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <!-- Username Field -->
                            <div>
                                <label for="username" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Username
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="username" 
                                        name="username" 
                                        value="{{ old('username') }}" 
                                        required 
                                        class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50/50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium"
                                        placeholder="Masukkan username" 
                                    />
                                </div>
                                @error('username')
                                    <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                    Password
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password" 
                                        required 
                                        class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl bg-slate-50/50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium"
                                        placeholder="••••••••" 
                                    />
                                </div>
                                @error('password')
                                    <p class="text-rose-600 text-xs mt-1.5 flex items-center gap-1">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-teal-600 bg-slate-50 focus:ring-2 focus:ring-teal-500/30 focus:ring-offset-0 transition-colors" />
                                    <span class="text-xs text-slate-500 font-medium">Ingat saya di perangkat ini</span>
                                </label>
                            </div>

                            <!-- Login Button -->
                            <button 
                                type="submit" 
                                class="w-full px-4 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-md shadow-teal-500/10 hover:shadow-teal-500/30 transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h7a3 3 0 013 3v1" />
                                </svg>
                                Masuk Sekarang
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="flex items-center gap-3 my-4">
                            <div class="flex-1 border-t border-slate-100"></div>
                            <span class="text-slate-400 text-xs font-semibold uppercase tracking-wider">ATAU</span>
                            <div class="flex-1 border-t border-slate-100"></div>
                        </div>

                        <!-- Footer Links -->
                        <div class="space-y-3 text-center">
                            @if (Route::has('register'))
                                <div>
                                    <p class="text-xs text-slate-400 font-medium mb-3">
                                        Belum memiliki akun konsultasi?
                                    </p>
                                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-2.5 border border-teal-200 text-teal-600 hover:bg-teal-50/50 hover:border-teal-300 rounded-xl text-xs font-semibold transition-all duration-200">
                                        Buat Akun Baru
                                        <svg class="w-3.5 h-3.5 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                    </a>
                                </div>
                            @endif

                            @if (Route::has('password.request'))
                                <div>
                                    <a href="{{ route('password.request') }}" class="text-xs text-teal-600 hover:text-teal-700 font-semibold transition-colors">
                                        Lupa password Anda?
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endauth
                </div>

                <!-- Footer Info -->
                <div class="px-6 py-2.5 bg-slate-50/50 border-t border-slate-100 text-center">
                    <p class="text-[10px] text-slate-400 font-medium">
                        © 2026 {{ config('app.name', 'EarScope') }}. Semua hak dilindungi.
                    </p>
                </div>
            </div>

            <!-- Security Info -->
            <div class="mt-4 text-center text-xs text-slate-400">
                <p class="flex items-center justify-center gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Koneksi terenkripsi & data medis Anda aman
                </p>
            </div>
        </div>
    </body>
</html>
