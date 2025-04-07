@script
  <script>
    Alpine.data('userComments', () => ({
      observers: [],
      init() {
        hljs.highlightAll();

        let userCommentsObserver = new MutationObserver(() => {
          this.$refs.userComments
            .querySelectorAll('pre code:not(.hljs)')
            .forEach((element) => {
              hljs.highlightElement(element);
            });
        });

        userCommentsObserver.observe(this.$refs.userComments, {
          childList: true,
          subtree: true,
          attributes: true,
          characterData: false
        });

        this.observers.push(userCommentsObserver);
      },
      destroy() {
        this.observers.forEach((observer) => {
          observer.disconnect();
        });
      }
    }));
  </script>
@endscript

{{-- 會員留言 --}}
<div
  class="w-full space-y-6"
  x-data="userComments"
  x-ref="userComments"
>
  @forelse ($comments as $comment)
    <x-dashed-card
      class="group relative"
      wire:key="comment-{{ $comment->id }}"
    >
      <div class="mask-b-from-50% max-h-64 overflow-hidden">
        <a
          class="absolute right-0 top-0 z-20 block h-full w-full bg-transparent"
          href="{{ $comment->post->link_with_slug }}#comments"
          wire:navigate
        ></a>

        <span class="group-gradient-underline-grow text-xl dark:text-gray-50">
          {{ $comment->post->title }}
        </span>

        {{-- 留言 --}}
        <div class="rich-text">
          {!! $comment->body !!}
        </div>
      </div>

      <div
        class="dark:bg-lividus-600 absolute bottom-3 right-3 z-10 flex items-center rounded-lg bg-emerald-500 px-2 py-1 text-sm text-gray-50"
      >
        <x-icon.clock class="w-4" />
        <time
          class="ml-2"
          datetime="{{ $comment->created_at->toDateString() }}"
        >{{ $comment->created_at->diffForHumans() }}</time>
      </div>
    </x-dashed-card>
  @empty
    <x-card class="flex h-32 items-center justify-center text-gray-400 dark:text-gray-600">
      <x-icon.exclamation-circle class="w-6" />
      <span class="ml-2">找篇文章留言吧！</span>
    </x-card>
  @endforelse

  {{ $comments->onEachSide(1)->links() }}
</div>
