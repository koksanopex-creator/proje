@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Sayfalama" class="koksan-pagination">
        {{-- Önceki Sayfa Butonu --}}
        @if ($paginator->onFirstPage())
            <span class="page-item disabled" aria-disabled="true" aria-label="Önceki">
                <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="page-item" rel="prev" aria-label="Önceki">
                <span class="page-link">&lsaquo;</span>
            </a>
        @endif

        {{-- Sayfa Numaraları --}}
        @foreach ($elements as $element)
            {{-- Üç Nokta Ayırıcı --}}
            @if (is_string($element))
                <span class="page-item disabled" aria-disabled="true"><span class="page-link dots">{{ $element }}</span></span>
            @endif

            {{-- Sayfa Linkleri Dizisi --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></span>
                    @else
                        <a href="{{ $url }}" class="page-item"><span class="page-link">{{ $page }}</span></a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Sonraki Sayfa Butonu --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="page-item" rel="next" aria-label="Sonraki">
                <span class="page-link">&rsaquo;</span>
            </a>
        @else
            <span class="page-item disabled" aria-disabled="true" aria-label="Sonraki">
                <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </span>
        @endif
    </nav>
@endif
