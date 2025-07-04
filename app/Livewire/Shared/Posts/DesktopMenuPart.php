<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Posts;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DesktopMenuPart extends Component
{
    #[Locked]
    public $postId;

    public $postTitle;

    #[Locked]
    public $authorId;

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn () => $post->delete());

        $this->dispatch('toast', status: 'success', message: '成功刪除文章！');

        $this->redirectRoute(
            name: 'users.show',
            parameters: [
                'id' => auth()->id(),
                'tab' => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ],
            // @pest-mutate-ignore
            navigate: true,
        );
    }

    public function render(): View
    {
        return view('livewire.shared.posts.desktop-menu-part');
    }
}
