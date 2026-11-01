<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Edit Advertisement</h1>
            <p class="ah-page-sub">{{ $title }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.advertisements.index') }}" class="uk-button uk-button-default">All Advertisements</a>
        </div>
    </div>

    {{-- Messages --}}
    @if (session('message'))
        <div class="uk-alert-success ah-flash" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="update" class="ah-busy">
        <span uk-spinner="ratio: 0.8" wire:ignore></span>
        <span>Saving...</span>
    </div>

    <form wire:submit.prevent="update" class="ah-form-card--narrow" novalidate>
        @include('livewire.admin.advertisements.partials.form')

        <div class="ah-form-actions">
            <a href="{{ route('admin.advertisements.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="update,image">Update Advertisement</button>
        </div>
    </form>
</div>
