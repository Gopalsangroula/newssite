@if ($publications->isNotEmpty())
    <section class="hp-band hp-band--tight">
        <div class="hp-wrap">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'प्रकाशन', 'url' => '/publication'])

            <div class="hp-cards hp-cards--four">
                @foreach ($publications as $post)
                    @include('livewire.frontend.home-parts.story', ['post' => $post])
                @endforeach
            </div>
        </div>
    </section>
@endif
