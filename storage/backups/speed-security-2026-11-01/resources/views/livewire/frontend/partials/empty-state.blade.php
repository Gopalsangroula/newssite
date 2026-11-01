{{-- Message for a list with nothing in it. Takes $title and $text, and optionally $icon. --}}
<div class="lb-empty">
    <span class="lb-empty__icon" uk-icon="icon: {{ $icon ?? 'file-text' }}; ratio: 1.6" wire:ignore></span>
    <h2 class="lb-empty__title">{{ $title }}</h2>
    <p class="lb-empty__text">{{ $text }}</p>
    <a class="lb-btn" href="{{ route('frontend.welcome') }}">गृहपृष्ठमा फर्कनुहोस्</a>
</div>
