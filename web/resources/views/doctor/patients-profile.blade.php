<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Patients Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if($patients->isEmpty())
                    <p class="text-gray-500">No patients found</p>
                @else

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">
                                <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Gender</th>
                                    <th class="px-4 py-2">Address</th>
                                    <th class="px-4 py-2">Email</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($patients as $patient)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">
                                            {{ $patient->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $patient->gender ?? '-' }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $patient->address ?? '-' }}
                                        </td>

                                        <td class="px-4 py-2">
                                            {{ $patient->user->email ?? '-' }}
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