{{-- Stat Card Component --}}
<div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-600 text-sm font-medium">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $value }}</p>
        </div>
        <div class="@if($color === 'blue') bg-blue-100 @elseif($color === 'green') bg-green-100 @elseif($color === 'purple') bg-purple-100 @else bg-gray-100 @endif rounded-full p-4">
            @if($icon === 'doctor')
                <svg class="w-8 h-8 @if($color === 'blue') text-blue-600 @elseif($color === 'green') text-green-600 @elseif($color === 'purple') text-purple-600 @else text-gray-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            @elseif($icon === 'patient')
                <svg class="w-8 h-8 @if($color === 'blue') text-blue-600 @elseif($color === 'green') text-green-600 @elseif($color === 'purple') text-purple-600 @else text-gray-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M9 13h6m-3-8v.01M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9z"></path>
                </svg>
            @elseif($icon === 'consultation')
                <svg class="w-8 h-8 @if($color === 'blue') text-blue-600 @elseif($color === 'green') text-green-600 @elseif($color === 'purple') text-purple-600 @else text-gray-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            @endif
        </div>
    </div>
    @if($description ?? null)
        <p class="text-gray-500 text-xs mt-4">{{ $description }}</p>
    @endif
</div>
