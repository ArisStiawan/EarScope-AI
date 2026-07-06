<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight">{{ __('Dashboard Pasien') }}</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Kelola janji temu dan hasil pemeriksaan medis telinga Anda</p>
                </div>
            </div>
            <div>
                @if (!$activeConsultation)
                <a href="{{ route('patient.create-consultation') }}"
                    class="relative inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-all duration-300 shadow-md shadow-teal-500/20 hover:shadow-teal-500/35 transform hover:-translate-y-0.5 active:translate-y-0 animate-pulse-ring">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Konsultasi Baru
                </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if (session('success'))
                <div
                    class="mb-5 flex items-center gap-3 bg-green-50 border border-green-300 text-green-800 px-5 py-3 rounded-lg">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div
                    class="mb-5 flex items-center gap-3 bg-red-50 border border-red-300 text-red-800 px-5 py-3 rounded-lg">
                    <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <div class="mb-6 bg-white p-6 rounded-lg shadow border border-gray-100 flex items-center gap-4">
                <div class="h-16 w-16 rounded-full bg-gradient-to-r from-teal-500 to-emerald-500 flex items-center justify-center shrink-0 shadow-sm text-white text-2xl font-bold">
                    {{ strtoupper(substr($patient->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ $patient->name }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            No. RM: {{ $patient->medical_record_number ?? '-' }}
                        </span>
                        <span class="text-sm text-gray-500">| {{ $patient->age }} Tahun | {{ ucfirst($patient->gender) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Permintaan Konsultasi</h3>
                    <p class="mt-3 text-2xl font-bold">{{ $totalRequests }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Konsultasi Selesai</h3>
                    <p class="mt-3 text-2xl font-bold">{{ $totalDone }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Jadwal Konsultasi Terdekat</h3>
                    <p class="text-2xl font-bold">
                        @if ($nextScheduled)
                            {{ \Carbon\Carbon::parse($nextScheduled->scheduled_date)->format('d M Y') }}
                        @else
                            -
                        @endif
                    </p>
                    @if ($nextScheduled)
                        <p class="text-sm text-indigo-600 font-bold mt-1">Antrean: {{ $nextScheduled->queue_number ?? '-' }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg border sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Header tabel --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ __('Upcoming Consultations') }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Jadwal konsultasi yang akan datang</p>
                        </div>
                        <!-- <a href="{{ route('patient.create-consultation') }}"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Request Consultation
                        </a> -->
                    </div>

                    @if (!$activeConsultation)
                        {{-- Empty state --}}
                        <div class="text-center py-14">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-3 text-sm text-gray-500">Tidak ada konsultasi yang sedang berjalan.</p>
                            <a href="{{ route('patient.create-consultation') }}"
                                class="mt-4 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                                Ajukan Konsultasi Sekarang
                            </a>
                        </div>
                    @else
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Doctor Information -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Dokter</h4>
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                            <span class="text-indigo-700 text-lg font-bold">
                                                {{ strtoupper(substr($activeConsultation->doctor->name ?? 'D', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-lg">dr. {{ $activeConsultation->doctor->name ?? '-' }}</p>
                                            <p class="text-sm text-gray-500">{{ $activeConsultation->doctor->user->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status Information -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-4 border-b pb-2">Status Konsultasi</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</p>
                                            @php
                                                $statusMap = [
                                                    'pending' => ['label' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                    'scheduled' => ['label' => 'Scheduled', 'class' => 'bg-green-100 text-green-800'],
                                                ];
                                                $s = $statusMap[$activeConsultation->status] ?? ['label' => ucfirst($activeConsultation->status), 'class' => 'bg-gray-100 text-gray-800'];
                                            @endphp
                                            <span class="mt-1 px-2.5 py-1 inline-flex text-xs font-semibold rounded-full {{ $s['class'] }}">
                                                {{ $s['label'] }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pengajuan</p>
                                            <p class="mt-1 text-sm text-gray-900">{{ $activeConsultation->created_at->format('d M Y') }}</p>
                                        </div>
                                        @if ($activeConsultation->scheduled_date)
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Jadwal</p>
                                                <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($activeConsultation->scheduled_date)->format('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Antrean</p>
                                                <p class="mt-1 text-sm text-gray-900 font-bold text-indigo-600">{{ $activeConsultation->queue_number ?? '-' }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-2 border-b pb-2">Detail Keluhan</h4>
                                <div class="bg-white p-4 rounded-lg border border-gray-200 text-gray-700 text-sm">
                                    {{ $activeConsultation->complaint }}
                                </div>
                            </div>

                            @if ($activeConsultation->notes)
                                <div class="mt-4">
                                    <div class="rounded-lg bg-teal-50 border border-teal-100 p-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <h4 class="text-xs font-bold text-teal-700 uppercase tracking-wider">Catatan Dokter</h4>
                                        </div>
                                        <p class="text-sm text-teal-900 leading-relaxed">{{ $activeConsultation->notes }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-6 flex justify-end">
                                <button type="button"
                                    onclick="cancelActiveConsultation('{{ $activeConsultation->id }}')"
                                    class="inline-flex items-center gap-2 bg-white border border-red-300 text-red-700 hover:bg-red-50 font-medium py-2 px-5 rounded-lg text-sm transition shadow-sm">
                                    Batalkan Konsultasi
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <script>
        function cancelActiveConsultation(consultationId) {
            if (!confirm('Are you sure you want to cancel this consultation?')) return;

            $.ajax({
                url: '/patient/consultation/' + consultationId + '/cancel',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        alert('Consultation cancelled successfully.');
                        location.reload();
                    } else {
                        alert(response.message || 'Unable to cancel consultation.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert(response?.message || 'Failed to cancel consultation.');
                }
            });
        }
    </script>
</x-app-layout>
