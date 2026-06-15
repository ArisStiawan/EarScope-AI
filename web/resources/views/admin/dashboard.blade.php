<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                <svg class="w-6 h-6 animate-heartbeat" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ __('Dashboard Admin') }}
                </h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">Kelola data dokter, pantau registrasi pasien, dan audit log aktivitas sistem</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- STATS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Stats 1: Dokter Aktif -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Dokter Aktif</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">
                            {{ $stats['total_doctors'] ?? 0 }}
                        </p>
                    </div>
                    <div class="p-4 bg-teal-50 rounded-2xl text-teal-600 group-hover:bg-teal-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>

                <!-- Stats 2: Pasien Terdaftar -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pasien Terdaftar</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">
                            {{ $stats['total_patients'] ?? 0 }}
                        </p>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:bg-emerald-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Stats 3: Konsultasi Bulan Ini -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Konsultasi Bulan Ini</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">
                            {{ $stats['total_consultations_month'] ?? 0 }}
                        </p>
                    </div>
                    <div class="p-4 bg-sky-50 rounded-2xl text-sky-600 group-hover:bg-sky-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

            </div>

            <!-- QUICK ACTIONS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <a href="{{ route('admin.doctors.index') }}" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm group hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Manajemen Data Dokter</h3>
                        <p class="text-slate-400 text-xs mt-1">Kelola lisensi, username, spesialisasi, dan biodata dokter sistem</p>
                    </div>
                    <div class="p-3 bg-teal-50 text-teal-600 rounded-xl group-hover:bg-teal-600 group-hover:text-white transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
                
                <a href="{{ route('admin.patients.index') }}" class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm group hover:-translate-y-0.5 hover:shadow-md transition-all duration-300 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Manajemen Data Pasien</h3>
                        <p class="text-slate-400 text-xs mt-1">Lihat dan monitor daftar registrasi pasien yang aktif di sistem</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-600 group-hover:text-white transition duration-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>

            </div>

            <!-- CHART -->
            <div class="bg-white shadow-sm border border-slate-100 rounded-2xl p-6">
                <div class="mb-5">
                    <h3 class="font-bold text-lg text-slate-800">Statistik Konsultasi Bulanan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Visualisasi total konsultasi masuk selama 6 bulan terakhir</p>
                </div>
                <div class="w-full">
                    <canvas id="consultationChart" height="90"></canvas>
                </div>
            </div>

            <!-- ACTIVITY TABLE -->
            <div class="bg-white shadow-sm border border-slate-100 rounded-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="font-bold text-lg text-slate-800">Audit Log & Aktivitas Sistem</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Catatan audit log real-time dari tindakan admin, dokter, dan pasien</p>
                </div>

                @if(empty($activityLogs))
                    <div class="text-center py-12 px-6">
                        <p class="text-slate-400 text-sm">Tidak ada aktivitas sistem yang tercatat.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">Waktu Kejadian</th>
                                    <th class="px-6 py-4">Pelaku Aktivitas</th>
                                    <th class="px-6 py-4">Tipe Aksi</th>
                                    <th class="px-6 py-4">Deskripsi Aktivitas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($activityLogs as $log)
                                    <tr class="hover:bg-slate-50/40 transition-colors duration-250">
                                        <td class="px-6 py-4 text-slate-500 font-medium">
                                            {{ $log['timestamp']->format('d M Y H:i') }} WIB
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 border border-slate-200/50">
                                                    <span class="text-slate-600 text-[10px] font-bold">
                                                        {{ strtoupper(substr($log['user_name'], 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="font-bold text-slate-700">{{ $log['user_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md border bg-slate-50 text-slate-600 border-slate-200/50">
                                                {{ $log['activity_type'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 font-medium">
                                            {{ $log['description'] }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const consultationData = @json($consultationStats ?? ['labels' => [], 'data' => []]);

        const ctx = document.getElementById('consultationChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: consultationData.labels,
                datasets: [{
                    label: 'Jumlah Konsultasi',
                    data: consultationData.data,
                    backgroundColor: 'rgba(13, 148, 136, 0.75)',
                    borderColor: 'rgba(13, 148, 136, 1)',
                    borderWidth: 1.5,
                    borderRadius: 8,
                    hoverBackgroundColor: 'rgba(13, 148, 136, 0.9)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Plus Jakarta Sans',
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(241, 245, 249, 1)'
                        },
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: 'Plus Jakarta Sans'
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>
