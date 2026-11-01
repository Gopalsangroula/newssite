<div>
    <!-- Header with Stats -->
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-large-bottom">
        <div>
            <h1 class="uk-heading-medium uk-margin-remove">Dashboard Overview</h1>
            <p class="uk-text-muted uk-margin-remove">Welcome to Appharu content management system</p>
        </div>
        <div class="uk-flex uk-flex-middle">
            <button wire:click="refreshStats" class="uk-button uk-button-default uk-button-small">
                <span wire:loading.remove wire:target="refreshStats" uk-icon="refresh"></span>
                <span wire:loading wire:target="refreshStats" uk-spinner="ratio: 0.8"></span>
                Refresh
            </button>
        </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="uk-grid uk-grid-small uk-child-width-1-1 uk-child-width-1-3@m uk-child-width-1-4@l uk-margin-large-bottom"
        uk-grid>
        <!-- Posts -->
        <div>
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div uk-icon="icon: file-edit; ratio: 2" class="uk-text-primary uk-margin-small-bottom"></div>
                    <h2 class="uk-card-title uk-margin-remove uk-text-primary">
                        {{ $stats['total_posts'] }}</h2>
                    <p class="uk-text-meta uk-margin-remove">Total Posts</p>
                    <div class="uk-grid uk-grid-collapse uk-margin-small-top" uk-grid>
                        <div class="uk-width-1-2">
                            <span
                                class="uk-label uk-label-success">{{ $stats['published_posts'] }}
                                Published</span>
                        </div>
                        <div class="uk-width-1-2">
                            <span class="uk-label uk-label-warning">{{ $stats['draft_posts'] }}
                                Draft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Featured & Breaking -->
        <div>
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div uk-icon="icon: star; ratio: 2" class="uk-text-warning uk-margin-small-bottom"></div>
                    <h2 class="uk-card-title uk-margin-remove uk-text-warning">
                        {{ $stats['featured_posts'] }}</h2>
                    <p class="uk-text-meta uk-margin-remove">Featured Posts</p>
                    <div class="uk-margin-small-top">
                        <span class="uk-label uk-label-danger">{{ $stats['breaking_posts'] }}
                            Breaking News</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories & Tags -->
        <div>
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div uk-icon="icon: folder; ratio: 2" class="uk-text-success uk-margin-small-bottom"></div>
                    <h2 class="uk-card-title uk-margin-remove uk-text-success">
                        {{ $stats['total_categories'] }}</h2>
                    <p class="uk-text-meta uk-margin-remove">Categories</p>
                    <div class="uk-margin-small-top">
                        <span class="uk-label uk-label-default">{{ $stats['total_tags'] }}
                            Tags</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users & Comments -->
        <div>
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div uk-icon="icon: users; ratio: 2" class="uk-text-secondary uk-margin-small-bottom"></div>
                    <h2 class="uk-card-title uk-margin-remove uk-text-secondary">
                        {{ $stats['total_users'] }}</h2>
                    <p class="uk-text-meta uk-margin-remove">Users</p>
                    <div class="uk-margin-small-top">
                        <span class="uk-label uk-label-primary">{{ $stats['total_comments'] }}
                            Comments</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media & Ads -->
        <div>
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-body uk-text-center">
                    <div uk-icon="icon: image; ratio: 2" class="uk-text-danger uk-margin-small-bottom"></div>
                    <h2 class="uk-card-title uk-margin-remove uk-text-danger">
                        {{ $stats['total_media'] }}</h2>
                    <p class="uk-text-meta uk-margin-remove">Media Files</p>
                    <div class="uk-margin-small-top">
                        <span class="uk-label uk-label-success">{{ $stats['total_ads'] }}
                            Ads</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Activity Summary -->
        <div>
            <div class="uk-card uk-card-default">
                <div class="uk-card-body">
                    <h4 class="uk-card-title uk-margin-remove">Activity Summary</h4>
                    <div class="uk-margin-small-top">
                        <div class="uk-grid uk-grid-small uk-text-small" uk-grid>
                            <div class="uk-width-1-2">Today's Posts:</div>
                            <div class="uk-width-1-2 uk-text-right uk-text-bold">
                                {{ $todayStats['today_posts'] }}
                            </div>

                            <div class="uk-width-1-2">This Week:</div>
                            <div class="uk-width-1-2 uk-text-right uk-text-bold">
                                {{ $todayStats['week_posts'] }}
                            </div>

                            <div class="uk-width-1-2">This Month:</div>
                            <div class="uk-width-1-2 uk-text-right uk-text-bold">
                                {{ $todayStats['month_posts'] }}
                            </div>

                            <div class="uk-width-1-2">Today's Comments:</div>
                            <div class="uk-width-1-2 uk-text-right uk-text-bold">
                                {{ $todayStats['today_comments'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Quick Actions -->
        <div>
        <div class="uk-card uk-card-default uk-card-hover uk-margin-bottom">
            <div class="uk-card-header">
                <h3 class="uk-card-title uk-margin-remove">
                    <span uk-icon="icon: bolt" class="uk-margin-small-right"></span>
                    Quick Actions
                </h3>
            </div>
            <div class="uk-card-body uk-padding-small">
                <div class="uk-grid uk-grid-small uk-child-width-1-1" uk-grid>
                    <div>
                        <a href="{{ route('admin.posts.create') }}"
                            class="uk-button uk-button-primary uk-button-large uk-width-1-1 uk-text-left">
                            <span uk-icon="file-edit" class="uk-margin-small-right"></span>
                            Create New Post
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.media.create') }}"
                            class="uk-button uk-button-default uk-button-large uk-width-1-1 uk-text-left">
                            <span uk-icon="image" class="uk-margin-small-right"></span>
                            Upload Media
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.categories.create') }}"
                            class="uk-button uk-button-default uk-button-large uk-width-1-1 uk-text-left">
                            <span uk-icon="folder" class="uk-margin-small-right"></span>
                            Manage Categories
                        </a>
                    </div>
                </div>
            </div>
        </div> 
        </div>
    </div>

    <div class="uk-grid uk-grid-small uk-margin-large-bottom" uk-grid>
        <!-- Recent Posts -->
        <div class="uk-width-1-1@l">
            <div class="uk-card uk-card-default uk-card-hover">
                <div class="uk-card-header">
                    <div class="uk-flex uk-flex-between uk-flex-middle">
                        <h3 class="uk-card-title uk-margin-remove">
                            <span uk-icon="icon: file-edit" class="uk-margin-small-right"></span>
                            Recent Posts
                        </h3>
                        <a href="{{ route('admin.posts.index') }}"
                            class="uk-button uk-button-text uk-button-small">
                            View All <span uk-icon="chevron-right"></span>
                        </a>
                    </div>
                </div>
                <div class="uk-card-body uk-padding-remove">
                    <div class="uk-overflow-auto">
                        <table class="uk-table uk-table-divider uk-table-small uk-table-middle">
                            <thead>
                                <tr>
                                    <th class="uk-table-shrink">Cover</th>
                                    <th>Title & Excerpt</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="uk-table-shrink">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPosts as $post)
                                    <tr>
                                        <td>
                                            @if($post['cover_image'])
                                                <div class="uk-cover-container uk-border-circle"
                                                    style="width: 40px; height: 40px;">
                                                    <img src="{{ Storage::url($post['cover_image']) }}"
                                                        alt="Cover" uk-cover>
                                                </div>
                                            @else
                                                <div class="uk-border-circle uk-background-muted uk-flex uk-flex-center uk-flex-middle"
                                                    style="width: 40px; height: 40px;">
                                                    <span uk-icon="icon: image; ratio: 0.8"></span>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="uk-text-bold uk-text-small">
                                                {{ $post['title'] }}</div>
                                            @if($post['sub_title'])
                                                <div class="uk-text-muted uk-text-small">
                                                    {{ $post['sub_title'] }}</div>
                                            @endif
                                            <div class="uk-flex uk-flex-wrap uk-margin-small-top">
                                                @if($post['is_featured'])
                                                    <span
                                                        class="uk-badge uk-badge-warning uk-margin-small-right uk-margin-small-bottom">Featured</span>
                                                @endif
                                                @if($post['is_breaking'])
                                                    <span
                                                        class="uk-badge uk-badge-danger uk-margin-small-right uk-margin-small-bottom">Breaking</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="uk-badge uk-badge-default">{{ $post['category'] }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="uk-badge uk-badge-{{ $post['status'] === 'published' ? 'success' : 'warning' }}">
                                                {{ ucfirst($post['status']) }}
                                            </span>
                                        </td>
                                        <td class="uk-text-nowrap">
                                            <div class="uk-text-small">
                                                {{ $post['published_at'] ?? 'Not published' }}
                                            </div>
                                            <div class="uk-text-meta uk-text-small">
                                                {{ $post['created_at'] }}</div>
                                        </td>
                                        <td>
                                            <div class="uk-button-group">
                                                <a href="{{ route('admin.posts.edit', $post['id']) }}"
                                                    class="uk-button uk-button-small uk-button-default"
                                                    uk-tooltip="Edit post">
                                                    Edit
                                                </a>
                                                <a href="{{ route('frontend.details.show', $post['slug']) }}"
                                                    class="uk-button uk-button-small uk-button-default"
                                                    uk-tooltip="View post" target="_blank">
                                                    View
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Stats Row -->
    <div class="uk-grid uk-grid-small uk-child-width-1-1 uk-child-width-expand@m" uk-grid>
        <!-- Storage Usage -->
        <div>
            <div class="uk-card uk-card-default">
                <div class="uk-card-body">
                    <h4 class="uk-card-title uk-margin-remove">Storage Usage</h4>
                    <div class="uk-margin-small-top">
                        @foreach($storageUsage as $usage)
                            <div class="uk-flex uk-flex-between uk-text-small uk-margin-small-bottom">
                                <span
                                    class="uk-text-capitalize">{{ $usage['file_type'] }}</span>
                                <span class="uk-text-bold">{{ $usage['count'] }} files
                                    ({{ $usage['total_size'] }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


        <!-- Posts Chart -->
        <div>
            <div class="uk-card uk-card-default">
                <div class="uk-card-body">
                    <h4 class="uk-card-title uk-margin-remove">Posts Overview</h4>
                    <div class="uk-margin-small-top">
                        @foreach($postsPerMonth as $month)
                            <div class="uk-flex uk-flex-between uk-text-small uk-margin-small-bottom">
                                <span>{{ $month['label'] }}</span>
                                <span class="uk-badge">{{ $month['count'] }} posts</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->


    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', function () {
        // Listen for refresh event
        Livewire.on('notify', (event) => {
            UIkit.notification({
                message: event.message,
                status: event.type,
                pos: 'top-right',
                timeout: 3000
            });
        });
    });
</script>