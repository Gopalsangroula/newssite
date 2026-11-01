{{-- The buttons beside the share bar. Takes $post and $commentCount. --}}
<a class="post-comment-button" href="#comments" uk-scroll="offset: 70">
    <span uk-icon="icon: comment; ratio: 0.9"></span>
    प्रतिक्रिया दिनुहोस्
    @if ($commentCount > 0)
        <span class="post-comment-button__count">{{ \App\Support\NepaliDate::digits($commentCount) }}</span>
    @endif
</a>

<button class="post-tool" type="button" data-save-post aria-pressed="false"
    data-url="{{ route('frontend.details.show', $post->slug) }}"
    data-title="{{ $post->title }}"
    data-image="{{ $post->cover_image ? Storage::url($post->cover_image) : '' }}">
    <span uk-icon="icon: bookmark; ratio: 0.9"></span>
    <span data-save-label>सेभ गर्नुहोस्</span>
</button>

<span class="post-text-size" role="group" aria-label="अक्षरको आकार">
    <button class="post-tool" type="button" data-post-size="-1" aria-label="अक्षर सानो गर्नुहोस्">अ-</button>
    <button class="post-tool" type="button" data-post-size="1" aria-label="अक्षर ठूलो गर्नुहोस्">अ+</button>
</span>
