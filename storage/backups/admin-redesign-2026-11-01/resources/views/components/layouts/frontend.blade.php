<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Facebook Page ID -->
    <meta property="fb:pages" content="737640319702735">

    <!-- Basic SEO -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Appharu">

    @php
        // Detect which model is available and set meta variables
        $currentModel = $post ?? $page ?? null;
        $currentUrl = url()->current();
        $siteName = config('app.name');
        
        // Set default values
        $metaTitle = $title ?? $siteName;
        $metaDescription = $metaDescription ?? 'Nepal\'s oldest leading online portal';
        $metaImage = asset('img/libang.jpeg'); // Default fallback image
        
        // Override with model data if available
        if ($currentModel) {
            $metaTitle = $currentModel->title . ' | ' . $siteName;
            $metaDescription = $metaDescription ?? 
                Str::limit(strip_tags($currentModel->excerpt ?? $currentModel->content ?? $currentModel->body ?? $currentModel->sub_title ?? ''), 160);
            
            // Get image from model
            if (isset($currentModel->cover_image) && $currentModel->cover_image) {
                $metaImage = asset('storage/' . $currentModel->cover_image);
            } elseif (isset($currentModel->featured_image) && $currentModel->featured_image) {
                $metaImage = asset('storage/' . $currentModel->featured_image);
            } elseif (isset($currentModel->media) && $currentModel->media->isNotEmpty()) {
                $metaImage = $currentModel->media->first()->getUrl();
            }
        }
        
        // Determine content type
        $contentType = isset($post) ? 'article' : (isset($page) ? 'website' : 'website');
    @endphp

    <!-- Primary Meta Tags -->
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $currentUrl }}">

    <!-- Open Graph Meta Tags -->
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:type" content="{{ $contentType }}">
    <meta property="og:locale" content="en_US">
    
    <!-- Image Dimensions for Open Graph -->
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $metaTitle }}">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    <meta name="twitter:site" content="@appharu">
    <meta name="twitter:creator" content="@appharu">
    <meta name="twitter:image:alt" content="{{ $metaTitle }}">

    <!-- Additional Article Meta Tags for Posts -->
    @if(isset($post) && $post)
        <meta property="article:published_time" content="{{ $post->created_at->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $post->updated_at->toIso8601String() }}">
        <meta property="article:author" content="{{ $post->createdBy->name ?? 'Appharu' }}">
        
        @if($post->category)
            <meta property="article:section" content="{{ $post->category->name }}">
        @endif
        
        @if($post->tags && $post->tags->count() > 0)
            @foreach($post->tags as $tag)
                <meta property="article:tag" content="{{ $tag->name }}">
            @endforeach
        @endif
    @endif

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
    <link rel="stylesheet" href="{{ asset('css/frontends.css') }}">
    

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
<main class="frontend-main">
    <div class="uk-container uk-margin">
        {{ $slot }}
    </div>
</main>

<!-- Footer -->
<footer class="frontend-footer">
    @include('partials.frontend-footer')
</footer>
