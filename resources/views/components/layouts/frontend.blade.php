<!DOCTYPE html>
<html lang="ne" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme-init')

    @include('partials.seo-meta', ['seo' => \App\Support\Seo::meta($post ?? $page ?? null, $title ?? null, $metaDescription ?? null)])

    <!-- Favicons -->
    <link rel="icon" href="{{ asset('img/libang.jpeg') }}" sizes="any">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @include('partials.frontend-fonts')

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/css/uikit.min.css" integrity="sha384-J9OVyqGnSOrzhOrLDdDl4v9DL967HVjiBVrE9AN5pptwiWaZ2DJ6JvGd/8N1HaLP" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/frontends.css') }}?v={{ filemtime(public_path('css/frontends.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-header.css') }}?v={{ filemtime(public_path('css/site-header.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/site-dark.css') }}?v={{ filemtime(public_path('css/site-dark.css')) }}">
    @if (request()->routeIs('frontend.search'))
        <link rel="stylesheet" href="{{ asset('css/site-tools.css') }}?v={{ filemtime(public_path('css/site-tools.css')) }}">
    @endif
    @if (request()->routeIs('frontend.details.show', 'frontend.frontend.pages.show'))
        {{-- Layout for content written in the Gutenberg editor: columns, galleries, covers. --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@wordpress/block-library@9.24.0/build-style/style.css" integrity="sha384-w1e97srBN7/fM0R1EDV6bsQdayAKiPM1f9/wkIRHVfpkvJjARu2hiySVlmExfJz8" crossorigin="anonymous">
    @endif
    @if (request()->routeIs('frontend.details.show'))
        <link rel="stylesheet" href="{{ asset('css/post-comments.css') }}?v={{ filemtime(public_path('css/post-comments.css')) }}">
    @endif

    @stack('styles')
</head>


<body class="frontend-body">

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

@include('partials.frontend-scripts')
</body>

</html>
