@php
    $topics = [
        [
            'tab' => 'मृत्यु संस्कार',
            'title' => 'हाम्रो लिबाङ मृत्यु संस्कार र संस्कृति',
            'url' => '/death-culture',
            'image' => 'https://libang.org.np/storage/media/2026/08/limbu-death-culture.jpg',
            'text' => 'नेपालको बहुसांस्कृतिक र बहुजातीय पहिचानमा किराँती संस्कार र संस्कृतिको अत्यन्तै विशिष्ट र महत्त्वपूर्ण स्थान छ। मुख्यगरी राई, लिम्बू, सुनुवार, र याक्खा जातिका समुदायहरूमा फैलिएको यो संस्कृति प्राचीन, प्रकृतिमुखी र मौलिक जीवनशैलीसँग जोडिएको छ।',
        ],
        [
            'tab' => 'अनुष्ठान',
            'title' => 'लिबाङ अनुष्ठान',
            'url' => '/ritual',
            'image' => 'https://libang.org.np/storage/media/2026/08/anustha.jpg',
            'text' => 'किराँती समुदायभित्र पर्ने लिम्बू (याक्थुङ) जातिमा जन्मदेखि मृत्युपर्यन्त गरिने विविध धार्मिक तथा सामाजिक अनुष्ठानहरू रहेका छन्। लिम्बू समुदायका सम्पूर्ण अनुष्ठानहरू उनीहरूको मौखिक वेद/दर्शन ‘मुन्धुम’ मा आधारित हुन्छन्। यी अनुष्ठानहरू फेदाङ्मा, साम्बा, येबा तथा येमा (पुजारी/धामी) हरूद्वारा सम्पन्न गराइन्छ।',
        ],
        [
            'tab' => 'विवाह संस्कार',
            'title' => 'लिबाङ विवाह संस्कार',
            'url' => '/marriage-ceremony',
            'image' => 'https://libang.org.np/storage/media/2026/08/limbu-marriage.jpg',
            'text' => 'किराँती (लिम्बू/याक्थुङ) संस्कृतिमा विवाह संस्कारलाई ‘मेखिम’ (Mekkhim) भनिन्छ। लिम्बू समुदायमा विवाह केवल दुई व्यक्तिको मिलन मात्र नभई दुई कुल, परिवार र समाजलाई मुन्धुमी विधि अनुसार जोड्ने एउटा अत्यन्तै पवित्र र सामाजिक-धार्मिक अनुष्ठान हो।',
        ],
        [
            'tab' => 'शुभ कार्यहरू',
            'title' => 'शुभ कार्यहरू',
            'url' => '/auspicious-deeds',
            'image' => 'https://libang.org.np/storage/media/2026/08/limbu-marriage.jpg',
            'text' => 'किराँती लिम्बू (याक्थुङ) समुदायमा ‘लिबाङ’ (Libang) थर/थान वा वंश विशेषसँग जोडिएका उत्सव, माङगेन्ना र पारम्परिक अनुष्ठानहरूमा विभिन्न प्रकारका शुभ कार्य तथा मङ्गलमय अनुष्ठानहरू सम्पन्न गरिन्छन्।',
        ],
        [
            'tab' => 'व्यक्ति व्यक्तित्व',
            'title' => 'किराँती लिम्बू (लिबाङ) समुदायमा व्यक्ति र व्यक्तित्व',
            'url' => '/person-personality',
            'image' => 'https://libang.org.np/storage/media/2026/08/limbu.JPG',
            'text' => 'किराँती लिम्बू (याक्थुङ) समुदायमा ‘व्यक्ति’ (Individual) र उनीहरूको ‘व्यक्तित्व’ (Personality/Character) लाई मुन्धुमी दर्शन, सामाजिक मूल्य-मान्यता र सांस्कृतिक जगसँग जोडेर हेरिन्छ।',
        ],
    ];
@endphp

<section class="hp-band">
    <div class="hp-wrap">
        @include('livewire.frontend.home-parts.section-head', ['title' => 'संस्कार र संस्कृति', 'url' => '/death-culture', 'icon' => 'album'])

        <div class="hp-culture">
            <ul class="hp-culture__tabs" uk-switcher="connect: #hp-culture-topics; animation: uk-animation-fade">
                @foreach ($topics as $topic)
                    <li @class(['uk-active' => $loop->first])><a href="#">{{ $topic['tab'] }}</a></li>
                @endforeach
            </ul>

            <div id="hp-culture-topics" class="uk-switcher">
                @foreach ($topics as $topic)
                    <article @class(['hp-culture__topic', 'uk-active' => $loop->first])>
                        <img src="{{ $topic['image'] }}" alt="" loading="lazy" decoding="async">
                        <div class="hp-culture__text">
                            <h3><a href="{{ $topic['url'] }}">{{ $topic['title'] }}</a></h3>
                            <p>{{ $topic['text'] }}</p>
                            <a class="hp-more hp-more--light" href="{{ $topic['url'] }}">थप जानकारी</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
