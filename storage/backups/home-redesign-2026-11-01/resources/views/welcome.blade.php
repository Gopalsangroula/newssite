
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
    <meta name="theme-color" content="#8B0000">

    @yield('meta')

    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" sizes="any">
    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/2.0.0/trix.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/css/uikit.min.css" />
    <link rel="stylesheet" href="{{ asset('css/frontendstyle.css') }}?v={{ filemtime(public_path('css/frontendstyle.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v={{ filemtime(public_path('css/site-header.css')) }}">

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

<div class="uk-container">
    <livewire:frontend.home-parts.home-posts />
</div>

@include('partials.frontend-footer')


