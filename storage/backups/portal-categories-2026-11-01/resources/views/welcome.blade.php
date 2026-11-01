
<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-meta', ['seo' => \App\Support\Seo::meta()])

    <!-- SEO Favicons & App Icons -->
    <link rel="icon" href="{{ asset('logo.jpg') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/logo-libang.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/logo-libang.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/logo-libang.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#00286b">

    @yield('meta')

    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" sizes="any">
    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/css/uikit.min.css" />
    <link rel="stylesheet" href="{{ asset('css/frontendstyle.css') }}?v={{ filemtime(public_path('css/frontendstyle.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v={{ filemtime(public_path('css/site-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/frontend-portal.css') }}?v={{ filemtime(public_path('css/frontend-portal.css')) }}">

    @stack('styles')
    @livewireStyles
</head>
<body class="frontend-body">
    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script>
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10&appId=401514826619301";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
@include('partials.frontend-header')

<main id="main-content">
    <livewire:frontend.home-parts.home-posts />
</main>

@include('partials.frontend-footer')


