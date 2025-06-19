<?php

declare(strict_types=1);

namespace App\Livewire\Shared\Users;

use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * @property-read array<int, array<int, Post>> $groupPostsByYear
 */
class PostsPart extends Component
{
    public int $userId;

    // This will be set from the url parameter
    #[Url(as: 'current-posts-year')]
    public string $currentPostsYear = '';

    /**
     * Get the post-list of the user. This list will be grouped by year.
     * The first year will be the latest year
     * format: [2021 => [Post, Post, ...], 2020 => [Post, Post, ...], ...]
     *
     * @return array<int, non-empty-list<Post>> $postsGroupByYear
     */
    #[Computed]
    public function groupPostsByYear(): array
    {
        $posts = Post::whereUserId($this->userId)
            ->when(auth()->id() === $this->userId, function ($query) {
                return $query->withTrashed();
            }, function ($query) {
                return $query->where('is_private', false);
            })
            ->with('category')
            ->latest()
            ->get();

        $postsGroupByYear = [];

        foreach ($posts as $post) {
            $year = $post->created_at->format('Y');

            if (! isset($postsGroupByYear[$year])) {
                // php array will convert the numeric string key to int
                $postsGroupByYear[$year] = [];
            }

            $postsGroupByYear[$year][] = $post;
        }

        return $postsGroupByYear;
    }

    public function mount(): void
    {
        if (! array_key_exists($this->currentPostsYear, $this->groupPostsByYear)) {
            $this->currentPostsYear = (string) array_key_first($this->groupPostsByYear) ?? '';
        }
    }

    public function render(): View
    {
        return view('livewire.shared.users.posts-part');
    }
}
