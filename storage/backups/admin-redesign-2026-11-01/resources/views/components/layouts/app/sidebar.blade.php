<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="uk-height-1-1">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Dashboard') }} | {{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.head')
</head>

<body class="uk-background-default uk-height-1-1">

    <!-- Offcanvas Mobile Menu -->
    <div id="offcanvas-nav" uk-offcanvas="overlay: true; flip: true; mode: slide">
        <div class="uk-offcanvas-bar uk-background-muted uk-padding-small ah-cms-sidebar-list">

            <!-- Logo in Mobile -->
            <div class="uk-margin-bottom uk-text-center">
                <a href="{{ route('dashboard') }}" wire:navigate>
                    <div  class="uk-flex uk-flex-middle">
                        <x-app-logo class="uk-width-small" />
                    </div>
                </a>
            </div>

            <button class="uk-offcanvas-close" type="button" uk-close></button>

            <!-- Mobile Navigation -->
            <ul class="uk-nav uk-nav-default">
                <!-- Genealogy -->
                <li class="uk-nav-header">{{ __('Genealogy') }}</li>
                <li>
                    <a href="{{ route('admin.genealogys.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.genealogys.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: list"></span> {{ __('All Genealogy') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.genealogys.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.genealogys.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus-circle"></span> {{ __('Add Genealogy') }}
                    </a>
                </li>

                <!-- Posts -->
                <li class="uk-nav-header">{{ __('Posts') }}</li>
                <li>
                    <a href="{{ route('admin.posts.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.posts.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: album"></span> {{ __('All Posts') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.posts.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.posts.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus"></span> {{ __('Add Post') }}
                    </a>
                </li>

                <!-- Categories -->
                <li class="uk-nav-header">{{ __('Categories') }}</li>
                <li>
                    <a href="{{ route('admin.categories.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.categories.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: thumbnails"></span> {{ __('All Categories') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.categories.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus-circle"></span> {{ __('Add Category') }}
                    </a>
                </li>

                <!-- Media -->
                <li class="uk-nav-header">{{ __('Media') }}</li>
                <li>
                    <a href="{{ route('admin.media.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.media.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: image"></span> {{ __('All Media') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.media.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.media.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: upload"></span> {{ __('Add Media') }}
                    </a>
                </li>

                <!-- Users -->
                <li class="uk-nav-header">{{ __('Users') }}</li>
                <li>
                    <a href="{{ route('admin.users') }}" wire:navigate
                        class="{{ request()->routeIs('admin.users') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: users"></span> {{ __('All Users') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.register') }}" wire:navigate
                        class="{{ request()->routeIs('admin.register') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: user-plus"></span> {{ __('Add User') }}
                    </a>
                </li>

                <!-- Tags -->
                <li class="uk-nav-header">{{ __('Tags') }}</li>
                <li>
                    <a href="{{ route('admin.tags.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.tags.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: tag"></span> {{ __('All Tags') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.tags.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.tags.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus-circle"></span> {{ __('Add Tag') }}
                    </a>
                </li>
                <!-- Pages -->
                <li class="uk-nav-header">{{ __('Pages') }}</li>
                <li>
                    <a href="{{ route('admin.pages.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.pages.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: album"></span> {{ __('All pages') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.pages.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.pages.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus"></span> {{ __('Add pages') }}
                    </a>
                </li>
                <!-- Advertisements -->
                <li class="uk-nav-header">{{ __('Advertisement') }}</li>
                <li>
                    <a href="{{ route('admin.advertisements.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.advertisements.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: bolt"></span> {{ __('All Advertisement') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.advertisements.create') }}" wire:navigate
                        class="{{ request()->routeIs('admin.advertisements.create') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: plus"></span> {{ __('Create Advertisement') }}
                    </a>
                </li>

                <!-- Comments -->
                <li class="uk-nav-header">{{ __('Comments') }}</li>
                <li>
                    <a href="{{ route('admin.comments.index') }}" wire:navigate
                        class="{{ request()->routeIs('admin.comments.index') ? 'uk-text-primary' : '' }}">
                        <span uk-icon="icon: comments"></span> {{ __('All Comments') }}
                    </a>
                </li>
            </ul>

            <!-- User Menu in Mobile -->
            <div class="uk-margin-top uk-text-center">
                @auth
                    <div class="uk-margin-small-bottom">
                        <p class="uk-text-small uk-text-bold uk-margin-remove-bottom">{{ auth()->user()->name }}</p>
                        <p class="uk-text-muted uk-text-small">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('settings.profile') }}" class="uk-button uk-button-small uk-button-default uk-width-1-1 uk-margin-small-bottom">
                        {{ __('Settings') }}
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="uk-inline">
                        @csrf
                        <button type="submit" class="uk-button uk-button-small uk-button-danger uk-width-1-1">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">
                        {{ __('Log In') }}
                    </a>
                    <a href="{{ route('register') }}" class="uk-button uk-button-default uk-width-1-1">
                        {{ __('Register') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Layout Grid -->
    <div class="uk-grid-collapse uk-gri" uk-grid uk-height-viewport="expand: true">

        <!-- Sidebar (Desktop) -->
        <div class="uk-visible@m uk-width-1-6@m">
            <div class="uk-position-fixed uk-width-1-6@m  uk-background-muted" style="top: 0; bottom: 0; overflow-y: auto;">
                <div class="uk-padding-small">

                    <!-- Logo -->
                    <div class="uk-text-center uk-margin-bottom">
                        <a href="{{ route('dashboard') }}" wire:navigate>
                            <div  class="uk-flex uk-flex-middle">
                            <x-app-logo class="uk-width-medium" />
                            </div>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <ul class="uk-nav uk-nav-default">
                        <!-- Genealogy -->
                        <li class="uk-nav-header">{{ __('Genealogy') }}</li>
                        <li class="{{ request()->routeIs('admin.genealogys.*') ? 'uk-active' : '' }}">
                            <a href="{{ route('admin.genealogys.index') }}" wire:navigate>
                                <span uk-icon="icon: list" class="uk-margin-small-right"></span>
                                {{ __('All Genealogy') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.genealogys.create') }}" wire:navigate>
                                <span uk-icon="icon: plus-circle" class="uk-margin-small-right"></span>
                                {{ __('Add Genealogy') }}
                            </a>
                        </li>

                        <!-- Posts -->
                        <li class="uk-nav-header">{{ __('Posts') }}</li>
                        <li>
                            <a href="{{ route('admin.posts.index') }}" wire:navigate>
                                <span uk-icon="icon: album" class="uk-margin-small-right"></span>
                                {{ __('All Posts') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.posts.create') }}" wire:navigate>
                                <span uk-icon="icon: plus" class="uk-margin-small-right"></span>
                                {{ __('Add Post') }}
                            </a>
                        </li>

                        <!-- Categories -->
                        <li class="uk-nav-header">{{ __('Categories') }}</li>
                        <li>
                            <a href="{{ route('admin.categories.index') }}" wire:navigate>
                                <span uk-icon="icon: thumbnails" class="uk-margin-small-right"></span>
                                {{ __('All Categories') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.categories.create') }}" wire:navigate>
                                <span uk-icon="icon: plus-circle" class="uk-margin-small-right"></span>
                                {{ __('Add Category') }}
                            </a>
                        </li>

                        <!-- Media -->
                        <li class="uk-nav-header">{{ __('Media') }}</li>
                        <li>
                            <a href="{{ route('admin.media.index') }}" wire:navigate>
                                <span uk-icon="icon: image" class="uk-margin-small-right"></span>
                                {{ __('All Media') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.media.create') }}" wire:navigate>
                                <span uk-icon="icon: upload" class="uk-margin-small-right"></span>
                                {{ __('Add Media') }}
                            </a>
                        </li>

                        <!-- Users -->
                        <li class="uk-nav-header">{{ __('Users') }}</li>
                        <li>
                            <a href="{{ route('admin.users') }}" wire:navigate>
                                <span uk-icon="icon: users" class="uk-margin-small-right"></span>
                                {{ __('All Users') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.register') }}" wire:navigate>
                                <span uk-icon="icon: plus-circle" class="uk-margin-small-right"></span>
                                {{ __('Add User') }}
                            </a>
                        </li>

                        <!-- Tags -->
                        <li class="uk-nav-header">{{ __('Tags') }}</li>
                        <li>
                            <a href="{{ route('admin.tags.index') }}" wire:navigate>
                                <span uk-icon="icon: tag" class="uk-margin-small-right"></span>
                                {{ __('All Tags') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tags.create') }}" wire:navigate>
                                <span uk-icon="icon: plus-circle" class="uk-margin-small-right"></span>
                                {{ __('Add Tag') }}
                            </a>
                        </li>
                        <!-- Pages -->
                        <li class="uk-nav-header">{{ __('Pages') }}</li>
                        <li>
                            <a href="{{ route('admin.pages.index') }}" wire:navigate
                                class="{{ request()->routeIs('admin.pages.index') ? 'uk-text-primary' : '' }}">
                                <span uk-icon="icon: album"></span> {{ __('All pages') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.pages.create') }}" wire:navigate
                                class="{{ request()->routeIs('admin.pages.create') ? 'uk-text-primary' : '' }}">
                                <span uk-icon="icon: plus"></span> {{ __('Add pages') }}
                            </a>
                        </li>
                        <!-- Advertisements -->
                        <li class="uk-nav-header">{{ __('Advertisement') }}</li>
                        <li>
                            <a href="{{ route('admin.advertisements.index') }}" wire:navigate>
                                <span uk-icon="icon: bolt" class="uk-margin-small-right"></span>
                                {{ __('All Advertisement') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.advertisements.create') }}" wire:navigate>
                                <span uk-icon="icon: plus" class="uk-margin-small-right"></span>
                                {{ __('Create Advertisement') }}
                            </a>
                        </li>

                        <!-- Comments -->
                        <li class="uk-nav-header">{{ __('Comments') }}</li>
                        <li>
                            <a href="{{ route('admin.comments.index') }}" wire:navigate>
                                <span uk-icon="icon: comments" class="uk-margin-small-right"></span>
                                {{ __('All Comments') }}
                            </a>
                        </li>
                    </ul>

                    <!-- User Menu (Desktop) -->
                    <div class="uk-margin-top uk-padding-small uk-padding-remove-top uk-background-default uk-border-rounded">
                        @auth
                            <div class="uk-flex uk-flex-middle uk-margin-small-bottom">
                                <div class="uk-margin-small-right">
                                    <div class="uk-width-xsmall uk-height-xsmall uk-background-primary uk-border-circle uk-flex uk-flex-middle uk-flex-center">
                                        <span class="uk-text-white uk-text-bold">{{ auth()->user()->initials() }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="uk-text-small uk-margin-remove">{{ auth()->user()->name }}</p>
                                    <p class="uk-text-muted uk-text-small uk-margin-remove">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                            <a href="{{ route('settings.profile') }}" class="uk-button uk-button-small uk-button-default uk-width-1-1 uk-margin-small-bottom">
                                {{ __('Settings') }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="uk-button uk-button-small uk-button-danger uk-width-1-1">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">
                                {{ __('Log In') }}
                            </a>
                            <a href="{{ route('register') }}" class="uk-button uk-button-default uk-width-1-1">
                                {{ __('Register') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="uk-width-expand@m">
            <!-- Mobile Header -->
            <div class="uk-hidden@m uk-background-muted uk-padding-small uk-flex uk-flex-between uk-flex-middle">
                <a href="#" uk-toggle="target: #offcanvas-nav">
                    <span uk-icon="icon: menu; ratio: 1.5"></span>
                </a>
                <a href="{{ route('dashboard') }}" wire:navigate>
                     <div  class="uk-flex uk-flex-middle">
                    <x-app-logo class="uk-width-small" />
                     </div>
                </a>
                <div></div>
            </div>

            <!-- Page Content -->
            <div class="uk-padding">
                {{ $slot }}
            </div>
        </div>
    </div>

    @include('partials.footer')
</body>

</html>