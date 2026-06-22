@php
    $role = Auth::user() ? Auth::user()->role : null;

    if ($role === 'doctor') {
        $dashboardRouteName = 'doctor.dashboard';
    } elseif ($role === 'patient') {
        $dashboardRouteName = 'patient.dashboard';
    } elseif ($role === 'admin') {
        $dashboardRouteName = 'admin.dashboard';
    } else {
        $dashboardRouteName = 'login'; // fallback aman
    }

    $dashboardRoute = route($dashboardRouteName);
@endphp

<nav x-data="{ open: false }" class="bg-gradient-to-r from-teal-600 to-emerald-600 backdrop-blur-md sticky top-0 z-50 border-b border-teal-100/60 shadow-sm transition-all duration-300">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ $dashboardRoute }}" class="flex items-center gap-2.5 group">
                        <x-application-logo class="h-9 w-9 text-white animate-heartbeat" />
                        <span class="text-lg font-bold text-white group-hover:text-teal-100 transition duration-300">EarScope</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-10 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="$dashboardRoute" :active="request()->routeIs($dashboardRouteName)" class="!text-white">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if($role === 'doctor')
                        <x-nav-link href="/doctor/consultations" :active="request()->is('doctor/consultations*')" class="!text-white">
                            {{ __('Consultation Request') }}
                        </x-nav-link>
                        <x-nav-link href="/doctor/consultation-results" :active="request()->is('doctor/consultation-results*')" class="!text-white">
                            {{ __('Consultation Result') }}
                        </x-nav-link>
                        <x-nav-link href="/doctor/diagnoses" :active="request()->is('doctor/diagnoses*')" class="!text-white">
                            {{ __('Diagnoses') }}
                        </x-nav-link>
                        <x-nav-link href="/doctor/patients-profile" :active="request()->is('doctor/patients-profile*')" class="!text-white">
                            {{ __('Patients Profile') }}
                        </x-nav-link>
                    @elseif($role === 'patient')
                        <x-nav-link href="/patient/create-consultation" :active="request()->is('patient/create-consultation*')" class="!text-white">
                            {{ __('Consultation Request') }}
                        </x-nav-link>
                        <x-nav-link href="/patient/consultation-results" :active="request()->is('patient/consultation-results*')" class="!text-white">
                            {{ __('Consultation Results') }}
                        </x-nav-link>
                    @elseif($role === 'admin')
                        <x-nav-link href="/admin/doctors" :active="request()->is('admin/doctors*')" class="!text-white">
                             {{ __('Doctor Management') }}
                        </x-nav-link>
                        <x-nav-link href="/admin/patients" :active="request()->is('admin/patients*')" class="!text-white">
                             {{ __('Patient Data') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white hover:text-teal-100 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->username }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-white hover:bg-white/20 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-teal-700/50">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="$dashboardRoute" :active="request()->routeIs($dashboardRouteName)" class="!text-white">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if($role === 'doctor')
                <x-responsive-nav-link href="/doctor/consultations" :active="request()->is('doctor/consultations*')" class="!text-white">
                    {{ __('Consultation Request') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="/doctor/consultation-results" :active="request()->is('doctor/consultation-results*')" class="!text-white">
                    {{ __('Hasil Konsultasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="/doctor/diagnoses" :active="request()->is('doctor/diagnoses*')" class="!text-white">
                    {{ __('Hasil Pemeriksaan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="/doctor/patients-profile" :active="request()->is('doctor/patients-profile*')" class="!text-white">
                    {{ __('Patients Profile') }}
                </x-responsive-nav-link>
            @elseif($role === 'patient')
                <x-responsive-nav-link href="/patient/create-consultation" :active="request()->is('patient/create-consultation*')" class="!text-white">
                    {{ __('Consultation Request') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="/patient/diagnoses" :active="request()->is('patient/diagnoses*')" class="!text-white">
                    {{ __('Hasil Pemeriksaan') }}
                </x-responsive-nav-link>
            @elseif($role === 'admin')
                <x-responsive-nav-link href="/admin/doctors" :active="request()->is('admin/doctors*')" class="!text-white">
                    {{ __('Doctor Management') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="/admin/patients" :active="request()->is('admin/patients*')" class="!text-white">
                    {{ __('Patient Data') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-white/20">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->username }}</div>
                <div class="font-medium text-sm text-white/70 capitalize">{{ Auth::user()->role }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="!text-white">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" class="!text-white"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
