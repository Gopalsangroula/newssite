{{-- $current is passed in because a Livewire update has no page route to compare against. --}}
@props(['current'])

<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">{{ __('Settings') }}</h1>
            <p class="ah-page-sub">{{ __('Manage your profile and account settings') }}</p>
        </div>
    </div>

    <nav class="ah-tabs" aria-label="{{ __('Settings') }}">
        @foreach (['profile' => __('Profile'), 'password' => __('Password'), 'appearance' => __('Appearance')] as $page => $label)
            <a
                href="{{ route('settings.' . $page) }}"
                @class(['is-active' => $current === $page])
                @if ($current === $page) aria-current="page" @endif
            >{{ $label }}</a>
        @endforeach
    </nav>

    <div class="ah-form-card--narrow">
        {{ $slot }}
    </div>
</div>
