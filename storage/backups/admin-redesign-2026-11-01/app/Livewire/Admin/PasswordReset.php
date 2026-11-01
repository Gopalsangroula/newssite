<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;

class PasswordReset extends Component
{
    public $userId;
    public $email;
    public $emailEditable = false;
    public $password;
    public $password_confirmation;

    public function mount($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->email = $user->email;
    }

    public function toggleEmailEditable()
    {
        $this->emailEditable = !$this->emailEditable;
    }

    public function sendResetLink()
    {
        $this->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', __($status));
        } else {
            session()->flash('warning', __($status));
        }
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
            ],
        ], [
            'password.regex' => 'Password must contain uppercase, lowercase, number, and special character.',
        ]);

        $user = User::findOrFail($this->userId);

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        session()->flash('message', 'Password updated successfully.');
        $this->reset('password', 'password_confirmation');
        return redirect()->route('admin.users');
    }

    public function render()
    {
        return view('livewire.admin.passwordreset');
    }
}
