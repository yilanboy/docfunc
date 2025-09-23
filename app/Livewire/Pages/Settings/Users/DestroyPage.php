<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Settings\Users;

use App\Mail\DestroyUserMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

class DestroyPage extends Component
{
    public User $user;

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
            'users.destroy',
            now()->addMinutes(5),
            ['user' => $user->id]
        );

        Mail::to($user)->queue(new DestroyUserMail($destroyUserLink));

        $this->dispatch('toast', status: 'success', message: '已寄出信件！');
    }

    #[Title('會員中心 - 刪除帳號')]
    public function render(): View
    {
        return view('livewire.pages.settings.users.destroy-page');
    }
}
