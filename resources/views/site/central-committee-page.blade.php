@php
    // Posts here are titled "सचिव - नाम" or "सदस्य : नाम", and their body repeats that title and
    // adds the person's address. A post with no role and a long title is an article, not a person.
    $entries = $posts->map(function ($post) {
        $title = trim(preg_replace('/\s+/u', ' ', $post->title));
        $role = trim((string) $post->sub_title);
        $name = $title;

        if (preg_match('/^(.{2,40}?)\s+[:\-–—]\s+(.+)$/u', $title, $parts)) {
            $role = $role ?: $parts[1];
            $name = $parts[2];
        }

        $body = \App\Support\Seo::plainText($post->content);
        $location = Str::startsWith($body, $title)
            ? preg_replace('/^[\s,।–—-]+|[\s,।–—-]+$/u', '', Str::after($body, $title))
            : '';

        return (object) ['post' => $post, 'role' => $role, 'name' => $name, 'location' => $location];
    });

    [$people, $articles] = $entries->partition(fn ($entry) => $entry->role !== '' || mb_strlen($entry->name) <= 40)->all();
@endphp

<div class="lb-page lb-page--committee">
    @include('site.partials.page-head', ['title' => $category->name])

    <section class="lb-results">
        @if($people->isNotEmpty())
            <div class="lb-committee__lead">
                @include('site.partials.person-card', ['entry' => $people->first()])
            </div>
        @endif

        @if($people->count() > 1)
            <div class="lb-committee">
                @foreach($people->slice(1) as $entry)
                    @include('site.partials.person-card', ['entry' => $entry])
                @endforeach
            </div>
        @endif

        @if($articles->isNotEmpty())
            <h2 class="lb-section-title">सम्बन्धित सामग्री</h2>
            <div class="lb-grid">
                @foreach($articles as $entry)
                    @include('site.partials.post-card', ['post' => $entry->post, 'headingTag' => 'h3'])
                @endforeach
            </div>
        @endif

        @if($posts->isEmpty())
            @include('site.partials.empty-state', [
                'icon' => 'users',
                'title' => 'समितिको विवरण अहिलेसम्म प्रकाशित भएको छैन',
                'text' => 'विवरण उपलब्ध भएपछि यहाँ देखिनेछ।',
            ])
        @endif
    </section>
</div>
