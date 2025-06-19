<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class CreateLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new link';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $title = text(
            label: 'What is the display title of a link?',
            placeholder: 'ex. Laravel',
            required: 'link name is required',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            }
        );

        $title = Str::of($title)->trim();

        $link = text(
            label: 'What is the link url?',
            placeholder: 'ex. https://laravel.com',
            required: 'link url is required',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The url must be at least 3 characters.',
                strlen($value) > 2048 => 'The url must not exceed 2048 characters.',
                ! Str::startsWith($value, 'https://') => 'The url must start with "https://"',
                default => null
            }
        );

        $link = Str::of($link)->trim();

        $this->info('Link title: '.$title);
        $this->info('Link url  : '.$link);

        $confirmed = confirm(
            label: 'Do you want to create this link?',
        );

        if ($confirmed) {
            Link::create([
                'title' => $title,
                'link' => $link,
            ]);

            $this->info('Link created successfully');

            return self::SUCCESS;
        }

        $this->error('Link creation canceled');

        return self::SUCCESS;
    }
}
