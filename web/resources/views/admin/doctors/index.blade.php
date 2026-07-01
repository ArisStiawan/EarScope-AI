@extends('admin.layouts.admin')

@section('admin-content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in-up">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Doctor Data Management</h1>
        <p class="text-xs text-slate-400 font-medium mt-1">Manage doctor data, usernames, license numbers, and specializations</p>
    </div>
    
    <a href="{{ route('admin.doctors.create') }}" 
       class="inline-flex items-center gap-2 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-xs uppercase tracking-wider transition-all duration-300 shadow-md shadow-teal-500/10 hover:shadow-teal-500/35 transform hover:-translate-y-0.5 active:translate-y-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New Doctor
    </a>
</div>

<!-- Doctors Table -->
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden animate-fade-in-up" style="animation-delay: 0.1s;">
    <div class="overflow-x-auto">
        @if($doctors->count() > 0)
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Doctor Name</th>
                        <th class="px-6 py-4">Account Username</th>
                        <th class="px-6 py-4">STR License Number</th>
                        <th class="px-6 py-4">Specialization</th>
                        <th class="px-6 py-4">Gender</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @foreach($doctors as $key => $doctor)
                        <tr class="hover:bg-slate-50/40 transition-colors duration-250">
                            <td class="px-6 py-4 text-slate-400 font-medium">{{ ($doctors->currentPage() - 1) * $doctors->perPage() + $key + 1 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-teal-50 flex items-center justify-center shrink-0 border border-teal-100/30">
                                        <span class="text-teal-700 text-xs font-bold">
                                            {{ strtoupper(substr($doctor->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <span class="font-bold text-slate-800">dr. {{ $doctor->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                {{ $doctor->user->username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-md border bg-teal-50 text-teal-700 border-teal-200/50">
                                    {{ $doctor->license_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $doctor->specialization ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 font-medium">
                                {{ $doctor->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold uppercase tracking-wider">
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="text-teal-600 hover:text-teal-700 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.doctors.delete', $doctor) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this doctor from the system?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-500 hover:text-rose-600 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <x-custom-pagination :paginator="$doctors" :perPage="$perPage" />
        @else
            <div class="px-6 py-16 text-center">
                <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 animate-float">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-700">No Doctor Data Yet</h3>
                <p class="text-xs text-slate-400 mt-1">The system has not registered any specialist doctor accounts.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.doctors.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-xs font-semibold rounded-xl text-white bg-teal-600 hover:bg-teal-700 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Doctor
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
