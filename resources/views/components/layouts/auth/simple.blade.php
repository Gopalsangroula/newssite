<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <link rel="stylesheet" href="{{ asset('css/admin-auth.css') }}?v={{ filemtime(public_path('css/admin-auth.css')) }}">
    </head>
    <body class="ah-auth antialiased">
        <div class="ah-auth-shell">
            <aside class="ah-auth-brand">
                <div class="ah-auth-emblem">
                    <img src="{{ asset('img/libang.jpeg') }}" alt="">
                </div>
                <h1 class="ah-auth-name">केत्तकुम लिबाङ पाङ्पत सयङ</h1>
                <p class="ah-auth-subname">Kettakum Libang Pangpat Sayang</p>
                <p class="ah-auth-motto">पुर्खाको इतिहास संरक्षण गरौँ, वर्तमानलाई संगठित गरौँ र भावी पुस्ताको भविष्य निर्माण गरौँ</p>
            </aside>

            <main class="ah-auth-main">
                <div class="ah-auth-panel">
                    <a href="{{ url('/') }}" class="ah-auth-logo">
                        <img src="{{ asset('img/libang-logo-limbu.png') }}" alt="{{ config('app.name', 'Libang') }}">
                    </a>

                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>

                    <a href="{{ url('/') }}" class="ah-auth-back">&larr; {{ __('Back to website') }}</a>
                </div>
            </main>
        </div>
        @fluxScripts
    </body>
</html>
