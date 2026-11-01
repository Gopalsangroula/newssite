<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">User Management</h1>
            <p class="ah-page-sub">{{ $users->total() }} {{ Str::plural('user', $users->total()) }}</p>
        </div>
    </div>

    {{-- Alerts --}}
    @foreach (['success' => 'uk-alert-success', 'message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session()->has($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="createUser,updateUser,deleteUser,resetPassword" class="ah-busy">
        <span uk-spinner="ratio: 0.8" wire:ignore></span>
        <span>Processing...</span>
    </div>

    {{-- Create/Edit Form --}}
    @if ($showCreateForm || $showEditForm)
        <form
            wire:key="user-form-{{ $editingUserId ?? 'new' }}"
            wire:submit.prevent="{{ $showEditForm ? 'updateUser' : 'createUser' }}"
            x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
            class="ah-card ah-form-card"
        >
            <div class="ah-card-head">
                <h2 class="ah-card-title">{{ $showEditForm ? 'Edit User' : 'Create New User' }}</h2>
            </div>

            <div class="ah-card-body">
                <div class="ah-form-grid">
                    {{-- Name --}}
                    <div class="ah-field">
                        <label for="name" class="uk-form-label">Name <span class="ah-required">*</span></label>
                        <input id="name" type="text" wire:model="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="Enter name">
                        @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="ah-field">
                        <label for="email" class="uk-form-label">Email <span class="ah-required">*</span></label>
                        <input id="email" type="email" wire:model="email" class="uk-input {{ $errors->has('email') ? 'uk-form-danger' : '' }}" placeholder="Enter email">
                        @error('email') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Slug --}}
                    <div class="ah-field">
                        <label for="slug" class="uk-form-label">Slug <span class="ah-required">*</span></label>
                        <input id="slug" type="text" wire:model="slug" class="uk-input {{ $errors->has('slug') ? 'uk-form-danger' : '' }}" placeholder="user-slug">
                        <div class="ah-field-hint">Only letters, numbers, and hyphens allowed</div>
                        @error('slug') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- User Image --}}
                    <div class="ah-field">
                        <label for="user_image" class="uk-form-label">User Image</label>
                        <input id="user_image" type="file" wire:model="user_image" accept="image/*" class="ah-file">
                        @error('user_image') <div class="ah-field-error">{{ $message }}</div> @enderror

                        {{-- Image Previews --}}
                        @if ($user_image)
                            <div class="ah-image-preview">
                                <img src="{{ $user_image->temporaryUrl() }}" alt="New image preview">
                                <span>New image preview</span>
                            </div>
                        @elseif ($existing_image)
                            <div class="ah-image-preview">
                                <img src="{{ Storage::url($existing_image) }}" alt="Current image">
                                <span>Current image</span>
                            </div>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div class="ah-field ah-field--full">
                        <label for="description" class="uk-form-label">Description</label>
                        <textarea id="description" wire:model="description" rows="3" class="uk-textarea {{ $errors->has('description') ? 'uk-form-danger' : '' }}" placeholder="Enter description"></textarea>
                        @error('description') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    {{-- Roles --}}
                    <fieldset class="ah-fieldset ah-field--full">
                        <legend class="uk-form-label">Roles <span class="ah-required">*</span></legend>
                        <div class="ah-option-panel">
                            <label class="ah-option ah-option--all">
                                <input type="checkbox" wire:model.live="selectAllRoles" class="uk-checkbox">
                                <span>Select All Roles</span>
                            </label>
                            <div class="ah-option-grid">
                                @foreach ($roles as $role)
                                    <label class="ah-option" wire:key="role-option-{{ $role }}">
                                        <input type="checkbox" wire:model.live="selectedRoles" value="{{ $role }}" class="uk-checkbox">
                                        <span>{{ $role }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('selectedRoles') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </fieldset>
                </div>
            </div>

            <div class="ah-card-foot ah-form-actions">
                <button type="button" wire:click="closeForm" class="uk-button uk-button-default">Cancel</button>
                <button type="submit" class="uk-button uk-button-primary">
                    {{ $showEditForm ? 'Update User' : 'Save User' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Users List --}}
    <div class="ah-card">
        <div class="ah-toolbar">
            <div class="ah-toolbar-grow">
                <input
                    wire:model.live.debounce.500ms="search"
                    class="uk-input"
                    type="text"
                    placeholder="Search users..."
                    aria-label="Search users"
                >
            </div>
            <button type="button" wire:click="toggleCreateForm" class="uk-button uk-button-primary">
                <span uk-icon="icon: plus; ratio: 0.8" wire:ignore></span>
                <span>Add New User</span>
            </button>
        </div>

        <div
            class="ah-table-wrap"
            wire:loading.class="ah-is-loading"
            wire:target="search,gotoPage,previousPage,nextPage"
        >
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                <thead>
                    <tr>
                        <th class="ah-col-avatar"><span class="uk-hidden-visually">Avatar</span></th>
                        <th class="ah-col-main">Name</th>
                        <th>Roles</th>
                        <th class="ah-col-text">Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="ah-col-avatar">
                                @if ($user->user_image)
                                    <img src="{{ Storage::url($user->user_image) }}" alt="" class="ah-avatar" width="36" height="36" loading="lazy" decoding="async">
                                @else
                                    <span class="ah-avatar" aria-hidden="true">{{ $user->initials() }}</span>
                                @endif
                            </td>
                            <td class="ah-col-main">
                                <span class="ah-table-title">{{ $user->name }}</span>
                                <span class="ah-table-meta">{{ $user->email }}</span>
                            </td>
                            <td>
                                @if ($user->roles->isEmpty())
                                    <span class="ah-muted">No roles</span>
                                @else
                                    <div class="ah-badges">
                                        @foreach ($user->roles->pluck('name') as $roleName)
                                            <span class="ah-badge ah-badge--info uk-text-capitalize">{{ $roleName }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="ah-col-text">
                                @if ($user->description)
                                    {{ Str::limit($user->description, 80) }}
                                @else
                                    <span class="ah-muted">No description</span>
                                @endif
                            </td>
                            <td>
                                <div class="ah-row-actions">
                                    <button type="button" wire:click="toggleEditForm({{ $user->id }})">Edit</button>
                                    @if (! $user->hasRole('super-admin'))
                                        <a href="{{ route('admin.passwordreset', ['userId' => $user->id]) }}">Reset Password</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="ah-empty">No users found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="ah-card-foot">
                {{ $users->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>

    @script
    <script>
        // Reset preview when form is reset
        $wire.on('reset-preview', () => {
            const fileInput = document.getElementById('user_image');
            if (fileInput) fileInput.value = '';
        });
    </script>
    @endscript
</div>
