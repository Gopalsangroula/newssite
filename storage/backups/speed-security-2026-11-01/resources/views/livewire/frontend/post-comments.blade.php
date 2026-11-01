<section id="comments" class="pc" aria-labelledby="pc-title">
    <h2 id="pc-title" class="pc__title">
        प्रतिक्रिया
        @if ($total > 0)
            <span class="pc__count">{{ \App\Support\NepaliDate::digits($total) }}</span>
        @endif
    </h2>

    @if ($sent)
        <div class="pc__sent" role="status">
            <p><strong>धन्यवाद!</strong> तपाईंको प्रतिक्रिया प्राप्त भयो। स्वीकृत भएपछि यहाँ देखिनेछ।</p>
            <button class="pc__again" type="button" wire:click="writeAnother">अर्को प्रतिक्रिया लेख्नुहोस्</button>
        </div>
    @else
        <form class="pc__form" wire:submit="submit" novalidate>
            <div class="pc__field">
                <label for="pc-name">तपाईंको नाम</label>
                <input id="pc-name" class="uk-input" type="text" wire:model="name" maxlength="80" autocomplete="name">
                @error('name') <p class="pc__error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="pc__field">
                <label for="pc-email">इमेल <span class="pc__hint">(सार्वजनिक गरिँदैन)</span></label>
                <input id="pc-email" class="uk-input" type="email" wire:model="email" maxlength="255" autocomplete="email">
                @error('email') <p class="pc__error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="pc__field pc__field--full">
                <label for="pc-body">तपाईंको प्रतिक्रिया</label>
                <textarea id="pc-body" class="uk-textarea" rows="4" wire:model="body" maxlength="1000"></textarea>
                @error('body') <p class="pc__error" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="pc__trap" aria-hidden="true">
                <label for="pc-website">Website</label>
                <input id="pc-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="pc__foot">
                <button class="pc__submit" type="submit" wire:loading.attr="disabled" wire:target="submit">प्रतिक्रिया पठाउनुहोस्</button>
                <p class="pc__note">स्वीकृत भएपछि मात्र प्रकाशित हुनेछ।</p>
            </div>
        </form>
    @endif

    @if ($comments->isNotEmpty())
        <ol class="pc__list">
            @foreach ($comments as $comment)
                <li class="pc__item" wire:key="comment-{{ $comment->id }}">
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
    @elseif (! $sent)
        <p class="pc__empty">अहिलेसम्म कुनै प्रतिक्रिया छैन। पहिलो प्रतिक्रिया तपाईंले दिनुहोस्।</p>
    @endif
</section>
