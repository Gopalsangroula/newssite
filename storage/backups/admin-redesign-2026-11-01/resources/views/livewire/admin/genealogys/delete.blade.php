<!-- View for confirming deletion of a genealogy record -->
<div class="uk-container uk-margin-top">
    <h2 class="uk-heading-small uk-text-center uk-margin-medium-bottom">Delete Member</h2>

    <div class="uk-card uk-card-default uk-width-1-2@s uk-align-center uk-padding">
        <div class="uk-card-header">
            <h3 class="uk-card-title">Confirm Deletion</h3>
        </div>
        <div class="uk-card-body">
            <p>Are you sure you want to delete <strong>{{ $member->name }}</strong> (Pusta Number: {{ $member->pustanumber }})? This action cannot be undone.</p>

            <div class="uk-margin uk-text-center">
                <button wire:click="delete" class="uk-button uk-button-danger"><span uk-icon="trash"></span> Confirm Delete</button>
                <a href="{{ route('genealogy.manager') }}" class="uk-button uk-button-default">Cancel</a>
            </div>
        </div>
    </div>

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