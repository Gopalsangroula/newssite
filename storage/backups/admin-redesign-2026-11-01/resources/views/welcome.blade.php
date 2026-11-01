
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary SEO Meta Tags -->
    <title>@yield('title', 'केत्तकुम लिबाङ पाङ्पत सयङ | Kettakum Libang Pangpat Sayang - Limbu Genealogy & Culture')</title>
    <meta name="title" content="@yield('meta_title', 'केत्तकुम लिबाङ पाङ्पत सयङ | Kettakum Libang Pangpat Sayang')">
    <meta name="description" content="@yield('meta_description', 'केत्तकुम लिबाङ पाङ्पत सयङ लिम्बू (याक्थुङ) समुदायको वंशावली, संस्कृति र इतिहास संरक्षण गर्ने सामाजिक संस्था हो। Official genealogy & cultural portal of Libang Limbu community.')">
    <meta name="keywords" content="@yield('meta_keywords', 'केत्तकुम लिबाङ पाङ्पत सयङ, Kettakum Libang Pangpat Sayang, Libang Vansawali, Limbu Genealogy, Libang lineage, Yakthung Limbu, Limbuwan, Vansawali Nepal')">
    <meta name="author" content="Kettakum Libang Pangpat Sayang">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">
    <meta property="fb:app_id" content="401514826619301" />
    <link rel="canonical" href="@yield('canonical', url()->current())" />

    <!-- Open Graph / Facebook / Social Media -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('meta_title', 'केत्तकुम लिबाङ पाङ्पत सयङ | Kettakum Libang Pangpat Sayang')">
    <meta property="og:description" content="@yield('meta_description', 'लिम्बू (याक्थुङ) समुदायको केत्तकुम लिबाङ वंश वंशावली, इतिहास, मुन्धुम र संस्कृति संरक्षण संस्था।')">
    <meta property="og:image" content="@yield('og_image', asset('img/logo-libang.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Kettakum Libang Pangpat Sayang">
    <meta property="og:locale" content="ne_NP">
    <meta property="og:locale:alternate" content="en_US">
    <meta property="fb:pages" content="737640319702735" />

    <!-- Twitter Card Data -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('canonical', url()->current())">
    <meta name="twitter:title" content="@yield('meta_title', 'केत्तकुम लिबाङ पाङ्पत सयङ | Kettakum Libang Pangpat Sayang')">
    <meta name="twitter:description" content="@yield('meta_description', 'Official genealogy organization site of Kettakum Libang Pangpat Sayang (Limbu Community).')">
    <meta name="twitter:image" content="@yield('og_image', asset('img/logo-libang.png'))">

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
    <link rel="stylesheet" href="{{ asset('css/frontendstyle.css') }}">

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


