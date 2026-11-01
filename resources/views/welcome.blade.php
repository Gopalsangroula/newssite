<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    @include('partials.theme-init')

    @include('partials.seo-meta', ['seo' => \App\Support\Seo::meta()])

    <!-- SEO Favicons & App Icons -->
    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo-libang.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/logo-libang.png') }}">
    <meta name="theme-color" content="#00286b">

    @yield('meta')

    @include('partials.frontend-fonts')

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/css/uikit.min.css" integrity="sha384-J9OVyqGnSOrzhOrLDdDl4v9DL967HVjiBVrE9AN5pptwiWaZ2DJ6JvGd/8N1HaLP" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/frontendstyle.css') }}?v={{ filemtime(public_path('css/frontendstyle.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v={{ filemtime(public_path('css/site-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/frontend-portal.css') }}?v={{ filemtime(public_path('css/frontend-portal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-dark.css') }}?v={{ filemtime(public_path('css/site-dark.css')) }}">

    @stack('styles')
</head>
<body class="frontend-body">
@include('partials.frontend-header')

<main id="main-content">
    @include('site.home-parts.home-posts')
</main>

@include('partials.frontend-footer')

@include('partials.frontend-scripts')
</body>

</html>
