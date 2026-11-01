<div>
    <h2 class="uk-heading-small uk-text-center uk-margin-medium-bottom">Create New Member</h2>

    <form wire:submit.prevent="save" class="uk-form-stacked uk-align-center">
        <div class="uk-child-width-expand@s" uk-grid>
            
            <!-- LEFT SIDE -->
            <div>
                <!-- Pusta Number -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="pustanumber">Pusta Number</label>
                    <input wire:model="pustanumber" id="pustanumber" type="number" min="0"
                        class="uk-input @error('pustanumber') uk-form-danger @enderror"
                        placeholder="Enter pusta number">
                    @error('pustanumber') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">Name</label>
                    <input wire:model="name" id="name" type="text"
                        class="uk-input @error('name') uk-form-danger @enderror"
                        placeholder="Enter name">
                    @error('name') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Sex -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="sex">Sex</label>
                    <select wire:model="sex" id="sex"
                        class="uk-select @error('sex') uk-form-danger @enderror">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    @error('sex') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Date of Birth -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="date_of_birth">Date of Birth</label>
                    <input wire:model="date_of_birth" id="date_of_birth" type="date"
                        class="uk-input @error('date_of_birth') uk-form-danger @enderror">
                    @error('date_of_birth') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Date of Death -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="date_of_death">Date of Death</label>
                    <input wire:model="date_of_death" id="date_of_death" type="date"
                        class="uk-input @error('date_of_death') uk-form-danger @enderror">
                    @error('date_of_death') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div>

                <!-- Description -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="description">Description</label>
                    <textarea wire:model="description" id="description" rows="5"
                        class="uk-textarea @error('description') uk-form-danger @enderror"
                        placeholder="Enter description"></textarea>
                    @error('description') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Father (Native Select) -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="father_id">Father</label>
                    <select wire:model="father_id" id="father_id"
                        class="uk-select @error('father_id') uk-form-danger @enderror">
                        <option value="">Select Father</option>
                        @foreach ($allMembers as $member)
                            <option value="{{ $member->id }}">
                                {{ $member->name }} ({{ $member->pustanumber }})
                            </option>
                        @endforeach
                    </select>
                    @error('father_id')
                        <span class="uk-text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Mother Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="mother_name">Mother Name</label>
                    <input wire:model="mother_name" id="mother_name" type="text"
                        class="uk-input @error('mother_name') uk-form-danger @enderror">
                    @error('mother_name') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Wife Name -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="wife_name">Wife Name</label>
                    <input wire:model="wife_name" id="wife_name" type="text"
                        class="uk-input @error('wife_name') uk-form-danger @enderror">
                    @error('wife_name') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Image -->
                <div class="uk-margin">
                    <label class="uk-form-label" for="image">Image</label>
                    <input wire:model="image" id="image" type="file" accept="image/*"
                        class="uk-input @error('image') uk-form-danger @enderror">
                    @error('image') <span class="uk-text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <div class="uk-margin uk-text-center">
            <button type="submit" class="uk-button uk-button-primary">Create Member</button>
            <a href="/" class="uk-button uk-button-default">Cancel</a>
        </div>
    </form>

    @if (session('message'))
    <div class="uk-alert-success uk-margin-top" uk-alert>
        <p>{{ session('message') }}</p>
    </div>
    @endif

    @if (session('error'))
    <div class="uk-alert-danger uk-margin-top" uk-alert>
        <p>{{ session('error') }}</p>
    </div>
    @endif
</div>