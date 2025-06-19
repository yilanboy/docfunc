<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Posts;

use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Livewire\Component;

class IndexPage extends Component
{
    public function render(): View
    {
        $title = (Route::currentRouteName() === 'root')
            ? config('app.name')
            : '所有文章';

        return view('livewire.pages.posts.index-page')->title($title);
    }
}
