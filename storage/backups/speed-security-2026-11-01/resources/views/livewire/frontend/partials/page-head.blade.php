{{-- Page heading for the category, committee, video and tag pages. Takes $title and an optional $count line. --}}
<header class="lb-head">
    @include('livewire.frontend.partials.breadcrumb', ['crumbs' => [
        ['name' => 'गृहपृष्ठ', 'url' => route('frontend.welcome')],
        ['name' => $title, 'url' => null],
    ]])

    <div class="lb-head__row">
        {{-- Some names run on after commas with no space, so give them a place to wrap. --}}
        <h1 class="lb-head__title">{!! str_replace(',', ',<wbr>', e($title)) !!}</h1>
        @if(! empty($count))
            <p class="lb-head__count">{{ $count }}</p>
        @endif
    </div>
</header>
