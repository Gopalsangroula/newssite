{{-- The site's custom-pagination buttons for the public pages. Links stay real URLs; Livewire takes over the click. --}}
@php
    // During a Livewire update the current URL is /livewire/update, so point the links at the page itself.
    $paginator->withPath(\Livewire\Livewire::originalUrl());
    $pageName = $paginator->getPageName();
    $backToList = "this.closest('.lb-results').scrollIntoView({behavior: 'smooth'})";
@endphp

@if ($paginator->hasPages())
    <nav class="lb-pagination" aria-label="पृष्ठहरू">
        <ul class="uk-pagination custom-pagination">
            <li @class(['uk-disabled' => $paginator->onFirstPage()])>
                @if ($paginator->onFirstPage())
                    <span class="pagination-btn pagination-arrow" aria-hidden="true">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 1 1 7l6 6"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->url($paginator->currentPage() - 1) }}"
                       class="pagination-btn pagination-arrow"
                       rel="prev"
                       aria-label="अघिल्लो पृष्ठ"
                       wire:click.prevent="previousPage('{{ $pageName }}')"
                       onclick="{{ $backToList }}">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 1 1 7l6 6"/></svg>
                    </a>
                @endif
            </li>

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="uk-disabled"><span class="pagination-dots">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="uk-active">
                                <span class="pagination-btn pagination-active" aria-current="page">{{ $page }}</span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $paginator->url($page) }}"
                                   class="pagination-btn"
                                   wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')"
                                   onclick="{{ $backToList }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            <li @class(['uk-disabled' => ! $paginator->hasMorePages()])>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->url($paginator->currentPage() + 1) }}"
                       class="pagination-btn pagination-arrow"
                       rel="next"
                       aria-label="अर्को पृष्ठ"
                       wire:click.prevent="nextPage('{{ $pageName }}')"
                       onclick="{{ $backToList }}">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m1 1 6 6-6 6"/></svg>
                    </a>
                @else
                    <span class="pagination-btn pagination-arrow" aria-hidden="true">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m1 1 6 6-6 6"/></svg>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
