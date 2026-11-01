<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Add User</h1>
            <p class="ah-page-sub">Create a new account and assign its role.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.users') }}" class="uk-button uk-button-default">All Users</a>
        </div>
    </div>

    @if (session('status'))
        <div class="uk-alert-success ah-flash" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('status') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="register" class="ah-card ah-form-card ah-form-card--narrow">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Account Details</h2>
        </div>

        <div class="ah-card-body">
            <div class="ah-form-grid">
                {{-- Name --}}
                <div class="ah-field">
                    <label for="name" class="uk-form-label">{{ __('Name') }} <span class="ah-required">*</span></label>
                    <input
                        id="name"
                        wire:model="name"
                        type="text"
                        class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="{{ __('Full name') }}"
                    >
                    @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Email --}}
                <div class="ah-field">
                    <label for="email" class="uk-form-label">{{ __('Email address') }} <span class="ah-required">*</span></label>
                    <input
                        id="email"
                        wire:model="email"
                        type="email"
                        class="uk-input {{ $errors->has('email') ? 'uk-form-danger' : '' }}"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    >
                    @error('email') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Password. wire:ignore.self keeps the show/hide type across re-renders. --}}
                <div class="ah-field {{ $errors->has('password') ? 'ah-field--error' : '' }}" x-data="{ show: false }">
                    <label for="password" class="uk-form-label">{{ __('Password') }} <span class="ah-required">*</span></label>
                    <div class="uk-inline uk-width-1-1">
                        <button
                            type="button"
                            class="uk-form-icon uk-form-icon-flip ah-password-toggle"
                            x-on:click="show = ! show"
                            x-bind:aria-label="show ? 'Hide password' : 'Show password'"
                        ><span uk-icon="icon: eye" wire:ignore></span></button>
                        <input
                            id="password"
                            wire:model="password"
                            wire:ignore.self
                            type="password"
                            x-bind:type="show ? 'text' : 'password'"
                            class="uk-input"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Password') }}"
                        >
                    </div>
                    @error('password') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="ah-field" x-data="{ show: false }">
                    <label for="password_confirmation" class="uk-form-label">{{ __('Confirm password') }} <span class="ah-required">*</span></label>
                    <div class="uk-inline uk-width-1-1">
                        <button
                            type="button"
                            class="uk-form-icon uk-form-icon-flip ah-password-toggle"
                            x-on:click="show = ! show"
                            x-bind:aria-label="show ? 'Hide password' : 'Show password'"
                        ><span uk-icon="icon: eye" wire:ignore></span></button>
                        <input
                            id="password_confirmation"
                            wire:model="password_confirmation"
                            wire:ignore.self
                            type="password"
                            x-bind:type="show ? 'text' : 'password'"
                            class="uk-input"
                            required
                            autocomplete="new-password"
                            placeholder="{{ __('Confirm password') }}"
                        >
                    </div>
                </div>

                {{-- Role --}}
                <div class="ah-field">
                    <label for="role" class="uk-form-label">{{ __('Role') }} <span class="ah-required">*</span></label>
                    <select
                        id="role"
                        wire:model="role"
                        class="uk-select {{ $errors->has('role') ? 'uk-form-danger' : '' }}"
                        required
                    >
                        <option value="">-- Select Role --</option>
                        <option value="editor">Editor</option>
                        <option value="user">User</option>
                        <option value="advertiser">Advertiser</option>
                    </select>
                    @error('role') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="ah-card-foot ah-form-actions">
            <button type="submit" class="uk-button uk-button-primary">{{ __('Add User') }}</button>
        </div>
    </form>
</div>
