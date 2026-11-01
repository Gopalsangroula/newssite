<div>
    <div uk-grid>
        <div class="uk-width-1-5@s"></div>

        <div class="uk-width-expand@s">
            <div class="uk-card uk-card-default uk-padding">
                <div class="flex flex-col gap-6">
                    <x-auth-header 
                        :title="__('Create an account')" 
                        :description="__('Enter details to create admin/super-admin')" 
                    />

                    <x-auth-session-status class="text-center" :status="session('status')" />

                    <form wire:submit.prevent="register" class="flex flex-col gap-6">
                        <!-- Name -->
                        <flux:input 
                            wire:model="name" 
                            :label="__('Name')" 
                            type="text" 
                            required 
                            autofocus 
                            autocomplete="name" 
                            :placeholder="__('Full name')" 
                        />

                        <!-- Email -->
                        <flux:input 
                            wire:model="email" 
                            :label="__('Email address')" 
                            type="email" 
                            required 
                            autocomplete="email" 
                            placeholder="email@example.com" 
                        />

                        <!-- Password -->
                        <flux:input 
                            wire:model="password" 
                            :label="__('Password')" 
                            type="password" 
                            required 
                            autocomplete="new-password" 
                            :placeholder="__('Password')" 
                            viewable 
                        />

                        <!-- Confirm Password -->
                        <flux:input 
                            wire:model="password_confirmation" 
                            :label="__('Confirm password')" 
                            type="password" 
                            required 
                            autocomplete="new-password" 
                            :placeholder="__('Confirm password')" 
                            viewable 
                        />

                        <!-- Role -->
                        <div>
                            <label class="uk-form-label text-sm font-semibold text-gray-600">
                                {{ __('Role') }}
                            </label>
                            <select 
                                wire:model="role" 
                                class="uk-select mt-1"
                                required
                            >
                                <option value="">-- Select Role --</option>
                                <option value="editor">Editor</option>
                                 <option value="user">User</option>
                                <option value="advertiser">Adertiser</option>
                            </select>
                            @error('role') 
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div> 
                            @enderror
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-end">
                            <flux:button type="submit" variant="primary" class="w-full">
                                {{ __('Register Admin') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="uk-width-1-5@s"></div>
    </div>
</div>
