<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;

class DeleteTagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tag:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a tag';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = search(
            label: 'Search for the tag that should be deleted',
            options: fn (string $value) => $value !== ''
                ? Tag::where('name', 'like', "%{$value}%")->pluck('name', 'id')->all()
                : []
        );

        $tag = Tag::find($id, ['id', 'name']);

        $confirmed = confirm(
            label: 'Do you want to delete the tag "'.$tag->name.'"?',
        );

        if ($confirmed) {
            Tag::destroy($id);

            $this->info('Tag deleted successfully');

            return self::SUCCESS;
        }

        $this->error('Canceled deleting tag');

        return self::SUCCESS;
    }
}
