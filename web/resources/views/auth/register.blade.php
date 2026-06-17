<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'EarScope') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-slate-100 via-slate-50 to-teal-50/30 text-slate-800 flex items-center justify-center min-h-screen p-3 overflow-hidden relative">
    <div class="absolute top-10 left-10 w-72 h-72 bg-teal-200/20 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-emerald-200/20 rounded-full blur-3xl animate-float"
        style="animation-delay: 2s;"></div>

    <div class="w-full max-w-3xl relative z-10 animate-fade-in-up">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-xl border border-teal-100/40 overflow-hidden">
            <div
                class="bg-gradient-to-br from-teal-600 via-teal-600 to-emerald-600 px-6 py-5 text-center relative overflow-hidden">
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:16px_16px]">
                </div>
                <div class="flex items-center justify-center mb-3 relative z-10">
                    <div class="p-2.5 bg-white/10 rounded-2xl backdrop-blur-sm border border-white/20 shadow-inner">
                        <x-application-logo class="w-12 h-12 text-white animate-heartbeat" />
                    </div>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight mb-1 relative z-10">
                    {{ config('app.name', 'EarScope') }}</h1>
                <p class="text-teal-100 text-xs font-medium relative z-10">Daftar untuk akses konsultasi kesehatan
                    telinga terintegrasi</p>
            </div>

            <div class="px-6 py-5">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <label for="username"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                                <input id="username" name="username" type="text" value="{{ old('username') }}"
                                    required autofocus
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                                @error('username')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="birth_date"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Tanggal
                                    Lahir</label>
                                <input id="birth_date" name="birth_date" type="date" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                                @error('birth_date')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="address"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Alamat</label>
                                <textarea id="address" name="address" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                                <input id="password" name="password" type="password" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                                @error('password')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="name"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Nama
                                    Lengkap</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}"
                                    required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                                @error('name')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="gender"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Gender</label>
                                <select id="gender" name="gender" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium">
                                    <option value="">-- Pilih --</option>
                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki
                                    </option>
                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan
                                    </option>
                                </select>
                                @error('gender')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                                @error('email')
                                    <p class="text-rose-600 text-xs mt-1.5">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="password_confirmation"
                                    class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Confirm
                                    Password</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 transition-all duration-200 text-sm font-medium" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold rounded-xl transition-all duration-300 shadow-md shadow-teal-500/10 hover:shadow-teal-500/30">
                            Register
                        </button>
                        <div class="flex items-center justify-center gap-1">
                            <span class="text-xs text-slate-400">Sudah punya akun?</span>
                            <a href="{{ url('/') }}"
                                class="text-xs text-center text-teal-600 hover:text-teal-700 font-semibold transition-colors">
                                Login
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-6 py-2.5 bg-slate-50/50 border-t border-slate-100 text-center">
                <p class="text-[10px] text-slate-400 font-medium">
                    © 2026 {{ config('app.name', 'EarScope') }}. Semua hak dilindungi.
                </p>
            </div>
        </div>

        <div class="mt-4 text-center text-xs text-slate-400">
            <p class="flex items-center justify-center gap-1.5 font-medium">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Koneksi terenkripsi & data medis Anda aman
            </p>
        </div>
    </div>
</body>

</html>
