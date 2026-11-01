{{-- resources/views/frontend/genealogy/index.blade.php --}}
<div class="genealogy-page uk-margin-large-top">
<div class="uk-text-center"> <h1 class="genealogy-title">केत्तकुम लिबाङ पाङ्पत सयङ वंशवृक्ष</h1> </div>
    @if(empty($newsGenealogy) || count($newsGenealogy) === 0)
        <!-- Empty State -->
        <div class="uk-text-center uk-padding-large uk-padding-medium@s">
            <div class="uk-margin-bottom">
                <span uk-icon="icon: search; ratio: 3" class="uk-text-muted uk-icon-large@m"></span>
            </div>
            <h3 class="uk-text-large uk-text-medium@m uk-text-muted">कुनै व्यक्ति फेला परेन</h3>
            <p class="uk-text-muted uk-text-small@s">कृपया आफ्नो खोज परिवर्तन गर्नुहोस्</p>
        </div>
    @else
        {{-- MOBILE: SIMPLE LIST VIEW --}}
        <div class="uk-card uk-card-default uk-card-body uk-border-rounded uk-hidden@m uk-margin-medium-top">
            <ul class="uk-list uk-list-divider uk-margin-remove">
                @foreach($newsGenealogy as $person)
                    <li class="uk-flex uk-flex-middle uk-margin-small-bottom">
                        <!-- Image -->
                        <div class="uk-margin-small-right uk-flex-shrink-0">
                            @if($person->image)
                                <img src="{{ asset('storage/'.$person->image) }}"
                                    class="uk-border-circle" width="50" height="50" loading="lazy"
                                    alt="{{ $person->name }}">
                            @else
                                <div class="uk-border-circle uk-background-primary uk-flex uk-flex-center uk-flex-middle"
                                    style="width: 50px; height: 50px;">
                                    <span class="uk-text-bold uk-light">
                                        {{ substr($person->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="uk-flex-1">
                            <!-- Name -->
                            <div>
                                <a href="{{ route('frontend.genealogy.show', $person) }}"
                                    class="uk-text-bold uk-text-small uk-link-heading uk-display-block">
                                    {{ $person->name }}
                                </a>
                            </div>

                            <!-- Pustanumber -->
                            <div class="uk-text-muted uk-text-small uk-margin-remove">
                                पु.नं: {{ $person->pustanumber ?? '—' }}
                            </div>

                            <!-- Dates -->
                            @if($person->date_of_birth || $person->date_of_death)
                                <div class="uk-text-muted uk-text-small uk-margin-remove">
                                    @if($person->date_of_birth)
                                        {{ $person->date_of_birth->format('d M Y') }}
                                    @endif
                                    @if($person->date_of_death)
                                        – {{ $person->date_of_death->format('d M Y') }}
                                    @endif
                                </div>
                            @endif

                            <!-- Wife Name -->
                            @if($person->wife_name)
                                <div class="uk-text-muted uk-text-small uk-margin-remove">
                                    <span class="uk-margin-xsmall-right" uk-icon="icon: heart; ratio: 0.7"></span>
                                    {{ $person->wife_name }}
                                </div>
                            @endif
                        </div>

                        <!-- View Button -->
                        <div class="uk-flex-shrink-0 uk-margin-small-left">
                            <a href="/genealogy"
                                class="uk-button uk-button-primary uk-button-small uk-border-rounded"
                                title="विस्तृत हेर्नुहोस्">
                                <span uk-icon="icon: arrow-right; ratio: 0.8"></span>
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>

            <!-- Simple Pagination -->
            @if(method_exists($newsGenealogy, 'links'))
                <div class="uk-margin-top">
                    {{ $newsGenealogy->links() }}
                </div>
            @endif
        </div>

        <!-- DESKTOP: TREE VIEW -->
        <div class="ah-tree-section uk-visible@m">
            @php
                $all = $newsGenealogy;
                $roots = $all->whereNull('father_id')->sortBy('date_of_birth');

                // If no traditional roots, find potential starting points
                if($roots->isEmpty()) {
                    // Find people with no parents in the dataset
                    $allIds = $all->pluck('id')->toArray();
                    $roots = $all->whereNotIn('father_id', $allIds)
                        ->sortBy('pustanumber')
                        ->take(3);
                }
            @endphp

            <!-- Family Trees -->
            <div class="genealogy-container">
                @foreach($roots as $rootIndex => $root)
                    <div class="family-tree-section">
                        @if($roots->count() > 1)
                            <div class="uk-card uk-card-default uk-card-body uk-margin-bottom">
                                <h3 class="uk-h4 uk-margin-remove">
                                    <span uk-icon="icon: git-branch; ratio: 0.8"></span>
                                    Family Branch {{ $loop->iteration }}: {{ $root->name }}
                                </h3>
                                <p class="uk-text-meta uk-margin-remove-top">
                                    {{ $all->where('father_id', $root->id)->count() }}
                                    direct descendants
                                </p>
                            </div>
                        @endif

                        <div class="ah-tree-wrapper" id="tree-{{ $rootIndex }}">
                            <div class="tree uk-flex uk-flex-center">
                                <ul class="tree-root">
                                    {!! buildTreeNode($root, $all) !!}
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if(!$loop->last)
                        <hr class="uk-divider-icon uk-margin-large">
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    @php
        function buildTreeNode($person, $collection, $level = 0) {
            $children = $collection->where('father_id', $person->id)->sortBy('date_of_birth');

            // Image with fallback
            $image = $person->image
                ? asset('storage/' . $person->image)
                : 'https://ui-avatars.com/api/?name=' . urlencode($person->name) .
                '&background=' . ($level === 0 ? '1e87f0' : ($children->isNotEmpty() ? '32d296' : 'f8f8f8')) .
                '&color=' . ($level === 0 || $children->isNotEmpty() ? 'fff' : '666') .
                '&bold=true&size=200';

            // Date formatting
            $dob = $person->date_of_birth?->format('d M Y') ?? '—';
            $dod = $person->date_of_death?->format('d M Y');
            $dates = $dod ? "$dob – $dod" : $dob;

            // Calculate age
            $age = null;
            if ($person->date_of_birth) {
                $age = $person->date_of_death
                    ? $person->date_of_death->diffInYears($person->date_of_birth)
                    : now()->diffInYears($person->date_of_birth);
            }
            $lifespan = $age ? "($age years)" : '';

            $hasChildren = $children->isNotEmpty();
            $isRoot = $level === 0;

            $html = '<li class="tree-node" data-level="' . $level . '" data-person-id="' . $person->id . '">';

                // Node container
                $html .= '<div class="node-container">';

                    // Person card
                    $html .= '<div
                        class="person-card ' . ($isRoot ? 'root-card' : ($hasChildren ? 'parent-card' : 'leaf-card')) . '">
                        ';
                        $html .= '<a href="/genealogy" class="card-link">';

                            // Card image
                            $html .= '<div class="card-image">';
                                $html .= '<div class="image-wrapper">';
                                $html .= '<img src="' . $image . '" alt="' . e($person->name) . '" loading="lazy">';
                                $html .= '</div>';
                                $html .= '</div>';

                            // Card content
                            $html .= '<div class="card-content">';
                                $html .= '<h4 class="person-name">' . e($person->name) . '</h4>';

                                // Additional info
                                if ($person->pustanumber) {
                                    $html .= '<div class="person-meta">Pusta: ' . $person->pustanumber . '</div>';
                                }

                                $html .= '</div>';
                            $html .= '</a>';
                        $html .= '</div>'; // Close person-card

                    // Toggle button for children
                    if ($hasChildren) {
                        $html .= '<button class="toggle-children uk-button uk-button-small uk-button-default"
                            uk-tooltip="Toggle children">';
                            $html .= '<span class="toggle-icon" uk-icon="chevron-down"></span>';
                            $html .= '</button>';
                    }

                    $html .= '</div>'; // Close node-container

                // Children list
                if ($hasChildren) {
                    $html .= '<ul class="children-list level-' . ($level + 1) . '">';
                        foreach ($children as $child) {
                            $html .= buildTreeNode($child, $collection, $level + 1);
                        }
                        $html .= '</ul>';
                }

                $html .= '</li>';

            return $html;
        }
    @endphp
    <p uk-margin class="uk-text-center"> <a class="uk-button uk-button-default uk-text-bold" href="/genealogy">सम्पूर्ण  विवरण  हेर्न यहाँ क्लिक गर्नुहोस</a></p>
</div>