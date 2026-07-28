<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profil Pasien
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if($patients->isEmpty())
                    <p class="text-gray-500">Tidak ada pasien ditemukan</p>
                @else

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                    <th class="px-4 py-2">Nama</th>
                                    <th class="px-4 py-2">Jenis Kelamin</th>
                                    <th class="px-4 py-2">Alamat</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($patients as $patient)
                                    <tr class="border-t hover:bg-slate-50 transition">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-teal-50 border border-teal-100 flex items-center justify-center shrink-0">
                                                    <span class="text-teal-700 text-xs font-bold">{{ strtoupper(substr($patient->name ?? 'P', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">{{ $patient->name ?? '-' }}</p>
                                                    <p class="text-[10px] text-indigo-500 font-bold">{{ $patient->medical_record_number ?? '-' }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-4 py-3 text-sm text-gray-700 capitalize">
                                            {{ $patient->gender === 'male' ? 'Laki-laki' : ($patient->gender === 'female' ? 'Perempuan' : ($patient->gender ?? '-')) }}
                                        </td>

                                        <td class="px-4 py-3 text-sm text-gray-700 max-w-xs truncate">
                                            {{ $patient->address ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $patient->user->email ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3">
                                            <a href="{{ route('doctor.patient.history', $patient->id) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Riwayat Pemeriksaan
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                        <x-custom-pagination :paginator="$patients" :perPage="$perPage" />
                    </div>

                @endif

            </div>
        </div>
    </div>
</x-app-layout>