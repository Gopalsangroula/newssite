{{-- Sidebar menu. Expects $navName, which keeps only one group open in this copy of the menu. --}}
@php
    $adminMenu = [
        '' => [
            ['label' => __('Dashboard'), 'icon' => 'home', 'route' => 'dashboard'],
            ['label' => __('Analytics'), 'icon' => 'eye', 'route' => 'admin.analytics', 'can' => 'access analytics'],
        ],
        __('Content') => [
            ['label' => __('Posts'), 'icon' => 'file-edit', 'items' => [
                ['label' => __('All Posts'), 'route' => 'admin.posts.index', 'also' => 'admin.posts.edit', 'can' => 'access posts'],
                ['label' => __('Add Post'), 'route' => 'admin.posts.create', 'can' => 'access posts'],
            ]],
            ['label' => __('Pages'), 'icon' => 'file-text', 'items' => [
                ['label' => __('All Pages'), 'route' => 'admin.pages.index', 'also' => 'admin.pages.edit', 'can' => 'access pages'],
                ['label' => __('Add Page'), 'route' => 'admin.pages.create', 'can' => 'access pages'],
            ]],
            ['label' => __('Media'), 'icon' => 'image', 'items' => [
                ['label' => __('Library'), 'route' => 'admin.media.index', 'also' => 'admin.media.edit', 'can' => 'access media'],
                ['label' => __('Add Media'), 'route' => 'admin.media.create', 'can' => 'access media'],
            ]],
            ['label' => __('Categories'), 'icon' => 'folder', 'items' => [
                ['label' => __('All Categories'), 'route' => 'admin.categories.index', 'also' => 'admin.categories.edit', 'can' => 'access categories'],
                ['label' => __('Add Category'), 'route' => 'admin.categories.create', 'can' => 'access categories'],
            ]],
            ['label' => __('Tags'), 'icon' => 'tag', 'items' => [
                ['label' => __('All Tags'), 'route' => 'admin.tags.index', 'also' => 'admin.tags.edit', 'can' => 'access tags'],
                ['label' => __('Add Tag'), 'route' => 'admin.tags.create', 'can' => 'access tags'],
            ]],
            ['label' => __('Comments'), 'icon' => 'comments', 'route' => 'admin.comments.index', 'also' => 'admin.comments.edit', 'can' => 'access comments', 'badge' => \App\Models\Comment::pending()->count()],
        ],
        __('Reader Tools') => [
            ['label' => __('Rashifal'), 'icon' => 'star', 'route' => 'admin.rashifal', 'can' => 'access rashifal'],
            ['label' => __('Patro Events'), 'icon' => 'calendar', 'route' => 'admin.patro-events', 'can' => 'access patro'],
            ['label' => __('Gold & Silver'), 'icon' => 'credit-card', 'route' => 'admin.metal-rates', 'can' => 'access gold silver'],
        ],
        __('Community') => [
            ['label' => __('Genealogy'), 'icon' => 'git-branch', 'items' => [
                ['label' => __('All Members'), 'route' => 'admin.genealogys.index', 'also' => 'admin.genealogys.update', 'can' => 'access genealogy'],
                ['label' => __('Add Member'), 'route' => 'admin.genealogys.create', 'can' => 'access genealogy'],
            ]],
        ],
        __('Marketing') => [
            ['label' => __('SEO'), 'icon' => 'search', 'items' => [
                ['label' => __('Overview'), 'route' => 'admin.seo', 'can' => 'access seo'],
                ['label' => __('Settings'), 'route' => 'admin.seo.settings', 'can' => 'access seo settings'],
            ]],
            ['label' => __('Advertisements'), 'icon' => 'bolt', 'items' => [
                ['label' => __('All Ads'), 'route' => 'admin.advertisements.index', 'also' => 'admin.advertisements.edit', 'can' => 'access advertisements'],
                ['label' => __('Create Ad'), 'route' => 'admin.advertisements.create', 'can' => 'access advertisements'],
            ]],
        ],
        __('Administration') => [
            ['label' => __('Users'), 'icon' => 'users', 'items' => [
                ['label' => __('All Users'), 'route' => 'admin.users', 'also' => 'admin.passwordreset', 'can' => 'access users'],
                ['label' => __('Add User'), 'route' => 'admin.register', 'can' => 'access users'],
                ['label' => __('Roles'), 'route' => 'admin.roles', 'can' => 'access roles'],
            ]],
        ],
    ];

    $allowed = fn ($item) => ! isset($item['can']) || auth()->user()?->can($item['can']);
    $isCurrent = fn ($item) => request()->routeIs($item['route'], $item['also'] ?? $item['route']);
@endphp

<nav class="ah-nav" aria-label="{{ __('Admin menu') }}">
    @foreach ($adminMenu as $section => $entries)
        @php
            $visible = [];

            foreach ($entries as $entry) {
                if (isset($entry['items'])) {
                    $entry['items'] = array_filter($entry['items'], $allowed);

                    if ($entry['items']) {
                        $visible[] = $entry;
                    }
                } elseif ($allowed($entry)) {
                    $visible[] = $entry;
                }
            }
        @endphp
        @continue(empty($visible))

        @if ($section)
            <div class="ah-nav-label">{{ $section }}</div>
        @endif

        @foreach ($visible as $entry)
            @if (isset($entry['items']))
                @php
                    $groupCurrent = collect($entry['items'])->contains($isCurrent);
                @endphp
                <details class="ah-nav-group" name="{{ $navName }}" @if ($groupCurrent) open @endif>
                    <summary @class(['ah-nav-link', 'is-current' => $groupCurrent])>
                        <span uk-icon="icon: {{ $entry['icon'] }}; ratio: 0.9"></span>
                        <span>{{ $entry['label'] }}</span>
                        <span class="ah-nav-chevron" uk-icon="icon: chevron-right; ratio: 0.8"></span>
                    </summary>
                    <div class="ah-nav-sub">
                        @foreach ($entry['items'] as $item)
                            <a href="{{ route($item['route']) }}" @class(['is-active' => $isCurrent($item)])>{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </details>
            @else
                <a href="{{ route($entry['route']) }}" @class(['ah-nav-link', 'is-active' => $isCurrent($entry)])>
                    <span uk-icon="icon: {{ $entry['icon'] }}; ratio: 0.9"></span>
                    <span>{{ $entry['label'] }}</span>
                    @if (! empty($entry['badge']))
                        <span class="ah-nav-badge">{{ $entry['badge'] }}</span>
                    @endif
                </a>
            @endif
        @endforeach
    @endforeach
</nav>
