@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        {{-- Mobile View --}}
        <div class="flex items-center justify-between sm:hidden">
            <div class="flex-1">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 rounded-xl hover:bg-teal-100 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </a>
                @endif
            </div>

            <div class="flex-shrink-0 px-3">
                <span class="text-xs font-bold text-slate-500">
                    {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>
            </div>

            <div class="flex-1 text-right">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-teal-700 bg-teal-50 border border-teal-100 rounded-xl hover:bg-teal-100 transition-all duration-200">
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed">
                        Next
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between">

            {{-- Results Info --}}
            <div>
                <p class="text-xs font-medium text-slate-400">
                    Showing
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-600">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-bold text-slate-600">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    of
                    <span class="font-bold text-slate-600">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            {{-- Page Numbers --}}
            <div>
                <span class="inline-flex items-center gap-1">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                            <span class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                           class="inline-flex items-center justify-center w-9 h-9 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all duration-200"
                           aria-label="{{ __('pagination.previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-slate-300 cursor-default">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center justify-center w-9 h-9 text-xs font-bold text-white bg-gradient-to-r from-teal-600 to-emerald-600 rounded-xl shadow-md shadow-teal-500/20 cursor-default">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="inline-flex items-center justify-center w-9 h-9 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all duration-200"
                                       aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                           class="inline-flex items-center justify-center w-9 h-9 text-slate-500 bg-white border border-slate-200 rounded-xl hover:bg-teal-50 hover:text-teal-600 hover:border-teal-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all duration-200"
                           aria-label="{{ __('pagination.next') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                            <span class="inline-flex items-center justify-center w-9 h-9 text-slate-300 bg-slate-50 border border-slate-100 rounded-xl cursor-not-allowed" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
