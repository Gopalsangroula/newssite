<div id="offcanvas-nav" class="uk-offcanvas" uk-offcanvas="mode: push; overlay: true">
    <div class="uk-offcanvas-bar ah-libang-side-nav">
        <button class="uk-offcanvas-close" type="button" uk-close aria-label="मेनु बन्द गर्नुहोस्"></button>

        <div class="site-side-tools">
            <a class="site-tool" href="/search" aria-label="खोज्नुहोस्"><span uk-icon="search"></span></a>
            <a class="site-tool" href="/saved-posts" aria-label="सेभ गरिएका सामग्री"><span uk-icon="bookmark"></span></a>
            @include('partials.theme-toggle', ['class' => 'site-tool'])
        </div>

        <ul class="uk-list side-nav uk-nav-default uk-nav-parent-icon" uk-nav>
            <li><a href="/"><span class="uk-margin-small-right" uk-icon="home"></span>गृहपृष्‍ठ</a></li>
            <li class="uk-parent">
                <a href="/news"><span class="uk-margin-small-right" uk-icon="rss"></span>समाचार<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
                    @foreach (\App\Models\Category::newsSections() as $section)
                        <li><a href="{{ route('frontend.category.show', $section->slug) }}">{{ $section->name }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="/notice"><span class="uk-margin-small-right" uk-icon="bell"></span>सूचना</a></li>
            <li><a href="/activities"><span class="uk-margin-small-right" uk-icon="calendar"></span>गतिविधि</a></li>
            <li class="uk-parent">
                <a href="/death-culture"><span class="uk-margin-small-right" uk-icon="star"></span>संस्कृति<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
                    <li><a href="/death-culture">मृत्यु संस्कार</a></li>
                    <li><a href="/ritual">अनुष्ठान</a></li>
                    <li><a href="/marriage-ceremony">विवाह संस्कार</a></li>
                    <li><a href="/auspicious-deeds">शुभ कार्यहरू</a></li>
                    <li><a href="/person-personality">व्यक्ति र व्यक्तित्व</a></li>
                    <li><a href="/sirijangaga-lipi">सिरिजङ्गा लिपि</a></li>
                    <li><a href="/historical-sthal">ऐतिहासिक स्थल तथा तस्वीर</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="/publication"><span class="uk-margin-small-right" uk-icon="file-text"></span>प्रकाशन<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
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
            </li>
            <li class="uk-parent">
                <a href="/photo-gallery"><span class="uk-margin-small-right" uk-icon="image"></span>मल्टिमिडिया<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
                    <li><a href="/photo-gallery">फोटो ग्यालरी</a></li>
                    <li><a href="/video">भिडियो</a></li>
                </ul>
            </li>
            <li class="uk-parent">
                <a href="/pages/about"><span class="uk-margin-small-right" uk-icon="users"></span>संस्था<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
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
            </li>
            <li class="uk-parent">
                <a href="/tools"><span class="uk-margin-small-right" uk-icon="thumbnails"></span>युटिलिटी<span uk-icon="icon: chevron-down; ratio: 1.1" class="dropdown-icon"></span></a>
                <ul class="uk-nav-sub side-nav">
                    @foreach (config('site-tools') as $tool)
                        <li><a href="{{ route($tool['route']) }}">{{ $tool['name'] }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li><a href="/pages/contact"><span class="uk-margin-small-right" uk-icon="receiver"></span>सम्पर्क गर्नुहोस्</a></li>
        </ul>

        <div class="uk-margin-medium-top">
            @include('partials.frontend-social')
        </div>
    </div>
</div>
