<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Manage Password</h1>
            <p class="ah-page-sub">{{ $email }}</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('admin.users') }}" class="uk-button uk-button-default">All Users</a>
        </div>
    </div>

    {{-- Messages --}}
    @foreach (['status' => 'uk-alert-success', 'message' => 'uk-alert-primary', 'warning' => 'uk-alert-warning'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    <div class="ah-card-grid">
        {{-- Password Reset Link --}}
        <form wire:submit.prevent="sendResetLink" class="ah-card ah-form-card">
            <div class="ah-card-head">
                <h2 class="ah-card-title">Send Reset Link</h2>
            </div>
            <div class="ah-card-body">
                <div class="ah-field">
                    <label for="reset-email" class="uk-form-label">Email Address</label>
                    <div class="uk-inline uk-width-1-1">
                        <a
                            class="uk-form-icon uk-form-icon-flip"
                            href="#"
                            wire:click.prevent="toggleEmailEditable"
                            uk-icon="icon: pencil"
                            aria-label="Edit email address"
                            wire:ignore
                        ></a>
                        <input
                            id="reset-email"
                            class="uk-input {{ $errors->has('email') ? 'uk-form-danger' : '' }}"
                            type="email"
                            wire:model="email"
                            {{ $emailEditable ? '' : 'disabled' }}
                        >
                    </div>
                    @error('email') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="ah-card-foot ah-form-actions">
                <button class="uk-button uk-button-primary" type="submit">Send Reset Link</button>
            </div>
        </form>

        {{-- Manual Password Update. wire:confirm only fires on the element holding wire:submit. --}}
        <form
            wire:submit.prevent="updatePassword"
            wire:confirm="Are you sure you want to update the password for {{ $email }}? This action cannot be undone."
            class="ah-card ah-form-card"
        >
            <div class="ah-card-head">
                <h2 class="ah-card-title">Set New Password</h2>
            </div>
            <div class="ah-card-body">
                <div class="ah-form-grid ah-form-grid--single">
                    <div class="ah-field">
                        <label for="password" class="uk-form-label">New Password</label>
                        <input
                            id="password"
                            class="uk-input {{ $errors->has('password') ? 'uk-form-danger' : '' }}"
                            type="password"
                            wire:model="password"
                            autocomplete="new-password"
                            placeholder="Enter new password"
                        >
                        @error('password') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="ah-field">
                        <label for="password_confirmation" class="uk-form-label">Confirm New Password</label>
                        <input
                            id="password_confirmation"
                            class="uk-input"
                            type="password"
                            wire:model="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>
            </div>
            <div class="ah-card-foot ah-form-actions">
                <button class="uk-button uk-button-danger" type="submit">Update Password</button>
            </div>
        </form>
    </div>
</div>
