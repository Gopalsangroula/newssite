{{-- Attachment details. Expects $detail, $previousId and $nextId. --}}
@php($detailName = $detail->original_name ?: $detail->filename)

<div class="ahm-modal" role="dialog" aria-modal="true" aria-labelledby="ahm-detail-title">
    <div class="ahm-modal-backdrop" x-on:click="closeDetails()"></div>

    <div class="ahm-modal-panel" x-trap.noscroll="true">
        <header class="ahm-modal-head">
            <h2 id="ahm-detail-title" class="ah-card-title">Attachment details</h2>

            <div class="ahm-modal-tools">
                <span class="ahm-spinner ahm-spinner--small" wire:loading.delay wire:target="openItem" aria-hidden="true"></span>
                <button type="button" class="ahm-icon-btn" x-ref="previous" title="Previous file" aria-label="Previous file"
                        @disabled(! $previousId) x-on:click="step({{ $previousId ?? 'null' }})">
                    @include('livewire.admin.media.partials.icon', ['name' => 'previous'])
                </button>
                <button type="button" class="ahm-icon-btn" x-ref="next" title="Next file" aria-label="Next file"
                        @disabled(! $nextId) x-on:click="step({{ $nextId ?? 'null' }})">
                    @include('livewire.admin.media.partials.icon', ['name' => 'next'])
                </button>
                <button type="button" class="ahm-icon-btn" title="Close" aria-label="Close details" autofocus x-on:click="closeDetails()">
                    @include('livewire.admin.media.partials.icon', ['name' => 'close'])
                </button>
            </div>
        </header>

        {{-- Keyed so the Alpine state below starts fresh for every item. --}}
        <div class="ahm-detail"
             wire:key="detail-{{ $detail->id }}"
             x-data="ahmDetail()"
             x-effect="dirty = description !== saved"
             data-description="{{ $detail->img_desc }}"
             data-media-id="{{ $detail->id }}"
             data-media-name="{{ $detailName }}"
             data-media-attached="{{ $detail->mediable_type && $detail->mediable_id ? 1 : 0 }}">
            <div class="ahm-preview">
                @if ($detail->kind === 'image')
                    <img src="{{ $detail->url }}" alt="{{ $detail->img_desc ?: $detailName }}" x-ref="image" x-show="! broken"
                         x-on:load="measure($el.naturalWidth, $el.naturalHeight)" x-on:error="markBroken()">
                    <div class="ahm-preview-file" x-show="broken" x-cloak>
                        @include('livewire.admin.media.partials.icon', ['name' => 'image'])
                        <p>This file could not be loaded.</p>
                    </div>
                @elseif ($detail->kind === 'video')
                    <video src="{{ $detail->url }}" controls preload="metadata"
                           x-on:loadedmetadata="measure($el.videoWidth, $el.videoHeight)"></video>
                @elseif ($detail->kind === 'audio')
                    <div class="ahm-preview-file">
                        @include('livewire.admin.media.partials.icon', ['name' => 'audio'])
                        <audio src="{{ $detail->url }}" controls preload="metadata"></audio>
                    </div>
                @else
                    <div class="ahm-preview-file">
                        @include('livewire.admin.media.partials.icon', ['name' => $detail->kind])
                        <p>{{ $detailName }}</p>
                        <a href="{{ $detail->url }}" target="_blank" rel="noopener" class="uk-button uk-button-default">Open file</a>
                    </div>
                @endif
            </div>

            <div class="ahm-info">
                <dl class="ahm-meta">
                    <div>
                        <dt>File name</dt>
                        <dd>{{ $detail->filename }}</dd>
                    </div>
                    @if ($detail->original_name && $detail->original_name !== $detail->filename)
                        <div>
                            <dt>Original name</dt>
                            <dd>{{ $detail->original_name }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt>File type</dt>
                        <dd>{{ $detail->mime_type }}</dd>
                    </div>
                    <div>
                        <dt>File size</dt>
                        <dd>{{ $detail->formatted_size }}</dd>
                    </div>
                    @if (in_array($detail->kind, ['image', 'video'], true))
                        <div>
                            <dt>Dimensions</dt>
                            <dd x-text="dimensions"></dd>
                        </div>
                    @endif
                    <div>
                        <dt>Uploaded on</dt>
                        <dd>{{ $detail->created_at?->format('F j, Y') ?? 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt>Attached to</dt>
                        <dd>@include('livewire.admin.media.partials.attached', ['media' => $detail])</dd>
                    </div>
                </dl>

                <div class="ahm-field">
                    <label for="ahm-description">Alt text / description</label>
                    <textarea id="ahm-description" class="uk-textarea" rows="3" maxlength="255"
                              x-model="description" @disabled(! $canEdit)></textarea>
                    @error('description')
                        <span class="ahm-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="ahm-field">
                    <label for="ahm-url">File URL</label>
                    <div class="ahm-copy">
                        <input id="ahm-url" class="uk-input" type="text" readonly value="{{ url($detail->url) }}"
                               x-ref="url" x-on:focus="$el.select()">
                        <button type="button" class="uk-button uk-button-default" x-on:click="copy()">
                            <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                        </button>
                    </div>
                </div>

                <div class="ahm-actions">
                    @if ($canEdit)
                        <button type="button" class="uk-button uk-button-primary" :disabled="! dirty || saving"
                                x-on:click="save({{ $detail->id }})">
                            <span x-text="saving ? 'Saving…' : 'Save'">Save</span>
                        </button>
                    @endif
                    <span class="ahm-muted" x-show="dirty" x-cloak>Unsaved changes</span>
                </div>

                <div class="ah-row-actions ahm-links">
                    @if ($canEdit)
                        <a href="{{ route('admin.media.edit', $detail->id) }}">Edit more details</a>
                    @endif
                    <a href="{{ $detail->url }}" target="_blank" rel="noopener">View file</a>
                    @if ($canDelete)
                        <button type="button" class="ah-action-danger" x-on:click="askDelete($el.closest('.ahm-detail'))">Delete permanently</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
