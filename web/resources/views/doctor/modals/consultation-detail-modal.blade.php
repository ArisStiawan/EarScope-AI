{{-- Consultation Detail Modal --}}
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
                        <h3 class="text-base font-bold text-slate-800">Detail Rekam Konsultasi</h3>
                        <p class="text-[10px] text-slate-400 font-medium">Data pasien & hasil pemeriksaan otoskop AI</p>
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
            <div id="modalFooter" class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex justify-end">
                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentConsultationId = null;

    function openDetailModal(consultationId) {
        currentConsultationId = consultationId;

        // Reset content
        $('#modalContent').html(`
            <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                <svg class="w-8 h-8 animate-spin mb-3 text-teal-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="text-xs font-medium">Memuat data...</p>
            </div>
        `);
        $('#modalFooter').html(`
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition">
                Tutup
            </button>
        `);
        $('#consultationDetailModal').removeClass('hidden');

        $.ajax({
            url: '/doctor/consultation/' + consultationId + '/details',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Define helper variables
                const genderMap = { 'male': 'Laki-laki', 'female': 'Perempuan' };
                const gender = genderMap[data.patient?.gender] || data.patient?.gender || '-';

                const statusConfig = {
                    'pending':   { label: 'Menunggu',    class: 'bg-amber-50 text-amber-700 border-amber-200/50' },
                    'scheduled': { label: 'Dijadwalkan', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/50' },
                    'cancelled': { label: 'Dibatalkan',  class: 'bg-rose-50 text-rose-700 border-rose-200/50' },
                    'done':      { label: 'Selesai',     class: 'bg-sky-50 text-sky-700 border-sky-200/50' },
                };
                const sc = statusConfig[data.status] || { label: data.status, class: 'bg-slate-50 text-slate-700 border-slate-200/50' };
                const statusLabel = sc.label;
                const statusClass = sc.class;

                // Build AI screening section
                let aiSection = '';
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
                                        <p class="text-xs font-bold text-slate-700 mt-0.5">${typeof img.ai_screening_result === 'object' ? JSON.stringify(img.ai_screening_result) : img.ai_screening_result}</p>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('');
                    }

                    aiSection = `
                        <div class="pt-5 border-t border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Hasil Pemeriksaan AI Otoskop</p>
                            <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Diagnosis</p>
                                <p class="text-sm font-bold text-slate-800 mt-0.5">${d.diagnosis_result || '-'}</p>
                            </div>
                            ${d.notes ? `
                                <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Catatan Dokter</p>
                                    <p class="text-sm text-slate-700 mt-0.5">${d.notes}</p>
                                </div>
                            ` : ''}
                            <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Status Verifikasi</p>
                                <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold rounded-full border ${d.is_verified ? 'bg-emerald-50 text-emerald-700 border-emerald-200/50' : 'bg-amber-50 text-amber-700 border-amber-200/50'}">
                                    ${d.is_verified ? 'Terverifikasi' : 'Belum Diverifikasi'}
                                </span>
                            </div>
                            ${imagesHtml ? `
                                <div class="grid grid-cols-2 gap-3 mt-3">${imagesHtml}</div>
                            ` : ''}
                        </div>

                        ${!d.is_verified && data.status !== 'done' ? `
                            <div class="pt-5 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Verifikasi Dokter</p>
                                <form id="verifyForm" onsubmit="submitVerification(event, ${data.id})">
                                    <textarea id="doctorNotes" rows="3" class="w-full rounded-xl border-slate-200 text-sm placeholder-slate-300 focus:border-teal-400 focus:ring-teal-400" placeholder="Tambahkan catatan dokter (opsional)...">${d.notes || ''}</textarea>
                                </form>
                            </div>
                        ` : ''}
                    `;
                } else if (data.status === 'approved' || data.status === 'scheduled') {
                    aiSection = `
                        <div class="pt-5 border-t border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Verifikasi Dokter</p>
                            <p class="text-xs text-slate-400 mb-3">Hasil pemeriksaan AI belum tersedia. Anda dapat menyelesaikan secara manual.</p>
                            <form id="verifyForm" onsubmit="submitVerification(event, ${data.id})">
                                <textarea id="doctorNotes" rows="3" class="w-full rounded-xl border-slate-200 text-sm placeholder-slate-300 focus:border-teal-400 focus:ring-teal-400" placeholder="Tambahkan catatan dokter (opsional)..."></textarea>
                            </form>
                        </div>
                    `;
                }

                // Build modal content
                let content = `
            <div class="space-y-0">
                {{-- Patient Info --}}
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Data Pasien</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Nama</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.name ?? '-'}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Usia</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.age ?? '-'} Tahun</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Email</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.email ?? '-'}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Jenis Kelamin</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">${gender}</p>
                        </div>
                        <div class="col-span-2 bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Alamat</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.address ?? '-'}</p>
                        </div>
                    </div>
                </div>

                {{-- Complaint & Status --}}
                <div class="pt-5 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Detail Konsultasi</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2 bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Keluhan Pasien</p>
                            <p class="text-sm text-slate-700 mt-1 leading-relaxed">${data.complaint}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Status</p>
                            <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold rounded-full border ${statusClass}">${statusLabel}</span>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Jadwal</p>
                            <p class="text-sm font-bold text-slate-800 mt-0.5">
                                ${data.scheduled_date ? data.scheduled_date + ' · ' + data.scheduled_time : 'Belum dijadwalkan'}
                            </p>
                        </div>
                    </div>
                </div>

                ${aiSection}
            </div>`;

        $('#modalContent').html(content);

        // Show verify button in footer if applicable
        const showVerify = data.diagnosis && !data.diagnosis.is_verified && data.status !== 'done';
        const showManualVerify = !data.diagnosis && (data.status === 'approved' || data.status === 'scheduled');
        if (showVerify || showManualVerify) {
            $('#modalFooter').html(`
                <button type="button" onclick="closeDetailModal()" class="px-4 py-2 text-xs font-semibold uppercase tracking-wider rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition mr-2">
                    Tutup
                </button>
                <button type="button" onclick="$('#verifyForm').submit()"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white transition shadow-md shadow-teal-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verifikasi & Selesaikan
                </button>`);
        }
    }

    function submitVerification(event, consultationId) {
        event.preventDefault();
        const notes = $('#doctorNotes').val();
        const btn = $('#modalFooter button:last-child');
        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: '/doctor/consultation/' + consultationId + '/verify',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                notes: notes
            },
            success: function() {
                closeDetailModal();
                showNotification('Konsultasi berhasil diverifikasi & diselesaikan', 'success');
                // Update the badge in the table
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                btn.prop('disabled', false).html(`
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg> Verifikasi & Selesaikan`);
                showNotification('Gagal menyimpan verifikasi', 'error');
            }
        });
    }

    function closeDetailModal() {
        $('#consultationDetailModal').addClass('hidden');
    }

    function getBadgeClass(status) {
        const classes = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'scheduled': 'bg-green-100 text-green-800',
            'cancelled': 'bg-red-100 text-red-800',
            'done': 'bg-blue-100 text-blue-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    // Close on overlay click handled inline
</script>
