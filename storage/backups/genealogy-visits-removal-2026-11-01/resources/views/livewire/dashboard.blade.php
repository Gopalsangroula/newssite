<div>
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Dashboard</h1>
            <p class="ah-page-sub">Welcome back, {{ auth()->user()->name }}. Today is {{ now()->format('l, F j, Y') }}.</p>
        </div>
        <div class="ah-page-actions">
            @can('access posts')
                <a href="{{ route('admin.posts.create') }}" class="uk-button uk-button-primary">
                    <span wire:ignore uk-icon="icon: plus; ratio: 0.8"></span>
                    New post
                </a>
            @endcan
            @cannot('access posts')
                @can('access advertisements')
                    <a href="{{ route('admin.advertisements.index') }}" class="uk-button uk-button-default">
                        <span wire:ignore uk-icon="icon: bolt; ratio: 0.8"></span>
                        Advertisements
                    </a>
                @endcan
            @endcannot
            <button type="button" wire:click="refreshStats" wire:loading.attr="disabled" wire:target="refreshStats" class="uk-button uk-button-default">
                <span wire:ignore wire:loading.remove wire:target="refreshStats" uk-icon="icon: refresh; ratio: 0.8"></span>
                <span wire:ignore wire:loading wire:target="refreshStats" uk-spinner="ratio: 0.5"></span>
                Refresh
            </button>
        </div>
    </div>

    @php
        // A 14-day trend for the headline tiles. Purely decorative: the chart below holds the numbers.
        $sparkline = function (?array $days) {
            $values = array_slice($days ?? [], -14);

            if (array_sum($values) === 0) {
                return null;
            }

            $highest = max($values);
            $last = count($values) - 1;

            return collect($values)
                ->map(fn ($value, $index) => round($index / $last * 100, 1).','.round(27 - $value / $highest * 24, 1))
                ->implode(' ');
        };

        $changeFromYesterday = function (?array $days) {
            $values = array_slice($days ?? [], -2);

            if (count($values) < 2 || array_sum($values) === 0) {
                return null;
            }

            $change = $values[1] - $values[0];

            return ($change > 0 ? '+' : '').number_format($change).' from yesterday';
        };

        $visitorDays = $canSeeVisits ? $charts['visitors']['visitors'] : null;
        $viewDays = $canSeeVisits ? $charts['visitors']['views'] : null;

        $tiles = [];

        if ($canSeeVisits) {
            $tiles[] = ['label' => 'Reading now', 'icon' => 'bolt', 'value' => $visitSummary['reading_now'], 'meta' => 'Last 15 minutes'];
            $tiles[] = ['label' => 'Visitors today', 'icon' => 'users', 'value' => $visitSummary['visitors_today'], 'meta' => $changeFromYesterday($visitorDays) ?? 'Different people', 'trend' => $sparkline($visitorDays)];
            $tiles[] = ['label' => 'Page views today', 'icon' => 'file-text', 'value' => $visitSummary['views_today'], 'meta' => $changeFromYesterday($viewDays) ?? 'Pages opened', 'trend' => $sparkline($viewDays)];
            $tiles[] = ['label' => 'Unique visitors', 'icon' => 'world', 'value' => $visitSummary['visitors_period'], 'meta' => 'Last 30 days'];
        }

        if ($newsroom) {
            $postsUrl = route('admin.posts.index');
            $nextUp = $newsroom['upcoming']->first();

            $tiles[] = ['label' => 'Published today', 'icon' => 'file-edit', 'value' => $newsroom['published_today'], 'meta' => 'Yesterday '.number_format($newsroom['published_yesterday']).', '.number_format($newsroom['published_total']).' in total', 'url' => $postsUrl.'?statusFilter=published'];
            $tiles[] = ['label' => 'Drafts', 'icon' => 'pencil', 'value' => $newsroom['drafts'], 'meta' => 'Waiting to publish', 'url' => $postsUrl.'?statusFilter=draft'];
            $tiles[] = ['label' => 'Scheduled', 'icon' => 'clock', 'value' => $newsroom['scheduled'], 'meta' => $nextUp ? 'Next '.$nextUp->published_at->diffForHumans() : 'Nothing queued', 'url' => $postsUrl.'?statusFilter=scheduled'];
            $tiles[] = ['label' => 'Breaking now', 'icon' => 'warning', 'value' => $newsroom['breaking'], 'meta' => number_format($newsroom['featured']).' featured'];
        } else {
            $tiles[] = ['label' => 'Published posts', 'icon' => 'file-edit', 'value' => $stats['published_posts'], 'meta' => 'of '.number_format($stats['total_posts']).' total'];
        }
    @endphp

    <div class="ah-tiles">
        @foreach ($tiles as $tile)
            <div @class(['ah-card', 'ah-tile', 'ah-tile--link' => isset($tile['url'])])>
                <div class="ah-tile-head">
                    <span class="ah-tile-label">{{ $tile['label'] }}</span>
                    <span class="ah-tile-icon" wire:ignore uk-icon="icon: {{ $tile['icon'] }}; ratio: 0.8"></span>
                </div>
                <div class="ah-tile-value">{{ number_format($tile['value']) }}</div>
                <div class="ah-tile-meta">{{ $tile['meta'] }}</div>
                @if (! empty($tile['trend']))
                    <svg class="ah-spark" viewBox="0 0 100 30" preserveAspectRatio="none" aria-hidden="true">
                        <polyline points="{{ $tile['trend'] }}"></polyline>
                    </svg>
                @endif
                @isset($tile['url'])
                    <a href="{{ $tile['url'] }}" class="ah-tile-cover" aria-label="{{ $tile['label'] }}: open the list"></a>
                @endisset
            </div>
        @endforeach
    </div>

    @if ($newsroom)
        <div class="ah-section-label">Newsroom</div>

        <div class="ah-grid">
            <section class="ah-card ah-col-4">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Breaking and featured</h2>
                    <span class="ah-card-note">Live now</span>
                </div>
                <div class="ah-card-body">
                    @if ($newsroom['headlines']->isEmpty())
                        <div class="ah-empty">No breaking or featured story is live. Tick Breaking or Featured while writing a post.</div>
                    @else
                        <ul class="ah-list">
                            @foreach ($newsroom['headlines'] as $post)
                                <li wire:key="headline-{{ $post->id }}">
                                    <div class="ah-list-main">
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title">{{ $post->title }}</a>
                                        <span class="ah-table-meta">{{ $post->category->name }}, {{ ($post->published_at ?? $post->created_at)->diffForHumans() }}</span>
                                    </div>
                                    @if ($post->is_breaking)
                                        <span class="ah-badge ah-badge--danger">Breaking</span>
                                    @else
                                        <span class="ah-badge ah-badge--warning">Featured</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="ah-card ah-col-4">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Publishing queue</h2>
                    <a href="{{ $postsUrl }}?statusFilter=draft" class="ah-card-link">All drafts</a>
                </div>
                <div class="ah-card-body">
                    @if ($newsroom['upcoming']->isEmpty() && $newsroom['draft_list']->isEmpty())
                        <div class="ah-empty">Nothing is waiting. Scheduled posts and drafts appear here.</div>
                    @endif

                    @if ($newsroom['upcoming']->isNotEmpty())
                        <div class="ah-list-head">Scheduled</div>
                        <ul class="ah-list">
                            @foreach ($newsroom['upcoming'] as $post)
                                <li wire:key="upcoming-{{ $post->id }}">
                                    <div class="ah-list-main">
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title">{{ $post->title }}</a>
                                        <span class="ah-table-meta">{{ $post->category->name }}, {{ $post->published_at->format('M j, g:i A') }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($newsroom['draft_list']->isNotEmpty())
                        <div class="ah-list-head">Drafts</div>
                        <ul class="ah-list">
                            @foreach ($newsroom['draft_list'] as $post)
                                <li wire:key="draft-{{ $post->id }}">
                                    <div class="ah-list-main">
                                        <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title">{{ $post->title ?: 'Untitled' }}</a>
                                        <span class="ah-table-meta">{{ $post->category->name }}, edited {{ $post->updated_at->diffForHumans() }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="ah-card ah-col-4">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Needs attention</h2>
                    @if ($canSeeSeo)
                        <a href="{{ route('admin.seo') }}" class="ah-card-link">SEO page</a>
                    @else
                        <span class="ah-card-note">Live posts</span>
                    @endif
                </div>
                <div class="ah-card-body">
                    <dl class="ah-rows">
                        @foreach ($attention as $label => $count)
                            <div>
                                <dt>{{ $label }}</dt>
                                <dd><span @class(['ah-badge', 'ah-badge--warning' => $count > 0, 'ah-badge--success' => $count === 0])>{{ number_format($count) }}</span></dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </section>
        </div>
    @endif

    @if ($canSeeVisits)
        @php
            $hasVisits = $visitSummary['visitors_period'] > 0;
            $days = $charts['visitors'];
            $hasRecentHours = array_sum($charts['hours']['views']) > 0;
        @endphp

        <div class="ah-section-label">Audience</div>

        <div class="ah-grid">
            <section class="ah-card ah-col-8">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Visitors per day</h2>
                    <span class="ah-card-note">Last 30 days</span>
                </div>
                <div class="ah-card-body">
                    @unless ($hasVisits)
                        <div class="ah-empty">
                            <strong class="ah-empty-title">No visitors recorded yet</strong>
                            Tracking is on. Numbers appear here as soon as someone opens the public site. Logged-in users and bots are not counted.
                        </div>
                    @endunless

                    <div @class(['ah-viz', 'ah-viz--empty' => ! $hasVisits]) wire:loading.class="ah-viz--stale" wire:target="refreshStats">
                        <ul class="ah-legend">
                            <li><span class="ah-key ah-key--line"></span>Visitors</li>
                            <li><span class="ah-key ah-key--line ah-key--2"></span>Page views</li>
                        </ul>
                        <div class="ah-viz-canvas" wire:ignore>
                            <canvas id="ah-chart-visitors" tabindex="0" role="img" aria-label="Line chart of visitors and page views per day for the last 30 days. The same numbers are in the table below."></canvas>
                        </div>
                    </div>

                    @if ($hasVisits)
                        <details class="ah-viz-table" wire:ignore.self>
                            <summary>View as table</summary>
                            <div class="ah-table-wrap">
                                <table class="uk-table uk-table-divider uk-table-small ah-table">
                                    <thead>
                                        <tr>
                                            <th>Day</th>
                                            <th class="ah-num">Visitors</th>
                                            <th class="ah-num">Page views</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($days['titles'] as $index => $title)
                                            <tr wire:key="day-{{ $index }}">
                                                <td>{{ $title }}</td>
                                                <td class="ah-num">{{ number_format($days['visitors'][$index]) }}</td>
                                                <td class="ah-num">{{ number_format($days['views'][$index]) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                </div>
            </section>

            <section class="ah-card ah-col-4">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Devices</h2>
                    <span class="ah-card-note">Last 30 days</span>
                </div>
                <div class="ah-card-body">
                    @if ($hasVisits)
                        <div class="ah-split" role="img" aria-label="Share of visitors by device. The numbers are listed below.">
                            @foreach ($devices as $device)
                                @if ($device['count'] > 0)
                                    <span class="ah-split-part ah-key--{{ $loop->iteration }}" style="flex-grow: {{ $device['count'] }}" title="{{ $device['label'] }}: {{ $device['share'] }}%"></span>
                                @endif
                            @endforeach
                        </div>
                        <ul class="ah-device-list">
                            @foreach ($devices as $device)
                                <li>
                                    <span class="ah-key ah-key--{{ $loop->iteration }}"></span>
                                    <span class="ah-device-name">{{ $device['label'] }}</span>
                                    <span class="ah-device-share">{{ $device['share'] }}%</span>
                                    <span class="ah-device-count">{{ number_format($device['count']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="ah-empty">Device numbers appear with the first visitor.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="ah-grid">
            <section class="ah-card ah-col-8">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Most read news</h2>
                    <a href="{{ route('admin.analytics') }}" class="ah-card-link">All posts</a>
                </div>
                @if ($topPosts->isEmpty())
                    <div class="ah-empty">No post has been viewed in the last 30 days.</div>
                @else
                    <div class="ah-table-wrap">
                        <table class="uk-table uk-table-divider ah-table">
                            <thead>
                                <tr>
                                    <th>Post</th>
                                    <th class="ah-num">Today</th>
                                    <th class="ah-num">7 days</th>
                                    <th class="ah-num">30 days</th>
                                    <th class="ah-num">Unique</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topPosts as $post)
                                    <tr wire:key="top-post-{{ $post->id }}">
                                        <td class="ah-post-name">
                                            @if ($post->trashed())
                                                <span class="ah-table-title">{{ $post->title }}</span>
                                                <span class="ah-badge ah-badge--danger">Trashed</span>
                                            @else
                                                <a href="{{ route('admin.posts.edit', $post->id) }}" class="ah-table-title">{{ $post->title }}</a>
                                            @endif
                                            <span class="ah-table-meta">{{ $post->category->name }}</span>
                                        </td>
                                        <td class="ah-num">{{ number_format($post->views_today) }}</td>
                                        <td class="ah-num">{{ number_format($post->views_week) }}</td>
                                        <td class="ah-num">{{ number_format($post->views) }}</td>
                                        <td class="ah-num">{{ number_format($post->visitors) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="ah-card ah-col-4">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Traffic sources</h2>
                    <span class="ah-card-note">Last 30 days</span>
                </div>
                <div class="ah-card-body">
                    @if (count($sources) > 0)
                        <ul class="ah-bars">
                            @foreach ($sources as $bar)
                                <li wire:key="source-{{ $loop->index }}">
                                    <span class="ah-bars-label">{{ $bar['label'] }}</span>
                                    <span class="ah-bars-track"><span class="ah-bars-fill" style="width: {{ $bar['percent'] }}%"></span></span>
                                    <span class="ah-bars-value">{{ number_format($bar['value']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="ah-empty">Sources such as Facebook and Google appear with the first visitor.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="ah-grid">
            <section class="ah-card ah-col-7">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Reading hours</h2>
                    <span class="ah-card-note">Page views, last 7 days</span>
                </div>
                <div class="ah-card-body">
                    @unless ($hasRecentHours)
                        <div class="ah-empty">The busiest hours appear once people read the site.</div>
                    @endunless

                    <div @class(['ah-viz', 'ah-viz--empty' => ! $hasRecentHours]) wire:loading.class="ah-viz--stale" wire:target="refreshStats">
                        <div class="ah-viz-canvas" wire:ignore>
                            <canvas id="ah-chart-hours" tabindex="0" role="img" aria-label="Column chart of page views for each hour of the day over the last 7 days. The same numbers are in the table below."></canvas>
                        </div>
                    </div>

                    @if ($hasRecentHours)
                        <details class="ah-viz-table" wire:ignore.self>
                            <summary>View as table</summary>
                            <div class="ah-table-wrap">
                                <table class="uk-table uk-table-divider uk-table-small ah-table">
                                    <thead>
                                        <tr>
                                            <th>Hour</th>
                                            <th class="ah-num">Page views</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($charts['hours']['titles'] as $index => $title)
                                            <tr wire:key="hour-{{ $index }}">
                                                <td>{{ $title }}</td>
                                                <td class="ah-num">{{ number_format($charts['hours']['views'][$index]) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                </div>
            </section>

            <section class="ah-card ah-col-5">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Most read categories</h2>
                    <span class="ah-card-note">Last 30 days</span>
                </div>
                <div class="ah-card-body">
                    @if (count($categoryViews) > 0)
                        <ul class="ah-bars">
                            @foreach ($categoryViews as $bar)
                                <li wire:key="category-view-{{ $loop->index }}">
                                    <span class="ah-bars-label">{{ $bar['label'] }}</span>
                                    <span class="ah-bars-track"><span class="ah-bars-fill" style="width: {{ $bar['percent'] }}%"></span></span>
                                    <span class="ah-bars-value">{{ number_format($bar['value']) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="ah-empty">Categories appear once a post has been read.</div>
                    @endif
                </div>
            </section>
        </div>

        <div class="ah-grid">
            <section @class(['ah-card', 'ah-col-7' => $canSeeDetails, 'ah-col-12' => ! $canSeeDetails])>
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Reporters</h2>
                    <span class="ah-card-note">Last 30 days</span>
                </div>
                @if ($reporters->isEmpty())
                    <div class="ah-empty">Reporters appear once a post is published.</div>
                @else
                    <div class="ah-table-wrap">
                        <table class="uk-table uk-table-divider ah-table">
                            <thead>
                                <tr>
                                    <th>Reporter</th>
                                    <th class="ah-num">New posts</th>
                                    <th class="ah-num">All posts</th>
                                    <th class="ah-num">Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reporters as $reporter)
                                    <tr wire:key="reporter-{{ $loop->index }}">
                                        <td><span class="ah-table-title">{{ $reporter->name }}</span></td>
                                        <td class="ah-num">{{ number_format($reporter->recent_posts) }}</td>
                                        <td class="ah-num">{{ number_format($reporter->posts) }}</td>
                                        <td class="ah-num">{{ number_format($reporter->views) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            @if ($canSeeDetails)
                <section class="ah-card ah-col-5">
                    <div class="ah-card-head">
                        <h2 class="ah-card-title">Top IPs</h2>
                        <span class="ah-card-note">Last 30 days</span>
                    </div>
                    @if ($topIps->isEmpty())
                        <div class="ah-empty">IP addresses appear with the first visitor.</div>
                    @else
                        <div class="ah-table-wrap">
                            <table class="uk-table uk-table-divider ah-table">
                                <thead>
                                    <tr>
                                        <th>IP address</th>
                                        <th class="ah-num">Visits</th>
                                        <th>Last seen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topIps as $ip)
                                        <tr wire:key="ip-{{ $loop->index }}">
                                            <td class="ah-ip">{{ $ip->ip_address }}</td>
                                            <td class="ah-num">{{ number_format($ip->visits) }}</td>
                                            <td class="uk-text-nowrap">
                                                <time datetime="{{ $ip->last_seen->toIso8601String() }}" title="{{ $ip->last_seen->format('M j, Y g:i A T') }}">{{ $ip->last_seen->diffForHumans() }}</time>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
        </div>

        @if ($canSeeDetails)
            <section class="ah-card ah-section">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Recent visitors</h2>
                    <a href="{{ route('admin.analytics') }}" class="ah-card-link">Visitor log</a>
                </div>
                @if ($recentVisits->isEmpty())
                    <div class="ah-empty">The latest visitors will be listed here.</div>
                @else
                    @include('livewire.admin.analytics.visit-table', ['visits' => $recentVisits])
                @endif
            </section>
        @endif
    @endif

    <div class="ah-section-label">Content</div>

    <div class="ah-grid">
        <section class="ah-card ah-col-7">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Posts published per month</h2>
                <span class="ah-card-note">Last 12 months</span>
            </div>
            <div class="ah-card-body">
                <div class="ah-viz" wire:loading.class="ah-viz--stale" wire:target="refreshStats">
                    <div class="ah-viz-canvas" wire:ignore>
                        <canvas id="ah-chart-months" tabindex="0" role="img" aria-label="Column chart of posts published per month for the last 12 months. The same numbers are in the table below."></canvas>
                    </div>
                </div>
                <details class="ah-viz-table" wire:ignore.self>
                    <summary>View as table</summary>
                    <div class="ah-table-wrap">
                        <table class="uk-table uk-table-divider uk-table-small ah-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="ah-num">Posts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($charts['months']['titles'] as $index => $title)
                                    <tr wire:key="month-{{ $index }}">
                                        <td>{{ $title }}</td>
                                        <td class="ah-num">{{ number_format($charts['months']['posts'][$index]) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            </div>
        </section>

        <section class="ah-card ah-col-5">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Posts per category</h2>
                <span class="ah-card-note">Published posts</span>
            </div>
            <div class="ah-card-body">
                @if (count($categoryBars) > 0)
                    <ul class="ah-bars">
                        @foreach ($categoryBars as $bar)
                            <li wire:key="category-{{ $loop->index }}">
                                <span class="ah-bars-label">{{ $bar['label'] }}</span>
                                <span class="ah-bars-track"><span class="ah-bars-fill" style="width: {{ $bar['percent'] }}%"></span></span>
                                <span class="ah-bars-value">{{ number_format($bar['value']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="ah-empty">No published posts yet.</div>
                @endif
            </div>
        </section>
    </div>

    <div class="ah-grid">
        <section class="ah-card ah-col-8">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Recent posts</h2>
                @if ($canManageContent)
                    <a href="{{ route('admin.posts.index') }}" class="ah-card-link">All posts</a>
                @endif
            </div>
            @if ($recentPosts->isEmpty())
                <div class="ah-empty">No posts yet.</div>
            @else
                <div class="ah-table-wrap">
                    <table class="uk-table uk-table-divider ah-table">
                        <thead>
                            <tr>
                                <th>Post</th>
                                <th>Status</th>
                                <th class="ah-hide-phone">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPosts as $post)
                                <tr wire:key="recent-post-{{ $post->id }}">
                                    <td>
                                        <div class="ah-post-cell">
                                            @if ($post->cover_image)
                                                <img src="{{ Storage::url($post->cover_image) }}" alt="" class="ah-thumb" loading="lazy">
                                            @else
                                                <span class="ah-thumb ah-thumb--empty"><span wire:ignore uk-icon="icon: image"></span></span>
                                            @endif
                                            <div class="ah-post-text">
                                                <span class="ah-table-title">{{ $post->title }}</span>
                                                <span class="ah-table-meta">{{ $post->category->name }}, by {{ $post->author->name }}</span>
                                                <div class="ah-row-actions">
                                                    @if ($canManageContent)
                                                        <a href="{{ route('admin.posts.edit', $post->id) }}">Edit</a>
                                                    @endif
                                                    @if ($post->isPublished())
                                                        <a href="{{ route('frontend.details.show', $post->slug) }}" target="_blank" rel="noopener">View</a>
                                                    @endif
                                                    @if ($post->is_featured)
                                                        <span class="ah-badge ah-badge--warning">Featured</span>
                                                    @endif
                                                    @if ($post->is_breaking)
                                                        <span class="ah-badge ah-badge--danger">Breaking</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span @class([
                                            'ah-badge',
                                            'ah-badge--success' => $post->status === 'published',
                                            'ah-badge--warning' => $post->status === 'draft',
                                            'ah-badge--info' => $post->status === 'scheduled',
                                        ])>{{ ucfirst($post->status) }}</span>
                                    </td>
                                    <td class="uk-text-nowrap ah-hide-phone">
                                        {{ $post->published_at?->format('M j, Y') ?? 'Not published' }}
                                        <span class="ah-table-meta">{{ $post->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="ah-card ah-col-4">
            <div class="ah-card-head">
                <h2 class="ah-card-title">At a glance</h2>
            </div>
            <div class="ah-card-body">
                <dl class="ah-rows">
                    @foreach ([
                        'Total posts' => $stats['total_posts'],
                        'Published' => $stats['published_posts'],
                        'Drafts' => $stats['draft_posts'],
                        'Scheduled' => $stats['scheduled_posts'],
                        'In trash' => $stats['trashed_posts'],
                        'Featured' => $stats['featured_posts'],
                        'Breaking' => $stats['breaking_posts'],
                        'Pages' => $stats['total_pages'],
                        'Categories' => $stats['total_categories'],
                        'Tags' => $stats['total_tags'],
                        'Genealogy records' => $stats['total_genealogy'],
                        'Users' => $stats['total_users'],
                        'Comments' => $stats['total_comments'],
                        'Media files' => $stats['total_media'],
                        'Active ads' => $stats['total_ads'],
                    ] as $label => $count)
                        <div><dt>{{ $label }}</dt><dd>{{ number_format($count) }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </section>
    </div>

    <div class="ah-section-label">Site</div>

    <div class="ah-grid ah-grid--auto">
        <section class="ah-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Activity</h2>
            </div>
            <div class="ah-card-body">
                <dl class="ah-rows">
                    <div><dt>Published today</dt><dd>{{ number_format($todayStats['today_posts']) }}</dd></div>
                    <div><dt>Last 7 days</dt><dd>{{ number_format($todayStats['week_posts']) }}</dd></div>
                    <div><dt>Last 30 days</dt><dd>{{ number_format($todayStats['month_posts']) }}</dd></div>
                    <div><dt>Comments today</dt><dd>{{ number_format($todayStats['today_comments']) }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="ah-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Storage</h2>
            </div>
            <div class="ah-card-body">
                @if (count($storageUsage) > 0)
                    <dl class="ah-rows">
                        @foreach ($storageUsage as $usage)
                            <div>
                                <dt>{{ ucfirst($usage['file_type']) }}</dt>
                                <dd>{{ number_format($usage['count']) }} files, {{ $usage['total_size'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @else
                    <div class="ah-empty">No media uploaded yet.</div>
                @endif
            </div>
        </section>

        @if ($adStats)
            <section class="ah-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Advertisements</h2>
                    <a href="{{ route('admin.advertisements.index') }}" class="ah-card-link">All ads</a>
                </div>
                <div class="ah-card-body">
                    <dl class="ah-rows">
                        @foreach ($adStats as $label => $count)
                            <div><dt>{{ $label }}</dt><dd>{{ number_format($count) }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>
        @endif

        @if ($canSeeComments)
            <section class="ah-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Latest comments</h2>
                    @if ($pendingComments > 0)
                        <a href="{{ route('admin.comments.index') }}?status=pending" class="ah-card-link">{{ number_format($pendingComments) }} waiting</a>
                    @else
                        <a href="{{ route('admin.comments.index') }}" class="ah-card-link">All comments</a>
                    @endif
                </div>
                <div class="ah-card-body">
                    @if ($latestComments->isEmpty())
                        <div class="ah-empty">No comments yet.</div>
                    @else
                        <ul class="ah-list">
                            @foreach ($latestComments as $comment)
                                <li wire:key="comment-{{ $comment->id }}">
                                    <div class="ah-list-main">
                                        <span class="ah-table-title">{{ $comment->author_name }}</span>
                                        <span class="ah-table-meta">{{ Str::limit($comment->body, 80) }}</span>
                                        <span class="ah-table-meta">{{ $comment->post?->title }}, {{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    @unless ($comment->is_approved)
                                        <span class="ah-badge ah-badge--warning">Pending</span>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        @endif

        @if ($topTags->isNotEmpty())
            <section class="ah-card">
                <div class="ah-card-head">
                    <h2 class="ah-card-title">Top tags</h2>
                    <span class="ah-card-note">Posts</span>
                </div>
                <div class="ah-card-body">
                    <dl class="ah-rows">
                        @foreach ($topTags as $tag)
                            <div wire:key="tag-{{ $tag->id }}"><dt>{{ $tag->name }}</dt><dd>{{ number_format($tag->posts_count) }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </section>
        @endif
    </div>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js" integrity="sha384-XcdcwHqIPULERb2yDEM4R0XaQKU3YnDsrTmjACBZyfdVVqjh6xQ4/DCMd7XLcA6Y" crossorigin="anonymous"></script>
    @endassets

    @script
        <script>
            const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

            const colors = {
                first: token('--ah-viz-1'),
                firstHover: token('--ah-viz-1-hover'),
                second: token('--ah-viz-2'),
                surface: token('--ah-surface'),
                grid: token('--ah-border'),
                text: token('--ah-text'),
                muted: token('--ah-muted'),
            };

            const compact = (value) => (value >= 1000 ? `${value / 1000}K` : value);

            // A hairline that follows the pointer to the nearest day.
            const crosshair = {
                id: 'ahCrosshair',
                afterDatasetsDraw(chart) {
                    const [active] = chart.tooltip.getActiveElements();

                    if (!active) {
                        return;
                    }

                    const { ctx, chartArea } = chart;

                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(active.element.x, chartArea.top);
                    ctx.lineTo(active.element.x, chartArea.bottom);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = colors.muted;
                    ctx.stroke();
                    ctx.restore();
                },
            };

            const options = () => ({
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { color: colors.grid },
                        ticks: { color: colors.muted, maxRotation: 0, autoSkipPadding: 8 },
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 4,
                        grid: { color: colors.grid },
                        border: { display: false },
                        ticks: { color: colors.muted, precision: 0, maxTicksLimit: 5, callback: compact },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: colors.surface,
                        titleColor: colors.text,
                        bodyColor: colors.text,
                        borderColor: colors.grid,
                        borderWidth: 1,
                        cornerRadius: 6,
                        padding: 10,
                        usePointStyle: true,
                        callbacks: {
                            title: ([item]) => item.chart.data.titles[item.dataIndex],
                            label: (item) => `${item.formattedValue}  ${item.dataset.label}`,
                            labelPointStyle: () => ({ pointStyle: 'line', rotation: 0 }),
                            labelColor: ({ dataset }) => ({ borderColor: dataset.borderColor, backgroundColor: dataset.borderColor }),
                        },
                    },
                },
            });

            const line =(label, data, color, fill) => ({
                label,
                data,
                fill,
                borderColor: color,
                backgroundColor: `${color}1a`,
                borderWidth: 2,
                borderCapStyle: 'round',
                borderJoinStyle: 'round',
                pointBackgroundColor: color,
                pointBorderColor: colors.surface,
                pointBorderWidth: 2,
                pointHoverBorderWidth: 2,
                pointHoverRadius: 5,
                pointRadius: (context) => (context.dataIndex === context.dataset.data.length - 1 ? 5 : 0),
            });

            const visitorData = ({ labels, titles, visitors, views }) => ({
                labels,
                titles,
                datasets: [
                    line('Visitors', visitors, colors.first, 'origin'),
                    line('Page views', views, colors.second, false),
                ],
            });

            const barData = ({ labels, titles }, data, label) => ({
                labels,
                titles,
                datasets: [{
                    label,
                    data,
                    backgroundColor: colors.first,
                    borderColor: colors.first,
                    borderWidth: 0,
                    hoverBackgroundColor: colors.firstHover,
                    borderRadius: { topLeft: 4, topRight: 4 },
                    borderSkipped: 'bottom',
                    maxBarThickness: 24,
                }],
            });

            // Arrow keys walk through the chart and show the same readout as hovering.
            const addKeyboard = (chart) => {
                let index = -1;

                const show = (active) => {
                    chart.setActiveElements(active);
                    chart.tooltip.setActiveElements(active, { x: 0, y: 0 });
                    chart.update('none');
                };

                chart.canvas.addEventListener('keydown', (event) => {
                    const step = { ArrowRight: 1, ArrowLeft: -1 }[event.key];

                    if (!step) {
                        return;
                    }

                    event.preventDefault();

                    const last = chart.data.labels.length - 1;

                    index = index < 0 ? last : Math.min(last, Math.max(0, index + step));
                    show(chart.data.datasets.map((dataset, datasetIndex) => ({ datasetIndex, index })));
                });

                chart.canvas.addEventListener('blur', () => {
                    index = -1;
                    show([]);
                });
            };

            const charts = {};

            const draw = (id, config) => {
                if (charts[id]) {
                    charts[id].data = config.data;
                    charts[id].update('none');

                    return;
                }

                charts[id] = new Chart(document.getElementById(id), { ...config, options: options() });
                addKeyboard(charts[id]);
            };

            const drawAll = (data) => {
                // Without the library the tables below each chart still hold every number.
                if (typeof Chart === 'undefined') {
                    return;
                }

                Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;

                draw('ah-chart-months', { type: 'bar', data: barData(data.months, data.months.posts, 'Posts') });

                if (data.visitors) {
                    draw('ah-chart-visitors', { type: 'line', data: visitorData(data.visitors), plugins: [crosshair] });
                    draw('ah-chart-hours', { type: 'bar', data: barData(data.hours, data.hours.views, 'Page views') });
                }
            };

            drawAll(@js($charts));

            $wire.on('dashboard-charts', ({ charts: data }) => drawAll(data));

            $wire.on('notify', ({ message, type }) => {
                UIkit.notification({ message, status: type, pos: 'top-right', timeout: 3000 });
            });
        </script>
    @endscript
</div>
