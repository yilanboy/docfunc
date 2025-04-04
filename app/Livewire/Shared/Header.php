<?php

namespace App\Livewire\Shared;

use App\Livewire\Actions\Logout;
use App\Models\Category;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Header extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->dispatch('info-badge', status: 'success', message: '成功登出！');

        $this->redirect(route('login'), navigate: true);
    }

    public function render()
    {
        $categories = Cache::remember('categories', now()->addDay(), function () {
            return Category::all(['id', 'name', 'icon']);
        });

        $showRegisterButton = SettingService::isRegisterAllowed();

        return view(
            'livewire.shared.header',
            compact('categories', 'showRegisterButton')
        );
    }
}
