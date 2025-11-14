<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;
use function Laravel\Prompts\text;

class EditTagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tag:edit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Edit a tag';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = search(
            label: 'Search for the tag that should be edited',
            options: fn (string $value) => $value !== ''
                ? Tag::where('name', 'like', "%{$value}%")->pluck('name', 'id')->all()
                : []
        );

        $tag = Tag::find($id, ['id', 'name']);

        $name = text(
            label: 'What should the tag name be?',
            placeholder: $tag?->name,
            default: $tag?->name,
            required: 'Tag name is required',
            validate: fn (string $value) => match (true) {
                strlen($value) < 3 => 'The name must be at least 3 characters.',
                strlen($value) > 50 => 'The name must not exceed 50 characters.',
                default => null
            }
        );

        $name = Str::of($name)->ucfirst()->trim();

        if (Tag::where('name', $name)->exists()) {
            $this->error('Tag "'.$name.'" already exists');

            return self::FAILURE;
        }

        $confirmed = confirm(
            label: 'Do you want to change the tag "'.$tag->name.'" to "'.$name.'"?',
        );

        if ($confirmed) {
            $tag->update(['name' => $name]);

            $this->info('Tag name change successfully');

            return self::SUCCESS;
        }

        $this->error('Canceled editing tag');

        return self::SUCCESS;
    }
}
