{{-- A post's own video, in place of its picture. Nothing loads from YouTube or Facebook until the reader
     presses play; without JavaScript the button opens the video there. Takes $post, and optionally $class,
     $width for the cover picture and $isFirst for one at the top of the page. --}}
@php
    $video = $post->video;
    $poster = $post->cover_image
        ? \App\Support\Thumb::url($post->cover_image, $width ?? 1024)
        : ($video['site'] === 'youtube' ? 'https://i.ytimg.com/vi/'.$video['id'].'/hqdefault.jpg' : null);
@endphp

<div class="site-player {{ $class ?? '' }}">
    @if ($poster)
        <img src="{{ $poster }}" alt="" @if (! empty($isFirst)) fetchpriority="high" @else loading="lazy" @endif decoding="async" onerror="this.remove()">
    @endif

    <a class="site-player__play" href="{{ $video['url'] }}" target="_blank" rel="noopener"
       @if ($video['site'] === 'youtube') data-video-id="{{ $video['id'] }}" @else data-facebook-video="{{ $video['url'] }}" @endif
       aria-label="भिडियो हेर्नुहोस्: {{ $post->title }}">
        <span class="site-player__button"></span>
        @unless ($poster)
            <span>Facebook भिडियो</span>
        @endunless
    </a>
</div>
