{{-- A section title with its link. Takes $title, $url and $icon. --}}
<header class="hp-head">
    <span class="hp-head__icon" uk-icon="icon: {{ $icon }}; ratio: 0.95"></span>
    <h2 class="hp-head__title"><a href="{{ $url }}">{{ $title }}</a></h2>
    <a class="hp-head__more" href="{{ $url }}" uk-icon="icon: chevron-right; ratio: 0.95" aria-label="{{ $title }}: सबै हेर्नुहोस्"></a>
</header>
