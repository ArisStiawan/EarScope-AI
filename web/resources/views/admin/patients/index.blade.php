@extends('admin.layouts.admin')

@section('admin-content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Data Pasien</h1>
        <p class="text-gray-600 mt-2">Daftar seluruh pasien yang telah terdaftar di sistem</p>
    </div>
    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
        Total: {{ $patients->count() }} Pasien
    </span>
</div>

<!-- Patients Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        @if($patients->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Username</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Tanggal Registrasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($patients as $key => $patient)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $key + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full bg-green-100 flex items-center justify-center">
                                        <span class="text-green-700 font-semibold text-sm">
                                            {{ strtoupper(substr($patient->username, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $patient->username }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-900">{{ $patient->created_at->format('d M Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $patient->created_at->format('H:i') }} WIB</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada pasien</h3>
                <p class="mt-1 text-sm text-gray-500">Belum ada pasien yang melakukan registrasi</p>
            </div>
        @endif
    </div>
</div>
@endsection
