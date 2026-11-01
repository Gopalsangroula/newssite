@if ($paginator->hasPages())
    <div class="uk-flex uk-flex-center uk-margin-large-top">
        <ul class="uk-pagination custom-pagination" uk-margin>

            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="uk-disabled">
                    <span class="pagination-btn">
                        <span uk-pagination-previous></span>
                    </span>
                </li>
            @else
                <li>
                    <a href="#"
                       class="pagination-btn pagination-arrow"
                       wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')"
                       wire:loading.attr="disabled">
                        <span uk-pagination-previous></span>
                    </a>
                </li>
            @endif


            {{-- Pagination Elements --}}
            @foreach ($elements as $element)

                {{-- Separator --}}
                @if (is_string($element))
                    <li class="uk-disabled">
                        <span class="pagination-dots">
                            {{ $element }}
                        </span>
                    </li>
                @endif


                {{-- Page Numbers --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)

                        @if ($page == $paginator->currentPage())
                            <li class="uk-active">
                                <span class="pagination-btn pagination-active">
                                    {{ $page }}
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="#"
                                   class="pagination-btn"
                                   wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif

                    @endforeach
                @endif

            @endforeach


            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="#"
                       class="pagination-btn pagination-arrow"
                       wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')"
                       wire:loading.attr="disabled">
                        <span uk-pagination-next></span>
                    </a>
                </li>
            @else
                <li class="uk-disabled">
                    <span class="pagination-btn">
                        <span uk-pagination-next></span>
                    </span>
                </li>
            @endif

        </ul>
    </div>
@endif