@if ($committee->isNotEmpty())
    <section class="hp-band hp-band--wash">
        <div class="hp-wrap">
            @include('livewire.frontend.home-parts.section-head', ['title' => 'केन्द्रीय कार्य समिति', 'url' => '/centralcomittee'])

            <ul class="hp-people">
                @foreach ($committee as $member)
                    <li>
                        <a href="{{ route('frontend.details.show', $member->post->slug) }}">
                            <span class="hp-people__photo" aria-hidden="true">
                                {{ mb_substr($member->name, 0, 1) }}
                                @if ($member->post->cover_image)
                                    <img src="{{ Storage::url($member->post->cover_image) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                                @endif
                            </span>
                            <span class="hp-people__name">{{ $member->name }}</span>
                            @if ($member->role)
                                <span class="hp-people__role">{{ $member->role }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
@endif
