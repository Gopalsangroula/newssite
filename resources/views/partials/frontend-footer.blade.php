@php
    $readyPages = \App\Models\Page::published()->whereIn('slug', ['contact', 'privacy-policy'])->pluck('slug');

    $footerLinks = array_filter([
        'हाम्रो बारेमा' => '/pages/about',
        'संस्थाको संरचना' => '/pages/organizational-structure',
        'केन्द्रीय कार्य समिति' => '/centralcomittee',
        'अक्षयकोष' => '/pages/endowment-fund',
        'सहयोग गर्नुहोस्' => '/pages/donate',
        'सम्पर्क गर्नुहोस्' => $readyPages->contains('contact') ? '/pages/contact' : null,
        'गोपनीयता नीति' => $readyPages->contains('privacy-policy') ? '/pages/privacy-policy' : null,
    ]);
@endphp

<a href="#" class="ah-scrtop" uk-totop="uk-totop" uk-scroll="uk-scroll" aria-label="माथि जानुहोस्"></a>
<div class="site-footer">
    <div class="site-wrap site-footer__grid">
        <div class="site-footer__org">
            <a href="/" class="site-footer__logo"><img src="{{ asset('img/libang.jpeg') }}" alt="" width="84" height="84"></a>
            <div>
                <h2>केत्तकुम लिबाङ पाङ्पत सयङ</h2>
                <p>केन्द्रीय कार्यालय</p>
                <p>विर्तामोड - १, झापा</p>
            </div>
        </div>

        <div class="site-footer__contact">
            <h3>सम्पर्क</h3>
            <p><a href="tel:+9779851129961">9851129961</a></p>
            <p><a href="mailto:libangsayang@gmail.com">libangsayang@gmail.com</a></p>
            <div class="site-footer__social">
                @include('partials.frontend-social')
            </div>
        </div>

        <nav class="site-footer__menu" aria-label="समाचार">
            <h3>समाचार</h3>
            <ul>
                @foreach (\App\Models\Category::newsSections() as $section)
                    <li><a href="{{ route('frontend.category.show', $section->slug) }}">{{ $section->name }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav class="site-footer__menu" aria-label="उपयोगी लिंकहरू">
            <h3>उपयोगी लिंकहरू</h3>
            <ul>
                @foreach ($footerLinks as $label => $url)
                    <li><a href="{{ $url }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav class="site-footer__menu" aria-label="युटिलिटी">
            <h3>युटिलिटी</h3>
            <ul>
                @foreach (config('site-tools') as $tool)
                    <li><a href="{{ route($tool['route']) }}">{{ $tool['name'] }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div class="site-footer__credit">
        <div class="site-wrap">
            &copy;{{ date('Y') }} केत्तकुम लिबाङ पाङ्पत सयङ |
            Designed &amp; Developed by :
            <a href="https://www.appharu.com/?ref=libang">Appharu</a>
        </div>
    </div>
</div>