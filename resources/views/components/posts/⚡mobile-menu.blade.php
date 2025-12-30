<?php

declare(strict_types=1);

use App\Models\Post;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $postId;

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn () => $post->delete());

        $this->dispatch('toast', status: 'success', message: '成功刪除文章！');

        $this->redirect(
            route('users.show', [
                'id'                 => auth()->id(),
                'tab'                => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ]),
        );
    }
};
?>

<div
    class="inline-flex gap-0.5 justify-end items-center mb-6 w-full text-sm rounded-md xl:hidden isolate text-zinc-400">
    <a
        class="inline-flex relative items-center py-2 px-4 rounded-l-xl focus:z-10 bg-zinc-50 text-zinc-400 dark:bg-zinc-800 dark:hover:bg-zinc-700 hover:bg-zinc-100"
        href="{{ route('posts.edit', ['id' => $postId]) }}"
    >
        <x-icons.pencil class="w-4" />
        <span class="ml-2">編輯</span>
    </a>

    <button
        class="inline-flex relative items-center py-2 px-4 -ml-px rounded-r-xl cursor-pointer focus:z-10 bg-zinc-50 text-zinc-400 dark:bg-zinc-800 dark:hover:bg-zinc-700 hover:bg-zinc-100"
        type="button"
        wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
        wire:click="destroy({{ $postId }})"
    >
        <x-icons.trash class="w-4" />
        <span class="ml-2">刪除</span>
    </button>
</div>
