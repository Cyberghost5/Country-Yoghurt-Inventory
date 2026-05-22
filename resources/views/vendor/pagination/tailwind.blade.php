@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="pag-nav">

        {{-- Results summary --}}
        <span class="pag-summary">
            Showing
            @if ($paginator->firstItem())
                <strong>{{ $paginator->firstItem() }}</strong> &ndash;
                <strong>{{ $paginator->lastItem() }}</strong>
            @else
                {{ $paginator->count() }}
            @endif
            of <strong>{{ $paginator->total() }}</strong> results
        </span>

        {{-- Page links --}}
        <div class="pag-links">

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="pag-btn pag-btn--disabled" aria-disabled="true">
                    <i class="bi bi-chevron-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pag-btn" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pag-btn pag-btn--dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pag-btn pag-btn--active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pag-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pag-btn" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            @else
                <span class="pag-btn pag-btn--disabled" aria-disabled="true">
                    <i class="bi bi-chevron-right"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
