<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

class EditLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:edit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit a link';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = search(
            label: 'Search for the tag that should be edited',
            options: fn (string $value) => $value !== ''
                ? Link::where('title', 'like', "%{$value}%")
                    ->orWhere('url', 'like', "%{$value}%")
                    ->pluck('title', 'id')->all()
                : []
        );

        $link = Link::find($id, ['id', 'title', 'url']);

        $title = text(
            label: 'What is the link display title?',
            placeholder: $link?->title,
            default: $link?->title,
            required: 'link title is required',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 255 => 'The name must not exceed 255 characters.',
                default => null
            }
        );

        $title = Str::of($title)->trim();

        $url = text(
            label: 'What is the link url?',
            placeholder: $link?->url,
            default: $link?->url,
            required: 'link url is required',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The url must be at least 3 characters.',
                strlen($value) > 2048 => 'The url must not exceed 2048 characters.',
                ! Str::startsWith($value, 'https://') => 'The url must start with "https://"',
                default => null
            }
        );

        $url = Str::of($url)->trim();

        $this->warn('Origin title: '.$link->title);
        $this->warn('Origin url  : '.$link->url);
        $this->info('New title: '.$title);
        $this->info('New url  : '.$url);

        if (confirm('Do you want to save this link?')) {
            $link->title = $title;
            $link->url = $url;
            $link->save();

            $this->info('Link updated successfully');
        } else {
            $this->info('Link not updated');
        }
    }
}
