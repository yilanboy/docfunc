<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Traits\MarkdownConverter;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use MarkdownConverter;
    use WithPagination;

    #[Locked]
    public int $userId;

    public function render()
    {
        // get the comments from this user
        $comments = Comment::whereUserId($this->userId)
            ->select(['id', 'created_at', 'post_id', 'body'])
            ->whereHas('post', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with('post:id,title,slug')
            ->latest()
            ->paginate(10, ['*'], 'comments-page')
            ->withQueryString();

        // convert the body from Markdown to HTML
        $comments->getCollection()->transform(function ($comment) {
            $comment->body = $this->convertToHtml($comment->body);

            return $comment;
        });

        return $this->view([
            'comments' => $comments,
        ]);
    }
};
?>

@script
  <script>
    Alpine.data('usersCommentsPart', () => ({
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
  x-data="usersCommentsPart"
  x-ref="userComments"
>
  @forelse ($comments as $comment)
    <x-dashed-card
      class="group relative"
      wire:key="comment-{{ $comment->id }}"
    >
      <a
        class="absolute right-0 top-0 z-10 block h-full w-full bg-transparent"
        href="{{ route('comments.show', ['id' => $comment->id]) }}"
        wire:navigate
      ></a>

      <div class="mask-b-from-50% max-h-64 overflow-hidden">
        <span class="group-gradient-underline-grow text-xl dark:text-zinc-50">
          {{ $comment->post->title }}
        </span>

        {{-- 留言 --}}
        <div class="rich-text">
          {!! $comment->body !!}
        </div>
      </div>

      <div
        class="absolute bottom-3 right-3 flex items-center rounded-lg bg-zinc-200/60 px-2 py-1 text-sm text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-50"
      >
        <x-icons.clock class="w-4" />
        <time
          class="ml-2"
          datetime="{{ $comment->created_at->toDateString() }}"
        >{{ $comment->created_at->diffForHumans() }}</time>
      </div>
    </x-dashed-card>
  @empty
    <x-card class="flex h-32 items-center justify-center text-zinc-400 dark:text-zinc-600">
      <x-icons.exclamation-circle class="w-6" />
      <span class="ml-2">找篇文章留言吧！</span>
    </x-card>
  @endforelse

  {{ $comments->onEachSide(1)->links() }}
</div>
