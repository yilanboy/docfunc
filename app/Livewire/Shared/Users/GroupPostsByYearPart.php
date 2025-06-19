<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Users;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class GroupPostsByYearPart extends Component
{
    public int $userId;

    /**
     * @var array<Collection>|Collection
     */
    public array|Collection $posts;

    public string $year;

    public function privateStatusToggle(int $postId): void
    {
        $post = Post::withTrashed()->find($postId);

        $this->authorize('update', $post);

        $post->is_private = ! $post->is_private;

        $post->withoutTimestamps(fn () => $post->save());

        $this->refreshPostsByYear();

        $this->dispatch(
            'toast',
            status: 'success',
            message: $post->is_private ? '文章狀態已切換為私人' : '文章狀態已切換為公開',
        );
    }

    public function restore(int $postId): void
    {
        $post = Post::withTrashed()->find($postId);

        $this->authorize('update', $post);

        $post->withoutTimestamps(fn () => $post->restore());

        $this->refreshPostsByYear();

        $this->dispatch('toast', status: 'success', message: '文章已恢復');
    }

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn () => $post->delete());

        $this->refreshPostsByYear();

        $this->dispatch('refreshUserPosts');

        $this->dispatch('toast', status: 'success', message: '文章已刪除');
    }

    public function refreshPostsByYear(): void
    {
        $this->posts = Post::whereUserId($this->userId)
            ->when(auth()->id() === $this->userId, function ($query) {
                return $query->withTrashed();
            }, function ($query) {
                return $query->where('is_private', false);
            })
            ->whereYear('created_at', $this->year)
            ->with('category')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.shared.users.group-posts-by-year-part');
    }
}
