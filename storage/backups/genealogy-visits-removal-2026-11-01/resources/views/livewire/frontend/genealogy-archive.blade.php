<div class="lb-page lb-page--genealogy">

    <section class="lb-hero">
        @include('livewire.frontend.partials.breadcrumb', ['crumbs' => $breadcrumbs])

        <div class="lb-hero__row">
            <div class="lb-hero__heading">
                <h1 class="lb-hero__title">वंशावली</h1>
                <p class="lb-hero__text">केत्तकुम लिबाङ पाङ्पत सयङ वंशवृक्ष</p>
            </div>

            <dl class="lb-stats">
                <div>
                    <dt>सदस्य</dt>
                    <dd>{{ number_format($totalCount) }}</dd>
                </div>
                <div>
                    <dt>पुस्ता</dt>
                    <dd>{{ $generationCount }}</dd>
                </div>
            </dl>
        </div>

        <div class="lb-filters" role="search" wire:key="filters-{{ $filterKey }}">
            <div class="lb-search uk-inline">
                <span class="uk-form-icon" uk-icon="icon: search" wire:ignore></span>
                <input type="search"
                       class="uk-input"
                       wire:model.live.debounce.300ms="search"
                       placeholder="नाम, पुस्ता नम्बर वा पत्नीको नाम लेख्नुहोस्..."
                       aria-label="सदस्य खोज्नुहोस्">
                <span class="lb-spinner" wire:loading wire:target="search"></span>
            </div>

            <select class="uk-select" wire:model.live="genderFilter" aria-label="लिङ्ग">
                <option value="">सबै लिङ्ग</option>
                <option value="male">पुरुष</option>
                <option value="female">महिला</option>
            </select>

            <select class="uk-select" wire:model.live="generationFilter" aria-label="पुस्ता">
                <option value="">सबै पुस्ता</option>
                @foreach($generations as $number => $count)
                    <option value="{{ $number }}">पुस्ता {{ $number }} ({{ $count }})</option>
                @endforeach
            </select>

            <select class="uk-select" wire:model.live="sortBy" aria-label="क्रम">
                <option value="pustanumber">पुस्ताको क्रम</option>
                <option value="name">नामको क्रम</option>
            </select>
        </div>
    </section>

    <div class="lb-toolbar">
        <div class="lb-tabs" role="group" aria-label="देखाउने तरिका">
            <button type="button" wire:click="showTree" @class(['lb-tab', 'is-active' => ! $showList]) aria-pressed="{{ $showList ? 'false' : 'true' }}">वंशवृक्ष</button>
            <button type="button" wire:click="showList" @class(['lb-tab', 'is-active' => $showList]) aria-pressed="{{ $showList ? 'true' : 'false' }}">सदस्य सूची</button>
        </div>

        @if($showList)
            <div class="lb-toolbar__side">
                <span class="lb-toolbar__count">
                    @if($members->total() > 0)
                        {{ number_format($members->total()) }} मध्ये {{ $members->firstItem() }}–{{ $members->lastItem() }}
                    @endif
                </span>
                @if($activeFilterCount > 0 && $members->isNotEmpty())
                    <button type="button" class="lb-link" wire:click="resetFilters">फिल्टर हटाउनुहोस्</button>
                @endif
            </div>
        @else
            <div class="lb-toolbar__side">
                <button type="button" class="lb-link" data-tree-action="open">सबै खोल्नुहोस्</button>
                <button type="button" class="lb-link" data-tree-action="close">सबै बन्द गर्नुहोस्</button>
            </div>
        @endif
    </div>

    <section class="lb-results" wire:loading.class="lb-is-busy">
        @if($showList)
            @if($members->isNotEmpty())
                <div class="lb-members" wire:key="genealogy-list">
                    @foreach($members as $person)
                        <a class="lb-member" href="{{ route('frontend.genealogy.show', $person->id) }}" wire:key="member-{{ $person->id }}">
                            @include('livewire.frontend.partials.avatar', [
                                'name' => $person->name,
                                'image' => $person->image,
                                'sex' => $person->sex,
                            ])
                            <div class="lb-member__body">
                                <h2 class="lb-member__name">{{ $person->name }}</h2>
                                <p class="lb-pusta">पुस्ता {{ $person->pustanumber }}</p>

                                @if($person->father_id)
                                    <p class="lb-member__line"><span>पिता</span> {{ $person->father->name }}</p>
                                @endif
                                @if($person->mother_name)
                                    <p class="lb-member__line"><span>माता</span> {{ $person->mother_name }}</p>
                                @endif
                                @if($person->wife_name)
                                    <p class="lb-member__line"><span>{{ $person->sex === 'male' ? 'पत्नी' : 'जीवनसाथी' }}</span> {{ $person->wife_name }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                {{ $members->onEachSide(1)->links('livewire.frontend.partials.pagination') }}
            @else
                <div class="lb-empty" wire:key="genealogy-empty">
                    <span class="lb-empty__icon" uk-icon="icon: search; ratio: 1.6" wire:ignore></span>
                    <h2 class="lb-empty__title">कुनै परिणाम फेला परेन</h2>
                    <p class="lb-empty__text">नामको हिज्जे जाँच्नुहोस्, वा फिल्टर हटाएर फेरि खोज्नुहोस्।</p>
                    <button type="button" class="lb-btn" wire:click="resetFilters">सबै फिल्टर हटाउनुहोस्</button>
                </div>
            @endif
        @else
            <div class="lb-tree" data-tree tabindex="0" role="region" aria-label="वंशवृक्ष" wire:key="genealogy-tree"
                 x-data x-init="$nextTick(() => $el.scrollLeft = ($el.scrollWidth - $el.clientWidth) / 2)">
                <div class="lb-tree__canvas">
                    @foreach($roots as $root)
                        <ul class="lb-tree__root">
                            @include('livewire.frontend.partials.tree-node', [
                                'person' => $root,
                                'childrenByFather' => $childrenByFather,
                                'level' => 0,
                                'openLevels' => 13,
                            ])
                        </ul>
                    @endforeach
                </div>
            </div>

            {{ $roots->links('livewire.frontend.partials.pagination') }}
        @endif
    </section>

    <script>
        (function () {
            if (window.lbTreeReady) return;
            window.lbTreeReady = true;

            function setBranch(toggle, open) {
                toggle.closest('li').querySelector(':scope > ul').hidden = !open;
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function centre(tree) {
                tree.scrollLeft = (tree.scrollWidth - tree.clientWidth) / 2;
                tree.scrollTop = 0;
            }

            document.addEventListener('click', function (event) {
                var toggle = event.target.closest('[data-tree-toggle]');
                var action = event.target.closest('[data-tree-action]');

                if (toggle) {
                    // Keep the clicked person in place while the chart resizes around them.
                    var tree = toggle.closest('[data-tree]');
                    var before = toggle.getBoundingClientRect();
                    setBranch(toggle, toggle.getAttribute('aria-expanded') !== 'true');
                    var after = toggle.getBoundingClientRect();
                    tree.scrollLeft += after.left - before.left;
                    tree.scrollTop += after.top - before.top;
                } else if (action) {
                    var chart = document.querySelector('[data-tree]');
                    if (!chart) return;
                    chart.querySelectorAll('[data-tree-toggle]').forEach(function (button) {
                        setBranch(button, action.dataset.treeAction === 'open');
                    });
                    centre(chart);
                }
            });

            // Drag the empty part of the chart to pan it with a mouse. Touch screens scroll natively.
            var drag = null;

            document.addEventListener('pointerdown', function (event) {
                var tree = event.target.closest('[data-tree]');
                if (!tree || event.pointerType !== 'mouse' || event.button !== 0 || event.target.closest('a, button')) return;
                drag = { tree: tree, x: event.clientX, y: event.clientY, left: tree.scrollLeft, top: tree.scrollTop };
                tree.classList.add('is-dragging');
            });

            document.addEventListener('pointermove', function (event) {
                if (!drag) return;
                drag.tree.scrollLeft = drag.left - (event.clientX - drag.x);
                drag.tree.scrollTop = drag.top - (event.clientY - drag.y);
            });

            document.addEventListener('pointerup', function () {
                if (!drag) return;
                drag.tree.classList.remove('is-dragging');
                drag = null;
            });
        })();
    </script>
</div>
