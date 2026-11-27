<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ClearUnusedImagesAction;
use Illuminate\Console\Command;

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
    public function handle(ClearUnusedImagesAction $clearUnusedImages): int
    {
        $result = $clearUnusedImages->handle(function (array $unusedImages): bool {
            $this->warn('This operation will delete unused images on S3');
            $this->newLine();

            foreach ($unusedImages as $image) {
                $this->line('- '.$image);
            }
            $this->newLine();

            return (bool) $this->option('force') || $this->confirm('Do you wish to continue?');
        });

        if ($result['unused'] === []) {
            $this->info('There is not a single image that has not been used');

            return self::SUCCESS;
        }

        if ($result['deleted'] !== []) {
            $this->info('Clear operation finish');

            return self::SUCCESS;
        }

        $this->info('Stop this operation...');

        return self::FAILURE;
    }
}
