<?php

declare(strict_types=1);

use App\Models\Post;
use App\Services\ContentService;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /**
     * @var Post get the post from the url path
     *
     * Why not using implicit model binding?
     *
     * User can delete a post in a side menu, but there is a problem if you use implicit binding.
     * Although the page will redirect after deleting the post,
     * livewire will still try to hydrate the component before jumping.
     * At this time, post model 404 not found errors will occasionally occur.
     */
    public Post $post;

    public function mount(int $id): void
    {
        $this->post = Post::query()->withCount('comments')->findOrFail($id);

        // private post, only the author can see
        if ($this->post->is_private) {
            $this->authorize('update', $this->post)->asNotFound();
        }

        // redirect to url with slug if the url has no slug
        if ($this->post->slug && $this->post->slug !== request()->slug) {
            redirect()->to($this->post->link_with_slug);
        }
    }

    #[Computed]
    public function readTime(): int
    {
        return ContentService::getReadTime($this->post->body);
    }

    public function render()
    {
        return $this->view()->title($this->post->title);
    }
};
?>

@section('description', $post->excerpt)

@if (!empty($post->preview_url))
    @section('preview_url', $post->preview_url)
@endif

@assets
{{-- highlight code block --}}
@vite('resources/ts/shiki.ts')
{{-- code block copy button --}}
@vite('resources/ts/reader-helpers/code-block-helper.ts')
@vite('resources/ts/reader-helpers/image-block-helper.ts')
{{-- post read pregress bar --}}
@vite('resources/ts/progress-bar.ts')
{{-- scroll --}}
@vite('resources/ts/scroll-to-top-btn.ts')
@vite('resources/ts/scroll-to-anchor.ts')
{{-- social media share button --}}
@vite('resources/ts/sharer.ts')
{{-- media embed --}}
@vite('resources/ts/oembed/embed-youtube-oembed.ts')
@vite('resources/ts/oembed/embed-twitter-oembed.ts')
{{-- post content --}}
@vite('resources/ts/post-outline.ts')
@endassets

@script
<script>
    Alpine.data('postsShowPage', () => ({
        async init() {
            setupPostOutline(this.$refs.postOutline, this.$refs.postBody);
            await highlightAllInElement(this.$refs.postBody);
            codeBlockHelper(this.$refs.postBody);
            imageBlockHelper(this.$refs.postBody);
            processYoutubeOembeds();
            processTwitterOembeds(this.$refs.postBody);
            setupProgressBar(this.$refs.postCard, this.$refs.progressBar);
            setupScrollToTopButton(this.$refs.scrollToTopBtn);
            setupSharer();
            scrollToAnchor();
        }
    }));
</script>
@endscript

<x-layouts.main>
    <div
        class="relative grow"
        x-data="postsShowPage"
    >
        <x-posts.progress-bar x-ref="progressBar" />

        <x-posts.scroll-to-top-button x-ref="scrollToTopBtn" />

        <div class="container mx-auto">
            <div class="flex justify-center items-stretch lg:space-x-4 animate-fade-in">
                <div class="hidden xl:block xl:w-1/5">
                    {{-- content menu --}}
                    <div
                        class="flex sticky top-1/2 flex-col -translate-y-1/2"
                        x-ref="postOutline"
                    ></div>
                </div>

                <div class="flex flex-col justify-start items-center px-2 w-full max-w-3xl xl:px-0">
                    {{-- mobile menu --}}
                    @if (auth()->id() === $post->user_id)
                        <livewire:posts.mobile-menu :post-id="$post->id" />
                    @endif

                    <x-card
                        class="w-full"
                        x-ref="postCard"
                    >
                        <article>
                            {{-- post title --}}
                            <h1
                                class="py-6 px-4 -mx-4 -mt-4 text-2xl font-semibold leading-relaxed text-emerald-600 to-transparent rounded-t-xl md:text-4xl bg-linear-to-r from-emerald-100/60 dark:text-lividus-500 dark:from-lividus-800/60"
                            >
                                {{ $post->title }}
                            </h1>

                            {{-- post thumbnail --}}
                            @if (!empty($post->preview_url))
                                <div
                                    class="-mx-4"
                                    id="post-thumbnail"
                                >
                                    <img
                                        class="w-full"
                                        src="{{ $post->preview_url }}"
                                        alt="{{ $post->title }}"
                                    >
                                </div>
                            @endif

                            {{-- post information --}}
                            <div class="flex items-center mt-4 space-x-2 text-base text-neutral-400">
                                {{-- classfication --}}
                                <div class="flex items-center">
                                    <div class="size-4">{!! $post->category->icon !!}</div>

                                    <span class="ml-2">{{ $post->category->name }}</span>
                                </div>

                                <div class="hidden md:block">&bull;</div>

                                {{-- post created time --}}
                                <div class="hidden items-center md:flex">
                                    <x-icons.clock class="w-4" />
                                    <time
                                        class="ml-2"
                                        datetime="{{ $post->created_at->toDateString() }}"
                                    >{{ $post->created_at->toDateString() }}</time>

                                    @if ($post->created_at->toDateString() !== $post->updated_at->toDateString())
                                        <time datetime="{{ $post->updated_at->toDateString() }}">
                                            {{ '(最後更新於 ' . $post->updated_at->toDateString() . ')' }}
                                        </time>
                                    @endif
                                </div>

                                <div class="hidden md:block">&bull;</div>

                                <div class="hidden items-center md:flex">
                                    <x-icons.book-half class="w-4" />

                                    <span class="ml-2">{{ $this->readTime }} 分鐘</span>
                                </div>

                                <div class="hidden md:block">&bull;</div>

                                {{-- comments count --}}
                                <div class="hidden md:flex md:items-center">
                                    <x-icons.chat-square-text class="w-4" />
                                    <span class="ml-2">{{ $post->comments_count }}</span>
                                </div>
                            </div>

                            {{-- post tags --}}
                            @if ($post->tags()->exists())
                                <div class="flex flex-wrap items-center mt-4 text-base">
                                    <x-icons.tags class="mr-1 w-4 text-emerald-200 dark:text-lividus-700" />

                                    @foreach ($post->tags as $tag)
                                        <x-tag :href="route('tags.show', ['id' => $tag->id])">
                                            {{ $tag->name }}
                                        </x-tag>
                                    @endforeach
                                </div>
                            @endif

                            {{-- post body --}}
                            <div
                                class="mt-4 rich-text"
                                x-ref="postBody"
                            >
                                {!! $post->body !!}
                            </div>
                        </article>
                    </x-card>

                    {{-- about author --}}
                    <x-card class="grid grid-cols-12 gap-4 mt-6 w-full">
                        <div class="flex col-span-12 justify-start items-center md:col-span-2 md:justify-center">
                            <img
                                class="w-20 h-20 rounded-full"
                                src="{{ $post->user->gravatar_url }}"
                                alt="{{ $post->user->name }}"
                            >
                        </div>
                        <div class="col-span-12 space-y-2 md:col-span-10">
                            <div class="uppercase text-zinc-400">written by</div>
                            <a
                                class="inline-block text-2xl gradient-underline-grow dark:text-zinc-50"
                                href="{{ route('users.show', ['id' => $post->user->id]) }}"
                                wire:navigate
                            >
                                {{ $post->user->name }}
                            </a>
                            <p class="whitespace-pre-wrap dark:text-zinc-50">{{ $post->user->introduction }}</p>
                        </div>
                    </x-card>

                    <livewire:comments.board
                        :post-id="$post->id"
                        :post-user-id="$post->user_id"
                        :comment-counts="$post->comments_count"
                    />
                </div>

                <div class="hidden xl:block xl:w-1/5">
                    {{-- desktop side menu --}}
                    <livewire:posts.desktop-menu
                        :post-id="$post->id"
                        :post-title="$post->title"
                        :author-id="$post->user_id"
                    />
                </div>
            </div>
        </div>
    </div>
</x-layouts.main>
