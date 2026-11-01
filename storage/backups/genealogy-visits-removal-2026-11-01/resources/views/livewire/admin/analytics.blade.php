<div>
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Analytics</h1>
            <p class="ah-page-sub">Who visits the site and which posts they read.</p>
        </div>
    </div>

    <div class="ah-card ah-section">
        <div class="ah-toolbar">
            <label>
                Range
                <select wire:model.live="days" class="uk-select" aria-label="Date range">
                    @foreach ($ranges as $range)
                        <option value="{{ $range }}">Last {{ $range }} days</option>
                    @endforeach
                </select>
            </label>
        </div>
        <dl class="ah-summary">
            <div>
                <dt>Page views</dt>
                <dd>{{ number_format($summary->views) }}</dd>
            </div>
            <div>
                <dt>Unique visitors</dt>
                <dd>{{ number_format($summary->visitors) }}</dd>
            </div>
            <div>
                <dt>Posts viewed</dt>
                <dd>{{ number_format($summary->posts) }}</dd>
            </div>
        </dl>
    </div>

    <section class="ah-card ah-section">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Views per post</h2>
            <span class="ah-card-note">Last {{ $days }} days</span>
        </div>
        @if ($posts->isEmpty())
            <div class="ah-empty">
                <strong class="ah-empty-title">No post has been viewed in this period</strong>
                Tracking is on. Posts are listed here once visitors open them. Logged-in users and bots are not counted.
            </div>
        @else
            <div class="ah-table-wrap">
                <table class="uk-table uk-table-divider ah-table">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th class="ah-num" aria-sort="{{ $sort === 'asc' ? 'ascending' : 'descending' }}">
                                <button type="button" wire:click="toggleSort" class="ah-sort">
                                    Views <span aria-hidden="true">{{ $sort === 'asc' ? '↑' : '↓' }}</span>
                                </button>
                            </th>
                            <th class="ah-num">Unique visitors</th>
                            <th>Last viewed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr wire:key="post-{{ $post->id }}">
                                <td class="ah-post-name">
                                    @if ($post->trashed())
                                        <span class="ah-table-title">{{ $post->title }}</span>
                                        <span class="ah-badge ah-badge--danger">Trashed</span>
                                    @else
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title">{{ $post->title }}</a>
                                    @endif
                                </td>
                                <td class="ah-num">{{ number_format($post->views) }}</td>
                                <td class="ah-num">{{ number_format($post->visitors) }}</td>
                                <td class="uk-text-nowrap">
                                    <time datetime="{{ $post->last_viewed_at->toIso8601String() }}" title="{{ $post->last_viewed_at->format('M j, Y g:i A T') }}">{{ $post->last_viewed_at->diffForHumans() }}</time>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($posts->hasPages())
                <div class="ah-card-foot">
                    {{ $posts->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </section>

    @if ($canSeeDetails)
        <section class="ah-card ah-section">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Visitor log</h2>
                <span class="ah-card-note">Admins only</span>
            </div>
            <div class="ah-toolbar">
                <div class="ah-toolbar-grow">
                    <input wire:model.live.debounce.400ms="search" type="search" class="uk-input" placeholder="Search by IP or path" aria-label="Search visitor log by IP or path">
                </div>
            </div>
            @if ($visits->isEmpty())
                <div class="ah-empty">
                    @if (trim($search) !== '')
                        No visits match that search.
                    @else
                        No visits recorded in this period.
                    @endif
                </div>
            @else
                @include('livewire.admin.analytics.visit-table', ['visits' => $visits])
                @if ($visits->hasPages())
                    <div class="ah-card-foot">
                        {{ $visits->links('partials.admin-pagination') }}
                    </div>
                @endif
            @endif
        </section>
    @endif
</div>
