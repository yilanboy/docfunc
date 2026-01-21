<?php

declare(strict_types=1);

use App\Enums\PostOrderOptions;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public ?int $categoryId = null;

    public ?int $tagId = null;

    public string $badge = '全部文章';

    #[Url]
    public string $order = PostOrderOptions::LATEST->value;

    public function changeOrder(PostOrderOptions $newOrder): void
    {
        $this->order = $newOrder->value;

        $this->resetPage();
    }

    public function render(): View
    {
        $posts = Post::query()
            ->select(['id', 'category_id', 'user_id', 'title', 'excerpt', 'slug', 'created_at'])
            ->withCount('tags') // 計算標籤數目
            ->when($this->categoryId, function ($query) {
                return $query->where('category_id', $this->categoryId);
            })
            ->when($this->tagId, function ($query) {
                return $query->whereHas('tags', function ($query) {
                    $query->where('tag_id', $this->tagId);
                });
            })
            ->where('is_private', false)
            ->withOrder($this->order)
            ->with(['user:id,name', 'category:id,icon,name', 'tags:id,name']) // 預加載防止 N+1 問題
            ->paginate(10)
            ->withQueryString();

        return $this->view([
            'posts' => $posts,
        ]);
    }
};
?>

@script
<script>
    Alpine.data('postsListPart', () => ({
        order: '',
        tabButtonClicked(tabButton) {
            this.tabRepositionMarker(tabButton);
            this.order = tabButton.id.replace('-tab-button', '');

            this.$wire.changeOrder(this.order);
        },
        tabRepositionMarker(tabButton) {
            this.$refs.tabMarker.style.width = tabButton.offsetWidth + 'px';
            this.$refs.tabMarker.style.height = tabButton.offsetHeight + 'px';
            this.$refs.tabMarker.style.left = tabButton.offsetLeft + 'px';
        },
        init() {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);

            this.order = urlParams.get('order') ?? 'latest';

            const tabSelectedButtons = document.getElementById(this.order + '-tab-button');
            this.tabRepositionMarker(tabSelectedButtons);
        }
    }));
</script>
@endscript

<div
    class="space-y-6"
    x-data="postsListPart"
>
    {{-- Sort --}}
    <div class="flex w-full text-sm md:flex-row md:justify-between">
        <x-tabs.nav
            class="md:w-fit"
            wire:ignore
        >
            @foreach (PostOrderOptions::cases() as $postOrder)
                <x-tabs.button
                    id="{{ $postOrder->value . '-tab-button' }}"
                    x-on:click="tabButtonClicked($el)"
                    wire:key="{{ $postOrder->value }}-tab-button"
                >
                    <x-dynamic-component
                        class="w-3"
                        :component="$postOrder->iconComponentName()"
                        wire:loading.remove
                        wire:target="changeOrder('{{ $postOrder->value }}')"
                    />

                    <x-icons.animate-spin
                        class="w-3"
                        wire:loading
                        wire:target="changeOrder('{{ $postOrder->value }}')"
                    />
                    <span>{{ $postOrder->label() }}</span>
                </x-tabs.button>
            @endforeach

            <x-tabs.tab-marker
                x-ref="tabMarker"
                x-cloak
            />
        </x-tabs.nav>

        {{-- Class badge --}}
        <div
            class="hidden justify-center items-center py-1.5 px-3 rounded-xl md:flex bg-zinc-50 dark:bg-zinc-800 dark:text-zinc-50"
        >{{ $badge }}</div>
    </div>

    {{-- Post List --}}
    @forelse($posts as $post)
        <x-card class="grid overflow-hidden relative grid-cols-1 gap-4 cursor-pointer group isolate">
            {{-- Category icon in background --}}
            <div
                class="absolute -right-4 -bottom-16 z-0 transition-all duration-300 rotate-12 group-hover:-right-0 group-hover:-bottom-4 size-56 text-zinc-200/60 dark:text-zinc-700/60"
            >
                {!! $post->category->icon !!}
            </div>

            {{-- Post card link --}}
            <a
                class="absolute inset-0 z-20"
                href="{{ $post->link_with_slug }}"
                title="{{ $post->title }}"
                wire:navigate
            ></a>

            {{-- Title --}}
            <div class="z-10">
                <h1 class="inline text-xl font-semibold group-gradient-underline-grow dark:text-zinc-50">
                    {{ $post->title }}
                </h1>
            </div>

            {{-- Excerpt --}}
            <div class="z-10 text-base leading-relaxed text-zinc-500">
                {{ $post->excerpt }}
            </div>

            {{-- Tags --}}
            @if ($post->tags_count > 0)
                <div class="flex z-20 flex-wrap items-center text-base w-fit">
                    <x-icons.tags class="mr-1 w-4 text-emerald-200 dark:text-lividus-700" />

                    @foreach ($post->tags as $tag)
                        <x-tag :href="route('tags.show', ['id' => $tag->id])">
                            {{ $tag->name }}
                        </x-tag>
                    @endforeach
                </div>
            @endif

            <div class="hidden z-10 space-x-2 text-base md:flex md:items-center text-neutral-500">
                {{-- Category --}}
                <div class="flex items-center">
                    <div class="w-4">{!! $post->category->icon !!}</div>

                    <span class="ml-2">{{ $post->category->name }}</span>
                </div>

                <div>&bull;</div>

                {{-- Author --}}
                <div class="flex items-center">
                    <x-icons.person class="w-4" />
                    <span class="ml-2">{{ $post->user->name }}</span>
                </div>

                <div>&bull;</div>

                {{-- Published time --}}
                <div class="flex items-center">
                    <x-icons.clock class="w-4" />
                    <time
                        class="ml-2"
                        datetime="{{ $post->created_at->toDateString() }}"
                    >{{ $post->created_at->diffForHumans() }}</time>
                </div>

                <div>&bull;</div>

                {{-- Comments --}}
                <div class="flex items-center">
                    <x-icons.chat-square-text class="w-4" />
                    <span class="ml-2">{{ $post->comments_count }}</span>
                </div>
            </div>
        </x-card>

    @empty
        <x-card
            class="flex justify-center items-center w-full h-36 transition duration-150 ease-in hover:-translate-x-2 dark:text-zinc-50"
        >
            <span>Whoops！此分類底下還沒有文章，趕緊寫一篇吧！</span>
        </x-card>
    @endforelse

    {{ $posts->onEachSide(1)->links() }}
</div>
