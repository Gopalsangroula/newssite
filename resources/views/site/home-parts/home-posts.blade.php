<div class="hp">
    <h1 class="hp-hidden">केत्तकुम लिबाङ पाङ्पत सयङ</h1>

    @include('site.home-parts.ticker')
    @include('site.home-parts.lead')
    @include('site.home-parts.ad-slot', ['placement' => 'header'])

    <div class="hp-wrap hp-layout">
        <div class="hp-layout__main">
            @if ($firstSection)
                @include('site.home-parts.section', ['section' => $firstSection])
            @endif

            @include('site.home-parts.notices')

            @foreach ($mainSections as $section)
                @include('site.home-parts.section', ['section' => $section])
            @endforeach
        </div>

        @include('site.home-parts.rail')
    </div>

    @include('site.home-parts.message')
    @include('site.home-parts.special')

    @foreach ($wideSections as $section)
        <div class="hp-band">
            <div class="hp-wrap">
                @include('site.home-parts.section', ['section' => $section])
            </div>
        </div>
    @endforeach

    @include('site.home-parts.culture')
    @include('site.home-parts.publications')
    @include('site.home-parts.videos')
    @include('site.home-parts.ad-slot', ['placement' => 'photo'])
    @include('site.home-parts.gallery')
    @include('site.home-parts.ad-slot', ['placement' => 'committee'])

    @include('site.home-parts.committee')
    @include('site.home-parts.ad-slot', ['placement' => 'footer'])
</div>
