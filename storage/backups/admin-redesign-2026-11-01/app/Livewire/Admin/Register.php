<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'admin'; // default

    public function mount()
    {
        if (!Auth::check() || !Auth::user()->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }
    }

    public function register()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->role);

        session()->flash('success', 'New admin registered successfully.');

        // Optionally reset the form
        $this->reset(['name', 'email', 'password', 'password_confirmation']);
        return redirect()->to('/admin/users');
    }

    public function render()
    {
        return view('livewire.admin.register');
    }
}
