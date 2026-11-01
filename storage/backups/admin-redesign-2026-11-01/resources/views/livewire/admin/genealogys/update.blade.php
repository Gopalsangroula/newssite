<!-- View for updating an existing genealogy record -->
<div>
    <h2 class="uk-heading-small uk-text-center uk-margin-medium-bottom">Edit Member</h2>

    <form wire:submit.prevent="update" class="uk-form-stacked uk-align-center">
        <div class="uk-child-width-expand@s" uk-grid>
            <div>
                <!-- Pusta Number -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="pustanumber">Pusta Number</label>
                    <div class="uk-form-controls">
                        <input wire:model="pustanumber" class="uk-input @error('pustanumber') uk-form-danger @enderror"
                            id="pustanumber" type="number" min="0" placeholder="Enter pusta number" aria-describedby="pustanumber-error">
                        @error('pustanumber') <span id="pustanumber-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">Name</label>
                    <div class="uk-form-controls">
                        <input wire:model="name" class="uk-input @error('name') uk-form-danger @enderror" id="name"
                            type="text" placeholder="Enter name" aria-describedby="name-error">
                        @error('name') <span id="name-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Sex -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="sex">Sex</label>
                    <div class="uk-form-controls">
                        <select wire:model="sex" class="uk-select @error('sex') uk-form-danger @enderror" id="sex"
                            aria-describedby="sex-error">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        @error('sex') <span id="sex-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="date_of_birth">Date of Birth</label>
                    <div class="uk-form-controls">
                        <input wire:model="date_of_birth"
                            class="uk-input @error('date_of_birth') uk-form-danger @enderror" id="date_of_birth"
                            type="date" aria-describedby="date_of_birth-error">
                        @error('date_of_birth') <span id="date_of_birth-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Date of Death -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="date_of_death">Date of Death</label>
                    <div class="uk-form-controls">
                        <input wire:model="date_of_death"
                            class="uk-input @error('date_of_death') uk-form-danger @enderror" id="date_of_death"
                            type="date" aria-describedby="date_of_death-error">
                        @error('date_of_death') <span id="date_of_death-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div>
                <!-- Description -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="description">Description</label>
                    <div class="uk-form-controls">
                        <textarea wire:model="description"
                            class="uk-textarea @error('description') uk-form-danger @enderror" id="description" rows="5"
                            placeholder="Enter description" aria-describedby="description-error"></textarea>
                        @error('description') <span id="description-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Father -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="father_id">Father</label>
                    <div class="uk-form-controls">
                        <select wire:model="father_id" class="uk-select @error('father_id') uk-form-danger @enderror"
                            id="father_id" aria-describedby="father_id-error">
                            <option value="">Select Father</option>
                            @foreach ($allMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->pustanumber }})</option>
                            @endforeach
                        </select>
                        @error('father_id') <span id="father_id-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Mother Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="mother_name">Mother Name</label>
                    <div class="uk-form-controls">
                        <input wire:model="mother_name" class="uk-input @error('mother_name') uk-form-danger @enderror"
                            id="mother_name" type="text" placeholder="Enter mother name" aria-describedby="mother_name-error">
                        @error('mother_name') <span id="mother_name-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Wife Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="wife_name">Wife Name</label>
                    <div class="uk-form-controls">
                        <input wire:model="wife_name" class="uk-input @error('wife_name') uk-form-danger @enderror"
                            id="wife_name" type="text" placeholder="Enter wife name" aria-describedby="wife_name-error">
                        @error('wife_name') <span id="wife_name-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Image -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="image">Image</label>
                    <div class="uk-form-controls">
                        @if ($imagePreview)
                            <div class="uk-margin-small-bottom">
                                <img src="{{ $imagePreview }}" alt="Current Image" class="uk-width-small">
                                <button wire:click="removeImage" class="uk-button uk-button-danger uk-button-small">Remove Image</button>
                            </div>
                        @endif
                        <input wire:model="image" class="uk-input @error('image') uk-form-danger @enderror" id="image"
                            type="file" accept="image/*" aria-describedby="image-error">
                        @error('image') <span id="image-error" class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
        <!-- Form Actions -->
        <div class="uk-margin uk-text-center">
            <button type="submit" class="uk-button uk-button-primary">Update Member</button>
            <a href="/" class="uk-button uk-button-default">Cancel</a>
        </div>
    </form>

    <!-- Success/Error Messages -->
    @if (session('message'))
        <div class="uk-alert-success uk-margin-top" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="uk-alert-danger uk-margin-top" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif
</div>