<?php

namespace App\Livewire\Pages\Settings\Users;

use App\Mail\DestroyUser;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

class DestroyUserPage extends Component
{
    use AuthorizesRequests;

    public User $user;

    #[Locked]
    public string $destroyUserConfirmationRouteName = 'users.destroy';

    #[Locked]
    public int $urlValidMinutes = 5;

    public function mount(int $id): void
    {
        $this->user = User::findorFail($id);

        $this->authorize('update', $this->user);
    }

    public function sendDestroyEmail(User $user): void
    {
        $this->authorize('update', $user);

        // 生成一次性連結，並設定 5 分鐘後失效
        $destroyUserLink = URL::temporarySignedRoute(
            $this->destroyUserConfirmationRouteName,
            now()->addMinutes($this->urlValidMinutes),
            ['user' => $user->id]
        );

        Mail::to($user)->queue(new DestroyUser($destroyUserLink));

        $this->dispatch('info-badge', status: 'success', message: '已寄出信件！');
    }

    #[Title('會員中心 - 刪除帳號')]
    public function render(): View
    {
        return view('livewire.pages.settings.users.destroy-user-page');
    }
}
