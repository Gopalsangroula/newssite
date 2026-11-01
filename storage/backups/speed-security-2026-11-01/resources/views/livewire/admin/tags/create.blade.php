<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add Tag</h1>
            <p class="ah-page-sub">Create a new tag for posts.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.tags.index') }}" class="uk-button uk-button-default">All Tags</a>
        </div>
    </div>

    <form wire:submit.prevent="save" class="ah-card ah-form-card ah-form-card--narrow">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Tag Details</h2>
        </div>

        @include('livewire.admin.tags.partials.form')

        <div class="ah-card-foot ah-form-actions">
            <a href="{{ route('admin.tags.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save">Save Tag</button>
        </div>
    </form>
</div>
