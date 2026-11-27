<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Post;
use App\Services\ContentService;
use App\Services\FormatTransferService;
use Illuminate\Support\Facades\DB;

final readonly class CreatePostAction
{
    public function __construct(
        private FormatTransferService $formatTransferService,
    ) {}

    /**
     * @param  array{user_id: int, category_id: int, cover_image_url: ?string, is_private: bool, title: string, tags: string, body: string}  $data
     */
    public function handle(array $data): Post
    {
        return DB::transaction(function () use ($data): Post {
            $body = ContentService::getPurifiedBody($data['body']);
            $slug = ContentService::getSlug($data['title']);
            $excerpt = ContentService::getExcerpt($body);

            $post = Post::create([
                'user_id'         => $data['user_id'],
                'category_id'     => $data['category_id'],
                'cover_image_url' => $data['cover_image_url'],
                'is_private'      => $data['is_private'],
                'title'           => $data['title'],
                'body'            => $body,
                'slug'            => $slug,
                'excerpt'         => $excerpt,
            ]);

            $post->tags()->attach(
                $this->formatTransferService->tagsJsonToTagIdsArray($data['tags'])
            );

            return $post;
        });
    }
}
