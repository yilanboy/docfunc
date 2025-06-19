<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Posts;

use App\Livewire\Forms\PostForm;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditPage extends Component
{
    use WithFileUploads;

    public PostForm $form;

    public Collection $categories;

    public Post $post;

    public function mount(int $id): void
    {
        $this->post = Post::findOrFail($id);

        $this->authorize('update', $this->post);

        $this->categories = Category::all(['id', 'name']);

        $this->form->user_id = auth()->id();

        $this->form->setPost($this->post);
    }

    public function save(Post $post): void
    {
        $this->form->update($post);

        $this->dispatch('toast', status: 'success', message: '成功更新文章！');

        $this->redirect($post->link_with_slug, navigate: true);
    }

    #[Title('編輯文章')]
    public function render(): View
    {
        return view('livewire.pages.posts.edit-page');
    }
}
