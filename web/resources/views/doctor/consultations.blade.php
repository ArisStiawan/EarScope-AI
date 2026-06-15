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
                    {{ __('Daftar Permintaan Konsultasi') }}
                </h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">Kelola janji temu dan diagnosa keluhan telinga pasien</p>
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
                            <h4 class="text-base font-bold text-slate-700">Tidak Ada Permintaan</h4>
                            <p class="text-xs text-slate-400 mt-1">Belum ada pasien yang mengajukan sesi konsultasi untuk status ini.</p>
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
                                                        <p class="text-[10px] text-slate-400 font-semibold mt-0.5">Umur: {{ $consultation->patient->age ?? '-' }} Tahun</p>
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
                                                        <span class="text-[11px] text-slate-400 font-medium">{{ $consultation->scheduled_time }} WIB</span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-400 font-medium italic">Belum dijadwalkan</span>
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
        // Approve -> delegate ke schedule modal
        function approveConsultation(consultationId) {
            openScheduleModal(consultationId, true);
        }

        // Reject Consultation
        function rejectConsultation(consultationId) {
            if (confirm('Apakah Anda yakin ingin menolak konsultasi ini?')) {
                $.ajax({
                    url: '/doctor/consultation/' + consultationId + '/reject',
                    type: 'POST',
                    data: {
                        _token: $('[name="_token"]').val()
                    },
                    success: function(response) {
                        // Update status badge
                                $('#status-' + consultationId).removeClass('bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Cancelled');
                        
                        showNotification('Consultation cancelled successfully', 'success');
                    },
                    error: function(xhr) {
                        showNotification('Gagal menolak konsultasi', 'error');
                    }
                });
            }
        }

        // Show Notification
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