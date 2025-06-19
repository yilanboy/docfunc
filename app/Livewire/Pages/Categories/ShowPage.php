<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Categories;

use App\Models\Category;
use Illuminate\View\View;
use Livewire\Component;

class ShowPage extends Component
{
    public Category $category;

    public function mount(int $id): void
    {
        $this->category = Category::findOrFail($id);
    }

    public function render(): View
    {
        // because name is optional, we can't use route parameters
        if (! empty($this->category->name) && $this->category->name !== request()->name) {
            redirect()->to($this->category->link_with_name);
        }

        return view('livewire.pages.categories.show-page')
            ->title($this->category->name);
    }
}
