<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Posts;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Component;

class ShowPage extends Component
{
    /**
     * @var Post get the post id from url path
     *
     * Why not using implicit model binding?
     *
     * User can delete post in side menu, but there is a problem if you use implicit binding.
     * Although the page will redirect after deleting the post,
     * but livewire will still try to hydrate the component before jumping.
     * At this time, post model 404 not found errors will occasionally occur.
     */
    public Post $post;

    public function mount(int $id): void
    {
        $this->post = Post::query()
            ->withCount('comments')
            ->findOrFail($id);

        // private post, only the author can see
        if ($this->post->is_private) {
            $this->authorize('update', $this->post)->asNotFound();
        }

        // redirect to url with slug if the url has no slug
        if ($this->post->slug && $this->post->slug !== request()->slug) {
            redirect()->to($this->post->link_with_slug);
        }
    }

    public function render(): View
    {
        return view('livewire.pages.posts.show-page')
            ->title($this->post->title);
    }
}
