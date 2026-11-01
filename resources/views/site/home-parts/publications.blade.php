@if ($publications->isNotEmpty())
    <section class="hp-band hp-band--tight">
        <div class="hp-wrap">
            @include('site.home-parts.section-head', ['title' => 'प्रकाशन', 'url' => '/publication', 'icon' => 'file-text'])

            <div class="hp-cards hp-cards--four">
                @foreach ($publications as $post)
                    @include('site.home-parts.story', ['post' => $post])
                @endforeach
            </div>
        </div>
    </section>
@endif
