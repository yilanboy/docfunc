<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Post;
use App\Services\ContentService;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

final readonly class ClearUnusedImagesAction
{
    public function __construct(
        private ContentService $contentService,
    ) {}

    /**
     * @param  (Closure(list<string>): bool)|null  $shouldDelete
     * @return array{unused: list<string>, deleted: list<string>}
     */
    public function handle(?Closure $shouldDelete = null): array
    {
        $imagesInPosts = [];

        Post::select(['id', 'body', 'cover_image_url'])
            ->chunkById(200, function (Collection $posts) use (&$imagesInPosts): void {
                foreach ($posts as $post) {
                    array_push($imagesInPosts, ...$this->contentService->getImagesInContent($post->body));

                    if (! empty($post->cover_image_url)) {
                        $imagesInPosts[] = basename($post->cover_image_url);
                    }
                }
            });

        $usedImages = array_map(
            static fn (string $image): string => 'images/'.$image,
            array_unique($imagesInPosts),
        );

        /** @var list<string> $unusedImages */
        $unusedImages = array_values(array_diff(Storage::disk()->files('images'), $usedImages));

        if ($unusedImages === []) {
            return ['unused' => [], 'deleted' => []];
        }

        if ($shouldDelete === null || $shouldDelete($unusedImages)) {
            Storage::disk()->delete($unusedImages);

            return ['unused' => $unusedImages, 'deleted' => $unusedImages];
        }

        return ['unused' => $unusedImages, 'deleted' => []];
    }
}
