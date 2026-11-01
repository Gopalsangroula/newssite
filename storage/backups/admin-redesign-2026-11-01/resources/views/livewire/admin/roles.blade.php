<div class="uk-container uk-container-large uk-padding">
    <!-- Header and Search -->
    <div class="uk-flex uk-flex-between uk-flex-middle uk-margin-bottom">
        <h1 class="uk-h1 uk-margin-remove">Role Management</h1>
        <div class="uk-flex uk-flex-middle uk-flex-right">
            <div class="uk-margin-right">
                <input wire:model.live.debounce.500ms="search" class="uk-input uk-width-medium" type="text" placeholder="Search roles...">
            </div>
            <button wire:click="toggleCreateForm" class="uk-button uk-button-primary uk-flex uk-flex-middle">
                <span uk-icon="icon: {{ $showCreateForm || $showEditForm ? 'close' : 'plus' }}"></span>
                <span class="uk-margin-small-left">{{ $showCreateForm || $showEditForm ? 'Cancel' : 'Create New Role' }}</span>
            </button>
        </div>
    </div>

    <!-- Messages -->
    <div class="uk-margin-bottom">
        @if (session()->has('success'))
            <div class="uk-alert uk-alert-success uk-animation-fade" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><span uk-icon="icon: check; ratio: 1.2"></span> {{ session('success') }}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="uk-alert uk-alert-danger uk-animation-fade" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <p><span uk-icon="icon: warning; ratio: 1.2"></span> {{ session('error') }}</p>
            </div>
        @endif
    </div>

    <!-- Loading State -->
    <div wire:loading wire:target="createRole,updateRole,deleteRole,search" class="uk-overlay-default uk-position-fixed uk-position-cover uk-flex uk-flex-center uk-flex-middle" style="z-index: 1000;">
        <div class="uk-card uk-card-default uk-card-body uk-flex uk-flex-middle uk-flex-center">
            <span uk-spinner="ratio: 1.5"></span>
            <span class="uk-margin-small-left">Processing...</span>
        </div>
    </div>

    <!-- Create/Edit Form -->
    @if ($showCreateForm || $showEditForm)
        <form wire:submit.prevent="{{ $showEditForm ? 'updateRole' : 'createRole' }}" class="uk-card uk-card-default uk-card-body uk-margin-bottom">
            <div class="uk-margin">
                <label for="name" class="uk-form-label">Role Name <span class="uk-text-danger">*</span></label>
                <input type="text" wire:model.live="name" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="Enter role name">
                @error('name') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            <div class="uk-margin">
                <label class="uk-form-label">Permissions <span class="uk-text-danger">*</span></label>
                <div class="uk-margin-small-bottom">
                    <label class="uk-flex uk-flex-middle">
                        <input type="checkbox" wire:model="selectAllPermissions" class="uk-checkbox">
                        <span class="uk-margin-small-left">Select All Permissions</span>
                    </label>
                </div>
                <div class="uk-grid uk-grid-small uk-child-width-1-2@s uk-child-width-1-3@m uk-child-width-1-4@l uk-overflow-auto uk-height-medium uk-padding-small uk-background-muted">
                    @foreach($permissions as $permission)
                        <div class="uk-flex uk-flex-middle">
                            <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permission }}" class="uk-checkbox">
                            <label class="uk-margin-small-left uk-text-capitalize">{{ $permission }}</label>
                        </div>
                    @endforeach
                </div>
                @error('selectedPermissions') <div class="uk-text-danger uk-text-small uk-margin-top">{{ $message }}</div> @enderror
            </div>

            <div class="uk-margin uk-text-right">
                <button type="button" wire:click="toggleCreateForm" class="uk-button uk-button-default uk-margin-small-right">Cancel</button>
                <button type="submit" class="uk-button uk-button-primary">
                    {{ $showEditForm ? 'Update Role' : 'Save Role' }}
                </button>
            </div>
        </form>
    @endif

    <!-- Roles Table -->
    <div class="uk-card uk-card-default uk-card-body">
        <table class="uk-table uk-table-divider uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th class="uk-text-uppercase uk-text-small">Name</th>
                    <th class="uk-text-uppercase uk-text-small">Permissions</th>
                    <th class="uk-text-right uk-text-uppercase uk-text-small">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>
                            @if($role->permissions->isEmpty())
                                <span class="uk-text-muted">No permissions</span>
                            @else
                                <div class="uk-flex uk-flex-wrap uk-grid-small" uk-grid>
                                    @foreach($role->permissions->pluck('name') as $permission)
                                        <span class="uk-label uk-label-success uk-text-capitalize">{{ $permission }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="uk-text-right">
                            <button wire:click="toggleEditForm({{ $role->id }})" class="uk-button uk-button-text uk-margin-small-right">Edit</button>
                            @if($role->name !== 'super-admin')
                                <button wire:click="showDeleteModal({{ $role->id }})" class="uk-button uk-button-text uk-text-danger">Delete</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="uk-text-center uk-text-muted">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($roles->hasPages())
            <div class="uk-margin-top">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="uk-modal" uk-modal>
        <div class="uk-modal-dialog uk-modal-body">
            <h2 class="uk-modal-title">Confirm Deletion</h2>
            <p>Are you sure you want to delete the <span x-text="$wire.deleteRoleName"></span> role?</p>
            <div class="uk-text-right">
                <button class="uk-button uk-button-default uk-modal-close uk-margin-small-right" type="button">Cancel</button>
                <button class="uk-button uk-button-danger uk-modal-close" type="button" x-on:click="$wire.dispatch('delete-confirmed', { roleId: $wire.deleteRoleId })">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-delete-modal', ({ roleId, roleName }) => {
                window.$wire.set('deleteRoleId', roleId);
                window.$wire.set('deleteRoleName', roleName);
                UIkit.modal('#delete-modal').show();
            });
        });
    </script>
</div>