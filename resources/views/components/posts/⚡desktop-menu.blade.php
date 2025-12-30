<?php

declare(strict_types=1);

use App\Models\Post;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $postId;

    public string $postTitle;

    #[Locked]
    public int $authorId;

    public function destroy(Post $post): void
    {
        $this->authorize('destroy', $post);

        $post->withoutTimestamps(fn () => $post->delete());

        $this->dispatch('toast', status: 'success', message: '成功刪除文章！');

        $this->redirectRoute(
            name: 'users.show',
            parameters: [
                'id'                 => auth()->id(),
                'tab'                => 'posts',
                'current-posts-year' => $post->created_at->format('Y'),
            ],
            // @pest-mutate-ignore
            navigate: true,
        );
    }
};
?>

<div class="flex sticky top-1/2 flex-col space-y-2 -translate-y-1/2">
    {{-- Home --}}
    <x-tooltip
        :tooltip-text="'返回首頁'"
        :tooltip-position="'right'"
    >
        <a
            class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
            href="{{ route('posts.index') }}"
            role="button"
            wire:navigate
        >
            <x-icons.home
                class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12" />
        </a>
    </x-tooltip>

    <!-- Facebook share button -->
    <x-tooltip
        :tooltip-text="'分享至 FB'"
        :click-text="'好耶！'"
        :tooltip-position="'right'"
    >
        <button
            class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
            data-sharer="facebook"
            data-hashtag="{{ config('app.name') }}"
            data-url="{{ request()->fullUrl() }}"
            type="button"
        >
            <x-icons.facebook
                class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12" />
        </button>
    </x-tooltip>

    <!-- x share button -->
    <x-tooltip
        :tooltip-text="'分享至 X'"
        :click-text="'稍等...'"
        :tooltip-position="'right'"
    >
        <button
            class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
            data-sharer="x"
            data-title="{{ $postTitle }}"
            data-hashtags="{{ config('app.name') }}"
            data-url="{{ request()->fullUrl() }}"
            type="button"
        >
            <x-icons.twitter-x
                class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12"
            />
        </button>
    </x-tooltip>

    <!-- Copy link button -->
    <x-tooltip
        :tooltip-text="'複製連結'"
        :click-text="'好囉！'"
        :tooltip-position="'right'"
    >
        <button
            class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
            data-clipboard="{{ urldecode(request()->fullUrl()) }}"
            type="button"
        >
            <x-icons.link-45deg
                class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12"
            />
        </button>
    </x-tooltip>

    {{-- 編輯文章 --}}
    @if (auth()->id() === $authorId)
        <div class="w-14 h-[2px] bg-zinc-300 dark:bg-zinc-600"></div>

        <x-tooltip
            :tooltip-text="'編輯文章'"
            :tooltip-position="'right'"
        >
            <a
                class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
                href="{{ route('posts.edit', ['id' => $postId]) }}"
                role="button"
                wire:navigate
            >
                <x-icons.pencil-square
                    class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12"
                />
            </a>
        </x-tooltip>

        {{-- 刪除 --}}
        <x-tooltip
            :tooltip-text="'刪除文章'"
            :tooltip-position="'right'"
        >
            <button
                class="flex justify-center items-center w-14 h-14 cursor-pointer group text-zinc-500 dark:text-zinc-400"
                type="button"
                title="刪除文章"
                wire:confirm="你確定要刪除文章嗎？（7 天之內可以還原）"
                wire:click="destroy({{ $postId }})"
            >
                <x-icons.trash
                    class="w-6 text-2xl transition duration-150 ease-in group-hover:scale-125 group-hover:rotate-12" />
            </button>
        </x-tooltip>
    @endif
</div>
