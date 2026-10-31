@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center h-10 px-4 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] text-[13px] font-semibold cursor-not-allowed">Précédent</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-[#f2f3ff] text-[#434655] text-[13px] font-semibold hover:bg-[#e2e7ff] hover:text-[#131b2e]">Précédent</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center h-10 px-4 rounded-lg bg-[#f2f3ff] text-[#434655] text-[13px] font-semibold hover:bg-[#e2e7ff] hover:text-[#131b2e]">Suivant</a>
        @else
            <span class="inline-flex items-center justify-center h-10 px-4 rounded-lg text-[#c3c6d7] bg-[#f2f3ff] text-[13px] font-semibold cursor-not-allowed">Suivant</span>
        @endif
    </nav>
@endif
