<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShowLatestPostController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $posts = Post::latest()->take(6)->get();

        return PostResource::collection($posts);
    }
}
