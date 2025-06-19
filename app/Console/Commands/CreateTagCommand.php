<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class CreateTagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tag:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new tag';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = text(
            label: 'What should the tag name be?',
            placeholder: 'ex. Laravel',
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
            label: 'Do you want to create a tag with the name "'.$name.'"?',
        );

        if ($confirmed) {
            Tag::create(['name' => $name]);
            $this->info('Tag created successfully');

            return self::SUCCESS;
        }

        $this->error('Tag creation canceled');

        return self::SUCCESS;
    }
}
