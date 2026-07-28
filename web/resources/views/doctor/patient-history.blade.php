<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('doctor.patients-profile') }}" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">Riwayat Pemeriksaan</h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">{{ $patient->name ?? '-' }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Patient Info Card --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center shrink-0 shadow-lg shadow-teal-200">
                        <span class="text-white text-2xl font-bold">{{ strtoupper(substr($patient->name ?? 'P', 0, 1)) }}</span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-x-8 gap-y-2 flex-1">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">{{ $patient->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. RM</p>
                            <p class="text-sm font-bold text-indigo-600 mt-0.5">{{ $patient->medical_record_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jenis Kelamin</p>
                            <p class="text-sm font-semibold text-slate-700 mt-0.5 capitalize">{{ $patient->gender === 'male' ? 'Laki-laki' : ($patient->gender === 'female' ? 'Perempuan' : ($patient->gender ?? '-')) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Umur</p>
                            <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $patient->age ?? '-' }} Tahun</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</p>
                            <p class="text-sm text-slate-600 mt-0.5">{{ $patient->user->email ?? '-' }}</p>
                        </div>
                        <div class="md:col-span-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat</p>
                            <p class="text-sm text-slate-600 mt-0.5">{{ $patient->address ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- History Timeline --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800 text-lg">Riwayat Konsultasi</h3>
                    <span class="text-xs font-semibold bg-slate-100 text-slate-500 px-3 py-1 rounded-full">
                        {{ $consultations->count() }} Kunjungan
                    </span>
                </div>

                @if($consultations->isEmpty())
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-400 font-medium">Belum ada riwayat konsultasi</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($consultations as $consultation)
                            @php
                                $statusConfig = [
                                    'pending'   => ['label' => 'Menunggu',    'cls' => 'bg-amber-50 text-amber-700 border-amber-200',   'dot' => 'bg-amber-400'],
                                    'scheduled' => ['label' => 'Dijadwalkan', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-400'],
                                    'cancelled' => ['label' => 'Dibatalkan',  'cls' => 'bg-rose-50 text-rose-700 border-rose-200',     'dot' => 'bg-rose-400'],
                                    'done'      => ['label' => 'Selesai',     'cls' => 'bg-sky-50 text-sky-700 border-sky-200',        'dot' => 'bg-sky-400'],
                                ];
                                $sc = $statusConfig[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'cls' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-400'];
                            @endphp
                            <div class="rounded-2xl border border-slate-100 overflow-hidden hover:shadow-md transition-shadow">
                                {{-- Header Konsultasi --}}
                                <div class="flex items-center justify-between px-5 py-3.5 bg-slate-50 border-b border-slate-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full {{ $sc['dot'] }}"></div>
                                        <span class="text-xs font-bold text-slate-500">{{ $consultation->created_at->format('d M Y') }}</span>
                                        @if($consultation->scheduled_date)
                                            <span class="text-xs text-slate-400">→ Jadwal: <span class="font-semibold text-slate-600">{{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}</span></span>
                                        @endif
                                        @if($consultation->queue_number)
                                            <span class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-2 py-0.5 rounded-full">Antrean #{{ $consultation->queue_number }}</span>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center text-[11px] font-bold border rounded-full px-2.5 py-0.5 {{ $sc['cls'] }}">
                                        {{ $sc['label'] }}
                                    </span>
                                </div>

                                {{-- Body --}}
                                <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Keluhan</p>
                                        <p class="text-sm text-slate-700 leading-relaxed">{{ $consultation->complaint }}</p>
                                    </div>

                                    @if($consultation->notes)
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Catatan Dokter</p>
                                            <p class="text-sm text-slate-700 leading-relaxed">{{ $consultation->notes }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Diagnosis Section --}}
                                @if($consultation->diagnosis)
                                    @php $diag = $consultation->diagnosis; @endphp
                                    <div class="px-5 py-4 bg-teal-50/60 border-t border-teal-100">
                                        <p class="text-[10px] font-bold text-teal-500 uppercase tracking-wider mb-3">Hasil Diagnosis AI Otoskop</p>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mb-1">Diagnosis</p>
                                                <p class="text-sm font-bold text-slate-800">{{ $diag->diagnosis_result ?? $diag->result ?? '-' }}</p>

                                                @if($diag->notes)
                                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-3 mb-1">Catatan Hasil</p>
                                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $diag->notes }}</p>
                                                @endif
                                            </div>

                                            @if($diag->images && $diag->images->count())
                                                <div class="grid grid-cols-2 gap-2">
                                                    @foreach($diag->images->take(4) as $img)
                                                        <div class="rounded-xl overflow-hidden border border-slate-100 bg-white">
                                                            <img src="{{ Storage::url($img->image_path) }}" alt="Otoscope Image" class="w-full h-24 object-cover" />
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
