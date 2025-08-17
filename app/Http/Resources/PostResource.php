<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'excerpt'    => $this->excerpt,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'url'        => $this->link_with_slug,
        ];
    }
}
