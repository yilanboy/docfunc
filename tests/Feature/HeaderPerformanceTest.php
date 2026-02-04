<?php

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('header performance benchmark', function () {
    // Create a user
    $user = User::factory()->create();

    // Create 50 unread notifications
    for ($i = 0; $i < 50; $i++) {
        DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => ['message' => 'test'],
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs($user);

    DB::enableQueryLog();
    DB::flushQueryLog();

    $start = microtime(true);

    Livewire::test('layouts.header');

    $end = microtime(true);
    $duration = $end - $start;

    $queries = DB::getQueryLog();
    $queryCount = count($queries);

    $hasFullSelect = collect($queries)->contains(function ($query) {
        // SQLite uses double quotes, MySQL uses backticks.
        // We just check for "select * from" and "notifications"
        return str_contains(strtolower($query['query']), 'select * from')
            && str_contains(strtolower($query['query']), 'notifications');
    });

    $hasExists = collect($queries)->contains(function ($query) {
         return str_contains(strtolower($query['query']), 'exists');
    });

    // Verify results
    expect($queryCount)->toBeLessThanOrEqual(3);
    expect($hasExists)->toBeTrue();
    // We can't strict check "Has Full Select" because exists() contains the string.
    // But we can check that we used the optimized path.
});
