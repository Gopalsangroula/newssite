@props([
    'paginator',
])

@if ($paginator->hasPages())
    <div class="uk-margin-large-top uk-margin-large-bottom"
         wire:key="genealogy-pagination-{{ $paginator->currentPage() }}">

        <ul class="uk-pagination uk-flex-center uk-flex-middle">
            {{-- Previous --}}
            <li class="{{ $paginator->onFirstPage() ? 'uk-disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span uk-pagination-previous></span>
                @else
                    <a href="#"
                       wire:click.prevent="previousPage"
                       wire:loading.attr="disabled"
                       aria-label="Previous page">
                        <span uk-pagination-previous></span>
                    </a>
                @endif
            </li>

            {{-- First page + ellipsis --}}
            @if ($paginator->currentPage() > 3)
                <li>
                    <a href="#" wire:click.prevent="gotoPage(1)" wire:loading.attr="disabled">1</a>
                </li>
                @if ($paginator->currentPage() > 4)
                    <li class="uk-disabled"><span>…</span></li>
                @endif
            @endif

            {{-- Page window (current ± 2) --}}
            @foreach (range(
                max(1, $paginator->currentPage() - 2),
                min($paginator->lastPage(), $paginator->currentPage() + 2)
            ) as $page)
                <li class="{{ $page === $paginator->currentPage() ? 'uk-active' : '' }}">
                    @if ($page === $paginator->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="#"
                           wire:click.prevent="gotoPage({{ $page }})"
                           wire:loading.attr="disabled">
                            {{ $page }}
                        </a>
                    @endif
                </li>
            @endforeach

            {{-- Last page + ellipsis --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                    <li class="uk-disabled"><span>…</span></li>
                @endif
                <li>
                    <a href="#"
                       wire:click.prevent="gotoPage({{ $paginator->lastPage() }})"
                       wire:loading.attr="disabled">
                        {{ $paginator->lastPage() }}
                    </a>
                </li>
            @endif

            {{-- Next --}}
            <li class="{{ $paginator->hasMorePages() ? '' : 'uk-disabled' }}">
                @if ($paginator->hasMorePages())
                    <a href="#"
                       wire:click.prevent="nextPage"
                       wire:loading.attr="disabled"
                       aria-label="Next page">
                        <span uk-pagination-next></span>
                    </a>
                @else
                    <span uk-pagination-next></span>
                @endif
            </li>
        </ul>

        {{-- Info text --}}
        <div class="uk-text-center uk-text-small uk-text-muted uk-margin-small-top">
            परिवार
            <strong>{{ number_format($paginator->firstItem() ?? 0) }}</strong>
            देखि
            <strong>{{ number_format($paginator->lastItem() ?? 0) }}</strong>
            सम्म देखाइँदैछ
            — कुल
            <strong>{{ number_format($paginator->total()) }}</strong>
            परिवार
            <span class="uk-margin-small-left">
                (पृष्ठ {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }})
            </span>
        </div>

        {{-- Quick jump --}}
        @if ($paginator->lastPage() > 1)
            <div class="uk-text-center uk-margin-small-top">
                <div class="uk-inline">
                    <span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: chevron-down"></span>
                    <select
                        wire:model.live="page"
                        class="uk-select uk-form-width-small"
                        aria-label="Jump to page"
                    >
                        @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                            <option value="{{ $i }}">पृष्ठ {{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
        @endif
    </div>
@endif