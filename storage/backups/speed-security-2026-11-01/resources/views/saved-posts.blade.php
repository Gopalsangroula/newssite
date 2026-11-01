<x-layouts.frontend title="सेभ गरिएका सामग्री | Libang">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/site-tools.css') }}?v={{ filemtime(public_path('css/site-tools.css')) }}">
    @endpush

    <div class="lb-page">
        @include('livewire.frontend.partials.page-head', ['title' => 'सेभ गरिएका सामग्री'])

        {{-- Saved posts live in this browser, so site-tools.js fills the list. --}}
        <ul class="tool-saved" data-saved-list></ul>

        <p class="tool-note" data-saved-empty hidden>अहिलेसम्म केही सेभ गरिएको छैन। कुनै सामग्रीमा सेभ बटन थिचेपछि यहाँ देखिनेछ।</p>
    </div>
</x-layouts.frontend>
