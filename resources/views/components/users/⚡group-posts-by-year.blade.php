<?php

declare(strict_types=1);

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component {
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

        $post->is_private = !$post->is_private;

        $post->withoutTimestamps(fn() => $post->save());

        $this->refreshPostsByYear();

        $this->dispatch('toast', status: 'success', message: $post->is_private ? '文章狀態已切換為私人' : '文章狀態已切換為公開');
    }

    public function restore(int $postId): void
    {
        $post = Post::withTrashed()->find($postId);

        $this->authorize('update', $post);

        $post->withoutTimestamps(fn() => $post->restore());

        $this->refreshPostsByYear();

        $this->dispatch('toast', status: 'success', message: '文章已恢復');
    }

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn() => $post->delete());

        $this->refreshPostsByYear();

        $this->dispatch('refreshUserPosts');

        $this->dispatch('toast', status: 'success', message: '文章已刪除');
    }

    public function refreshPostsByYear(): void
    {
        $this->posts = Post::whereUserId($this->userId)
            ->when(
                auth()->id() === $this->userId,
                function ($query) {
                    return $query->withTrashed();
                },
                function ($query) {
                    return $query->where('is_private', false);
                },
            )
            ->whereYear('created_at', $this->year)
            ->with('category')
            ->latest()
            ->get();
    }
};
?>

<div class="p-2">
  {{-- posts list --}}
  @foreach ($posts as $post)
    <div
      class="group flex justify-between gap-2 rounded-sm px-2 py-2 transition duration-100 hover:bg-zinc-100 lg:gap-0 dark:hover:bg-zinc-700"
      {{-- in this list, these post attribue will be change in the loop, so we have to track them down --}}
      wire:key="{{ $post->id . $post->is_private . $post->deleted_at }}"
    >
      <x-icons.arrow-right class="w-5 dark:text-zinc-400" />

      <div class="ml-2 w-full">
        @if ($post->trashed())
          <span class="text-red-400 line-through">{{ $post->title . ' (已刪除)' }}</span>
        @elseif ($post->is_private)
          <a
            class="duration-200 ease-out hover:text-zinc-900 hover:underline dark:text-zinc-400 dark:hover:text-zinc-50"
            href="{{ $post->link_with_slug }}"
            wire:navigate
          >
            {{ $post->title . ' (未公開)' }}
          </a>
        @else
          <a
            class="duration-200 ease-out hover:text-zinc-900 hover:underline dark:text-zinc-400 dark:hover:text-zinc-50"
            href="{{ $post->link_with_slug }}"
            wire:navigate
          >
            {{ $post->title }}
          </a>
        @endif
      </div>

      @if ($post->user_id === auth()->id())
        <div
          class="ml-2 hidden items-center space-x-4 opacity-0 transition duration-100 group-hover:opacity-100 lg:flex"
        >

          {{-- restore --}}
          @if ($post->trashed())
            <button
              class="cursor-pointer text-zinc-500 duration-200 ease-out hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
              type="button"
              title="還原文章"
              wire:loading.attr="disabled"
              wire:confirm="你確定要還原該文章？"
              wire:click="restore({{ $post->id }})"
            >
              <x-icons.arrow-counterclockwise class="w-5" />
            </button>
          @else
            {{-- private --}}
            @if ($post->is_private)
              <button
                class="cursor-pointer text-zinc-500 duration-200 ease-out hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
                type="button"
                title="公開文章"
                wire:loading.attr="disabled"
                wire:confirm="你確定要將該文章設為公開？"
                wire:click="privateStatusToggle({{ $post->id }})"
              >
                <x-icons.lock class="w-5" />
              </button>
            @else
              <button
                class="cursor-pointer text-zinc-500 duration-200 ease-out hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
                type="button"
                title="關閉文章"
                wire:loading.attr="disabled"
                wire:confirm="你確定要將該文章設為不公開？"
                wire:click="privateStatusToggle({{ $post->id }})"
              >

                <x-icons.unlock class="w-5" />
              </button>
            @endif

            {{-- edit --}}
            <a
              class="text-zinc-500 duration-200 ease-out hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50"
              href="{{ route('posts.edit', ['id' => $post->id]) }}"
              title="編輯文章"
              role="button"
              wire:navigate
            >
              <x-icons.pencil-square class="w-5" />
            </a>

            {{-- destroy --}}
            <button
              class="cursor-pointer text-red-400 duration-200 ease-out hover:text-red-700 dark:hover:text-red-200"
              type="button"
              title="刪除文章"
              wire:loading.attr="disabled"
              wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
              wire:click.stop="destroy({{ $post->id }})"
            >
              <x-icons.x class="w-5" />
            </button>
          @endif

        </div>
      @endif
    </div>
  @endforeach
</div>
