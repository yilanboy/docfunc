<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Tags;

use App\Models\Tag;
use Illuminate\View\View;
use Livewire\Component;

class ShowPage extends Component
{
    public Tag $tag;

    public function mount(int $id): void
    {
        $this->tag = Tag::findOrFail($id);
    }

    public function render(): View
    {
        return view('livewire.pages.tags.show-page')
            ->title($this->tag->name);
    }
}
