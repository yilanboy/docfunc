<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\UploadImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Image\Image;
use Random\RandomException;

use function response;

class UploadImageController extends Controller
{
    /**
     * upload the image in the post to AWS S3
     *
     * @throws RandomException
     */
    public function __invoke(UploadImageRequest $request, UploadImageAction $uploadImage): JsonResponse
    {
        $image = $request->image('upload');

        if (! $image instanceof Image) {
            return response()->json([
                'error' => ['message' => 'Please select an image to upload.'],
            ], 413);
        }

        $url = $uploadImage->handle($image);

        return response()->json(['url' => $url]);
    }
}
