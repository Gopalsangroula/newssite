<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <meta name="robots" content="noindex">
    <title>403 — Forbidden | {{ config('app.name') }}</title>
    @include('partials.footer')
</head>
<body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">

    <div class="uk-container uk-container-small uk-text-center" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
        <div>
            <div class="uk-margin-small-bottom">
                <div class="uk-child-width-1-3@m uk-text-center" uk-grid>
                    <div>
                        
                    </div>
                    <div>
                       <x-app-logo-icon class="inline-block size-12 fill-current text-black dark:text-white" />
                    </div>
                    <div>
                       
                    </div>
                </div>
            </div>

            <h1 class="uk-heading-medium uk-margin-remove">403</h1>
            <p class="uk-text-lead uk-margin-small">You don’t have permission to access this page.</p>

            @if (session('error'))
                <div class="uk-alert-danger uk-margin" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            @isset($message)
                <p class="uk-text-meta uk-margin-small">{{ $message }}</p>
            @endisset

            <div class="uk-margin">
                <a href="{{ url()->previous() }}" class="uk-button uk-button-default uk-margin-small-right" aria-label="Go back">Go Back</a>
                <a href="{{ route('home') }}" class="uk-button uk-button-primary uk-margin-small-right" aria-label="Go to homepage" wire:navigate>Home</a>

                @auth
                    @if (Route::has('dashboard'))
                        <a href="{{ route('dashboard') }}" class="uk-button uk-button-secondary" aria-label="Go to dashboard" wire:navigate>Dashboard</a>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="uk-button uk-button-secondary" aria-label="Login" wire:navigate>Login</a>
                    @endif
                @endauth
            </div>

            <div class="uk-text-muted uk-margin-top">
                If you believe this is a mistake, contact an administrator or request the correct role.
            </div>
        </div>
    </div>

    @fluxScripts
</body>
</html>
