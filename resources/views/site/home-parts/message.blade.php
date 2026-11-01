@if ($pagemessage || $pageabout)
    <section class="hp-band hp-band--wash">
        <div class="hp-wrap hp-org">
            @if ($pagemessage)
                <article class="hp-chair">
                    @if (\App\Support\Thumb::url($pagemessage->cover_image, 640))
                        <img class="hp-chair__photo" src="{{ \App\Support\Thumb::url($pagemessage->cover_image, 640) }}" alt="{{ $pagemessage->title }}" loading="lazy" decoding="async" onerror="this.remove()">
                    @endif

                    <div class="hp-chair__body">
                        <p class="hp-kicker">अध्यक्षको सन्देश</p>
                        <blockquote class="hp-chair__quote">पुर्खाको इतिहास संरक्षण गरौँ, वर्तमानलाई संगठित गरौँ र भावी पुस्ताको भविष्य निर्माण गरौँ</blockquote>
                        <p class="hp-chair__text">{{ Str::limit(\App\Support\Seo::plainText($pagemessage->content), 230) }}</p>
                        <p class="hp-chair__name">{{ $pagemessage->title }}</p>
                        <a class="hp-more" href="/pages/chairman-massage">पूरा पढ्नुहोस्</a>
                    </div>
                </article>
            @endif

            @if ($pageabout)
                <article class="hp-about">
                    @if (\App\Support\Thumb::url($pageabout->cover_image, 640))
                        <a class="hp-about__media" href="/pages/about" tabindex="-1" aria-hidden="true">
                            <img src="{{ \App\Support\Thumb::url($pageabout->cover_image, 640) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        </a>
                    @endif

                    <p class="hp-kicker">हाम्रो बारेमा</p>
                    <h2 class="hp-about__title"><a href="/pages/about">{{ $pageabout->title }}</a></h2>
                    <p class="hp-about__text">{{ Str::limit(\App\Support\Seo::plainText($pageabout->content), 200) }}</p>
                    <a class="hp-more" href="/pages/about">पूरा पढ्नुहोस्</a>
                </article>
            @endif
        </div>
    </section>
@endif
