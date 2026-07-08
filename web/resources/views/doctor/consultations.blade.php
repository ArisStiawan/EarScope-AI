<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                <svg class="w-6 h-6 animate-heartbeat" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ __('Consultation Request List') }}
                </h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">Manage appointments and ear complaint diagnoses</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white border border-slate-100 shadow-sm rounded-2xl overflow-hidden">
                <div class="p-6">                    
                    <!-- Status Filter Tabs -->
                    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 pb-4">
                        <a href="{{ route('doctor.consultations', ['status' => 'all']) }}" 
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All
                        </a>
                        <a href="{{ route('doctor.consultations', ['status' => 'pending']) }}" 
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Pending
                        </a>
                        <a href="{{ route('doctor.consultations', ['status' => 'scheduled']) }}" 
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'scheduled' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Scheduled
                        </a>
                        <a href="{{ route('doctor.consultations', ['status' => 'cancelled']) }}" 
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Cancelled
                        </a>
                        <a href="{{ route('doctor.consultations', ['status' => 'done']) }}" 
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $status === 'done' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Done
                        </a>
                    </div>
                    
                    @if($consultations->isEmpty())
                        <div class="text-center py-16 px-6">
                            <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 animate-float">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-slate-700">No Requests</h4>
                            <p class="text-xs text-slate-400 mt-1">No patients have submitted consultation requests for this status.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-sm">
                                    @foreach($consultations as $consultation)
                                        <tr id="row-{{ $consultation->id }}" class="hover:bg-slate-50/40 transition-colors duration-250">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-9 w-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 border border-teal-100/30">
                                                        <span class="text-teal-700 text-xs font-bold">
                                                            {{ strtoupper(substr($consultation->patient->name ?? 'P', 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="font-bold text-slate-800">
                                                            {{ $consultation->patient->name ?? 'N/A' }}
                                                        </span>
                                                        <div class="grid items-center gap-2 mt-0.5">
                                                            <span class="text-[10px] text-indigo-500 font-bold bg-indigo-50 px-1.5 py-0.5 rounded">RM: {{ $consultation->patient->medical_record_number ?? '-' }}</span>
                                                            <span class="text-[10px] text-slate-400 font-semibold">Umur: {{ $consultation->patient->age ?? '-' }} Tahun</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                {{ $consultation->patient->user->email ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 text-slate-600 font-medium max-w-xs truncate">
                                                {{ Str::limit($consultation->complaint, 45) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span id="status-{{ $consultation->id }}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $consultation->status === 'scheduled' ? 'bg-green-100 text-green-800' : 
                                                       ($consultation->status === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                                       ($consultation->status === 'done' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                                    {{ ucfirst($consultation->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($consultation->scheduled_date)
                                                    <div class="flex flex-col">
                                                        <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}</span>
                                                        <span class="text-[11px] text-indigo-600 font-bold">Antrean: {{ $consultation->queue_number }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-400 font-medium italic">Not yet scheduled</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold uppercase tracking-wider space-x-2.5">
                                                <button type="button" onclick="openDetailModal('{{ $consultation->id }}')" class="text-teal-600 hover:text-teal-700 transition">
                                                    Detail
                                                </button>

                                                @if($consultation->status === 'pending')
                                                    <button type="button"
                                                        onclick="openScheduleModal('{{ $consultation->id }}', true)"
                                                        class="text-green-600 hover:text-green-900 underline">
                                                        Set Schedule
                                                    </button>
                                                @endif

                                                @if($consultation->status === 'scheduled' && !$consultation->diagnosis)
                                                    <button type="button" onclick="openScheduleModal('{{ $consultation->id }}', false)" class="text-blue-600 hover:text-blue-900 underline">
                                                        Reschedule
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <x-custom-pagination :paginator="$consultations" :perPage="$perPage" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Include Modals -->
    @include('doctor.modals.consultation-detail-modal')
    @include('doctor.modals.schedule-modal')

    <script>
        let currentConsultationId = null;

        // ========== DETAIL MODAL ==========
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
                    const genderMap = { 'male': 'Male', 'female': 'Female' };
                    const gender = genderMap[data.patient?.gender] || data.patient?.gender || '-';

                    const statusConfig = {
                        'pending':   { label: 'Pending',    class: 'bg-amber-50 text-amber-700 border-amber-200/50' },
                        'scheduled': { label: 'Scheduled', class: 'bg-emerald-50 text-emerald-700 border-emerald-200/50' },
                        'cancelled': { label: 'Cancelled',  class: 'bg-rose-50 text-rose-700 border-rose-200/50' },
                        'done':      { label: 'Done',     class: 'bg-sky-50 text-sky-700 border-sky-200/50' },
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
                                            <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">AI Result</p>
                                            <p class="text-xs font-bold text-slate-700 mt-0.5">${typeof img.ai_screening_result === 'object' ? JSON.stringify(img.ai_screening_result) : img.ai_screening_result}</p>
                                        </div>
                                    ` : ''}
                                </div>
                            `).join('');
                        }

                        aiSection = `
                            <div class="pt-5 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">AI Otoscope Examination Results</p>
                                <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Diagnosis</p>
                                    <p class="text-sm font-bold text-slate-800 mt-0.5">${d.diagnosis_result || '-'}</p>
                                </div>
                                ${data.notes ? `
                                    <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Doctor Notes</p>
                                        <p class="text-sm text-slate-700 mt-0.5">${data.notes}</p>
                                    </div>
                                ` : ''}
                                <div class="bg-slate-50 rounded-xl p-3 mb-3">
                                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Verification Status</p>
                                    <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold rounded-full border ${d.is_verified ? 'bg-emerald-50 text-emerald-700 border-emerald-200/50' : 'bg-amber-50 text-amber-700 border-amber-200/50'}">
                                        ${d.is_verified ? 'Verified' : 'Not Verified'}
                                    </span>
                                </div>

                                ${(d.raw_video_url || d.processed_video_url) ? `
                                    <div class="mt-4 space-y-4">
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Video Pemeriksaan Earscope</p>
                                        ${d.raw_video_url ? `
                                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-black">
                                                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider px-3 pt-2 pb-1 bg-slate-900">Raw Video (Tanpa Deteksi)</p>
                                                <video controls class="w-full" style="max-height:260px;" preload="metadata">
                                                    <source src="${d.raw_video_url}" type="video/mp4">
                                                    Browser Anda tidak mendukung pemutaran video.
                                                </video>
                                            </div>
                                        ` : ''}
                                        ${d.processed_video_url ? `
                                            <div class="rounded-xl overflow-hidden border border-teal-200 bg-black">
                                                <p class="text-[10px] font-semibold text-teal-400 uppercase tracking-wider px-3 pt-2 pb-1 bg-slate-900">Processed Video (Deteksi YOLO AI)</p>
                                                <video controls class="w-full" style="max-height:260px;" preload="metadata">
                                                    <source src="${d.processed_video_url}" type="video/mp4">
                                                    Browser Anda tidak mendukung pemutaran video.
                                                </video>
                                            </div>
                                        ` : ''}
                                    </div>
                                ` : ''}
                                ${imagesHtml ? `
                                    <div class="grid grid-cols-2 gap-3 mt-3">${imagesHtml}</div>
                                ` : ''}
                            </div>
                        `;
                    }

                    // Build modal content
                    let content = `
                        <div class="space-y-0">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Patient Data</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Name</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.name ?? '-'}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Age</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.age ?? '-'} years</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">No. RM</p>
                                        <p class="text-sm font-bold text-indigo-600 mt-0.5">${data.patient?.medical_record_number ?? '-'}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Email</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.user?.email ?? '-'}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Gender</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${gender}</p>
                                    </div>
                                    <div class="col-span-2 bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Address</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">${data.patient?.address ?? '-'}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-5 border-t border-slate-100">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3">Consultation Details</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="col-span-2 bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Patient Complaint</p>
                                        <p class="text-sm text-slate-700 mt-1 leading-relaxed">${data.complaint}</p>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Status</p>
                                        <span class="mt-1 inline-flex px-2 py-0.5 text-xs font-bold rounded-full border ${statusClass}">${statusLabel}</span>
                                    </div>
                                    <div class="bg-slate-50 rounded-xl p-3">
                                        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Schedule</p>
                                        <p class="text-sm font-bold text-slate-800 mt-0.5">
                                            ${data.scheduled_date ? data.scheduled_date + ' · Antrean: ' + data.queue_number : 'Belum dijadwalkan'}
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

                            ${aiSection}
                        </div>`;

                    $('#modalContent').html(content);
                },
                error: function(xhr) {
                    $('#modalContent').html(`
                        <div class="text-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs font-medium">Failed to load consultation details</p>
                        </div>
                    `);
                }
            });
        }

        function closeDetailModal() {
            $('#consultationDetailModal').addClass('hidden');
        }

        // ========== EXISTING FUNCTIONS ==========
        function approveConsultation(consultationId) {
            openScheduleModal(consultationId, true);
        }

        function rejectConsultation(consultationId) {
            if (confirm('Are you sure you want to reject this consultation?')) {
                $.ajax({
                    url: '/doctor/consultation/' + consultationId + '/reject',
                    type: 'POST',
                    data: {
                        _token: $('[name="_token"]').val()
                    },
                    success: function(response) {
                        $('#status-' + consultationId).removeClass('bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Cancelled');
                        showNotification('Consultation cancelled successfully', 'success');
                    },
                    error: function(xhr) {
                        showNotification('Failed to reject consultation', 'error');
                    }
                });
            }
        }

        function showNotification(message, type) {
            let bgColor = type === 'success' ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200';
            let textColor = type === 'success' ? 'text-emerald-800' : 'text-rose-800';
            
            let notification = `
                <div class="fixed top-4 right-4 rounded-xl border ${bgColor} p-4 shadow-lg z-50 animate-fade-in-up">
                    <p class="text-xs font-semibold ${textColor}">${message}</p>
                </div>
            `;
            
            $('body').append(notification);
            
            setTimeout(function() {
                $('body').find('.fixed.top-4').remove();
            }, 3000);
        }

        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
</x-app-layout>