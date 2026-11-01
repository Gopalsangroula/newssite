<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Password')] class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <x-settings.layout current="password">
        <form wire:submit="updatePassword" class="ah-card ah-form-card">
            <div class="ah-card-head">
                <div>
                    <h2 class="ah-card-title">{{ __('Update password') }}</h2>
                    <p class="ah-card-sub">{{ __('Ensure your account is using a long, random password to stay secure') }}</p>
                </div>
            </div>

            <div class="ah-card-body">
                <div class="ah-form-grid ah-form-grid--single">
                    <div class="ah-field">
                        <label for="current_password" class="uk-form-label">{{ __('Current password') }} <span class="ah-required">*</span></label>
                        <input id="current_password" type="password" wire:model="current_password" class="uk-input {{ $errors->has('current_password') ? 'uk-form-danger' : '' }}" required autocomplete="current-password">
                        @error('current_password') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="ah-field">
                        <label for="password" class="uk-form-label">{{ __('New password') }} <span class="ah-required">*</span></label>
                        <input id="password" type="password" wire:model="password" class="uk-input {{ $errors->has('password') ? 'uk-form-danger' : '' }}" required autocomplete="new-password">
                        @error('password') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="ah-field">
                        <label for="password_confirmation" class="uk-form-label">{{ __('Confirm Password') }} <span class="ah-required">*</span></label>
                        <input id="password_confirmation" type="password" wire:model="password_confirmation" class="uk-input" required autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="ah-card-foot ah-form-actions">
                <x-action-message class="ah-saved" on="password-updated">{{ __('Saved.') }}</x-action-message>
                <button type="submit" class="uk-button uk-button-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </x-settings.layout>
</section>
