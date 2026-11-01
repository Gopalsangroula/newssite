@php
    $digits = fn ($value) => \App\Support\NepaliDate::digits($value);
@endphp

@if ($genealogyStats['people'] > 0)
    <section class="hp-tree">
        <div class="hp-wrap hp-tree__grid">
            <div>
                <p class="hp-kicker hp-kicker--gold">वंशवृक्ष</p>
                <h2 class="hp-tree__title">आफ्नो वंश र पुर्खा खोज्नुहोस्</h2>
                <p class="hp-tree__text">केत्तकुम लिबाङ पाङ्पत वंशको डिजिटल अभिलेख। नाम लेखेर आफ्नो पुस्ता र परिवारको विवरण हेर्नुहोस्।</p>

                <form class="hp-tree__search" action="{{ route('frontend.genealogy.index') }}" method="get" role="search">
                    <label class="hp-hidden" for="hp-genealogy-search">व्यक्तिको नाम</label>
                    <input id="hp-genealogy-search" type="search" name="search" placeholder="व्यक्तिको नाम लेख्नुहोस्" autocomplete="off">
                    <button type="submit">खोज्नुहोस्</button>
                </form>

                <dl class="hp-tree__stats">
                    <div>
                        <dt>व्यक्तिको अभिलेख</dt>
                        <dd>{{ $digits($genealogyStats['people']) }}</dd>
                    </div>
                    <div>
                        <dt>पुस्ता</dt>
                        <dd>{{ $digits($genealogyStats['generations']) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="hp-tree__people">
                <ul class="hp-lineage">
                    @foreach ($newsGenealogy as $person)
                        <li>
                            <a href="{{ route('frontend.genealogy.show', $person) }}">
                                <span class="hp-lineage__avatar" aria-hidden="true">
                                    {{ mb_substr($person->name, 0, 1) }}
                                    @if ($person->image)
                                        <img src="{{ asset('storage/'.$person->image) }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                </span>
                                <span class="hp-lineage__name">{{ $person->name }}</span>
                                <span class="hp-lineage__generation">पुस्ता {{ $digits($person->pustanumber) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <a class="hp-more hp-more--light" href="{{ route('frontend.genealogy.index') }}">सम्पूर्ण वंशवृक्ष हेर्नुहोस्</a>
            </div>
        </div>
    </section>
@endif
