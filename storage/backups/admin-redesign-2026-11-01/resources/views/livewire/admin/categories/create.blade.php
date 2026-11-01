<div>
    <h2 class="uk-heading-line"><span>Create Category</span></h2>

    {{-- Flash Messages --}}
    @if (session('message'))
        <div class="uk-alert-success" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" class="uk-form-stacked uk-margin">

        {{-- Name --}}
        <div class="uk-margin">
            <label class="uk-form-label">Name</label>
            <div class="uk-form-controls">
                <input wire:model.debounce.500ms="name" wire:keyup="generateSlug" class="uk-input" type="text" placeholder="Enter category name">
                @error('name') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Slug --}}
        <div class="uk-margin">
            <label class="uk-form-label">Slug</label>
            <div class="uk-form-controls">
                <input wire:model="slug" class="uk-input" type="text" placeholder="Auto-generated or edit manually">
                @error('slug') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Parent Category --}}
        <div class="uk-margin">
            <label class="uk-form-label">Parent Category</label>
            <div class="uk-form-controls">
                <select wire:model="parent_id" class="uk-select">
                    <option value="">-- None --</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <span class="uk-text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="uk-margin">
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled">
                <span wire:loading wire:target="save">Saving...</span>
                <span wire:loading.remove wire:target="save">Create</span>
            </button>
            <button type="button" class="uk-button uk-button-default" wire:click="openCancelModal">Cancel</button>
        </div>
    </form>

    {{-- Cancel Confirmation Modal --}}
    <div wire:ignore.self class="uk-modal" id="cancel-modal">
        <div class="uk-modal-dialog">
            <div class="uk-modal-header">
                <h2 class="uk-modal-title">Confirm Cancel</h2>
            </div>
            <div class="uk-modal-body">
                <p>Are you sure you want to cancel? Any unsaved changes will be lost.</p>
            </div>
            <div class="uk-modal-footer uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close" type="button">No, Stay</button>
                <button class="uk-button uk-button-danger" type="button" wire:click="cancel">Yes, Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openCancelModal', () => {
            UIkit.modal('#cancel-modal').show();
        });
    });
</script>
@endpush