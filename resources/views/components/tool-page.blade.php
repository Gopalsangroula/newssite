{{-- The frame of a tools page. Takes $heading, and an optional $source line for where the figures come from. --}}
@props(['heading', 'source' => null])

<x-layouts.frontend :title="$heading.' | Libang'">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/site-tools.css') }}?v={{ filemtime(public_path('css/site-tools.css')) }}">
    @endpush

    <div class="lb-page">
        @include('site.partials.page-head', ['title' => $heading])

        <nav class="tool-tabs" aria-label="युटिलिटी">
            @foreach (config('site-tools') as $tool)
                <a href="{{ route($tool['route']) }}" @class(['is-active' => request()->routeIs($tool['route'])])>
                    <span uk-icon="icon: {{ $tool['icon'] }}; ratio: 0.9"></span>{{ $tool['name'] }}
                </a>
            @endforeach
        </nav>

        {{ $slot }}

        @if ($source)
            <p class="tool-note">{{ $source }}</p>
        @endif
    </div>
</x-layouts.frontend>
