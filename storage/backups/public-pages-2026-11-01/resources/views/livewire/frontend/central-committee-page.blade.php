<div class="uk-container uk-section-small">
    <!-- Category Header -->
    <div class="uk-margin-medium-bottom uk-text-center uk-text-left@m">
        <h1 class="uk-heading-small uk-margin-remove-bottom ah-cat-title">
            {{ $category->name }} 
        </h1>
        <hr class="uk-divider-small">
    </div>

    <!-- Post Grid -->
   @if($posts->count())

    <div class="uk-grid uk-grid-small uk-text-center" uk-grid>

        @foreach($posts as $post)

            @if($loop->first)

                {{-- First Post: Full Width --}}
                 <div class="uk-width-1-3@m">
                 </div>
                <div class="uk-width-1-3@m">
                    <div class="uk-card uk-padding-small uk-flex uk-flex-column uk-height-1-1 uk-border-rounded">

                        <a class="uk-link-reset uk-flex uk-flex-column"
                           href="{{ route('frontend.details.show', $post->slug) }}">

                            <div class="uk-media-top">
                                <img
                                    src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : asset('storage/default.jpg') }}"
                                    alt="{{ $post->title }}"
                                    class="uk-width-1-1 ah-committee-img"
                                >
                            </div>

                            <h2 class="uk-card-title uk-margin-small-top">
                                {{ $post->title }}
                            </h2>

                            <p class="uk-text-meta uk-text-emphasis">
                                {{ Str::limit($post->excerpt, 120) }}
                            </p>

                        </a>

                    </div>
                </div>
                <div class="uk-width-1-3@m">
                 </div>
            @else

                {{-- Remaining Posts: 3 Columns --}}
                <div class="uk-width-1-1 uk-width-1-2@s uk-width-1-3@m">
                    <div class="uk-card uk-padding-small uk-flex uk-flex-column uk-height-1-1 uk-border-rounded">

                        <a class="uk-link-reset uk-flex uk-flex-column"
                           href="{{ route('frontend.details.show', $post->slug) }}">

                            <div class="uk-media-top">
                                <img
                                    src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : asset('storage/default.jpg') }}"
                                    alt="{{ $post->title }}"
                                    class="uk-width-1-1 ah-committee-img"
                                >
                            </div>

                            <h2 class="uk-card-title uk-margin-small-top">
                                {{ $post->title }}
                            </h2>

                            <p class="uk-text-meta uk-text-emphasis">
                                {{ Str::limit($post->excerpt, 120) }}
                            </p>

                        </a>

                    </div>
                </div>

            @endif

        @endforeach

    </div>

@endif
</div>