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

<body class="ah-admin">

    <!-- Offcanvas Mobile Menu -->
    <div id="offcanvas-nav" uk-offcanvas="overlay: true; mode: slide">
        <div class="uk-offcanvas-bar ah-sidebar-offcanvas">
            <button class="uk-offcanvas-close" type="button" uk-close aria-label="{{ __('Close menu') }}"></button>
            @include('partials.admin-brand')
            @include('partials.admin-nav', ['navName' => 'ah-nav-mobile'])
        </div>
    </div>

    <!-- Sidebar (Desktop) -->
    <aside class="ah-sidebar uk-visible@m">
        @include('partials.admin-brand')
        @include('partials.admin-nav', ['navName' => 'ah-nav-desktop'])
    </aside>

    <!-- Main Content -->
    <div class="ah-main">
        @include('partials.admin-topbar')

        <!-- Page Content -->
        <main class="ah-content">
            {{ $slot }}
        </main>
    </div>

    @include('partials.footer')
</body>

</html>
