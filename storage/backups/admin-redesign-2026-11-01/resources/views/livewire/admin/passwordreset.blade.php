<div class="uk-section uk-section-default">
    <div class="uk-grid-medium" uk-grid>
        <div class="uk-width-auto                                                                                                                                                                                               @m">
        </div>
        <div class="uk-width-expand@m">
            <div class="uk-card uk-card-default uk-card-body">
            <h1 class="uk-heading-bullet">Manage Password for: {{ $email }}</h1>

            {{-- Password Reset Link --}}
            <form wire:submit.prevent="sendResetLink" class="uk-form-stacked uk-margin">
                <div class="uk-margin">
                    <label class="uk-form-label">Email Address</label>
                    <div class="uk-form-controls">
                        <div class="uk-inline uk-width-1-1">
                            <input class="uk-input" type="email" wire:model="email"
                                {{ $emailEditable ? '' : 'disabled' }} />
                            <a class="uk-form-icon uk-form-icon-flip" href="#" wire:click="toggleEmailEditable"
                                uk-icon="icon: pencil"></a>
                        </div>
                        @error('email') <span class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button class="uk-button uk-button-primary" type="submit"
                    uk-tooltip="Send a password reset link to the user">Send Reset Link</button>

                @if (session('status'))
                <div class="uk-alert-success uk-margin-small-top" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    {{ session('status') }}
                </div>
                @endif
            </form>

            <hr>

            {{-- Manual Password Update --}}
            <form wire:submit.prevent="updatePassword" class="uk-form-stacked uk-margin">
                <div class="uk-margin">
                    <label class="uk-form-label">New Password</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="password" wire:model="password"
                            placeholder="Enter new password" />
                        @error('password') <span class="uk-text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label">Confirm New Password</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" type="password" wire:model="password_confirmation"
                            placeholder="Confirm new password" />
                    </div>
                </div>

                <button class="uk-button uk-button-danger" type="submit"
                    wire:confirm="Are you sure you want to update the password for {{ $email }}? This action cannot be undone.">
                    Update Password
                </button>

                @if (session('message'))
                <div class="uk-alert-primary uk-margin-small-top" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    {{ session('message') }}
                </div>
                @endif
                @if (session('warning'))
                <div class="uk-alert-warning uk-margin-small-top" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    {{ session('warning') }}
                </div>
                @endif
            </form>
            </div>
        </div>
        <div class="uk-width-auto                                                                                                                                                                                               @m">
        </div>
    </div>
</div>
