<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Post;
use App\Services\ContentService;
use App\Services\FormatTransferService;
use Illuminate\Support\Facades\DB;

final readonly class UpdatePostAction
{
    public function __construct(
        private FormatTransferService $formatTransferService,
    ) {}

    /**
     * @param  array{category_id: int, cover_image_url: ?string, is_private: bool, title: string, tags: string, body: string}  $data
     */
    public function handle(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data): Post {
            $body = ContentService::getPurifiedBody($data['body']);
            $slug = ContentService::getSlug($data['title']);
            $excerpt = ContentService::getExcerpt($body);

            $post->update([
                'category_id'     => $data['category_id'],
                'cover_image_url' => $data['cover_image_url'],
                'is_private'      => $data['is_private'],
                'title'           => $data['title'],
                'body'            => $body,
                'slug'            => $slug,
                'excerpt'         => $excerpt,
            ]);

            $post->tags()->sync(
                $this->formatTransferService->tagsJsonToTagIdsArray($data['tags'])
            );

            return $post;
        });
    }
}
