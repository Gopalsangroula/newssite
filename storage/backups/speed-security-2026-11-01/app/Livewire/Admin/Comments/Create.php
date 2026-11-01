<?php

namespace App\Livewire\Admin\Comments;

use Livewire\Component;

class Create extends Component
{
    /**
     * Admins do not author comments, so this route only leads to the list.
     */
    public function mount()
    {
        return $this->redirectRoute('admin.comments.index');
    }

    public function render()
    {
        return view('livewire.admin.comments.create');
    }
}
