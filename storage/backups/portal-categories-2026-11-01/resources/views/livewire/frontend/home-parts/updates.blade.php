<section class="hp-band">
    <div class="hp-wrap hp-updates">
        <div class="hp-updates__notices">
            @if ($notices->isNotEmpty())
                <div>
                    @include('livewire.frontend.home-parts.section-head', ['title' => 'सूचना', 'url' => '/notice'])

                    <ul class="hp-board">
                        @foreach ($notices as $post)
                            @php
                                $noticeDate = $post->published_at ?? $post->created_at;
                                $noticeDay = \App\Support\NepaliDate::dayAndMonth($noticeDate);
                            @endphp
                            <li>
                                <time class="hp-board__date" datetime="{{ $noticeDate->toDateString() }}">
                                    <b>{{ $noticeDay['day'] }}</b>{{ $noticeDay['month'] }}
                                </time>
                                <a href="{{ route('frontend.details.show', $post->slug) }}">{{ $post->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <aside class="hp-fund" aria-label="अक्षयकोष">
                <div>
                    <p class="hp-kicker hp-kicker--gold">अक्षयकोष</p>
                    <h2 class="hp-fund__title">शैक्षिक अक्षयकोषमा सहयोग गर्नुहोस्</h2>
                    <p class="hp-fund__text">लिबाङ समुदायको शिक्षा र भविष्यका लागि तपाईंको सहयोग महत्त्वपूर्ण छ।</p>
                </div>
                <div class="hp-fund__actions">
                    <a class="hp-fund__button" href="/pages/donate">सहयोग गर्नुहोस्</a>
                    <a class="hp-more hp-more--light" href="/pages/endowment-fund">थप जानकारी</a>
                </div>
            </aside>
        </div>

        @if ($activities->isNotEmpty())
            <div>
                @include('livewire.frontend.home-parts.section-head', ['title' => 'गतिविधि', 'url' => '/activities'])

                <div class="hp-feature">
                    @foreach ($activities as $post)
                        @include('livewire.frontend.home-parts.story', ['post' => $post])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
