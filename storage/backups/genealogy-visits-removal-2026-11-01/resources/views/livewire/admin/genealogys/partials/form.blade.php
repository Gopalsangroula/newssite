{{-- The member fields, shared by genealogys/create and genealogys/update. --}}
<div class="ah-card-grid">
    {{-- Identity --}}
    <section class="ah-card ah-form-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Identity</h2>
        </div>
        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field ah-field--full">
                    <label for="name" class="uk-form-label">Name <span class="ah-required">*</span></label>
                    <input id="name" type="text" wire:model="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="Enter name">
                    @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="pustanumber" class="uk-form-label">Pusta Number <span class="ah-required">*</span></label>
                    <input id="pustanumber" type="number" min="0" wire:model="pustanumber" class="uk-input {{ $errors->has('pustanumber') ? 'uk-form-danger' : '' }}" placeholder="Enter pusta number">
                    <div class="ah-field-hint">The generation this member belongs to.</div>
                    @error('pustanumber') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="sex" class="uk-form-label">Sex <span class="ah-required">*</span></label>
                    <select id="sex" wire:model="sex" class="uk-select {{ $errors->has('sex') ? 'uk-form-danger' : '' }}">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    @error('sex') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field ah-field--full">
                    <label for="description" class="uk-form-label">Description</label>
                    <textarea id="description" wire:model="description" rows="4" class="uk-textarea {{ $errors->has('description') ? 'uk-form-danger' : '' }}" placeholder="Enter description"></textarea>
                    @error('description') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Family --}}
    <section class="ah-card ah-form-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Family</h2>
        </div>
        <div class="ah-card-body">
            <div class="ah-form-grid ah-form-grid--single">
                <div class="ah-field">
                    @include('livewire.admin.genealogys.partials.father-picker')
                    <div class="ah-field-hint">Leave empty if the father is not in the list.</div>
                    @error('father_id') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="mother_name" class="uk-form-label">Mother Name</label>
                    <input id="mother_name" type="text" wire:model="mother_name" class="uk-input {{ $errors->has('mother_name') ? 'uk-form-danger' : '' }}" placeholder="Enter mother name">
                    @error('mother_name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="wife_name" class="uk-form-label">Wife Name</label>
                    <input id="wife_name" type="text" wire:model="wife_name" class="uk-input {{ $errors->has('wife_name') ? 'uk-form-danger' : '' }}" placeholder="Enter wife name">
                    @error('wife_name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Life Dates --}}
    <section class="ah-card ah-form-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Life Dates</h2>
        </div>
        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field">
                    <label for="date_of_birth" class="uk-form-label">Date of Birth</label>
                    <input id="date_of_birth" type="date" wire:model="date_of_birth" class="uk-input {{ $errors->has('date_of_birth') ? 'uk-form-danger' : '' }}">
                    @error('date_of_birth') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="date_of_death" class="uk-form-label">Date of Death</label>
                    <input id="date_of_death" type="date" wire:model="date_of_death" class="uk-input {{ $errors->has('date_of_death') ? 'uk-form-danger' : '' }}">
                    @error('date_of_death') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </section>

    {{-- Photo --}}
    <section class="ah-card ah-form-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Photo</h2>
        </div>
        <div class="ah-card-body">
            <div class="ah-field">
                <label for="image" class="uk-form-label">Image</label>
                <input id="image" type="file" wire:model="image" accept="image/*" class="ah-file">
                <div class="ah-field-hint">JPEG, PNG or GIF, up to 4MB.</div>
                @error('image') <div class="ah-field-error">{{ $message }}</div> @enderror

                <div wire:loading wire:target="image" class="ah-field-hint">Uploading...</div>

                @if ($image && $image->isPreviewable())
                    <div class="ah-image-preview">
                        <img src="{{ $image->temporaryUrl() }}" alt="New image preview">
                        <span>New image preview</span>
                    </div>
                @elseif ($imagePreview ?? null)
                    <div class="ah-image-preview">
                        <img src="{{ $imagePreview }}" alt="Current image">
                        <div>
                            <span>Current image</span>
                            <div class="ah-row-actions">
                                <button type="button" class="ah-action-danger" wire:click="removeImage" wire:confirm="Remove this image? This takes effect straight away.">Remove image</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
