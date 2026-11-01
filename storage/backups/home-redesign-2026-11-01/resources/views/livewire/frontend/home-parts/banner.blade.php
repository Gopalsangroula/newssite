<div class="banner-section">
        <div uk-grid="uk-grid" class="uk-text-center uk-center">
        <div class="uk-width-1-1@s ">
            @forelse($bannerPosts  as $post)
            <div class="banner-list uk-margin-medium-top">
                <div class="uk-card">
                    <h2 class="banner-section-ah uk-card-title uk-margin-small-top">
                        <a href="{{ route('frontend.details.show', $post->slug) }}" class="ah-banner-title">
                            {{ $post->title }}
                        </a>
                    </h2>
                    @if(!empty($post->sub_title))<h4 class="banner-subtitle-ah"> {{ $post->sub_title }}</h4>@endif
                    @if(!$post->is_breaking)
                    <div class="uk-card-media uk-flex uk-flex-center">
                        <a href="{{ route('frontend.details.show', $post->slug) }}">
                            <img src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : asset('storage/default.jpg') }}"
                                alt="{{ $post->title }}" class="uk-border-rounded uk-object-cover">
                        </a>
                    </div>
                        @endif
                    <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
                </div>
            </div>
            @empty
            @endforelse
        </div>
    </div>
</div>



