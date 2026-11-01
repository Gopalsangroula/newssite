<aside class="hp-layout__rail" aria-label="ताजा र लोकप्रिय">
    @if ($latestPosts->isNotEmpty())
        <section class="hp-rail-block">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'भर्खरै', 'url' => '/news', 'icon' => 'clock'])

            <ul class="hp-latest">
                @foreach ($latestPosts as $post)
                    <li><a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif

    @include('livewire.frontend.home-parts.ad-slot', ['placement' => 'sidebar'])

    @if ($popularPosts->count() >= 3)
        <section class="hp-rail-block">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'ट्रेन्डिङ', 'url' => '/news', 'icon' => 'bolt'])

            <ol class="hp-rank">
                @foreach ($popularPosts as $post)
                    <li><a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a></li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($opinions->isNotEmpty())
        <section class="hp-rail-block">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'दृष्टिकोण', 'url' => '/drishtikon', 'icon' => 'quote-right'])

            <ul class="hp-opinions">
                @foreach ($opinions as $post)
                    <li>
                        <span class="hp-opinions__photo" aria-hidden="true">
                            @if ($post->cover_image)
                                <img src="{{ Storage::url($post->cover_image) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                            @endif
                        </span>
                        <div>
                            <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a>
                            @if ($post->author?->name)
                                <span class="hp-meta">{{ $post->author->name }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="hp-rail-block">
        @include('livewire.frontend.home-parts.section-head', ['title' => 'युटिलिटी', 'url' => '/tools', 'icon' => 'thumbnails'])

        <ul class="hp-tools">
            @foreach (config('site-tools') as $tool)
                <li>
                    <a href="{{ route($tool['route']) }}"><span uk-icon="icon: {{ $tool['icon'] }}; ratio: 0.9"></span>{{ $tool['name'] }}</a>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="hp-fund hp-rail-block" aria-label="अक्षयकोष">
        <p class="hp-kicker hp-kicker--gold">अक्षयकोष</p>
        <h2 class="hp-fund__title">शैक्षिक अक्षयकोषमा सहयोग गर्नुहोस्</h2>
        <p class="hp-fund__text">लिबाङ समुदायको शिक्षा र भविष्यका लागि तपाईंको सहयोग महत्त्वपूर्ण छ।</p>
        <div class="hp-fund__actions">
            <a class="hp-fund__button" href="/pages/donate">सहयोग गर्नुहोस्</a>
            <a class="hp-more hp-more--light" href="/pages/endowment-fund">थप जानकारी</a>
        </div>
    </section>
</aside>
