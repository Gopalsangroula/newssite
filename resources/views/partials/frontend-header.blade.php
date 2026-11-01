<!-- Header -->
<header class="frontend-header">
    <a class="site-skip" href="#main-content">मुख्य सामग्रीमा जानुहोस्</a>

    <div class="site-top">
        @include('site.topnav')
    </div>

    <div class="site-mast">
        <div class="site-wrap site-mast__row">
            <a class="site-mast__logo" href="/">
                <img src="{{ asset('img/libang-logo-limbu.png') }}" alt="केत्तकुम लिबाङ पाङ्पत सयङ" width="578" height="112">
            </a>

            <p class="site-mast__date">
                {{ \App\Support\NepaliDate::full(now()) }}
                <span>{{ now()->format('F j, Y') }}</span>
            </p>
        </div>
    </div>

    {{-- Only this row sticks, so the logo and date give their height back to the page. --}}
    <div class="site-nav-band" uk-sticky>
        @include('site.nav')
    </div>

    @include('site.side-nav')
</header>
