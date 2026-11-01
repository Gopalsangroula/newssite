{{-- Top bar: view the site, create something new, and the account menu. --}}
@php
    $newItems = array_filter([
        ['label' => __('Post'), 'icon' => 'file-edit', 'route' => 'admin.posts.create', 'can' => 'access posts'],
        ['label' => __('Page'), 'icon' => 'file-text', 'route' => 'admin.pages.create', 'can' => 'access pages'],
        ['label' => __('Media'), 'icon' => 'image', 'route' => 'admin.media.create', 'can' => 'access media'],
        ['label' => __('Advertisement'), 'icon' => 'bolt', 'route' => 'admin.advertisements.create', 'can' => 'access advertisements'],
        ['label' => __('User'), 'icon' => 'user', 'route' => 'admin.register', 'can' => 'access users'],
    ], fn ($item) => auth()->user()->can($item['can']));
@endphp

<header class="ah-topbar">
    <div class="ah-topbar-group">
        <a href="#" class="ah-topbar-icon uk-hidden@m" uk-toggle="target: #offcanvas-nav" aria-label="{{ __('Open menu') }}">
            <span uk-icon="icon: menu; ratio: 1.1"></span>
        </a>
        <a href="{{ url('/') }}" target="_blank" rel="noopener" class="ah-topbar-link">
            <span uk-icon="icon: world; ratio: 0.85"></span>
            <span>{{ __('View site') }}</span>
        </a>
    </div>

    <div class="ah-topbar-group">
        @if ($newItems)
            <div>
                <button type="button" class="uk-button uk-button-primary uk-button-small">
                    <span uk-icon="icon: plus; ratio: 0.75"></span>
                    <span>{{ __('New') }}</span>
                </button>
                <div class="ah-menu" uk-dropdown="mode: click; pos: bottom-right; offset: 8">
                    @foreach ($newItems as $item)
                        <a href="{{ route($item['route']) }}">
                            <span uk-icon="icon: {{ $item['icon'] }}; ratio: 0.85"></span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <button type="button" class="ah-topbar-user" aria-label="{{ __('Account menu') }}">
                @if (auth()->user()->user_image)
                    <img src="{{ Storage::url(auth()->user()->user_image) }}" alt="" class="ah-avatar">
                @else
                    <span class="ah-avatar">{{ auth()->user()->initials() }}</span>
                @endif
                <span class="ah-topbar-name">{{ auth()->user()->name }}</span>
                <span uk-icon="icon: chevron-down; ratio: 0.75"></span>
            </button>
            <div class="ah-menu" uk-dropdown="mode: click; pos: bottom-right; offset: 8">
                <div class="ah-menu-head">
                    <div class="ah-menu-name">{{ auth()->user()->name }}</div>
                    <div class="ah-menu-email">{{ auth()->user()->email }}</div>
                </div>
                <a href="{{ route('settings.profile') }}">
                    <span uk-icon="icon: cog; ratio: 0.85"></span>
                    <span>{{ __('Settings') }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <span uk-icon="icon: sign-out; ratio: 0.85"></span>
                        <span>{{ __('Log Out') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
