<?php

namespace App\Livewire\Shared;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

class SearchPart extends Component
{
    public string $search = '';

    public function render(): View
    {
        $results = collect();

        if (strlen($this->search) >= 2) {
            $results = Post::search($this->search)->take(10)->get();
        }

        return view('livewire.shared.search-part', ['results' => $results]);
    }
}
