<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight">Riwayat Permintaan Konsultasi</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Pantau status dan riwayat seluruh permintaan konsultasi Anda</p>
                </div>
            </div>

            {{-- Tombol Ajukan Konsultasi Baru --}}
            @if(!$hasActive)
                <a href="{{ route('patient.create-consultation') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-teal-600 to-emerald-600
                           hover:from-teal-700 hover:to-emerald-700 text-white text-xs font-semibold uppercase
                           tracking-wider rounded-xl transition shadow-md shadow-teal-500/20 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Konsultasi Baru
                </a>
            @else
                {{-- Disabled button jika masih ada konsultasi aktif --}}
                <span class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-400 text-xs
                             font-semibold uppercase tracking-wider rounded-xl cursor-not-allowed"
                      title="Selesaikan atau batalkan konsultasi aktif terlebih dahulu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Konsultasi Baru
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Messages --}}
            @if(session('error'))
                <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
                    <div class="p-1 bg-rose-500 text-white rounded-full shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm">
                    <div class="p-1 bg-emerald-500 text-white rounded-full shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-6">
                    {{-- Info banner jika ada konsultasi aktif --}}
                    @if($hasActive)
                        <div class="mb-5 flex items-center gap-3 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-xl text-sm font-medium">
                            <svg class="w-5 h-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Anda memiliki konsultasi yang sedang berjalan. Tombol "Ajukan Konsultasi Baru" akan aktif kembali setelah konsultasi selesai atau dibatalkan.
                        </div>
                    @endif

                    {{-- Status Filter Tabs --}}
                    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4">
                        @foreach(['all' => 'Semua', 'pending' => 'Menunggu', 'scheduled' => 'Dijadwalkan', 'cancelled' => 'Dibatalkan', 'done' => 'Selesai'] as $key => $label)
                            <a href="{{ route('patient.consultation-requests', ['status' => $key]) }}"
                                class="px-4 py-2 rounded-md text-sm font-medium transition
                                    {{ $status === $key
                                        ? ($key === 'pending'   ? 'bg-yellow-100 text-yellow-700'
                                        : ($key === 'scheduled' ? 'bg-green-100 text-green-700'
                                        : ($key === 'cancelled' ? 'bg-red-100 text-red-700'
                                        : ($key === 'done'      ? 'bg-blue-100 text-blue-700'
                                        : 'bg-indigo-100 text-indigo-700'))))
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>

                    {{-- Tabel --}}
                    @if($consultations->isEmpty())
                        <div class="text-center py-16 px-6">
                            <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-700">Tidak Ada Permintaan</h4>
                            <p class="text-xs text-slate-400 mt-1">Belum ada riwayat konsultasi untuk status yang dipilih.</p>
                            @if(!$hasActive)
                                <a href="{{ route('patient.create-consultation') }}"
                                    class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700
                                           text-white text-xs font-semibold uppercase tracking-wider rounded-xl transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajukan Konsultasi Pertama
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokter</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keluhan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($consultations as $consultation)
                                        <tr class="hover:bg-slate-50/40 transition-colors duration-150">

                                            {{-- Dokter --}}
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-9 w-9 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0 border border-indigo-100">
                                                        <span class="text-indigo-700 text-xs font-bold">
                                                            {{ strtoupper(substr($consultation->doctor->name ?? 'D', 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-slate-800">dr. {{ $consultation->doctor->name ?? '-' }}</span>
                                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                                            @if($consultation->doctor->practice_start_time && $consultation->doctor->practice_end_time)
                                                                {{ \Carbon\Carbon::parse($consultation->doctor->practice_start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($consultation->doctor->practice_end_time)->format('H:i') }} WIB
                                                            @else
                                                                Jam praktik belum diatur
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Keluhan --}}
                                            <td class="px-6 py-4 text-slate-600 font-medium max-w-xs">
                                                {{ Str::limit($consultation->complaint, 50) }}
                                            </td>

                                            {{-- Status Badge --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusConfig = [
                                                        'pending'   => ['label' => 'Menunggu',    'class' => 'bg-yellow-100 text-yellow-800'],
                                                        'scheduled' => ['label' => 'Dijadwalkan', 'class' => 'bg-green-100 text-green-800'],
                                                        'cancelled' => ['label' => 'Dibatalkan',  'class' => 'bg-red-100 text-red-800'],
                                                        'done'      => ['label' => 'Selesai',     'class' => 'bg-blue-100 text-blue-800'],
                                                    ];
                                                    $sc = $statusConfig[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'bg-gray-100 text-gray-800'];
                                                @endphp
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sc['class'] }}">
                                                    {{ $sc['label'] }}
                                                </span>
                                            </td>

                                            {{-- Jadwal --}}
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($consultation->scheduled_date)
                                                    <div class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}</div>
                                                    <div class="text-[11px] text-indigo-600 font-bold">Antrean: {{ $consultation->queue_number }}</div>
                                                @else
                                                    <span class="text-xs text-slate-400 italic">Belum dijadwalkan</span>
                                                @endif
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold uppercase tracking-wider space-x-3">
                                                <button type="button" onclick="openDetailModal('{{ $consultation->id }}')"
                                                    class="text-teal-600 hover:text-teal-800 transition underline">
                                                    Detail
                                                </button>

                                                @if(in_array($consultation->status, ['pending', 'scheduled']))
                                                    <button type="button" onclick="cancelConsultation('{{ $consultation->id }}')"
                                                        class="text-rose-500 hover:text-rose-700 transition underline">
                                                        Batalkan
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-4">
                                <x-custom-pagination :paginator="$consultations" :perPage="$perPage" />
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- Detail Modal (style sama dengan modal dokter) --}}
    <div id="consultationDetailModal" class="hidden fixed z-50 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeDetailModal()"></div>

            {{-- Panel --}}
            <div class="relative bg-white rounded-2xl shadow-2xl text-left overflow-hidden w-full max-w-2xl animate-fade-in-up border border-slate-100">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-teal-50 to-white">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-teal-100 rounded-xl text-teal-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Detail Permintaan Konsultasi</h3>
                            <p class="text-[10px] text-slate-400 font-medium">Informasi dokter, keluhan, jadwal, dan hasil diagnosis</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content (populated via AJAX) --}}
                <div id="modalContent" class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <svg class="w-8 h-8 animate-spin mb-3 text-teal-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-xs font-medium">Memuat data...</p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
                    <button type="button" onclick="closeDetailModal()"
                        class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cancel Confirmation Modal --}}
    <div id="cancelConfirmModal" class="hidden fixed z-[60] inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeCancelModal()"></div>

            {{-- Panel --}}
            <div class="relative bg-white rounded-2xl shadow-2xl text-left overflow-hidden w-full max-w-sm animate-fade-in-up border border-slate-100">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">Batalkan Konsultasi?</h3>
                    <p class="text-sm text-slate-500 mb-6">Tindakan ini tidak dapat dibatalkan. Antrean Anda akan dihapus.</p>
                    
                    <div class="flex gap-3 justify-center">
                        <button type="button" onclick="closeCancelModal()"
                            class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                            Kembali
                        </button>
                        <button type="button" id="confirmCancelBtn"
                            class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-red-500 text-white hover:bg-red-600 transition shadow-lg shadow-red-500/30">
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDetailModal(consultationId) {
            // Reset ke loading spinner
            $('#modalContent').html(`
                <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                    <svg class="w-8 h-8 animate-spin mb-3 text-teal-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-xs font-medium">Memuat data...</p>
                </div>
            `);
            $('#consultationDetailModal').removeClass('hidden');

            $.ajax({
                url: '/patient/consultation/' + consultationId + '/details',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    const statusConfig = {
                        'pending':   { label: 'Menunggu',    cls: 'bg-amber-50 text-amber-700 border-amber-200/50'   },
                        'scheduled': { label: 'Dijadwalkan', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200/50' },
                        'cancelled': { label: 'Dibatalkan',  cls: 'bg-rose-50 text-rose-700 border-rose-200/50'      },
                        'done':      { label: 'Selesai',     cls: 'bg-sky-50 text-sky-700 border-sky-200/50'         },
                    };
                    const sc = statusConfig[data.status] || { label: data.status, cls: 'bg-slate-50 text-slate-700 border-slate-200/50' };

                    // Section diagnosis (hanya jika done dan ada diagnosis)
                    let diagnosisSection = '';
                    if (data.diagnosis) {
                        const d = data.diagnosis;

                        let imagesHtml = '';
                        if (d.images && d.images.length > 0) {
                            imagesHtml = d.images.map(img => `
                                <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">
                                    <img src="${img.image_url}" alt="Otoscope" class="w-full h-36 object-cover" />
                                    ${img.ai_screening_result ? `
                                        <div class="p-2.5">
                                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Hasil AI</p>
                                            <p class="text-xs font-bold text-slate-700 mt-0.5">
                                                ${typeof img.ai_screening_result === 'object' ? JSON.stringify(img.ai_screening_result) : img.ai_screening_result}
                                            </p>
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('');
                        }

                        diagnosisSection = `
                            <div class="pt-5 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Hasil Pemeriksaan AI Otoskop</p>
                                <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Diagnosis</p>
                                    <p class="text-sm font-bold text-slate-800 mt-0.5">${d.result || '-'}</p>
                                </div>
                                ${d.notes ? `
                                    <div class="bg-teal-50 border border-teal-100 rounded-xl p-3 mb-3">
                                        <p class="text-[10px] text-teal-600 font-bold uppercase tracking-wider mb-1">Catatan Dokter</p>
                                        <p class="text-sm text-teal-800 leading-relaxed">${d.notes}</p>
                                    </div>
                                ` : ''}
                                ${imagesHtml ? `<div class="grid grid-cols-2 gap-3 mt-3">${imagesHtml}</div>` : ''}
                            </div>`;
                    }

                    const content = `
                        <div class="space-y-0">
                            {{-- Section: Info Dokter --}}
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Informasi Dokter</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Nama Dokter</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">dr. ${data.doctor?.name ?? '-'}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Jam Praktik</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${data.doctor?.practice_hours ?? '-'}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Section: Detail Konsultasi --}}
                            <div class="pt-5 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Detail Konsultasi</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2 bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Keluhan Pasien</p>
                                        <p class="text-sm text-slate-700 mt-1 leading-relaxed">${data.complaint}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Status</p>
                                        <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold rounded-full border ${sc.cls}">${sc.label}</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Jadwal & Antrean</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">
                                            ${data.scheduled_date
                                                ? new Date(data.scheduled_date).toLocaleDateString('id-ID', {day:'2-digit', month:'long', year:'numeric'}) + ' · Antrean #' + data.queue_number
                                                : '<span class="text-slate-400 font-normal italic">Belum dijadwalkan</span>'}
                                        </p>
                                    </div>
                                    ${data.notes ? `
                                        <div class="col-span-2 bg-teal-50 border border-teal-100 rounded-xl p-3">
                                            <div class="flex items-center gap-1.5 mb-1.5">
                                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-[10px] text-teal-600 font-bold uppercase tracking-wider">Catatan Dokter</p>
                                            </div>
                                            <p class="text-sm text-teal-800 leading-relaxed">${data.notes}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>

                            ${diagnosisSection}
                        </div>`;

                    $('#modalContent').html(content);
                },
                error: function() {
                    $('#modalContent').html(`
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs font-medium">Gagal memuat detail konsultasi</p>
                        </div>
                    `);
                }
            });
        }

        function closeDetailModal() {
            $('#consultationDetailModal').addClass('hidden');
        }

        let cancelIdTarget = null;

        function cancelConsultation(consultationId) {
            cancelIdTarget = consultationId;
            $('#cancelConfirmModal').removeClass('hidden');
        }

        function closeCancelModal() {
            cancelIdTarget = null;
            $('#cancelConfirmModal').addClass('hidden');
        }

        $('#confirmCancelBtn').on('click', function() {
            if (!cancelIdTarget) return;
            
            // Loading state
            const originalText = $(this).text();
            $(this).prop('disabled', true).html('<svg class="w-5 h-5 animate-spin mx-auto text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>');

            $.ajax({
                url: '/patient/consultation/' + cancelIdTarget + '/cancel',
                type: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function() { location.reload(); },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Gagal membatalkan konsultasi.');
                    closeCancelModal();
                    $('#confirmCancelBtn').prop('disabled', false).text('Ya, Batalkan');
                }
            });
        });

        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
</x-app-layout>
