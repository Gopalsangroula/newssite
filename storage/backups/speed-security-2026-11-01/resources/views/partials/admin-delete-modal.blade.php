{{--
    Delete confirmation for a list page. Expects $title and $action (the method that deletes).
    Open it with $this->dispatch('showDeleteModal', message: '...') and close it with
    $this->dispatch('hideDeleteModal').

    "container: false" stops UIkit moving it to <body>: outside the Livewire root
    its wire:click buttons throw. wire:ignore stops a re-render wiping UIkit's state,
    so the script writes the message in.
--}}
<div id="delete-modal" class="ah-modal" uk-modal="container: false" wire:ignore>
    <div class="uk-modal-dialog">
        <button
            class="uk-modal-close-default"
            type="button"
            uk-close
            wire:click="$dispatch('hideDeleteModal')"
            aria-label="Close modal"
        ></button>
        <div class="uk-modal-header">
            <h2 class="uk-modal-title">{{ $title }}</h2>
        </div>
        <div class="uk-modal-body">
            <p id="delete-modal-message"></p>
        </div>
        <div class="uk-modal-footer uk-text-right">
            <button
                class="uk-button uk-button-default"
                type="button"
                wire:click="$dispatch('hideDeleteModal')"
            >
                Cancel
            </button>
            <button
                class="uk-button uk-button-danger"
                type="button"
                wire:click="{{ $action }}"
                wire:loading.attr="disabled"
            >
                Delete
            </button>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('showDeleteModal', ({ message }) => {
        document.getElementById('delete-modal-message').textContent = message;
        UIkit.modal('#delete-modal').show();
    });
    $wire.on('hideDeleteModal', () => UIkit.modal('#delete-modal').hide());
</script>
@endscript
