<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Link;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\search;

class DeleteLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a link';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = search(
            label: 'Search for the tag that should be edited',
            options: fn (string $value) => $value !== ''
                ? Link::where('title', 'like', "%{$value}%")
                    ->orWhere('link', 'like', "%{$value}%")
                    ->pluck('title', 'id')->all()
                : []
        );

        $link = Link::find($id, ['id', 'title', 'link']);

        $confirmed = confirm(
            label: 'Do you want to delete the link "'.$link->title.'"?',
        );

        if ($confirmed) {
            Link::destroy($id);

            $this->info('Link deleted successfully');

            return self::SUCCESS;
        }

        $this->error('Canceled deleting link');

        return self::SUCCESS;
    }
}
