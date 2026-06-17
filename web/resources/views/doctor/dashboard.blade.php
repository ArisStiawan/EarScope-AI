<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-teal-50 rounded-xl text-teal-600">
                <svg class="w-6 h-6 animate-heartbeat" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                    {{ __('Doctor Dashboard') }}
                </h2>
                <p class="text-xs font-medium text-slate-400 mt-0.5">Summary of patient diagnosis activity and your clinical consultations</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 animate-fade-in-up">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- STATS WIDGETS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Stats 1 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Consultations</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $pendingCount }}</p>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-2xl text-amber-500 group-hover:bg-amber-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Stats 2 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Today's Schedule</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $todayScheduleCount }}</p>
                    </div>
                    <div class="p-4 bg-teal-50 rounded-2xl text-teal-600 group-hover:bg-teal-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Stats 3 -->
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Patients Handled</h3>
                        <p class="text-3xl font-extrabold text-slate-800 mt-2">{{ $patientsHandledCount }}</p>
                    </div>
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600 group-hover:bg-emerald-100/70 transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>

            </div>

            <!-- UPCOMING CONSULTATIONS -->
            <div class="bg-white shadow rounded-lg p-6 overflow-x-auto">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                    <h3 class="font-bold text-lg">Upcoming Consultation</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('doctor.dashboard', ['filter' => 'all']) }}"
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            All
                        </a>
                        <a href="{{ route('doctor.dashboard', ['filter' => 'today']) }}"
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'today' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            Today
                        </a>
                        <a href="{{ route('doctor.dashboard', ['filter' => 'week']) }}"
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'week' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            This Week
                        </a>
                        <a href="{{ route('doctor.dashboard', ['filter' => 'month']) }}"
                           class="px-4 py-2 rounded-md text-sm font-medium {{ $filter === 'month' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            This Month
                        </a>
                    </div>
                </div>

                @if($consultations->isEmpty())
                    <p class="text-gray-500">No scheduled consultations found for the selected period.</p>
                @else
                    <div class="overflow-x-auto rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs text-gray-500 uppercase">
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($consultations as $consultation)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $consultation->patient->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ Str::limit($consultation->complaint, 40) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($consultation->scheduled_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $consultation->scheduled_time ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $statusMap = [
                                                    'pending'   => ['label' => 'Pending',   'class' => 'bg-amber-50 text-amber-700 border-amber-200/50'],
                                                    'scheduled' => ['label' => 'Scheduled','class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50'],
                                                    'cancelled' => ['label' => 'Cancelled', 'class' => 'bg-rose-50 text-rose-700 border-rose-200/50'],
                                                    'done'      => ['label' => 'Done',    'class' => 'bg-sky-50 text-sky-700 border-sky-200/50'],
                                                ];
                                                $s = $statusMap[$consultation->status] ?? ['label' => ucfirst($consultation->status), 'class' => 'bg-slate-50 text-slate-700 border-slate-200/50'];
                                            @endphp
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $s['class'] }}">
                                                {{ $s['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- WAITING FOR APPROVAL -->
            <div class="bg-white shadow rounded-lg p-6 overflow-x-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg">Waiting for Approval</h3>
                    <span class="text-sm text-gray-500">{{ $pendingRequests->count() }} request(s)</span>
                </div>

                @if($pendingRequests->isEmpty())
                    <p class="text-gray-500">No pending consultation requests at the moment.</p>
                @else
                    <div class="overflow-x-auto rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs text-gray-500 uppercase">
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complaint</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested At</th>
                                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingRequests as $request)
                                    <tr class="border-t">
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->patient->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                            {{ $request->patient->email ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ Str::limit($request->complaint, 40) }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            {{ $request->created_at->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($request->status) }}
                                            </span>
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
</x-app-layout>