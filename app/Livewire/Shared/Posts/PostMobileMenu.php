<?php

namespace App\Livewire\Shared\Posts;

use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PostMobileMenu extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public int $postId;

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn () => $post->delete());

        $this->dispatch('info-badge', status: 'success', message: '成功刪除文章！');

        $this->redirect(
            route('users.show', [
                'id' => auth()->id(),
                'tab' => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ])
        );
    }

    public function render(): View
    {
        return view('livewire.shared.posts.post-mobile-menu');
    }
}
