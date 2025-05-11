<?php

namespace App\Livewire\Pages\Notifications;

use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    #[Title('我的通知')]
    public function render(): View
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        auth()->user()->unreadNotifications->markAsRead();

        return view(
            'livewire.pages.notifications.index-page',
            compact('notifications'),
        );
    }
}
