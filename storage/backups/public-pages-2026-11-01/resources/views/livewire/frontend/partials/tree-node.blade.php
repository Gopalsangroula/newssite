@php
    $allChildren = $collection
        ->where('father_id', $person->id)
        ->sortBy('date_of_birth')
        ->values();

    $totalChildren = $allChildren->count();
    $isExpanded    = isset($expandedNodes[$person->id]);
    $maxChildren   = $maxChildren ?? 10;

    // Show all children only when expanded, otherwise max 10
    $showAll   = $isExpanded;
    $children  = $showAll ? $allChildren : $allChildren->slice(0, $maxChildren);
    $hasMore   = !$showAll && $totalChildren > $maxChildren;
    $hasChildren = $totalChildren > 0;

    $isMobile  = preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', request()->userAgent() ?? '');
    $imageSize = $isMobile ? 100 : 200;

    $imageUrl = $person->image
        ? asset('storage/' . $person->image)
        : 'https://ui-avatars.com/api/?name=' . urlencode($person->name)
          . '&background=1e87f0&color=fff&bold=true&size=' . $imageSize;

    $dob   = $person->date_of_birth?->format('d M Y') ?? '—';
    $dod   = $person->date_of_death?->format('d M Y');
    $dates = $dod ? "$dob – $dod" : $dob;

    $age = null;
    if ($person->date_of_birth) {
        $age = $person->date_of_birth->diffInYears($person->date_of_death ?? now());
    }
    $lifespan = $age !== null ? "($age वर्ष)" : '';
@endphp

<li data-level="{{ $level }}"
    data-person-id="{{ $person->id }}"
    class="tree-level-{{ $level }}">

    <div class="node-container">

        {{-- Person Card --}}
        <a href="{{ route('frontend.genealogy.show', $person) }}"
           class="person-card {{ $hasChildren ? 'has-children' : 'no-children' }}">

            <div class="card-image">
                <img src="{{ $imageUrl }}"
                     alt="{{ $person->name }}"
                     loading="lazy"
                     class="uk-border-circle">

                @if($hasChildren)
                    <div class="children-indicator uk-visible@m"
                         uk-tooltip="{{ $totalChildren }} सन्तान">
                        <span uk-icon="users"></span>
                        <span class="uk-margin-small-left">{{ $totalChildren }}</span>
                    </div>
                @endif
            </div>

            <div class="card-content">
                <div class="name">{{ $person->name }}</div>
                <div class="pustanumber uk-text-small uk-text-muted">
                    पु.नं: {{ $person->pustanumber }}
                </div>
                <div class="dates uk-text-small uk-text-muted">
                    {{ $dates }}
                    <span class="lifespan">{{ $lifespan }}</span>
                </div>

                @if($person->wife_name)
                    <div class="spouse uk-text-small uk-text-muted uk-visible@m">
                        <span uk-icon="heart"></span> {{ $person->wife_name }}
                    </div>
                @endif
            </div>
        </a>

        {{-- Toggle Button (exactly the class you requested) --}}
        @if($hasChildren)
            <button type="button"
                    class="toggle-children uk-button uk-button-small uk-button-default uk-button-tiny@s"
                    wire:click="toggleNode({{ $person->id }})"
                    uk-tooltip="{{ $isExpanded ? 'सन्तान लुकाउनुहोस्' : 'सन्तान देखाउनुहोस्' }}">
                <span uk-icon="chevron-{{ $isExpanded ? 'up' : 'down' }}"></span>
                <span class="uk-margin-small-left uk-text-tiny uk-visible@m">
                    {{ $totalChildren }}
                </span>
            </button>
        @endif
    </div>

    {{-- Children --}}
    @if($hasChildren && $isExpanded)
        {{-- Fully expanded – show ALL children --}}
        <ul class="children-level level-{{ $level + 1 }}">
            @foreach($allChildren as $child)
                @include('livewire.frontend.partials.tree-node', [
                    'person'        => $child,
                    'collection'    => $collection,
                    'level'         => $level + 1,
                    'maxChildren'   => $maxChildren,
                    'expandedNodes' => $expandedNodes,
                ])
            @endforeach
        </ul>

    @elseif($hasChildren)
        {{-- Collapsed – show max 10 children + Load More --}}
        <ul class="children-level level-{{ $level + 1 }}">
            @foreach($children as $child)
                @include('livewire.frontend.partials.tree-node', [
                    'person'        => $child,
                    'collection'    => $collection,
                    'level'         => $level + 1,
                    'maxChildren'   => $maxChildren,
                    'expandedNodes' => $expandedNodes,
                ])
            @endforeach

            @if($hasMore)
                <li class="load-more-node">
                    <div class="node-container">
                        <button type="button"
                                class="uk-button uk-button-default uk-button-small load-more-btn"
                                wire:click="toggleNode({{ $person->id }})">
                            <span uk-icon="plus-circle"></span>
                            थप {{ $totalChildren - $maxChildren }} सन्तान देखाउनुहोस्
                        </button>
                    </div>
                </li>
            @endif
        </ul>
    @endif
</li>