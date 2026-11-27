<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\FileService;
use Illuminate\Image\Image;
use Illuminate\Support\Facades\Storage;
use Random\RandomException;

final readonly class UploadImageAction
{
    public function __construct(
        private FileService $fileService,
    ) {}

    /**
     * @throws RandomException
     */
    public function handle(Image $image, string $directory = 'images'): string
    {
        $webpImage = $image->toWebp();
        $name = $this->fileService->generateFileName();
        $fileName = "$name.{$webpImage->extension()}";
        $directory = trim($directory, '/');

        $webpImage->storeAs(
            path: $directory,
            name: $fileName,
            disk: config('filesystems.default'),
        );

        $path = $directory !== '' ? $directory.'/'.$fileName : $fileName;

        return Storage::disk()->url($path);
    }
}
