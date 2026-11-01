{{-- Search and social tags for the public site. Expects $seo from App\Support\Seo::meta(). --}}
@php
    $seoSite = $seo['site'];
@endphp

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
@if ($seo['keywords'])
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="author" content="{{ $seo['author'] }}">
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['url'] }}">
@if ($seoSite['google_verification'])
    <meta name="google-site-verification" content="{{ $seoSite['google_verification'] }}">
@endif
@if ($seoSite['bing_verification'])
    <meta name="msvalidate.01" content="{{ $seoSite['bing_verification'] }}">
@endif

<!-- Open Graph: link previews on Facebook, Messenger, WhatsApp and Viber -->
<meta property="og:site_name" content="{{ $seoSite['site_name'] }}">
<meta property="og:locale" content="ne_NP">
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:url" content="{{ $seo['url'] }}">
<meta property="og:title" content="{{ $seo['social_title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">
@if ($seo['image_size'])
    <meta property="og:image:width" content="{{ $seo['image_size']['width'] }}">
    <meta property="og:image:height" content="{{ $seo['image_size']['height'] }}">
@endif
<meta property="og:image:alt" content="{{ $seo['social_title'] }}">
@if ($seoSite['facebook_app_id'])
    <meta property="fb:app_id" content="{{ $seoSite['facebook_app_id'] }}">
@endif
@if ($seoSite['facebook_page_id'])
    <meta property="fb:pages" content="{{ $seoSite['facebook_page_id'] }}">
@endif

<!-- Twitter / X card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['social_title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
<meta name="twitter:image:alt" content="{{ $seo['social_title'] }}">
@if ($seoSite['twitter_handle'])
    <meta name="twitter:site" content="{{ $seoSite['twitter_handle'] }}">
@endif

@if ($seo['post'])
    <meta property="article:published_time" content="{{ $seo['published_at']->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $seo['modified_at']->toIso8601String() }}">
    <meta property="article:author" content="{{ $seo['author'] }}">
    @if ($seo['post']->category)
        <meta property="article:section" content="{{ $seo['post']->category->name }}">
    @endif
    @foreach ($seo['post']->tags as $tag)
        <meta property="article:tag" content="{{ $tag->name }}">
    @endforeach
@endif

@if ($seo['schema'])
    <script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endif
