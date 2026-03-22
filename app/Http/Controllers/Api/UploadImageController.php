<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadImageRequest;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Random\RandomException;

use function response;

class UploadImageController extends Controller
{
    public function __construct(
        protected FileService $fileService
    ) {}

    /**
     * upload the image in the post to AWS S3
     *
     * @throws RandomException
     */
    public function __invoke(UploadImageRequest $request): JsonResponse
    {
        $file = $request->file('upload');
        $imageName = $this->fileService->generateFileName($file->getClientOriginalExtension());
        Storage::disk()->put('images/'.$imageName, $file->getContent());
        $url = Storage::disk()->url('images/'.$imageName);

        return response()->json(['url' => $url]);
    }
}
