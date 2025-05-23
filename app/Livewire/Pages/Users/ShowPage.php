<?php

namespace App\Livewire\Pages\Users;

use App\Enums\UserInfoOptions;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowPage extends Component
{
    public User $user;

    #[Url(as: 'tab', keep: true)]
    public string $tabSelected = UserInfoOptions::INFORMATION->value;

    public function mount(int $id): void
    {
        $this->user = User::findOrFail($id);
    }

    #[Renderless]
    public function changeTab(UserInfoOptions $userInfoTab): void
    {
        $this->tabSelected = $userInfoTab->value;
    }

    public function render(): View
    {
        return view('livewire.pages.users.show-page')
            ->title($this->user->name.' 的個人資訊');
    }
}
