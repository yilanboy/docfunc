<?php

namespace App\Livewire\Pages\Users;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class ShowPage extends Component
{
    public User $user;

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);
    }

    public function render(): View
    {
        return view('livewire.pages.users.show-page')
            ->title($this->user->name.' 的個人資訊');
    }
}
