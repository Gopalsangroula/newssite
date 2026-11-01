<!DOCTYPE html>
<html lang="ne" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo-meta', ['seo' => \App\Support\Seo::meta($post ?? $page ?? null, $title ?? null, $metaDescription ?? null)])

    <!-- Favicons -->
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <link rel="stylesheet" href="{{ asset('css/frontends.css') }}?v={{ filemtime(public_path('css/frontends.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v={{ filemtime(public_path('css/site-header.css')) }}">
    @if (request()->routeIs('frontend.details.show', 'frontend.frontend.pages.show', 'frontend.category.show'))
        {{-- Layout for content written in the Gutenberg editor: columns, galleries, covers. --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@wordpress/block-library@9.24.0/build-style/style.css">
    @endif
    @if (request()->routeIs('frontend.details.show'))
        <link rel="stylesheet" href="{{ asset('css/post-comments.css') }}?v={{ filemtime(public_path('css/post-comments.css')) }}">
    @endif

    @stack('styles')
    @livewireStyles
</head>


<body class="frontend-body">

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script>
        (function (d, s, id) {
            if (d.getElementById(id)) return;
            var js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10&appId=401514826619301";
            d.getElementsByTagName(s)[0].parentNode.insertBefore(js, d.getElementsByTagName(s)[0]);
        }(document, 'script', 'facebook-jssdk'));
    </script>
    <!-- Header -->
    @include('partials.frontend-header')
<!-- Main Content -->
<main id="main-content" class="frontend-main">
    <div class="uk-container uk-margin">
        {{ $slot }}
    </div>
</main>

<!-- Footer -->
<footer class="frontend-footer">
    @include('partials.frontend-footer')
</footer>
