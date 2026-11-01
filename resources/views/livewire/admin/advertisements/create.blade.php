<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add Advertisement</h1>
            <p class="ah-page-sub">Create an image or script advertisement.</p>
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

    @if ($errors->has('general'))
        <div class="uk-alert-danger ah-flash" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ $errors->first('general') }}</p>
        </div>
    @endif

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="save" class="ah-busy">
        <span uk-spinner="ratio: 0.8" wire:ignore></span>
        <span>Saving...</span>
    </div>

    <form wire:submit.prevent="save" class="ah-form-card--narrow" novalidate>
        @include('livewire.admin.advertisements.partials.form')

        <div class="ah-form-actions">
            <a href="{{ route('admin.advertisements.index') }}" class="uk-button uk-button-default">Cancel</a>
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save,image">Save Advertisement</button>
        </div>
    </form>
</div>
