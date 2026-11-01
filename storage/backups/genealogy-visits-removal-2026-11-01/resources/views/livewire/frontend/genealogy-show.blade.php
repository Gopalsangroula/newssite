@php
    $father = $genealogy->father_id ? $genealogy->father : null;
    $children = $genealogy->children;
    $sexLabel = ['male' => 'पुरुष', 'female' => 'महिला'][$genealogy->sex] ?? null;
    $spouseLabel = $genealogy->sex === 'male' ? 'पत्नी' : 'जीवनसाथी';
    $hasFamily = $father || $genealogy->mother_name || $genealogy->wife_name;
    $hasDates = $genealogy->date_of_birth || $genealogy->date_of_death;
@endphp

<div class="lb-page lb-page--member">
    @include('livewire.frontend.partials.breadcrumb', ['crumbs' => $breadcrumbs])

    <section class="lb-profile">
        @include('livewire.frontend.partials.avatar', [
            'name' => $genealogy->name,
            'image' => $genealogy->image,
            'sex' => $genealogy->sex,
            'size' => 'xl',
            'alt' => $genealogy->name,
        ])

        <div class="lb-profile__main">
            <h1 class="lb-profile__name">{{ $genealogy->name }}</h1>

            <ul class="lb-chips">
                @if($genealogy->pustanumber !== null)
                    <li class="lb-chip lb-chip--green">पुस्ता {{ $genealogy->pustanumber }}</li>
                @endif
                @if($sexLabel)
                    <li class="lb-chip">{{ $sexLabel }}</li>
                @endif
                @if($children->isNotEmpty())
                    <li class="lb-chip">{{ $children->count() }} सन्तान</li>
                @endif
            </ul>

            @if($hasDates)
                <dl class="lb-facts">
                    @if($genealogy->date_of_birth)
                        <div>
                            <dt>जन्म मिति</dt>
                            <dd>{{ $genealogy->date_of_birth->format('j F Y') }}</dd>
                        </div>
                    @endif
                    @if($genealogy->date_of_death)
                        <div>
                            <dt>मृत्यु मिति</dt>
                            <dd>{{ $genealogy->date_of_death->format('j F Y') }}</dd>
                        </div>
                    @endif
                    @if($genealogy->age > 0)
                        <div>
                            <dt>उमेर</dt>
                            <dd>{{ $genealogy->age }} वर्ष</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>
    </section>

    @if($hasFamily || $children->isNotEmpty())
        <div @class(['lb-panels', 'lb-panels--split' => $hasFamily && $children->isNotEmpty()])>
            @if($hasFamily)
                <section class="lb-panel">
                    <h2 class="lb-panel__title">परिवार</h2>
                    <ul class="lb-relatives">
                        @if($father)
                            <li>
                                <a class="lb-relative" href="{{ route('frontend.genealogy.show', $father->id) }}">
                                    @include('livewire.frontend.partials.avatar', [
                                        'name' => $father->name,
                                        'image' => $father->image,
                                        'sex' => $father->sex,
                                        'size' => 'sm',
                                    ])
                                    <span class="lb-relative__text">
                                        <span class="lb-relative__label">पिता</span>
                                        <span class="lb-relative__name">{{ $father->name }}</span>
                                        <span class="lb-relative__meta">पुस्ता {{ $father->pustanumber }}</span>
                                    </span>
                                </a>
                            </li>
                        @endif

                        @if($genealogy->mother_name)
                            <li>
                                <div class="lb-relative">
                                    @include('livewire.frontend.partials.avatar', [
                                        'name' => $genealogy->mother_name,
                                        'sex' => 'female',
                                        'size' => 'sm',
                                    ])
                                    <span class="lb-relative__text">
                                        <span class="lb-relative__label">माता</span>
                                        <span class="lb-relative__name">{{ $genealogy->mother_name }}</span>
                                    </span>
                                </div>
                            </li>
                        @endif

                        @if($genealogy->wife_name)
                            <li>
                                <div class="lb-relative">
                                    @include('livewire.frontend.partials.avatar', [
                                        'name' => $genealogy->wife_name,
                                        'sex' => $genealogy->sex === 'male' ? 'female' : 'male',
                                        'size' => 'sm',
                                    ])
                                    <span class="lb-relative__text">
                                        <span class="lb-relative__label">{{ $spouseLabel }}</span>
                                        <span class="lb-relative__name">{{ $genealogy->wife_name }}</span>
                                    </span>
                                </div>
                            </li>
                        @endif
                    </ul>
                </section>
            @endif

            @if($children->isNotEmpty())
                <section class="lb-panel">
                    <h2 class="lb-panel__title">सन्तान <small>({{ $children->count() }})</small></h2>
                    <ul class="lb-relatives">
                        @foreach($children as $child)
                            <li>
                                <a class="lb-relative" href="{{ route('frontend.genealogy.show', $child->id) }}">
                                    @include('livewire.frontend.partials.avatar', [
                                        'name' => $child->name,
                                        'image' => $child->image,
                                        'sex' => $child->sex,
                                        'size' => 'sm',
                                    ])
                                    <span class="lb-relative__text">
                                        <span class="lb-relative__label">{{ $child->sex === 'female' ? 'छोरी' : 'छोरा' }}</span>
                                        <span class="lb-relative__name">{{ $child->name }}</span>
                                        <span class="lb-relative__meta">पुस्ता {{ $child->pustanumber }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    @endif

    @if($genealogy->description)
        <section class="lb-panel lb-panel--text">
            <h2 class="lb-panel__title">परिचय / चिनारी</h2>
            <div class="lb-prose">{!! nl2br(e($genealogy->description)) !!}</div>
        </section>
    @endif

    <p class="lb-back">
        <a class="lb-btn" href="{{ $genealogyIndexUrl }}">वंशावलीमा फर्कनुहोस्</a>
    </p>
</div>
