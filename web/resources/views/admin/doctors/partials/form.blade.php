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

    <!-- Specialization -->
    <div>
        <label for="specialization" class="block text-sm font-medium text-gray-700">Spesialisasi</label>
        <select id="specialization" name="specialization" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm py-2 px-3 border focus:border-blue-500 focus:ring-blue-500">
            <option value="">-- Pilih Spesialisasi --</option>
            <option value="Umum" @selected(old('specialization', $doctor->specialization ?? '') === 'Umum')>Umum</option>
            <option value="Gigi" @selected(old('specialization', $doctor->specialization ?? '') === 'Gigi')>Gigi</option>
            <option value="Anak" @selected(old('specialization', $doctor->specialization ?? '') === 'Anak')>Anak</option>
            <option value="Kandungan" @selected(old('specialization', $doctor->specialization ?? '') === 'Kandungan')>Kandungan</option>
            <option value="Jantung" @selected(old('specialization', $doctor->specialization ?? '') === 'Jantung')>Jantung</option>
            <option value="Saraf" @selected(old('specialization', $doctor->specialization ?? '') === 'Saraf')>Saraf</option>
        </select>
        @error('specialization')
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
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
            {{ $submit_label ?? 'Simpan' }}
        </button>
        <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition">
            Batal
        </a>
    </div>
</form>
