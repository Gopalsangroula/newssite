<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Comments</h1>
            <p class="ah-page-sub">
                {{ number_format($comments->total()) }} {{ Str::plural('comment', $comments->total()) }}@if ($pendingCount), {{ number_format($pendingCount) }} waiting for approval @endif
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    <div class="ah-card">
        @if ($comments->total() === 0 && trim($search) === '' && $status === '')
            {{-- Empty State --}}
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: comments; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No comments yet</strong>
                <p>Comments left on posts will be listed here.</p>
            </div>
        @else
            {{-- Filters --}}
            <div class="ah-toolbar">
                <div class="ah-toolbar-grow">
                    <input
                        wire:model.live.debounce.500ms="search"
                        class="uk-input"
                        type="text"
                        placeholder="Search by author, email, comment or post..."
                        aria-label="Search comments"
                    >
                </div>
                <select wire:model.live="status" class="uk-select" aria-label="Filter by status">
                    <option value="">All comments</option>
                    <option value="pending">Waiting for approval</option>
                    <option value="approved">Approved</option>
                </select>
            </div>

            {{-- Comments Table --}}
            <div
                class="ah-table-wrap"
                wire:loading.class="ah-is-loading"
                wire:target="search,status,approve,unapprove,gotoPage,previousPage,nextPage"
            >
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th class="ah-col-name">Author</th>
                            <th class="ah-col-body">Comment</th>
                            <th class="ah-col-text">Post</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comments as $comment)
                            <tr wire:key="comment-{{ $comment->id }}">
                                <td class="ah-col-name">
                                    <span class="ah-table-title">{{ $comment->author_name }}</span>
                                    <span class="ah-table-meta">{{ $comment->author_email }}</span>
                                </td>
                                <td class="ah-col-body">
                                    <span class="ah-clamp-2">{{ $comment->body }}</span>
                                </td>
                                <td class="ah-col-text">
                                    @if (! $comment->post)
                                        <span class="ah-muted">Post not found</span>
                                    @elseif ($comment->post->trashed())
                                        <span class="ah-clamp-2">{{ $comment->post->title }}</span>
                                        <span class="ah-badge ah-badge--danger">Trashed</span>
                                    @else
                                        <a href="{{ route('admin.posts.edit', $comment->post->id) }}" class="ah-clamp-2">{{ $comment->post->title }}</a>
                                    @endif
                                </td>
                                <td>
                                    <span @class(['ah-badge', 'ah-badge--success' => $comment->is_approved, 'ah-badge--warning' => ! $comment->is_approved])>
                                        {{ $comment->is_approved ? 'Approved' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="ah-col-date">{{ $comment->created_at?->format('M j, Y') }}</td>
                                <td>
                                    <div class="ah-row-actions">
                                        @if ($comment->is_approved)
                                            <button type="button" wire:click="unapprove({{ $comment->id }})">Hide</button>
                                        @else
                                            <button type="button" wire:click="approve({{ $comment->id }})">Approve</button>
                                        @endif
                                        <a href="{{ route('admin.comments.edit', $comment->id) }}">Edit</a>
                                        <button type="button" class="ah-action-danger" wire:click="confirmDelete({{ $comment->id }})">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ah-empty">No comments match your search.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($comments->hasPages())
                <div class="ah-card-foot">
                    {{ $comments->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </div>

    @include('partials.admin-delete-modal', ['title' => 'Delete Comment', 'action' => 'delete'])
</div>
