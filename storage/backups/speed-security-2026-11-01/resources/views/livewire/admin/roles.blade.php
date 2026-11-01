<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Role Management</h1>
            <p class="ah-page-sub">{{ $roles->total() }} {{ Str::plural('role', $roles->total()) }}</p>
        </div>
    </div>

    {{-- Messages --}}
    @foreach (['success' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session()->has($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    {{-- Saving overlay --}}
    <div wire:loading.flex wire:target="createRole,updateRole,deleteRole" class="ah-busy">
        <span uk-spinner="ratio: 0.8" wire:ignore></span>
        <span>Processing...</span>
    </div>

    {{-- Create/Edit Form --}}
    @if ($showCreateForm || $showEditForm)
        <form
            wire:key="role-form-{{ $editingRoleId ?? 'new' }}"
            wire:submit.prevent="{{ $showEditForm ? 'updateRole' : 'createRole' }}"
            x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })"
            class="ah-card ah-form-card"
        >
            <div class="ah-card-head">
                <h2 class="ah-card-title">{{ $showEditForm ? 'Edit Role' : 'Create New Role' }}</h2>
            </div>

            <div class="ah-card-body">
                <div class="ah-form-grid ah-form-grid--single">
                    <div class="ah-field">
                        <label for="name" class="uk-form-label">Role Name <span class="ah-required">*</span></label>
                        <input id="name" type="text" wire:model.live="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="Enter role name">
                        @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>

                    <fieldset class="ah-fieldset">
                        <legend class="uk-form-label">Permissions <span class="ah-required">*</span></legend>
                        <div class="ah-option-panel">
                            <label class="ah-option ah-option--all">
                                <input type="checkbox" wire:model.live="selectAllPermissions" class="uk-checkbox">
                                <span>Select All Permissions</span>
                            </label>
                            <div class="ah-option-grid">
                                @foreach ($permissions as $permission)
                                    <label class="ah-option" wire:key="permission-option-{{ $permission }}">
                                        <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permission }}" class="uk-checkbox">
                                        <span>{{ $permission }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @error('selectedPermissions') <div class="ah-field-error">{{ $message }}</div> @enderror
                    </fieldset>
                </div>
            </div>

            <div class="ah-card-foot ah-form-actions">
                <button type="button" wire:click="closeForm" class="uk-button uk-button-default">Cancel</button>
                <button type="submit" class="uk-button uk-button-primary">
                    {{ $showEditForm ? 'Update Role' : 'Save Role' }}
                </button>
            </div>
        </form>
    @endif

    {{-- Page access grid --}}
    <div class="ah-card ah-form-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">Who can open each page</h2>
            <span class="ah-table-meta">Changes save as you click</span>
        </div>
        <div class="ah-table-wrap">
            <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table ah-access-table">
                <thead>
                    <tr>
                        <th>Page</th>
                        @foreach ($accessRoles as $role)
                            <th class="uk-text-capitalize">{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sections as $permission => $label)
                        <tr wire:key="access-{{ Str::slug($permission) }}">
                            <td><span class="ah-table-title">{{ $label }}</span></td>
                            @foreach ($accessRoles as $role)
                                @php
                                    $isSuperAdmin = $role->name === 'super-admin';
                                    $isOwnRolesAccess = $permission === 'access roles' && auth()->user()->hasRole($role->name);
                                @endphp
                                <td>
                                    <input
                                        type="checkbox"
                                        class="uk-checkbox"
                                        aria-label="{{ $label }} for {{ $role->name }}"
                                        wire:key="access-{{ Str::slug($permission) }}-{{ $role->id }}"
                                        wire:click="togglePageAccess({{ $role->id }}, '{{ $permission }}')"
                                        @checked($isSuperAdmin || $role->permissions->contains('name', $permission))
                                        @disabled($isSuperAdmin || $isOwnRolesAccess)
                                    >
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Roles List --}}
    <div class="ah-card">
        <div class="ah-toolbar">
            <div class="ah-toolbar-grow">
                <input
                    wire:model.live.debounce.500ms="search"
                    class="uk-input"
                    type="text"
                    placeholder="Search roles..."
                    aria-label="Search roles"
                >
            </div>
            <button type="button" wire:click="{{ $showCreateForm || $showEditForm ? 'closeForm' : 'toggleCreateForm' }}" class="uk-button uk-button-primary">
                <span
                    wire:key="role-toggle-icon-{{ $showCreateForm || $showEditForm ? 'close' : 'plus' }}"
                    uk-icon="icon: {{ $showCreateForm || $showEditForm ? 'close' : 'plus' }}; ratio: 0.8"
                    wire:ignore
                ></span>
                <span>{{ $showCreateForm || $showEditForm ? 'Cancel' : 'Create New Role' }}</span>
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
                        <th>Name</th>
                        <th class="ah-col-perms">Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="uk-text-nowrap"><span class="ah-table-title">{{ $role->name }}</span></td>
                            <td class="ah-col-perms">
                                @if ($role->permissions->isEmpty())
                                    <span class="ah-muted">No permissions</span>
                                @else
                                    <div class="ah-badges">
                                        @foreach ($role->permissions->pluck('name') as $permission)
                                            <span class="ah-badge uk-text-capitalize">{{ $permission }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="ah-row-actions">
                                    <button type="button" wire:click="toggleEditForm({{ $role->id }})">Edit</button>
                                    @if ($role->name !== 'super-admin')
                                        <button type="button" class="ah-action-danger" wire:click="showDeleteModal({{ $role->id }})">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="ah-empty">No roles found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($roles->hasPages())
            <div class="ah-card-foot">
                {{ $roles->links('partials.admin-pagination') }}
            </div>
        @endif
    </div>

    {{-- "container: false" keeps the modal inside the Livewire root so its $wire calls work. --}}
    <div id="delete-modal" class="ah-modal" uk-modal="container: false" wire:ignore>
        <div class="uk-modal-dialog uk-modal-body">
            <h2 class="uk-modal-title">Confirm Deletion</h2>
            <p>Are you sure you want to delete the <span x-text="$wire.deleteRoleName"></span> role?</p>
            <div class="uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close uk-margin-small-right" type="button">Cancel</button>
                <button class="uk-button uk-button-danger uk-modal-close" type="button" x-on:click="$wire.dispatch('delete-confirmed', { roleId: $wire.deleteRoleId })">Delete</button>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('show-delete-modal', () => UIkit.modal('#delete-modal').show());
    </script>
    @endscript
</div>
