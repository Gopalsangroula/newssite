<x-tool-page heading="युटिलिटी">
    <div class="tool-grid">
        @foreach (config('site-tools') as $tool)
            <a class="tool-card" href="{{ route($tool['route']) }}">
                <span class="tool-card__icon" uk-icon="icon: {{ $tool['icon'] }}; ratio: 1.3"></span>
                <strong>{{ $tool['name'] }}</strong>
                <span>{{ $tool['text'] }}</span>
            </a>
        @endforeach
    </div>
</x-tool-page>
