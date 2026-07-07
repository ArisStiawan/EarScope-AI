<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profil Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Detailed information based on your account.") }}
        </p>
    </header>

    <div class="mt-6 space-y-6">
        @if ($user->role === 'admin')
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <p class="text-sm text-gray-700"><strong>Role:</strong> Administrator</p>
                <p class="text-sm text-gray-500 mt-1">Anda memiliki akses penuh ke seluruh fitur pengelolaan sistem (Dashboard Admin).</p>
            </div>
        @elseif ($user->role === 'doctor' && $user->doctor)
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-blue-500 font-semibold uppercase">Nama Lengkap</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $user->doctor->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-500 font-semibold uppercase">Nomor Izin Praktik (STR)</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $user->doctor->license_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-500 font-semibold uppercase">Jenis Kelamin</p>
                        <p class="text-sm text-gray-900 font-medium">{{ ucfirst($user->doctor->gender) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-500 font-semibold uppercase">Jadwal Praktik</p>
                        <p class="text-sm text-gray-900 font-medium">
                            {{ $user->doctor->practice_start_time ? substr($user->doctor->practice_start_time, 0, 5) : '-' }} 
                            s.d 
                            {{ $user->doctor->practice_end_time ? substr($user->doctor->practice_end_time, 0, 5) : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-blue-500 font-semibold uppercase">Kuota Pasien / Hari</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $user->doctor->patient_quota }} Pasien</p>
                    </div>
                </div>
            </div>
        @elseif ($user->role === 'patient' && $user->patient)
            <div class="bg-teal-50 p-4 rounded-lg border border-teal-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-teal-600 font-semibold uppercase">Nomor Rekam Medis (RM)</p>
                        <p class="text-sm text-gray-900 font-bold">{{ $user->patient->medical_record_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-teal-600 font-semibold uppercase">Nama Lengkap</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $user->patient->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-teal-600 font-semibold uppercase">Tanggal Lahir / Umur</p>
                        <p class="text-sm text-gray-900 font-medium">{{ \Carbon\Carbon::parse($user->patient->birth_date)->format('d M Y') }} ({{ $user->patient->age }} Tahun)</p>
                    </div>
                    <div>
                        <p class="text-xs text-teal-600 font-semibold uppercase">Jenis Kelamin</p>
                        <p class="text-sm text-gray-900 font-medium">{{ ucfirst($user->patient->gender) }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs text-teal-600 font-semibold uppercase">Alamat</p>
                        <p class="text-sm text-gray-900 font-medium">{{ $user->patient->address }}</p>
                    </div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500">Profil spesifik belum dilengkapi.</p>
        @endif
    </div>
</section>
