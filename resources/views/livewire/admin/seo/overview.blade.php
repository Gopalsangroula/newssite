<div>
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">SEO Overview</h1>
            <p class="ah-page-sub">See which content is ready for Google and Facebook.</p>
        </div>
        @can('access seo settings')
            <div class="ah-page-actions">
                <a href="{{ route('admin.seo.settings') }}" class="uk-button uk-button-default">SEO Settings</a>
            </div>
        @endcan
    </div>

    <div class="ah-tiles">
        @foreach ($summary as $label => $count)
            <div class="ah-card ah-tile">
                <div class="ah-tile-label">{{ $label }}</div>
                <div class="ah-tile-value">{{ number_format($count) }}</div>
            </div>
        @endforeach
    </div>

    <div class="ah-card">
        <div class="ah-toolbar">
            <div class="ah-toolbar-grow">
                <input wire:model.live.debounce.500ms="search" class="uk-input" type="text" placeholder="Search by title..." aria-label="Search by title">
            </div>
            @if ($canSeePages)
                <select wire:model.live="type" class="uk-select" aria-label="Content type">
                    <option value="posts">Posts</option>
                    <option value="pages">Pages</option>
                </select>
            @endif
        </div>

        <div class="ah-table-wrap" wire:loading.class="ah-is-loading" wire:target="search,type,gotoPage,previousPage,nextPage">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                <thead>
                    <tr>
                        <th class="ah-col-main">Title</th>
                        <th>Score</th>
                        <th>Needs work</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $audit = \App\Support\Seo::audit($item);
                            $scoreBadge = match (true) {
                                $audit['score'] >= 80 => 'ah-badge--success',
                                $audit['score'] >= 50 => 'ah-badge--warning',
                                default => 'ah-badge--danger',
                            };
                            $missing = collect($audit['checks'])->reject()->keys()->map(fn ($check) => $checkLabels[$check]);
                            $editUrl = route($showPages ? 'admin.pages.edit' : 'admin.posts.edit', $item->id);
                            $publicUrl = $item->status === 'published' && $item->slug
                                ? route($showPages ? 'frontend.frontend.pages.show' : 'frontend.details.show', $item->slug)
                                : null;
                        @endphp
                        <tr wire:key="seo-{{ $showPages ? 'page' : 'post' }}-{{ $item->id }}">
                            <td class="ah-col-main">
                                <a href="{{ $editUrl }}" class="ah-table-title ah-clamp-2">{{ $item->title }}</a>
                                @if ($item->noindex)
                                    <span class="ah-table-meta">Hidden from search engines</span>
                                @endif
                            </td>
                            <td><span class="ah-badge {{ $scoreBadge }}">{{ $audit['score'] }}%</span></td>
                            <td class="ah-col-text">
                                @if ($missing->isEmpty())
                                    <span class="ah-muted">Nothing</span>
                                @else
                                    <span class="ah-table-meta">{{ ucfirst($missing->join(', ')) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="ah-row-actions">
                                    <a href="{{ $editUrl }}#seo-panel">Edit SEO</a>
                                    @if ($publicUrl)
                                        <a href="https://developers.facebook.com/tools/debug/?q={{ urlencode($publicUrl) }}" target="_blank" rel="noopener">Test on Facebook</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="ah-empty">Nothing found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <div class="ah-card-foot">
                {{ $items->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>
</div>
