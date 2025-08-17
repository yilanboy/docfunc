<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;

class ShowLatestPostController extends Controller
{
    public function __invoke()
    {
        $posts = Post::latest()->take(5)->get();

        return PostResource::collection($posts);
    }
}
