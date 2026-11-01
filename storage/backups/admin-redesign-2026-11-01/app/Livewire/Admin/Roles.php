<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;

class Roles extends Component
{
    use WithPagination;

    public $permissions;
    public $showCreateForm = false;
    public $showEditForm = false;
    public $editingRoleId = null;
    public $deleteRoleId = null;
    public $deleteRoleName = null;
    public $search = '';
    public $selectAllPermissions = false;

    public $name = '';
    public $selectedPermissions = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->permissions = Permission::orderBy('name')->pluck('name')->toArray();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAllPermissions()
    {
        if ($this->selectAllPermissions) {
            $this->selectedPermissions = $this->permissions;
        } else {
            $this->selectedPermissions = [];
        }
    }

    public function toggleCreateForm()
    {
        $this->resetForm();
        $this->showCreateForm = !$this->showCreateForm;
        $this->showEditForm = false;
    }

    public function toggleEditForm($roleId)
    {
        $this->resetForm();
        $role = Role::findOrFail($roleId);
        if ($role->name === 'super-admin') {
            session()->flash('error', 'Cannot edit super-admin role.');
            return;
        }
        $this->editingRoleId = $roleId;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->selectAllPermissions = count($this->selectedPermissions) === count($this->permissions);
        $this->showEditForm = true;
        $this->showCreateForm = false;
    }

    public function resetForm()
    {
        $this->reset(['name', 'selectedPermissions', 'editingRoleId', 'selectAllPermissions', 'deleteRoleId', 'deleteRoleName']);
        $this->resetErrorBag();
    }

    public function createRole()
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', Rule::unique('roles', 'name')],
            'selectedPermissions' => ['array', 'min:1', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $this->name]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role created successfully.');
        $this->toggleCreateForm();
        $this->loadData();
    }

    public function updateRole()
    {
        $this->validate([
            'name' => ['required', 'string', 'min:2', Rule::unique('roles', 'name')->ignore($this->editingRoleId)],
            'selectedPermissions' => ['array', 'min:1', 'exists:permissions,name'],
        ]);

        $role = Role::findOrFail($this->editingRoleId);
        $role->update(['name' => $this->name]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully.');
        $this->showEditForm = false;
        $this->resetForm();
        $this->loadData();
    }

    public function showDeleteModal($roleId)
    {
        $role = Role::findOrFail($roleId);
        if ($role->name !== 'super-admin') {
            $this->deleteRoleId = $roleId;
            $this->deleteRoleName = $role->name;
            $this->dispatch('show-delete-modal', roleId: $roleId, roleName: $role->name);
        } else {
            session()->flash('error', 'Cannot delete super-admin role.');
        }
    }

    #[On('delete-confirmed')]
    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        if ($role->name !== 'super-admin') {
            $role->delete();
            session()->flash('success', 'Role deleted successfully.');
            $this->loadData();
        } else {
            session()->flash('error', 'Cannot delete super-admin role.');
        }
        $this->reset(['deleteRoleId', 'deleteRoleName']);
    }

    public function render()
    {
        $roles = Role::with('permissions')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.roles', [
            'roles' => $roles,
        ]);
    }
}