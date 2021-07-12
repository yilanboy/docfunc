{{-- 會員文章 --}}
<div class="space-y-6">
    <div>
        {{ $posts->onEachSide(1)->withQueryString()->links() }}
    </div>

    @forelse ($posts as $post)
        <x-card
            x-data=""
            x-on:click="
                const targetTagName = $event.target.tagName.toLowerCase()
                const ignores = ['a', 'button']

                if (!ignores.includes(targetTagName)) {
                    $refs.postLink.click()
                }
            "
            class="flex flex-col md:flex-row justify-between hover:shadow-xl
            transform hover:-translate-x-2 transition duration-150 ease-in cursor-pointer"
        >
            {{-- 文章 --}}
            <div class="w-full flex flex-col justify-between">
                @if ($post->trashed())
                    <span class="text-red-500">此文章已被設定為刪除！</span>
                @endif

                {{-- 文章標題 --}}
                <span class="text-xl font-semibold mt-2 md:mt-0">
                    <a
                        x-ref="postLink"
                        href="@if ($post->trashed())
                            {{ route('posts.showSoftDeleted', [ 'id' => $post->id]) }}
                        @else
                            {{ $post->link_with_slug }}
                        @endif"
                        class="hover:underline"
                    >{{ $post->title }}</a>
                </span>

                {{-- 文章相關資訊 --}}
                <div class="flex items-center text-sm text-gray-400 mt-2 space-x-2">
                    {{-- 文章分類資訊 --}}
                    <div>
                        <a class="hover:text-gray-700"
                        href="{{ $post->category->link_with_name }}" title="{{ $post->category->name }}">
                            <i class="{{ $post->category->icon }}"></i><span class="ml-2">{{ $post->category->name }}</span>
                        </a>
                    </div>
                    <div>&bull;</div>
                    {{-- 文章發布時間 --}}
                    <div>
                        <a
                            href="@if ($post->trashed())
                                {{ route('posts.showSoftDeleted', [ 'id' => $post->id]) }}
                            @else
                                {{ $post->link_with_slug }}
                            @endif"
                            class="hover:text-gray-700"
                            title="文章發布於：{{ $post->created_at }}"
                        >
                            <i class="bi bi-clock-fill"></i><span class="ml-2">{{ $post->created_at->diffForHumans() }}</span>
                        </a>
                    </div>
                    <div>&bull;</div>
                    <div>
                        {{-- 文章留言數 --}}
                        <a class="hover:text-gray-700"
                        href="{{ $post->link_with_slug }}#post-{{ $post->id }}-replies-container">
                            <i class="bi bi-chat-square-text-fill"></i><span class="ml-2">{{ $post->reply_count }}</span>
                        </a>
                    </div>
                </div>
            </div>

            @if ($post->trashed())
                <div class="flex items-center mt-2 md:mt-0">
                    {{-- Restore Post --}}
                    <a
                        x-on:click.stop="return confirm('您確定恢復此文章嗎？');"
                        href="{{ route('posts.restorePost', [ 'id' => $post->id ]) }}"
                        class="flex justify-center items-center h-10 w-10 text-lg text-white font-bold bg-blue-600 hover:bg-blue-800 active:bg-blue-600 rounded-full
                        transform hover:scale-125 transition duration-150 ease-in shadow-md hover:shadow-xl"
                    >
                        <i class="bi bi-arrow-90deg-left"></i>
                    </a>

                    <form id="force-delete-post-{{ $post->id }}" action="{{ route('posts.forceDeletePost', ['id' => $post->id]) }}" method="POST"
                    class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                    {{-- Force Delete Post --}}
                    <button
                        x-on:click.stop="return confirm('您確定要完全刪除此文章嗎？（此動作無法復原）');"
                        type="submit"
                        form="force-delete-post-{{ $post->id }}"
                        class="flex justify-center items-center h-10 w-10 text-lg text-white font-bold bg-purple-600 hover:bg-purple-800 active:bg-purple-600 rounded-full
                        transform hover:scale-125 transition duration-150 ease-in shadow-md hover:shadow-xl ml-2"
                    >
                        <i class="bi bi-exclamation-diamond-fill"></i>
                    </button>
                </div>
            @else
                {{-- Edit Post --}}
                <div class="flex items-center mt-2 md:mt-0">
                    <a
                        href="{{ route('posts.edit', ['post' => $post->id]) }}"
                        class="flex justify-center items-center h-10 w-10 text-lg text-white font-bold bg-green-600 hover:bg-green-800 active:bg-green-600 rounded-full
                        transform hover:scale-125 hover:-rotate-45 transition duration-150 ease-in shadow-md hover:shadow-xl"
                    >
                        <i class="bi bi-pencil"></i>
                    </a>

                    <form id="delete-post-{{ $post->id }}" action="{{ route('posts.destroy', ['post' => $post->id]) }}" method="POST"
                    class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>

                    {{-- Delete Post --}}
                    <button
                        x-on:click.stop="return confirm('您確定要刪除此文章嗎？（時間內還可以恢復）');"
                        type="submit"
                        form="delete-post-{{ $post->id }}"
                        class="flex justify-center items-center h-10 w-10 text-lg text-white font-bold bg-red-600 hover:bg-red-800 active:bg-red-600 rounded-full
                        transform hover:scale-125 transition duration-150 ease-in shadow-md hover:shadow-xl ml-2"
                    >
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            @endif
        </x-card>

    @empty
        <x-card class="w-full h-36 flex justify-center items-center
        transform hover:-translate-x-2 transition duration-150 ease-in hover:shadow-xl">
            <span>目前沒有文章，有沒有什麼事情想要分享呢？</span>
        </x-card>
    @endforelse
</div>