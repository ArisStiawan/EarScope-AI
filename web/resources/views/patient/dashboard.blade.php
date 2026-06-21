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
                    <h2 class="font-bold text-2xl text-slate-800 leading-tight">{{ __('Patient Dashboard') }}</h2>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">Manage your appointments and ear examination results</p>
                </div>
            </div>
            <div>
                <a href="{{ route('patient.create-consultation') }}"
                    class="relative inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-2.5 px-5 rounded-xl text-sm transition-all duration-300 shadow-md shadow-teal-500/20 hover:shadow-teal-500/35 transform hover:-translate-y-0.5 active:translate-y-0 animate-pulse-ring">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Request New Consultation
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash success --}}
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Consultation Requests</h3>
                    <p class="mt-3 text-2xl font-bold">{{ $totalRequests }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Total Completed Consultations</h3>
                    <p class="mt-3 text-2xl font-bold">{{ $totalDone }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-gray-500">Next Scheduled Consultation</h3>
                    <p class="text-2xl font-bold">
                        @if ($nextScheduled)
                            {{ \Carbon\Carbon::parse($nextScheduled->scheduled_date)->format('d M Y') }}
                        @else
                            -
                        @endif
                    </p>
                    @if ($nextScheduled)
                        <p class="text-sm text-gray-500 mt-1">{{ $nextScheduled->scheduled_time ?? '' }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-lg border sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Header tabel --}}
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ __('Upcoming Consultations') }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Your upcoming scheduled consultations</p>
                        </div>
                        <!-- <a href="{{ route('patient.create-consultation') }}"
                            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Request Consultation
                        </a> -->
                    </div>

                    @if ($consultations->isEmpty())
                        {{-- Empty state --}}
                        <div class="text-center py-14">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-3 text-sm text-gray-500">No upcoming consultations found.</p>
                            <a href="{{ route('patient.create-consultation') }}"
                                class="mt-4 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                                Request Consultation Now
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto shadow-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Doctor</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Complaint</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Schedule</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($consultations as $i => $consultation)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $consultations->firstItem() + $i }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                                                        <span class="text-indigo-700 text-xs font-bold">
                                                            {{ strtoupper(substr($consultation->doctor->name ?? 'D', 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">
                                                        dr. {{ $consultation->doctor->name ?? '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">
                                                {{ Str::limit($consultation->complaint, 60) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($consultation->scheduled_date)
                                                    <div class="font-medium text-gray-800">
                                                        {{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}
                                                    </div>
                                                    <div class="text-xs text-gray-400">
                                                        {{ $consultation->scheduled_time ?? '' }}</div>
                                                @else
                                                    <span class="text-xs text-yellow-600 italic">Waiting for
                                                        approval</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusMap = [
                                                        'pending' => [
                                                            'label' => 'Pending',
                                                            'class' => 'bg-yellow-100 text-yellow-800',
                                                        ],
                                                        'scheduled' => [
                                                            'label' => 'Scheduled',
                                                            'class' => 'bg-green-100 text-green-800',
                                                        ],
                                                        'cancelled' => [
                                                            'label' => 'Cancelled',
                                                            'class' => 'bg-red-100 text-red-800',
                                                        ],
                                                        'done' => [
                                                            'label' => 'Done',
                                                            'class' => 'bg-blue-100 text-blue-800',
                                                        ],
                                                    ];
                                                    $s = $statusMap[$consultation->status] ?? [
                                                        'label' => ucfirst($consultation->status),
                                                        'class' => 'bg-gray-100 text-gray-800',
                                                    ];
                                                @endphp
                                                <span
                                                    class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $s['class'] }}">
                                                    {{ $s['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button type="button"
                                                    onclick="openDetailModal('{{ $consultation->id }}')"
                                                    class="text-indigo-600 hover:text-indigo-900 transition underline">View
                                                    Detail</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Links -->
                        <div class="mt-6">
                            {{ $consultations->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Consultation Detail Modal -->
    <div id="consultationDetailModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 mt-20 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal panel -->
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Consultation Request Detail
                        </h3>
                        <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div id="modalContent" class="mt-4">
                        <!-- Content will be loaded here -->
                        <div class="text-center">
                            <p class="text-gray-500">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6">
                    <div id="modalMessage" class="hidden mb-4 rounded-md px-4 py-3 text-sm"></div>
                    <div id="cancelConfirm" class="hidden mb-4 rounded-md border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        <p class="font-semibold">Are you sure you want to cancel this consultation?</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" onclick="confirmCancelConsultation()"
                                class="inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-red-50 text-sm font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Yes, cancel consultation
                            </button>
                            <button type="button" onclick="hideCancelConfirm()"
                                class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                No, keep consultation
                            </button>
                        </div>
                    </div>
                    <div class="sm:flex sm:flex-row-reverse gap-2">
                        <button type="button" id="cancelBtn" onclick="showCancelConfirm()" style="display:none;"
                            class="w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-red-50 text-base font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel Consultation
                        </button>
                        <button type="button" onclick="closeDetailModal()"
                            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentConsultationId = null;

        function openDetailModal(consultationId) {
            currentConsultationId = consultationId;
            $('#modalMessage').addClass('hidden').removeClass('bg-red-50 bg-green-50 text-red-700 text-green-700 bg-yellow-50 text-yellow-700');
            $('#cancelConfirm').addClass('hidden');
            // Fetch consultation details
            $.ajax({
                url: '/patient/consultation/' + consultationId + '/details',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Build the modal content
                    let statusBadge = getBadgeClass(data.status);
                    let content = `
                        <div class="space-y-4">
                            <div class="border-b pb-4">
                                <h4 class="font-semibold text-gray-900 mb-3">Doctor Information</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor Name</p>
                                        <p class="mt-1 text-sm text-gray-900">${data.doctor.name}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</p>
                                        <p class="mt-1 text-sm text-gray-900">${data.doctor.specialization}</p>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                                        <p class="mt-1 text-sm text-gray-900">${data.doctor.email}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-b pb-4">
                                <h4 class="font-semibold text-gray-900 mb-3">Consultation Details</h4>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</p>
                                    <p class="mt-1 text-sm text-gray-900">${data.complaint}</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Request Status</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</p>
                                        <p class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusBadge}">
                                            ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}
                                        </span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</p>
                                        <p class="mt-1 text-sm text-gray-900">${new Date(data.created_at).toLocaleDateString()}</p>
                                    </div>
                                    ${data.scheduled_date ? `
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</p>
                                            <p class="mt-1 text-sm text-gray-900">${data.scheduled_date}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Time</p>
                                            <p class="mt-1 text-sm text-gray-900">${data.scheduled_time}</p>
                                        </div>
                                        ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                    $('#modalContent').html(content);

                    // Show cancel button only for pending or scheduled status
                    if (data.status === 'pending' || data.status === 'scheduled') {
                        $('#cancelBtn').show();
                    } else {
                        $('#cancelBtn').hide();
                    }
                },
                error: function() {
                    $('#modalContent').html('<p class="text-red-600">Failed to load consultation details</p>');
                    $('#cancelBtn').hide();
                }
            });

            $('#consultationDetailModal').removeClass('hidden');
        }

        function closeDetailModal() {
            $('#consultationDetailModal').addClass('hidden');
            $('#cancelConfirm').addClass('hidden');
            currentConsultationId = null;
        }

        function showCancelConfirm() {
            $('#cancelConfirm').removeClass('hidden');
            $('#modalMessage').addClass('hidden');
        }

        function hideCancelConfirm() {
            $('#cancelConfirm').addClass('hidden');
        }

        function confirmCancelConsultation() {
            if (!currentConsultationId) return;

            $.ajax({
                url: '/patient/consultation/' + currentConsultationId + '/cancel',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showModalMessage('success', 'Consultation cancelled successfully.');
                        $('#cancelConfirm').addClass('hidden');
                        setTimeout(function() {
                            closeDetailModal();
                            location.reload();
                        }, 900);
                    } else {
                        showModalMessage('error', response.message || 'Unable to cancel consultation.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showModalMessage('error', response?.message || 'Failed to cancel consultation.');
                }
            });
        }

        function showModalMessage(type, message) {
            const $message = $('#modalMessage');
            $message.removeClass('hidden bg-red-50 bg-green-50 bg-yellow-50 text-red-700 text-green-700 text-yellow-700');
            if (type === 'success') {
                $message.addClass('bg-green-50 text-green-700').text(message);
            } else if (type === 'error') {
                $message.addClass('bg-red-50 text-red-700').text(message);
            } else {
                $message.addClass('bg-yellow-50 text-yellow-700').text(message);
            }
            $message.show();
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

        // Close modal when clicking outside
        $(document).click(function(event) {
            if (event.target.id === 'consultationDetailModal') {
                closeDetailModal();
            }
        });
    </script>
</x-app-layout>
