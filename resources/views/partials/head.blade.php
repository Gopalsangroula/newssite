<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.23.11/dist/css/uikit.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

@foreach (['adminah', 'admin-lists', 'admin-media', 'admin-analytics', 'admin-editor', 'admin-content'] as $stylesheet)
    <link rel="stylesheet" href="{{ asset("css/{$stylesheet}.css") }}?v={{ filemtime(public_path("css/{$stylesheet}.css")) }}">
@endforeach
<!-- Trix Editor CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/trix/2.0.0/trix.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js@11.2.4/public/assets/styles/choices.min.css" integrity="sha384-uLFsUvpIOC9MdzZeDr7vuIUG6LgTaguBn469csIwzNIG9N9TFVoYgM4Ov5PG1V1l" crossorigin="anonymous" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js@11.2.4/public/assets/scripts/choices.min.js" integrity="sha384-0dGX4oSRqvcKtSNa5YGzTI3pkW4p3uoor1Izx0R5/7emRjiR619oCIBVnvM9k3tj" crossorigin="anonymous"></script>



@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
