<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add Member</h1>
            <p class="ah-page-sub">Add a new person to the family tree.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.genealogys.index') }}" class="uk-button uk-button-default">All Members</a>
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

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="save" class="ah-busy">
        <span uk-spinner="ratio: 0.8" wire:ignore></span>
        <span>Saving...</span>
    </div>

    <form wire:submit.prevent="save" novalidate>
        @include('livewire.admin.genealogys.partials.form')

        <div class="ah-form-actions ah-form-footer">
            <a href="{{ route('admin.genealogys.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save,image">Create Member</button>
        </div>
    </form>
</div>
