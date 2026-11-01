<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add Category</h1>
            <p class="ah-page-sub">Create a new category for posts.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.categories.index') }}" class="uk-button uk-button-default">All Categories</a>
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

    <form wire:submit.prevent="save" class="ah-card ah-form-card ah-form-card--narrow">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Category Details</h2>
        </div>

        @include('livewire.admin.categories.partials.form')

        <div class="ah-card-foot ah-form-actions">
            <a href="{{ route('admin.categories.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading wire:target="save">Saving...</span>
                <span wire:loading.remove wire:target="save">Create Category</span>
            </button>
        </div>
    </form>
</div>
