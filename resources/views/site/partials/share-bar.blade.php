{{-- Plain links, so sharing needs no outside script. Takes $shareUrl and $shareTitle. --}}
@php
    $shareLinks = [
        ['name' => 'Facebook', 'icon' => 'facebook', 'url' => 'https://www.facebook.com/sharer/sharer.php?u='.urlencode($shareUrl)],
        ['name' => 'WhatsApp', 'icon' => 'whatsapp', 'url' => 'https://api.whatsapp.com/send?text='.urlencode($shareTitle.' '.$shareUrl)],
        ['name' => 'X', 'icon' => 'x', 'url' => 'https://twitter.com/intent/tweet?url='.urlencode($shareUrl).'&text='.urlencode($shareTitle)],
        ['name' => 'Telegram', 'icon' => 'telegram', 'url' => 'https://t.me/share/url?url='.urlencode($shareUrl).'&text='.urlencode($shareTitle)],
    ];
@endphp

<div class="post-share" role="group" aria-label="सेयर गर्नुहोस्">
    @foreach ($shareLinks as $link)
        <a class="post-share__link post-share__link--{{ $link['icon'] }}" href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer nofollow" aria-label="{{ $link['name'] }} मा सेयर गर्नुहोस्">
            <span uk-icon="icon: {{ $link['icon'] }}; ratio: 0.9"></span>
        </a>
    @endforeach
</div>
