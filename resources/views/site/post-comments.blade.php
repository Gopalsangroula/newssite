{{-- Comments under a post. Takes $post, $comments and $commentCount. site-tools.js sends the form, so the page itself can be stored. --}}
<section id="comments" class="pc" aria-labelledby="pc-title">
    <h2 id="pc-title" class="pc__title">
        प्रतिक्रिया
        @if ($commentCount > 0)
            <span class="pc__count">{{ \App\Support\NepaliDate::digits($commentCount) }}</span>
        @endif
    </h2>

    <div class="pc__sent" role="status" data-comment-sent hidden>
        <p><strong>धन्यवाद!</strong> तपाईंको प्रतिक्रिया प्राप्त भयो। स्वीकृत भएपछि यहाँ देखिनेछ।</p>
        <button class="pc__again" type="button" data-comment-again>अर्को प्रतिक्रिया लेख्नुहोस्</button>
    </div>

    <form class="pc__form" action="{{ route('comments.store', $post->id) }}" method="post" data-comment-form novalidate>
        <div class="pc__field">
            <label for="pc-name">तपाईंको नाम</label>
            <input id="pc-name" class="uk-input" type="text" name="name" maxlength="80" autocomplete="name">
            <p class="pc__error" role="alert" data-error="name" hidden></p>
        </div>

        <div class="pc__field">
            <label for="pc-email">इमेल <span class="pc__hint">(सार्वजनिक गरिँदैन)</span></label>
            <input id="pc-email" class="uk-input" type="email" name="email" maxlength="255" autocomplete="email">
            <p class="pc__error" role="alert" data-error="email" hidden></p>
        </div>

        <div class="pc__field pc__field--full">
            <label for="pc-body">तपाईंको प्रतिक्रिया</label>
            <textarea id="pc-body" class="uk-textarea" name="body" rows="4" maxlength="1000"></textarea>
            <p class="pc__error" role="alert" data-error="body" hidden></p>
        </div>

        <div class="pc__trap" aria-hidden="true">
            <label for="pc-website">Website</label>
            <input id="pc-website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="pc__foot">
            <button class="pc__submit" type="submit">प्रतिक्रिया पठाउनुहोस्</button>
            <p class="pc__note">स्वीकृत भएपछि मात्र प्रकाशित हुनेछ।</p>
        </div>
        <p class="pc__error" role="alert" data-error="form" hidden></p>
    </form>

    @if ($comments->isNotEmpty())
        <ol class="pc__list">
            @foreach ($comments as $comment)
                <li class="pc__item">
                    <span class="pc__avatar" aria-hidden="true">{{ mb_substr($comment->author_name, 0, 1) }}</span>
                    <div>
                        <p class="pc__byline">
                            <strong>{{ $comment->author_name }}</strong>
                            <time datetime="{{ $comment->created_at->toDateString() }}">{{ \App\Support\NepaliDate::withTime($comment->created_at) }}</time>
                        </p>
                        <p class="pc__body">{{ $comment->body }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <p class="pc__empty" data-comment-empty>अहिलेसम्म कुनै प्रतिक्रिया छैन। पहिलो प्रतिक्रिया तपाईंले दिनुहोस्।</p>
    @endif
</section>
