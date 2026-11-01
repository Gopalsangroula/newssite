<div>
    <div class="site-wrap">
        <nav class="uk-navbar site-nav" uk-navbar="delay-hide: 200" aria-label="मुख्य मेनु">
            {{-- Large screens: the full menu with its dropdowns. --}}
            <ul class="uk-navbar-nav site-menu">
                <li @class(['uk-active' => request()->is('/')])>
                    <a href="/" aria-label="गृहपृष्ठ"><span uk-icon="home"></span></a>
                </li>
                <li @class(['uk-active' => request()->is('news', ...\App\Models\Category::newsSections()->pluck('slug'))])>
                    <a href="/news">समाचार<span uk-icon="icon: chevron-down; ratio: 0.75"></span></a>
                    <div class="uk-navbar-dropdown site-drop site-drop--wide">
                        <ul>
                            @foreach (\App\Models\Category::newsSections() as $section)
                                <li><a href="{{ route('frontend.category.show', $section->slug) }}">{{ $section->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
                <li @class(['uk-active' => request()->is('notice')])><a href="/notice">सूचना</a></li>
                <li @class(['uk-active' => request()->is('activities')])><a href="/activities">गतिविधि</a></li>
                <li>
                    <a href="/death-culture">संस्कृति<span uk-icon="icon: chevron-down; ratio: 0.75"></span></a>
                    <div class="uk-navbar-dropdown site-drop">
                        <ul>
                            <li><a href="/death-culture">मृत्यु संस्कार</a></li>
                            <li><a href="/ritual">अनुष्ठान</a></li>
                            <li><a href="/marriage-ceremony">विवाह संस्कार</a></li>
                            <li><a href="/auspicious-deeds">शुभ कार्यहरू</a></li>
                            <li><a href="/person-personality">व्यक्ति र व्यक्तित्व</a></li>
                            <li><a href="/sirijangaga-lipi">सिरिजङ्गा लिपि</a></li>
                            <li><a href="/historical-sthal">ऐतिहासिक स्थल तथा तस्वीर</a></li>
                        </ul>
                    </div>
                </li>
                <li @class(['uk-active' => request()->is('publication')])>
                    <a href="/publication">प्रकाशन<span uk-icon="icon: chevron-down; ratio: 0.75"></span></a>
                    <div class="uk-navbar-dropdown site-drop site-drop--wide">
                        <ul>
                            <li><a href="/pages/genealogy-book-pdf">वंशावली पुस्तक</a></li>
                            <li><a href="/details/panapata-lbna-vashaka-itahasa-tatha-vashaval-1">प्रकाशन मण्डल</a></li>
                            <li><a href="/pages/Dedication-to-heroic-ancestors">समर्पण</a></li>
                            <li><a href="/shabhakamana">शुभकामना</a></li>
                            <li><a href="/details/parakhaka-khaja-vashaka-pahacana-ra-bhavashhayaka-sakalpa-lkasha-lbna">सम्पादकीय</a></li>
                            <li><a href="/details/manava-sashhata-manathhama-kamabthanata">याक्थुङ लिम्बूको इतिहास</a></li>
                            <li><a href="/pangpata-manggenna">वंशको उदय र माङगेन्ना यक</a></li>
                            <li><a href="/details/tanathhama-makhama-vavaha">विवाह संस्कार</a></li>
                            <li><a href="/sahayagdata-introduction">सहयोगदाता परिचय</a></li>
                            <li><a href="/ghardhuri-bibaran">घरधुरी विवरण शैक्षिक अवस्था</a></li>
                        </ul>
                    </div>
                </li>
                <li @class(['uk-active' => request()->is('photo-gallery', 'video')])>
                    <a href="/photo-gallery">मल्टिमिडिया<span uk-icon="icon: chevron-down; ratio: 0.75"></span></a>
                    <div class="uk-navbar-dropdown site-drop">
                        <ul>
                            <li><a href="/photo-gallery">फोटो ग्यालरी</a></li>
                            <li><a href="/video">भिडियो</a></li>
                        </ul>
                    </div>
                </li>
                <li @class(['uk-active' => request()->is('pages/about', 'pages/organizational-structure', 'centralcomittee')])>
                    <a href="/pages/about">संस्था<span uk-icon="icon: chevron-down; ratio: 0.75"></span></a>
                    <div class="uk-navbar-dropdown site-drop site-drop--wide">
                        <ul>
                            <li><a href="/pages/about">हाम्रो बारेमा</a></li>
                            <li><a href="/pages/organizational-structure">संस्थाको संरचना</a></li>
                            <li><a href="/centralcomittee">केन्द्रीय कार्य समिति</a></li>
                            <li><a href="/pages/endowment-fund">अक्षयकोष</a></li>
                            <li><a href="/upatyaka">उपत्यका शाखा</a></li>
                            <li><a href="/sangkhuwasabha">संखुवासभा शाखा</a></li>
                            <li><a href="/sunsari">सुनसरी शाखा</a></li>
                            <li><a href="/jhapamorang">झापा मोरङ शाखा</a></li>
                            <li><a href="/phalelung">फालेलुङ शाखा</a></li>
                            <li><a href="/phungling">फुङलिङ शाखा</a></li>
                            <li><a href="/phulbari">फुलबारी शाखा</a></li>
                            <li><a href="/phakumba">फाकुम्बा शाखा</a></li>
                            <li><a href="/libangshakha">लिबाङ शाखा</a></li>
                        </ul>
                    </div>
                </li>
            </ul>

            {{-- Small screens: the most used links to swipe through. The rest are in the side menu. --}}
            <ul class="site-chips">
                <li><a href="/">गृहपृष्ठ</a></li>
                <li><a href="/news">समाचार</a></li>
                <li><a href="/notice">सूचना</a></li>
                <li><a href="/activities">गतिविधि</a></li>
                <li><a href="/publication">प्रकाशन</a></li>
                <li><a href="/photo-gallery">फोटो ग्यालरी</a></li>
                <li><a href="/video">भिडियो</a></li>
                <li><a href="/tools">युटिलिटी</a></li>
            </ul>

            <div class="site-nav__tools">
                <a class="site-nav__donate" href="/pages/donate">
                    <span uk-icon="icon: heart; ratio: 0.85"></span>सहयोग<span class="site-nav__donate-more"> गर्नुहोस्</span>
                </a>
                <button class="site-tool" type="button" uk-toggle="target: #site-search" data-search-toggle aria-label="खोज्नुहोस्">
                    <span uk-icon="search"></span>
                </button>
                <a class="site-tool site-tool--wide" href="/saved-posts" aria-label="सेभ गरिएका सामग्री">
                    <span uk-icon="bookmark"></span>
                </a>
                @include('partials.theme-toggle', ['class' => 'site-tool site-tool--wide'])
                <button class="site-tool site-nav__toggle" type="button" uk-toggle="target: #offcanvas-nav" aria-label="मेनु खोल्नुहोस्">
                    <span uk-icon="icon: menu; ratio: 1.2"></span>
                </button>
            </div>
        </nav>
    </div>

    <div id="site-search" class="site-search" hidden>
        <form class="site-wrap site-search__form" action="{{ route('frontend.search') }}" method="get" role="search">
            <label class="site-sr" for="site-search-input">खोज्ने शब्द</label>
            <input id="site-search-input" type="search" name="q" placeholder="समाचार वा सामग्री खोज्नुहोस्" autocomplete="off">
            <button type="submit">खोज्नुहोस्</button>
        </form>
    </div>
</div>
