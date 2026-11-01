@if ($ticker['posts']->isNotEmpty())
    <section class="hp-ticker" aria-label="{{ $ticker['label'] }}">
        <div class="hp-wrap hp-ticker__row">
            <span class="hp-ticker__label">{{ $ticker['label'] }}</span>

            <div class="hp-ticker__window">
                {{-- The list runs twice so the loop has no gap. Readers and keyboards only get the first. --}}
                <div class="hp-ticker__track" style="--hp-ticker-time: {{ max(24, $ticker['posts']->count() * 8) }}s">
                    @foreach ([false, true] as $isCopy)
                        <ul class="hp-ticker__list" @if ($isCopy) aria-hidden="true" @endif>
                            @foreach ($ticker['posts'] as $post)
                                <li><a href="{{ route('frontend.details.show', $post->slug) }}" @if ($isCopy) tabindex="-1" @endif>{{ $post->title }}</a></li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
