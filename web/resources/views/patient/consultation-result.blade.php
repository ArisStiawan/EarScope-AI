<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Consultation Results') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ __('Hasil Konsultasi') }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Daftar konsultasi yang sudah selesai</p>
                        </div>
                    </div>

                    @if($consultations->isEmpty())
                        <div class="text-center py-14">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-3 text-sm text-gray-500">Belum ada hasil konsultasi tersedia.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokter</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keluhan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hasil Diagnosis</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($consultations as $i => $consultation)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                dr. {{ $consultation->doctor->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700 max-w-xs">
                                                {{ Str::limit($consultation->complaint, 60) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($consultation->scheduled_date)
                                                    <div class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}</div>
                                                    <div class="text-xs text-gray-400">{{ $consultation->scheduled_time ?? '' }}</div>
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                                <button type="button" onclick="openResultModal('{{ $consultation->id }}')" class="text-indigo-600 hover:text-indigo-900 underline">
                                                    View
                                                </button>
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

    <!-- Consultation Result Modal -->
    <div id="consultationResultModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Hasil Konsultasi</h3>
                        <button type="button" onclick="closeResultModal()" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div id="resultModalContent" class="mt-4">
                        <div class="text-center">
                            <p class="text-gray-500">Loading...</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="closeResultModal()" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openResultModal(consultationId) {
            $.ajax({
                url: '/patient/consultation/' + consultationId + '/details',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let statusBadge = getBadgeClass(data.status);
                    let diagnosisSection = data.diagnosis ? `
                        <div class="border-b pb-4">
                            <h4 class="font-semibold text-gray-900 mb-3">Diagnosis</h4>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Result</p>
                                <p class="mt-1 text-sm text-gray-900">${data.diagnosis.result}</p>
                            </div>
                            ${data.diagnosis.notes ? `
                            <div class="mt-4">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</p>
                                <p class="mt-1 text-sm text-gray-900">${data.diagnosis.notes}</p>
                            </div>
                            ` : ''}
                        </div>
                    ` : `
                        <div class="border-b pb-4">
                            <p class="text-sm text-gray-500">Tidak ada hasil diagnosis tersedia.</p>
                        </div>
                    `;

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

                            ${diagnosisSection}

                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Request Status</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</p>
                                        <p class="mt-1"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusBadge}">${
                                            data.status.charAt(0).toUpperCase() + data.status.slice(1)
                                        }</span></p>
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

                    $('#resultModalContent').html(content);
                },
                error: function() {
                    $('#resultModalContent').html('<p class="text-red-600">Gagal memuat detail hasil konsultasi.</p>');
                }
            });

            $('#consultationResultModal').removeClass('hidden');
        }

        function closeResultModal() {
            $('#consultationResultModal').addClass('hidden');
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

        $(document).click(function(event) {
            if (event.target.id === 'consultationResultModal') {
                closeResultModal();
            }
        });
    </script>
</x-app-layout>
