<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\ContentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearUnusedImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:clear {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear not in use image on S3 storage';

    /**
     * Execute the console command.
     */
    public function handle(ContentService $contentService): int
    {
        $imagesInPosts = [];

        Post::select(['id', 'body', 'preview_url'])
            ->chunkById(200, function ($posts) use (&$imagesInPosts, $contentService) {
                foreach ($posts as $post) {
                    array_push($imagesInPosts, ...$contentService->getImagesInContent($post->body));

                    if (! empty($post->preview_url)) {
                        $imagesInPosts[] = basename($post->preview_url);
                    }
                }
            });

        // add s3 directory prefix to each image
        // format:
        // [
        //     'images/2023_01_01_10_18_21_63b0ed6d06d52.jpg',
        //     'images/2022_12_30_22_39_21_63aef81999216.jpg',
        //     ...
        // ]
        $imagesInPosts = collect($imagesInPosts)->map(function ($item) {
            return 'images/'.$item;
        })->all();

        // find images that do not exist in the posts, but exist on S3
        $notInUseImages = array_diff(Storage::disk()->files('images'), $imagesInPosts);

        if ($notInUseImages === []) {
            $this->info('There is not a single image that has not been used');

            return self::SUCCESS;
        }

        $this->warn('This operation will delete unused images on S3');
        $this->newLine();

        foreach ($notInUseImages as $image) {
            $this->line('- '.$image);
        }
        $this->newLine();

        if ($this->option('force') || $this->confirm('Do you wish to continue?')) {
            Storage::disk()->delete($notInUseImages);

            $this->info('Clear operation finish');

            return self::SUCCESS;
        }

        $this->info('Stop this operation...');

        return self::FAILURE;
    }
}
