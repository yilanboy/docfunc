<?php

namespace Database\Seeders;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()
            ->has(Passkey::factory(3))
            ->count(10)
            ->create();

        // 單獨處理第一個會員的數據
        $user = User::query()->find(1);
        $user->update([
            'name' => 'Allen',
            'email' => 'allen@email.com',
        ]);
    }
}
