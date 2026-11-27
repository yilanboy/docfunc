<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;

final readonly class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function handle(array $data): User
    {
        $user = DB::transaction(fn (): User => User::create([
            'name'     => trim($data['name']),
            'email'    => $data['email'],
            'password' => $data['password'],
        ]));

        event(new Registered($user));

        return $user;
    }
}
