<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('patient.dashboard') }}" class="p-2 hover:bg-slate-100 rounded-xl transition text-slate-500 hover:text-slate-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ __('Ajukan Konsultasi') }}
                </h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">Ajukan permintaan diagnosis kesehatan telinga kepada dokter ahli</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert sukses --}}
            @if(session('success'))
                <div class="mb-5 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm">
                    <div class="p-1 bg-emerald-500 text-white rounded-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden border border-slate-100 shadow-lg rounded-2xl">
                <div class="p-8">

                    {{-- Form Header --}}
                    <div class="mb-8">
                        <div class="flex items-center gap-3.5 mb-2">
                            <div class="h-11 w-11 rounded-2xl bg-teal-50 flex items-center justify-center text-teal-600 shadow-inner">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Form Diagnosis Telinga</h3>
                                <p class="text-xs text-slate-400 font-medium">Lengkapi detail di bawah untuk konsultasi jarak jauh</p>
                            </div>
                        </div>
                    </div>

                    {{-- Form --}}
                    <form method="POST" action="{{ route('patient.store-consultation') }}" class="space-y-6">
                        @csrf

                        {{-- Pilih Dokter --}}
                        <div>
                            <label for="doctor_id" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Pilih Dokter <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </span>
                                <select id="doctor_id" name="doctor_id"
                                    class="w-full pl-11 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50/50 text-slate-800 text-sm font-medium
                                           focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20
                                           appearance-none cursor-pointer transition-all duration-200
                                           @error('doctor_id') border-rose-300 @enderror">
                                    <option value="" disabled selected>-- Pilih Dokter --</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                            {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            dr. {{ $doctor->name }} — {{ $doctor->specialization ?? 'Umum' }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Custom arrow icon --}}
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('doctor_id')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Keluhan --}}
                        <div>
                            <label for="complaint" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">
                                Keluhan Gejala <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="complaint" name="complaint" rows="6"
                                placeholder="Jelaskan secara rinci keluhan atau gejala yang Anda rasakan pada bagian telinga (misal: rasa nyeri berdenyut, tersumbat, atau keluar cairan)..."
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-800 text-sm font-medium bg-slate-50/50
                                       focus:outline-none focus:bg-white focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20
                                       resize-none transition-all duration-200 @error('complaint') border-rose-300 @enderror">{{ old('complaint') }}</textarea>
                            @error('complaint')
                                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-xs text-slate-400 font-medium">Petunjuk: Informasi keluhan yang lengkap membantu dokter memberikan analisis awal yang lebih akurat.</p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-100">
                            <a href="{{ route('patient.dashboard') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold text-slate-600 uppercase tracking-wider
                                       border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-800 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Batal
                            </a>
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700
                                       text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition shadow-md shadow-teal-500/15 transform hover:-translate-y-0.5 active:translate-y-0">
                                <svg class="w-4 h-4 animate-heartbeat" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Kirim Konsultasi
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
