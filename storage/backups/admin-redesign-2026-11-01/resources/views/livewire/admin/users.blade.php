<div>
    {{-- Header --}}
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom admin-mobile-adj-ah">
        <h1 class="uk-h1 uk-margin-remove">User Management</h1>
        <div class="uk-flex uk-flex-middle uk-flex-right admin-mobile-adj-ah">
            <div class="uk-margin-right">
                <input wire:model.live.debounce.500ms="search" class="uk-input uk-width-medium" type="text" placeholder="Search users...">
            </div>
            <button wire:click="toggleCreateForm" class="uk-button uk-button-primary uk-flex uk-flex-middle">
                <span uk-icon="icon: plus"></span>
                <span class="uk-margin-small-left">Create New User</span>
            </button>
        </div>
    </div>

    {{-- Alerts --}}
    <div class="uk-margin-bottom">
        @if (session()->has('success'))
            <div class="uk-alert-success" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><span uk-icon="icon: check; ratio: 1.2"></span> {{ session('success') }}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="uk-alert-danger" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><span uk-icon="icon: warning; ratio: 1.2"></span> {{ session('error') }}</p>
            </div>
        @endif
    </div>

    {{-- Loading overlay --}}
    <div wire:loading wire:target="createUser,updateUser,deleteUser,resetPassword,search" class="uk-overlay-default uk-position-fixed uk-position-cover uk-flex uk-flex-center uk-flex-middle" style="z-index: 1000;">
        <div class="uk-card uk-card-default uk-card-body uk-flex uk-flex-middle uk-flex-center">
            <span uk-spinner="ratio: 1.5"></span>
            <span class="uk-margin-small-left">Processing...</span>
        </div>
    </div>

    {{-- Create/Edit Form --}}
    @if ($showCreateForm || $showEditForm)
        <form wire:submit.prevent="{{ $showEditForm ? 'updateUser' : 'createUser' }}" class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            {{-- Name --}}
            <div class="uk-margin">
                <label for="name" class="uk-form-label">Name <span class="uk-text-danger">*</span></label>
                <input type="text" wire:model="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="Enter name">
                @error('name') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            {{-- Slug --}}
            <div class="uk-margin">
                <label for="slug" class="uk-form-label">Slug <span class="uk-text-danger">*</span></label>
                <div class="uk-inline uk-width-1-1">
                    <input type="text" wire:model="slug" class="uk-input {{ $errors->has('slug') ? 'uk-form-danger' : '' }}" placeholder="user-slug">
                </div>
                <div class="uk-text-meta">Only letters, numbers, and hyphens allowed</div>
                @error('slug') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            {{-- Email --}}
            <div class="uk-margin">
                <label for="email" class="uk-form-label">Email <span class="uk-text-danger">*</span></label>
                <input type="email" wire:model="email" class="uk-input {{ $errors->has('email') ? 'uk-form-danger' : '' }}" placeholder="Enter email">
                @error('email') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            {{-- Description --}}
            <div class="uk-margin">
                <label for="description" class="uk-form-label">Description</label>
                <textarea wire:model="description" class="uk-textarea {{ $errors->has('description') ? 'uk-form-danger' : '' }}" placeholder="Enter description"></textarea>
                @error('description') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            {{-- User Image --}}
            <div class="uk-margin">
                <label for="user_image" class="uk-form-label">User Image</label>
                <div uk-form-custom="target: true">
                    <input type="file" wire:model="user_image" accept="image/*" id="user_image">
                    <input class="uk-input uk-form-width-medium" type="text" placeholder="Select from browser" disabled>
                </div>
                @error('user_image') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror

                {{-- Image Previews --}}
                <div class="uk-margin-top">
                    @if ($existing_image && !$user_image)
                        <div>
                            <p class="uk-text-small uk-text-muted">Current Image:</p>
                            <img src="{{ Storage::disk('public')->url($existing_image) }}" class="uk-width-small uk-border-rounded" alt="Current Image">
                        </div>
                    @endif
                    
                    @if ($user_image)
                        <div>
                            <p class="uk-text-small uk-text-muted">New Image Preview:</p>
                            <img src="{{ $user_image->temporaryUrl() }}" class="uk-width-small uk-border-rounded" alt="Preview">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Roles --}}
            <div class="uk-margin">
                <label class="uk-form-label">Roles <span class="uk-text-danger">*</span></label>
                <div class="uk-margin-small-bottom">
                    <label class="uk-flex uk-flex-middle">
                        <input type="checkbox" wire:model="selectAllRoles" class="uk-checkbox">
                        <span class="uk-margin-small-left">Select All Roles</span>
                    </label>
                </div>
                <div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l uk-overflow-auto uk-height-medium uk-padding-small uk-background-muted">
                    @foreach($roles as $role)
                        <div class="uk-flex uk-flex-middle">
                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role }}" class="uk-checkbox">
                            <label class="uk-margin-small-left uk-text-capitalize">{{ $role }}</label>
                        </div>
                    @endforeach
                </div>
                @error('selectedRoles') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            {{-- Buttons --}}
            <div class="uk-margin uk-text-right">
                <button type="button" wire:click="toggleCreateForm" class="uk-button uk-button-default uk-margin-small-right">Cancel</button>
                <button type="submit" class="uk-button uk-button-primary">
                    {{ $showEditForm ? 'Update User' : 'Save User' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Users Table --}}
    <div class="uk-card uk-card-default uk-card-body uk-overflow-auto">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Description</th>
                    <th>Roles</th>
                    <th class="uk-text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            @if($user->user_image)
                                <img src="{{ Storage::disk('public')->url($user->user_image) }}" alt="{{ $user->name }}" class="uk-border-circle" width="40" height="40">
                            @else
                                <span class="uk-text-muted">No image</span>
                            @endif
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->description ?? 'No description' }}</td>
                        <td>
                            @if($user->roles->isEmpty())
                                <span class="uk-text-muted">No roles</span>
                            @else
                                <div class="uk-flex uk-flex-wrap uk-grid-small" uk-grid>
                                    @foreach($user->roles->pluck('name') as $role)
                                        <span class="uk-label uk-label-success uk-text-capitalize">{{ $role }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="uk-text-right">
                            <button wire:click="toggleEditForm({{ $user->id }})" class="uk-button uk-button-text uk-margin-small-right">Edit</button>
                            @if(!$user->hasRole('super-admin'))
                            <a href="{{ route('admin.passwordreset', ['userId' => $user->id]) }}">Reset Password</a>

                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="uk-text-center uk-text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="uk-margin-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>


    {{-- Scripts for modals --}}
    <script>
        document.addEventListener('livewire:init', () => {

            // Reset password modal
            Livewire.on('show-reset-password-modal', () => {
                UIkit.modal('#reset-password-modal').show();
            });

            // Reset preview when form is reset
            Livewire.on('reset-preview', () => {
                const fileInput = document.getElementById('user_image');
                if (fileInput) fileInput.value = '';
            });
        });
    </script>
</div>