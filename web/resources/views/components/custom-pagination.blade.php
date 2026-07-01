@props(['paginator', 'perPage' => 10])

@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 bg-gray-50">
        <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select onchange="window.location.href = this.value;" class="rounded-lg border-gray-300 text-xs py-1 pl-1.5 pr-6 focus:border-indigo-500 focus:ring-indigo-500/20 bg-white shadow-sm">
                @foreach([5, 10, 15] as $val)
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => $val, 'page' => 1]) }}" @selected($perPage == $val)>
                        {{ $val }}
                    </option>
                @endforeach
            </select>
            <span>data per halaman</span>
        </div>
        
        @if ($paginator->hasPages())
            <div class="flex flex-col sm:flex-row items-center gap-2">
                <div class="text-xs text-gray-500 hidden sm:block">
                    Menampilkan {{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} items
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1 mt-2 sm:mt-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="px-2.5 py-1 text-xs border border-gray-200 text-gray-400 bg-gray-50 rounded-md cursor-not-allowed">Prev</span>
                    @else
                        <a href="{{ $paginator->appends(request()->except('page'))->previousPageUrl() }}" class="px-2.5 py-1 text-xs border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 transition">Prev</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @php
                        $start = max($paginator->currentPage() - 2, 1);
                        $end = min($start + 4, $paginator->lastPage());
                        if ($end - $start < 4) {
                            $start = max($end - 4, 1);
                        }
                    @endphp

                    @for ($i = $start; $i <= $end; $i++)
                        @if ($i == $paginator->currentPage())
                            <span class="px-2.5 py-1 text-xs border border-teal-600 text-white bg-teal-600 rounded-md">{{ $i }}</span>
                        @else
                            <a href="{{ $paginator->appends(request()->except('page'))->url($i) }}" class="px-2.5 py-1 text-xs border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 transition">{{ $i }}</a>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->appends(request()->except('page'))->nextPageUrl() }}" class="px-2.5 py-1 text-xs border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 transition">Next</a>
                    @else
                        <span class="px-2.5 py-1 text-xs border border-gray-200 text-gray-400 bg-gray-50 rounded-md cursor-not-allowed">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
