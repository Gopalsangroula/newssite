<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;

#[Title('Users')]
class Users extends Component
{
    use WithPagination, WithFileUploads;

    public $roles;
    public $showCreateForm = false;
    public $showEditForm = false;
    public $editingUserId = null;
    public $selectedUserId = null;
    public $name = '';
    public $slug = '';
    public $email = '';
    public $description = '';
    public $user_image; // new upload
    public $existing_image = null; // previous DB image
    public $selectedRoles = [];
    public $selectAllRoles = false;
    public $search = '';
    public $newPassword = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->roles = Role::orderBy('name')->pluck('name')->toArray();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedName($value)
    {
        // Generate slug from name only when creating or if slug is empty
        if ($this->showCreateForm || empty($this->slug)) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug($value)
    {
        // Automatically slugify the input
        $this->slug = Str::slug($value);
    }

    public function updatedSelectAllRoles()
    {
        $this->selectedRoles = $this->selectAllRoles ? $this->roles : [];
    }

    public function updatedSelectedRoles()
    {
        // Keep the "Select All Roles" box truthful as single roles are ticked.
        $this->selectAllRoles = count($this->selectedRoles) === count($this->roles);
    }

    public function toggleCreateForm()
    {
        $this->resetForm();
        $this->showCreateForm = !$this->showCreateForm;
        $this->showEditForm = false;
    }

    public function closeForm()
    {
        $this->resetForm();
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function toggleEditForm($userId)
    {
        $this->resetForm();

        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Cannot edit super-admin user.');
            return;
        }

        $this->editingUserId   = $user->id;
        $this->name            = $user->name;
        $this->slug            = $user->slug;
        $this->email           = $user->email;
        $this->description     = $user->description;
        $this->existing_image  = $user->user_image;
        $this->selectedRoles   = $user->roles->pluck('name')->toArray();
        $this->selectAllRoles  = count($this->selectedRoles) === count($this->roles);

        $this->showEditForm    = true;
        $this->showCreateForm  = false;
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'slug',
            'email',
            'description',
            'user_image',
            'existing_image',
            'selectedRoles',
            'editingUserId',
            'selectAllRoles',
            'selectedUserId',
            'newPassword'
        ]);
        $this->resetErrorBag();
        $this->dispatch('reset-preview'); // Dispatch event to reset preview
    }

    public function createUser()
    {
        $this->validate([
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'slug'          => ['required', 'string', 'max:200', 'regex:/^[\w\-]+$/', Rule::unique('users', 'slug')],
            'email'         => ['required', 'email', Rule::unique('users', 'email')],
            'description'   => ['nullable', 'string', 'max:1000'],
            'user_image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'selectedRoles' => ['array', 'min:1', 'exists:roles,name'],
        ]);

        $imagePath = null;
        if ($this->user_image) {
            $imagePath = $this->storeImage($this->user_image);
        }

        $user = User::create([
            'name'              => $this->name,
            'slug'              => $this->slug,
            'email'             => $this->email,
            'description'       => $this->description,
            'user_image'        => $imagePath,
            'password'          => bcrypt(env('SEEDER_DEFAULT_PASSWORD', Str::random(12))),
            'email_verified_at' => now(),
        ]);

        $user->syncRoles($this->selectedRoles);

        session()->flash('success', 'User created successfully.');
        $this->toggleCreateForm();
        $this->loadData();
    }

    public function updateUser()
    {
        $this->validate([
            'name'          => ['required', 'string', 'min:2', 'max:255'],
            'slug'          => ['required', 'string', 'max:200', 'regex:/^[\w\-]+$/', Rule::unique('users', 'slug')->ignore($this->editingUserId)],
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'description'   => ['nullable', 'string', 'max:1000'],
            'user_image'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'selectedRoles' => ['array', 'min:1', 'exists:roles,name'],
        ]);

        $user = User::findOrFail($this->editingUserId);

        $imagePath = $this->existing_image;

        if ($this->user_image) {
            // Delete old file
            if ($this->existing_image && Storage::disk('public')->exists($this->existing_image)) {
                Storage::disk('public')->delete($this->existing_image);
            }

            $imagePath = $this->storeImage($this->user_image);
        }

        $user->update([
            'name'        => $this->name,
            'slug'        => $this->slug,
            'email'       => $this->email,
            'description' => $this->description,
            'user_image'  => $imagePath,
        ]);

        $user->syncRoles($this->selectedRoles);

        session()->flash('success', 'User updated successfully.');
        $this->showEditForm = false;
        $this->resetForm();
        $this->loadData();
    }

    private function storeImage($file)
    {
        $originalName = $file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->extension();
        $slugName = Str::slug($filename);
        $finalName = $slugName . '.' . $extension;

        $folder = 'users';
        $counter = 1;

        while (Storage::disk('public')->exists("$folder/$finalName")) {
            $finalName = $slugName . '-' . $counter++ . '.' . $extension;
        }

        return $file->storeAs($folder, $finalName, 'public');
    }

    // --- Delete user
    public function showDeleteModal($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Cannot delete super-admin user.');
            return;
        }

        $this->selectedUserId = $user->id;
        $this->name           = $user->name;
        $this->dispatch('show-delete-modal', userId: $user->id, userName: $user->name);
    }

    #[On('delete-confirmed')]
    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Cannot delete super-admin user.');
            return;
        }

        if ($user->user_image && Storage::disk('public')->exists($user->user_image)) {
            Storage::disk('public')->delete($user->user_image);
        }

        $user->delete();

        session()->flash('success', 'User deleted successfully.');
        $this->reset(['selectedUserId', 'name']);
        $this->loadData();
    }

    // --- Reset password
    public function showResetPasswordModal($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Cannot reset password for super-admin user.');
            return;
        }

        $this->selectedUserId = $user->id;
        $this->name           = $user->name;
        $this->newPassword    = '';
        $this->dispatch('show-reset-password-modal');
    }

    public function resetPassword()
    {
        $this->validate([
            'newPassword' => ['required', 'string', 'min:8'],
        ]);

        $user = User::findOrFail($this->selectedUserId);

        if ($user->hasRole('super-admin')) {
            session()->flash('error', 'Cannot reset password for super-admin user.');
            return;
        }

        $user->update([
            'password' => bcrypt($this->newPassword),
        ]);

        session()->flash('success', 'Password reset successfully for ' . $user->name);
        $this->reset(['newPassword', 'selectedUserId', 'name']);
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn($query) =>
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('slug', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.users', [
            'users' => $users,
        ]);
    }
}