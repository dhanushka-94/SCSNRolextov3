@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted">
            Showing
            <span class="font-semibold text-ink">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-semibold text-ink">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-semibold text-ink">{{ $paginator->total() }}</span>
        </p>

        <div class="flex flex-wrap gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn-secondary cursor-not-allowed px-3 py-1.5 opacity-50">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary px-3 py-1.5">Prev</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-2 py-1.5 text-sm text-muted">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-primary px-3 py-1.5">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn-secondary px-3 py-1.5">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary px-3 py-1.5">Next</a>
            @else
                <span class="btn-secondary cursor-not-allowed px-3 py-1.5 opacity-50">Next</span>
            @endif
        </div>
    </nav>
@endif
