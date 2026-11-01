<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section>
    <div class="ah-card ah-form-card ah-danger-zone">
        <div class="ah-card-head">
            <h2 class="ah-card-title">{{ __('Delete account') }}</h2>
        </div>
        <div class="ah-card-body">
            <p class="ah-field-note">{{ __('Delete your account and all of its resources') }}</p>
            <button type="button" class="uk-button uk-button-danger" wire:click="$dispatch('showDeleteAccountModal')">{{ __('Delete account') }}</button>
        </div>
    </div>

    {{--
        "container: false" keeps it inside the Livewire root. wire:ignore.self rather than
        wire:ignore, so that a wrong password can still show its error beside the field.
    --}}
    <div id="confirm-user-deletion" class="ah-modal" uk-modal="container: false" wire:ignore.self>
        <div class="uk-modal-dialog">
            <button class="uk-modal-close-default" type="button" uk-close wire:ignore aria-label="{{ __('Close') }}"></button>

            <form wire:submit="deleteUser">
                <div class="uk-modal-header">
                    <h2 class="uk-modal-title">{{ __('Are you sure you want to delete your account?') }}</h2>
                </div>

                <div class="uk-modal-body">
                    <p>{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>

                    <div class="ah-field">
                        <label for="delete-account-password" class="uk-form-label">{{ __('Password') }}</label>
                        <input id="delete-account-password" type="password" wire:model="password" class="uk-input {{ $errors->has('password') ? 'uk-form-danger' : '' }}" autocomplete="current-password">
                        @error('password') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="uk-modal-footer uk-text-right">
                    <button type="button" class="uk-button uk-button-default uk-modal-close">{{ __('Cancel') }}</button>
                    <button type="submit" class="uk-button uk-button-danger">{{ __('Delete account') }}</button>
                </div>
            </form>
        </div>
    </div>

    @script
    <script>
        $wire.on('showDeleteAccountModal', () => UIkit.modal('#confirm-user-deletion').show());
    </script>
    @endscript
</section>
