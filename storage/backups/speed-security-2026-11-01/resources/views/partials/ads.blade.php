@php
use App\Models\Advertisement;

$ads = Advertisement::where('placement', $placement)
    ->get()
    ->filter(fn($ad) => $ad->isCurrentlyActive());
@endphp

@if($ads->count())
    @foreach($ads as $ad)
        @if($ad->type === 'image')
            <a href="{{ $ad->target_url ?? '#' }}" target="_blank">
                <img src="{{ asset('storage/'.$ad->image_url) }}" alt="{{ $ad->title }}">
            </a>
        @elseif($ad->type === 'script')
            {!! $ad->content !!}
        @endif
    @endforeach
@endif
