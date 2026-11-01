<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') | @yield('subtitle') | {{ config('app.name', 'Libang') }}</title>
    <link rel="icon" href="{{ asset('img/libang.jpeg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Styles live here, not in a stylesheet, so this page still works when the app does not. --}}
    <style>
        *{
            box-sizing: border-box;
        }
        body{
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px 16px;
            background: #f4f6f9;
            color: #111a2e;
            font-family: "Inter", "Noto Sans Devanagari", system-ui, -apple-system, "Segoe UI", sans-serif;
            line-height: 1.6;
        }
        .err{
            width: 100%;
            max-width: 620px;
            padding: 40px 32px;
            border: 1px solid #e6e9ef;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 8px 28px rgba(16, 24, 40, 0.06);
            text-align: center;
            animation: err-rise 0.5s ease-out both;
        }
        .err-logo img{
            max-width: 100%;
            height: auto;
            max-height: 58px;
        }
        .err-code{
            margin: 24px 0 0;
            background: linear-gradient(120deg, #2456d6, #0f1c36);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-size: 84px;
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1;
        }
        .err-title{
            margin: 12px 0 0;
            font-size: 24px;
            font-weight: 700;
            line-height: 1.35;
        }
        .err-subtitle{
            margin: 2px 0 0;
            color: #5f6b84;
            font-size: 15px;
        }
        .err-text{
            max-width: 460px;
            margin: 14px auto 0;
            color: #5f6b84;
            font-size: 15px;
        }
        .err-actions{
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
            margin-top: 24px;
        }
        .err-button{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 18px;
            border: 1px solid #d3d9e2;
            border-radius: 8px;
            background: #ffffff;
            color: #111a2e;
            cursor: pointer;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }
        .err-button:hover{
            background: #f7f8fb;
        }
        .err-button--primary{
            border-color: #2456d6;
            background: #2456d6;
            color: #ffffff;
        }
        .err-button--primary:hover{
            border-color: #1c46b3;
            background: #1c46b3;
        }
        .err-more{
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid #e6e9ef;
            text-align: left;
        }
        .err-more-title{
            margin: 0 0 8px;
            color: #5f6b84;
            font-size: 13px;
            font-weight: 600;
        }
        .err-links{
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0 0 18px;
            padding: 0;
            list-style: none;
        }
        .err-links a{
            display: inline-block;
            padding: 5px 12px;
            border-radius: 999px;
            background: #e8eefc;
            color: #1c46b3;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }
        .err-links a:hover{
            background: #dbe4fb;
        }
        .err-posts{
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .err-posts li{
            padding: 8px 0;
            border-top: 1px solid #eef0f4;
        }
        .err-posts li:first-child{
            border-top: 0;
        }
        .err-posts a{
            color: #111a2e;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
        }
        .err-posts a:hover{
            color: #2456d6;
        }
        @keyframes err-rise{
            from{
                opacity: 0;
                transform: translateY(12px);
            }
            to{
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (prefers-reduced-motion: reduce){
            .err{
                animation: none;
            }
        }
        @media (max-width: 480px){
            .err{
                padding: 28px 18px;
            }
            .err-code{
                font-size: 64px;
            }
            .err-title{
                font-size: 21px;
            }
        }
    </style>
</head>
<body>
    @php
        // This page also shows when the database is down, so the user lookup must not be able to fail.
        try {
            $errorUser = auth()->user();
        } catch (\Throwable) {
            $errorUser = null;
        }

        $inAdmin = request()->is('admin', 'admin/*', 'dashboard', 'settings', 'settings/*');
    @endphp

    <main class="err">
        <a href="{{ url('/') }}" class="err-logo">
            <img src="{{ asset('img/libang-logo-limbu.png') }}" alt="{{ config('app.name', 'Libang') }}">
        </a>

        <p class="err-code">@yield('code')</p>
        <h1 class="err-title">@yield('title')</h1>
        <p class="err-subtitle">@yield('subtitle')</p>
        <p class="err-text">@yield('message')</p>

        <div class="err-actions">
            @hasSection('actions')
                @yield('actions')
            @else
                @if ($inAdmin && $errorUser)
                    <a href="{{ url('/dashboard') }}" class="err-button err-button--primary">Back to Dashboard</a>
                @else
                    <a href="{{ url('/') }}" class="err-button err-button--primary">गृहपृष्ठ (Home)</a>
                @endif
                <button type="button" class="err-button" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">पछाडि जानुहोस् (Go back)</button>
                @if ($inAdmin && ! $errorUser)
                    <a href="{{ url('/login') }}" class="err-button">Log in</a>
                @endif
            @endif
        </div>

        @yield('more')
    </main>
</body>
</html>
