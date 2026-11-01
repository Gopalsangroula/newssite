<div class="ahm"
     x-on:media-notice.window="window.UIkit && UIkit.notification({ message: $event.detail.message, status: $event.detail.status, pos: 'top-right' })">

    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add Media</h1>
            <p class="ah-page-sub">Upload new files to the media library.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.media.index') }}" class="uk-button uk-button-default">Back to library</a>
        </div>
    </div>

    <section class="ah-card ahm-section">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Upload files</h2>
        </div>
        <div class="ah-card-body">
            <livewire:admin.media.uploader wire:key="media-uploader" />
        </div>
    </section>

    <section class="ah-card ahm-section">
        <div class="ah-toolbar ahm-toolbar">
            <h2 class="ah-card-title ah-toolbar-grow">Recent uploads</h2>
            <select wire:model.live="fileTypeFilter" class="uk-select" aria-label="Filter by file type">
                <option value="">All media items</option>
                @foreach (\App\Models\Media::KINDS as $kind => $kindLabel)
                    <option value="{{ $kind }}">{{ $kindLabel }}</option>
                @endforeach
            </select>
            <span class="ahm-count">
                {{ number_format($mediaFiles->total()) }} {{ Str::plural('file', $mediaFiles->total()) }}
            </span>
        </div>

        @if ($mediaFiles->isEmpty())
            <div class="ah-empty">
                <p>No files here yet. Upload your first file using the form above.</p>
            </div>
        @else
            <div class="ah-table-wrap">
                <table class="uk-table uk-table-divider uk-table-middle ah-table ahm-table">
                    <thead>
                        <tr>
                            <th class="ahm-col-thumb" aria-label="Thumbnail"></th>
                            <th>File name</th>
                            <th class="ahm-col-extra">Type</th>
                            <th class="ahm-col-extra">Size</th>
                            <th class="ahm-col-extra">Attached to</th>
                            <th class="ahm-col-extra">Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mediaFiles as $media)
                            @php($name = $media->original_name ?: $media->filename)

                            <tr wire:key="row-{{ $media->id }}">
                                <td class="ahm-col-thumb">
                                    <span class="ah-thumb ah-thumb--empty">
                                        @include('livewire.admin.media.partials.icon', ['name' => $media->kind])
                                    </span>
                                    @if ($media->kind === 'image')
                                        <img class="ah-thumb" src="{{ $media->url }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                                    @endif
                                </td>
                                <td class="ahm-col-name">
                                    <a class="ah-table-title" href="{{ route('admin.media.index', ['item' => $media->id]) }}">{{ $name }}</a>
                                    @if ($media->img_desc)
                                        <span class="ah-table-meta">{{ $media->img_desc }}</span>
                                    @endif
                                    <span class="ah-table-meta ahm-row-meta">
                                        {{ $media->mime_type }} · {{ $media->formatted_size }} · {{ $media->created_at?->diffForHumans() }}
                                    </span>
                                    <div class="ah-row-actions">
                                        <a href="{{ route('admin.media.index', ['item' => $media->id]) }}">Details</a>
                                        @if ($canEdit)
                                            <a href="{{ route('admin.media.edit', $media->id) }}">Edit</a>
                                        @endif
                                        @if ($canDelete)
                                            <button type="button" class="ah-action-danger" wire:click="delete({{ $media->id }})"
                                                    wire:confirm="Are you sure you want to delete {{ $name }}?"
                                                    aria-label="Delete {{ $name }}">Delete</button>
                                        @endif
                                    </div>
                                </td>
                                <td class="ahm-col-extra">
                                    <span class="ah-badge ah-badge--info">{{ ucfirst($media->kind) }}</span>
                                    <span class="ah-table-meta">{{ $media->mime_type }}</span>
                                </td>
                                <td class="ahm-col-extra ahm-nowrap">{{ $media->formatted_size }}</td>
                                <td class="ahm-col-extra">
                                    @include('livewire.admin.media.partials.attached', ['media' => $media])
                                </td>
                                <td class="ahm-col-extra ahm-nowrap">{{ $media->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($mediaFiles->hasPages())
            <div class="ah-card-foot">
                {{ $mediaFiles->links('partials.admin-pagination') }}
            </div>
        @endif
    </section>
</div>
