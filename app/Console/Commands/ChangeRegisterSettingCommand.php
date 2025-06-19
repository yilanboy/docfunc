<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;

class ChangeRegisterSettingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setting:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the register setting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = select(
            'Allow guests to register?',
            ['Yes', 'No'],
        );

        if ($role == 'Yes') {
            Setting::where('key', 'allow_register')->update(['value' => 'true']);
            $this->info('Guests can register');
        } else {
            Setting::where('key', 'allow_register')->update(['value' => 'false']);
            $this->info('Guests cannot register');
        }
    }
}
