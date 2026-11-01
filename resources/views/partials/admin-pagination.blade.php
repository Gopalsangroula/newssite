@if ($paginator->hasPages())
    <nav class="ah-pagination" aria-label="Pagination">
        <span class="ah-pagination-info">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>

        <ul class="uk-pagination uk-margin-remove">
            <li class="{{ $paginator->onFirstPage() ? 'uk-disabled' : '' }}">
                <a href="#" wire:click.prevent="previousPage('{{ $paginator->getPageName() }}')" aria-label="Previous page">
                    <span uk-pagination-previous wire:ignore></span>
                </a>
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="uk-disabled"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li class="{{ $page == $paginator->currentPage() ? 'uk-active' : '' }}" wire:key="paginator-page-{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">{{ $page }}</span>
                            @else
                                <a href="#" wire:click.prevent="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            <li class="{{ $paginator->hasMorePages() ? '' : 'uk-disabled' }}">
                <a href="#" wire:click.prevent="nextPage('{{ $paginator->getPageName() }}')" aria-label="Next page">
                    <span uk-pagination-next wire:ignore></span>
                </a>
            </li>
        </ul>
    </nav>
@endif
