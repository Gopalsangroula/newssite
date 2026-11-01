{{-- Prints nothing unless an advertisement is running for $placement. --}}
@foreach ($ads->get($placement, []) as $ad)
    <aside class="hp-ad hp-ad--{{ $placement }}" aria-label="विज्ञापन">
        @if ($ad->type === 'image')
            <a href="{{ $ad->target_url ?: '#' }}" target="_blank" rel="noopener sponsored">
                <img src="{{ $ad->full_image_url }}" alt="{{ $ad->title }}" loading="lazy" decoding="async">
            </a>
        @elseif ($ad->type === 'script')
            {!! $ad->content !!}
        @endif
    </aside>
@endforeach
