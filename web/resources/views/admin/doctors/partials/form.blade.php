{{-- Shared Doctor Form for Create & Edit --}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <!-- Username (Read-only for edit) -->
    <div>
        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
        @if($edit)
            <input type="text" id="username" value="{{ $doctor->user->username }}" disabled class="mt-1 block w-full rounded-md bg-gray-100 border-gray-300 shadow-sm py-2 px-3 text-gray-600" />
        @else
            <input type="text" id="username" name="username" value="{{ old('username') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
            @error('username')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        @endif
    </div>

    <!-- Password (Only for create) -->
    @unless($edit)
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" id="password" name="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endunless

    <!-- Nama Dokter -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Dokter</label>
        <input type="text" id="name" name="name" value="{{ old('name', $doctor->name ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- License Number -->
    <div>
        <label for="license_number" class="block text-sm font-medium text-gray-700">Nomor Lisensi (STR)</label>
        <input type="text" id="license_number" name="license_number" value="{{ old('license_number', $doctor->license_number ?? '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
        @error('license_number')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Practice Hours -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="practice_start_time" class="block text-sm font-medium text-gray-700">Jam Mulai Praktik</label>
            <input type="time" id="practice_start_time" name="practice_start_time" value="{{ old('practice_start_time', (isset($doctor->practice_start_time) ? \Carbon\Carbon::parse($doctor->practice_start_time)->format('H:i') : '')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
            @error('practice_start_time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="practice_end_time" class="block text-sm font-medium text-gray-700">Jam Selesai Praktik</label>
            <input type="time" id="practice_end_time" name="practice_end_time" value="{{ old('practice_end_time', (isset($doctor->practice_end_time) ? \Carbon\Carbon::parse($doctor->practice_end_time)->format('H:i') : '')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500" />
            @error('practice_end_time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Kuota Pasien -->
    <div>
        <label for="patient_quota" class="block text-sm font-medium text-gray-700">Kuota Pasien / Hari</label>
        <input type="number" id="patient_quota" name="patient_quota" min="0"
            value="{{ old('patient_quota', $doctor->patient_quota ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500"
            placeholder="Kosongkan untuk hitung otomatis dari jam praktik" />
        <p class="mt-1 text-xs text-gray-500">
            Jika dikosongkan, kuota dihitung otomatis: <strong>3 pasien × jumlah jam praktik</strong>.
            @if(isset($doctor) && $doctor->practice_start_time && $doctor->practice_end_time)
                Estimasi otomatis: <strong>{{ $doctor->patient_quota }} pasien</strong>.
            @endif
        </p>
        @error('patient_quota')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Gender -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
        <div class="space-y-2">
            <div class="flex items-center">
                <input type="radio" id="gender_male" name="gender" value="male" @checked(old('gender', $doctor->gender ?? '') === 'male') required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                <label for="gender_male" class="ml-2 block text-sm text-gray-700">Laki-laki</label>
            </div>
            <div class="flex items-center">
                <input type="radio" id="gender_female" name="gender" value="female" @checked(old('gender', $doctor->gender ?? '') === 'female') required class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" />
                <label for="gender_female" class="ml-2 block text-sm text-gray-700">Perempuan</label>
            </div>
        </div>
        @error('gender')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Buttons -->
    <div class="flex gap-3 pt-6">
        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-md transition hover:from-teal-700 hover:to-emerald-700">
            {{ $submit_label ?? 'Simpan' }}
        </button>
        <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
            Batal
        </a>
    </div>
</form>
