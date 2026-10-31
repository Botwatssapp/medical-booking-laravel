@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="w-full">
        <div class="sc-pager-compact">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] cursor-not-allowed" aria-disabled="true" aria-label="Page précédente">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#434655] bg-[#f2f3ff] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors" aria-label="Page précédente">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                </a>
            @endif

            <p class="text-[13px] leading-5 font-semibold text-[#131b2e] whitespace-nowrap">
                Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </p>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#434655] bg-[#f2f3ff] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors" aria-label="Page suivante">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] cursor-not-allowed" aria-disabled="true" aria-label="Page suivante">
                    <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                </span>
            @endif
        </div>

        <ul class="sc-pager-full flex-wrap items-center justify-center gap-1.5">
            <li class="shrink-0">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] cursor-not-allowed" aria-disabled="true" aria-label="Page précédente">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#434655] bg-[#f2f3ff] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors" aria-label="Page précédente">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_left</span>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="shrink-0">
                        <span class="inline-flex items-center justify-center w-10 h-10 text-[13px] font-semibold text-[#737686]" aria-hidden="true">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="shrink-0">
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-[#0b1220] text-white text-[13px] font-semibold" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-[#f2f3ff] text-[#434655] text-[13px] font-semibold hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors" aria-label="Aller à la page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="shrink-0">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#434655] bg-[#f2f3ff] hover:bg-[#e2e7ff] hover:text-[#131b2e] transition-colors" aria-label="Page suivante">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] cursor-not-allowed" aria-disabled="true" aria-label="Page suivante">
                        <span class="material-symbols-outlined text-[20px]" aria-hidden="true">chevron_right</span>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
