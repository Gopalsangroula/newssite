<!-- Header -->
<header class="frontend-header">
    <a class="site-skip" href="#main-content">मुख्य सामग्रीमा जानुहोस्</a>

    <div class="site-top">
        <livewire:frontend.topnav />
    </div>

    <div class="site-mast">
        <a class="site-mast__logo" href="/">
            <img src="{{ asset('img/libang-logo-limbu.png') }}" alt="केत्तकुम लिबाङ पाङ्पत सयङ" width="578" height="112">
        </a>
    </div>

    {{-- Only this row sticks, so the logo and date give their height back to the page. --}}
    <div class="site-nav-band" uk-sticky>
        <livewire:frontend.nav />
    </div>

    <livewire:frontend.side-nav />
</header>
