@if ($galleries->isNotEmpty())
    <section class="hp-band">
        <div class="hp-wrap">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'फोटो ग्यालरी', 'url' => '/photo-gallery'])

            <div class="hp-gallery" data-count="{{ $galleries->count() }}">
                @foreach ($galleries as $post)
                    <a class="hp-gallery__tile" href="{{ route('frontend.details.show', $post->slug) }}">
                        @if ($post->cover_image)
                            <img src="{{ Storage::url($post->cover_image) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        @endif
                        <span>{{ $post->title }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
