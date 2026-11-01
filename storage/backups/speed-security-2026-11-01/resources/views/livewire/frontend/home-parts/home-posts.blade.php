<div class="hp">
    <h1 class="hp-hidden">केत्तकुम लिबाङ पाङ्पत सयङ</h1>

    @include('livewire.frontend.home-parts.ticker')
    @include('livewire.frontend.home-parts.lead')
    @include('livewire.frontend.home-parts.ad-slot', ['placement' => 'header'])

    <div class="hp-wrap hp-layout">
        <div class="hp-layout__main">
            @if ($firstSection)
                @include('livewire.frontend.home-parts.section', ['section' => $firstSection])
            @endif

            @include('livewire.frontend.home-parts.notices')

            @foreach ($mainSections as $section)
                @include('livewire.frontend.home-parts.section', ['section' => $section])
            @endforeach
        </div>

        @include('livewire.frontend.home-parts.rail')
    </div>

    @include('livewire.frontend.home-parts.message')
    @include('livewire.frontend.home-parts.special')

    @foreach ($wideSections as $section)
        <div class="hp-band">
            <div class="hp-wrap">
                @include('livewire.frontend.home-parts.section', ['section' => $section])
            </div>
        </div>
    @endforeach

    @include('livewire.frontend.home-parts.culture')
    @include('livewire.frontend.home-parts.publications')
    @include('livewire.frontend.home-parts.videos')
    @include('livewire.frontend.home-parts.gallery')
    @include('livewire.frontend.home-parts.committee')
    @include('livewire.frontend.home-parts.ad-slot', ['placement' => 'footer'])
</div>
