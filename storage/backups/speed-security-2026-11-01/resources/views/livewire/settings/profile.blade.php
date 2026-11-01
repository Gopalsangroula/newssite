<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Profile')] class extends Component {
    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <x-settings.layout current="profile">
        <form wire:submit="updateProfileInformation" class="ah-card ah-form-card">
            <div class="ah-card-head">
                <div>
                    <h2 class="ah-card-title">{{ __('Profile') }}</h2>
                    <p class="ah-card-sub">{{ __('Update your name and email address') }}</p>
                </div>
            </div>

            <div class="ah-card-body">
                <div class="ah-form-grid ah-form-grid--single">
                    <div class="ah-field">
                        <label for="name" class="uk-form-label">{{ __('Name') }} <span class="ah-required">*</span></label>
                        <input id="name" type="text" wire:model="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" required autofocus autocomplete="name">
                        @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="ah-field">
                        <label for="email" class="uk-form-label">{{ __('Email') }} <span class="ah-required">*</span></label>
                        <input id="email" type="email" wire:model="email" class="uk-input {{ $errors->has('email') ? 'uk-form-danger' : '' }}" required autocomplete="email">
                        @error('email') <div class="ah-field-error">{{ $message }}</div> @enderror

                        @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                            <div class="ah-field-hint">
                                {{ __('Your email address is unverified.') }}
                                <a href="#" wire:click.prevent="resendVerificationNotification">{{ __('Click here to re-send the verification email.') }}</a>
                            </div>

                            @if (session('status') === 'verification-link-sent')
                                <div class="ah-field-hint ah-saved">{{ __('A new verification link has been sent to your email address.') }}</div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <div class="ah-card-foot ah-form-actions">
                <x-action-message class="ah-saved" on="profile-updated">{{ __('Saved.') }}</x-action-message>
                <button type="submit" class="uk-button uk-button-primary">{{ __('Save') }}</button>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
