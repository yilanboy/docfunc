<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class ShowAllTagsController extends Controller
{
    public function __invoke()
    {
        return Cache::remember(
            'inputTags',
            now()->addDay(),
            fn () => TagResource::collection(Tag::all())->resolve()
        );
    }
}
