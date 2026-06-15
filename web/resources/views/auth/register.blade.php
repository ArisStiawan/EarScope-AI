<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" value="Username" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Nama -->
        <div class="mt-4">
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Tanggal Lahir -->
        <div class="mt-4">
            <x-input-label for="birth_date" value="Tanggal Lahir" />
            <x-text-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date" required />
            <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
        </div>

        <!-- Alamat -->
        <div class="mt-4">
            <x-input-label for="address" value="Alamat" />
            <textarea name="address" class="block mt-1 w-full border-gray-300 rounded-md" required>{{ old('address') }}</textarea>
            <x-input-error :messages="$errors->get('address')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Gender -->
        <div class="mt-4">
            <x-input-label for="gender" value="Gender" />
            <select name="gender" class="block mt-1 w-full border-gray-300 rounded-md" required>
                <option value="">-- Pilih --</option>
                <option value="male">Laki-laki</option>
                <option value="female">Perempuan</option>
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                          type="password"
                          name="password_confirmation"
                          required />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                Sudah punya akun?
            </a>

            <x-primary-button class="ms-4">
                Register
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>